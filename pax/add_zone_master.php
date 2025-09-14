<?php

/*  Poweradmin, a friendly web-based admin tool for PowerDNS.
 *  See <https://www.poweradmin.org> for more details.
 *
 *  Copyright 2007-2010 Rejo Zenger <rejo@zenger.nl>
 *  Copyright 2010-2023 Poweradmin Development Team
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * Script that handles requests to add new master zones
 *
 * @package     Poweradmin
 * @copyright   2007-2010 Rejo Zenger <rejo@zenger.nl>
 * @copyright   2010-2023 Poweradmin Development Team
 * @license     https://opensource.org/licenses/GPL-3.0 GPL
 */

use Poweradmin\Application\Dnssec\DnssecProviderFactory;
use Poweradmin\BaseController;
use Poweradmin\Dns;
use Poweradmin\DnsRecord;
use Poweradmin\LegacyLogger;
use Poweradmin\LegacyUsers;
use Poweradmin\ZoneTemplate;

require_once __DIR__ . '/vendor/autoload.php';

class AddZoneMasterController extends BaseController
{

    private LegacyLogger $logger;

    public function __construct()
    {
        parent::__construct();

        $this->logger = new LegacyLogger($this->db);
    }

    public function run(): void
    {
        $this->checkPermission('zone_master_add', _("You do not have the permission to add a master zone."));

        if ($this->isPost()) {
            $this->addZone();
        } else {
            $this->showForm();
        }
    }

    private function addZone(): void
    {
        $v = new Valitron\Validator($_POST);
        $v->rules([
            'required' => ['domain', 'dom_type', 'owner', 'zone_template'],
        ]);
        if (!$v->validate()) {
            $this->showFirstError($v->errors());
        }

        $pdnssec_use = $this->config('pdnssec_use');
        $dns_third_level_check = $this->config('dns_third_level_check');

        $zone_name = idn_to_ascii(trim($_POST['domain']), IDNA_NONTRANSITIONAL_TO_ASCII);
        $dom_type = $_POST["dom_type"];
        $owner = $_POST['owner'];
        $zone_template = $_POST['zone_template'] ?? "none";

        $dnsRecord = new DnsRecord($this->db, $this->getConfig());
        $dns = new Dns($this->db, $this->getConfig());
        if (!$dns->is_valid_hostname_fqdn($zone_name, 0)) {
            $this->setMessage('add_zone_master', 'error', _('Invalid hostname.'));
            $this->showForm();
        } elseif ($dns_third_level_check && DnsRecord::get_domain_level($zone_name) > 2 && $dnsRecord->domain_exists(DnsRecord::get_second_level_domain($zone_name))) {
            $this->setMessage('add_zone_master', 'error', _('There is already a zone_name with this name.'));
            $this->showForm();
        } elseif ($dnsRecord->domain_exists($zone_name) || DnsRecord::record_name_exists($this->db, $zone_name)) {
            $this->setMessage('add_zone_master', 'error', _('There is already a zone_name with this name.'));
            $this->showForm();
        } elseif ($dnsRecord->add_domain($this->db, $zone_name, $owner, $dom_type, '', $zone_template)) {
            $this->setMessage('list_zones', 'success', _('Zone has been added successfully.'));

            $zone_id = DnsRecord::get_zone_id_from_name($this->db, $zone_name);
            $this->logger->log_info(sprintf('client_ip:%s user:%s operation:add_zone zone_name:%s zone_type:%s zone_template:%s',
                $_SERVER['REMOTE_ADDR'], $_SESSION["userlogin"],
                $zone_name, $dom_type, $zone_template), $zone_id);

            if ($pdnssec_use) {
                $dnssecProvider = DnssecProviderFactory::create($this->db, $this->getConfig());

                if (isset($_POST['dnssec'])) {
                    $dnssecProvider->secureZone($zone_name);
                }

                $dnssecProvider->rectifyZone($zone_name);
            }

            $this->redirect('list_zones.php');
        }
    }

    private function showForm(): void
    {
        $perm_view_others = LegacyUsers::verify_permission($this->db, 'user_view_others');

        $this->render('add_zone_master.html', [
            'perm_view_others' => $perm_view_others,
            'session_user_id' => $_SESSION['userid'],
            'available_zone_types' => array("MASTER", "NATIVE"),
            'users' => LegacyUsers::show_users($this->db),
            'zone_templates' => ZoneTemplate::get_list_zone_templ($this->db, $_SESSION['userid']),
            'iface_zone_type_default' => $this->config('iface_zone_type_default'),
            'pdnssec_use' => $this->config('pdnssec_use'),
        ]);
    }
}

$controller = new AddZoneMasterController();
$controller->run();
