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
 * Script that handles record deletions from zones
 *
 * @package     Poweradmin
 * @copyright   2007-2010 Rejo Zenger <rejo@zenger.nl>
 * @copyright   2010-2023 Poweradmin Development Team
 * @license     https://opensource.org/licenses/GPL-3.0 GPL
 */

use Poweradmin\Application\Dnssec\DnssecProviderFactory;
use Poweradmin\BaseController;
use Poweradmin\DnsRecord;
use Poweradmin\LegacyLogger;
use Poweradmin\LegacyUsers;
use Poweradmin\Permission;
use Poweradmin\Validation;

require_once __DIR__ . '/vendor/autoload.php';

class DeleteRecordController extends BaseController {

    private LegacyLogger $logger;

    public function __construct() {
        parent::__construct();

        $this->logger = new LegacyLogger($this->db);
    }

    public function run(): void
    {
        if (!isset($_GET['id']) || !Validation::is_number($_GET['id'])) {
            $this->showError(_('Invalid or unexpected input given.'));
        }

        $record_id = htmlspecialchars($_GET['id']);
        $zid = DnsRecord::get_zone_id_from_record_id($this->db, $record_id);
        if ($zid == NULL) {
            $this->showError(_('There is no zone with this ID.'));
        }

        if (isset($_GET['confirm'])) {
            $record_info = DnsRecord::get_record_from_id($this->db, $record_id);
            if (DnsRecord::delete_record($this->db, $record_id)) {
                if (isset($record_info['prio'])) {
                    $this->logger->log_info(sprintf('client_ip:%s user:%s operation:delete_record record_type:%s record:%s content:%s ttl:%s priority:%s',
                        $_SERVER['REMOTE_ADDR'], $_SESSION["userlogin"],
                        $record_info['type'], $record_info['name'], $record_info['content'], $record_info['ttl'], $record_info['prio']), $zid);
                } else {
                    $this->logger->log_info(sprintf('client_ip:%s user:%s operation:delete_record record_type:%s record:%s content:%s ttl:%s',
                        $_SERVER['REMOTE_ADDR'], $_SESSION["userlogin"],
                        $record_info['type'], $record_info['name'], $record_info['content'], $record_info['ttl']), $zid);
                }

                DnsRecord::delete_record_zone_templ($this->db, $record_id);
                $dnsRecord = new DnsRecord($this->db, $this->getConfig());
                $dnsRecord->update_soa_serial($zid);

                if ($this->config('pdnssec_use')) {
                    $zone_name = DnsRecord::get_domain_name_by_id($this->db, $zid);
                    $dnssecProvider = DnssecProviderFactory::create($this->db, $this->getConfig());
                    $dnssecProvider->rectifyZone($zone_name);
                }

                $this->setMessage('edit', 'success', _('The record has been deleted successfully.'));
                $this->redirect('edit.php', ['id' => $zid]);
            }
        }

        $perm_edit = Permission::getEditPermission($this->db);

        $zone_info = DnsRecord::get_zone_info_from_id($this->db, $zid);
        $zone_id = DnsRecord::recid_to_domid($this->db, $record_id);
        $user_is_zone_owner = LegacyUsers::verify_user_is_owner_zoneid($this->db, $zone_id);
        if ($zone_info['type'] == "SLAVE" || $perm_edit == "none" || ($perm_edit == "own" || $perm_edit == "own_as_client") && $user_is_zone_owner == "0") {
            $this->showError(_("You do not have the permission to edit this record."));
        }

        $this->showQuestion($record_id, $zid, $zone_id);
    }

    public function showQuestion(string $record_id, $zid, int $zone_id): void
    {
        $zone_name = DnsRecord::get_domain_name_by_id($this->db, $zone_id);

        if (str_starts_with($zone_name, "xn--")) {
            $idn_zone_name = idn_to_utf8($zone_name, IDNA_NONTRANSITIONAL_TO_ASCII);
        } else {
            $idn_zone_name = "";
        }

        $this->render('delete_record.html', [
            'record_id' => $record_id,
            'zone_id' => $zid,
            'zone_name' => $zone_name,
            'idn_zone_name' => $idn_zone_name,
            'record_info' => DnsRecord::get_record_from_id($this->db, $record_id),
        ]);
    }
}

$controller = new DeleteRecordController();
$controller->run();
