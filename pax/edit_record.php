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
 * Script that handles requests to edit zone records
 *
 * @package     Poweradmin
 * @copyright   2007-2010 Rejo Zenger <rejo@zenger.nl>
 * @copyright   2010-2023 Poweradmin Development Team
 * @license     https://opensource.org/licenses/GPL-3.0 GPL
 */

use Poweradmin\Application\Dnssec\DnssecProviderFactory;
use Poweradmin\Application\Presenter\ErrorPresenter;
use Poweradmin\BaseController;
use Poweradmin\DnsRecord;
use Poweradmin\Domain\Error\ErrorMessage;
use Poweradmin\LegacyLogger;
use Poweradmin\LegacyUsers;
use Poweradmin\Permission;
use Poweradmin\RecordType;

require_once __DIR__ . '/vendor/autoload.php';

class EditRecordController extends BaseController {

    private LegacyLogger $logger;

    public function __construct() {
        parent::__construct();

        $this->logger = new LegacyLogger($this->db);
    }

    public function run(): void
    {
        $perm_view = Permission::getViewPermission($this->db);
        $perm_edit = Permission::getEditPermission($this->db);

        $record_id = $_GET['id'];
        $zid = DnsRecord::get_zone_id_from_record_id($this->db, $record_id);

        $user_is_zone_owner = LegacyUsers::verify_user_is_owner_zoneid($this->db, $zid);
        $zone_type = DnsRecord::get_domain_type($this->db, $zid);

        if ($perm_view == "none" || $perm_view == "own" && $user_is_zone_owner == "0") {
            $this->showError(_("You do not have the permission to view this record."));
        }

        if ($zone_type == "SLAVE" || $perm_edit == "none" || ($perm_edit == "own" || $perm_edit == "own_as_client") && $user_is_zone_owner == "0") {
            $error = new ErrorMessage(_("You do not have the permission to edit this record."));
            $errorPresenter = new ErrorPresenter();
            $errorPresenter->present($error);
        }

        if ($this->isPost()) {
            $this->saveRecord($zid);
        }

        $this->showRecordEditForm($record_id, $zone_type, $zid, $perm_edit, $user_is_zone_owner);
    }

    public function showRecordEditForm($record_id, string $zone_type, $zid, string $perm_edit, $user_is_zone_owner): void
    {
        $zone_name = DnsRecord::get_domain_name_by_id($this->db, $zid);

        $recordTypes = RecordType::getTypes();
        $record = DnsRecord::get_record_from_id($this->db, $record_id);
        $record['record_name'] = trim(str_replace(htmlspecialchars($zone_name), '', htmlspecialchars($record["name"])), '.');

        if (str_starts_with($zone_name, "xn--")) {
            $idn_zone_name = idn_to_utf8($zone_name, IDNA_NONTRANSITIONAL_TO_ASCII);
        } else {
            $idn_zone_name = "";
        }

        $this->render('edit_record.html', [
            'record_id' => $record_id,
            'record' => $record,
            'recordTypes' => $recordTypes,
            'zone_name' => $zone_name,
            'idn_zone_name' => $idn_zone_name,
            'zone_type' => $zone_type,
            'zid' => $zid,
            'perm_edit' => $perm_edit,
            'user_is_zone_owner' => $user_is_zone_owner,
        ]);
    }

    public function saveRecord($zid): void
    {
        $old_record_info = DnsRecord::get_record_from_id($this->db, $_POST["rid"]);
        $dnsRecord = new DnsRecord($this->db, $this->getConfig());

        $postData = $_POST;
        if (isset($postData['disabled']) && $postData['disabled'] == "on") {
            $postData['disabled'] = 1;
        } else {
            $postData['disabled'] = 0;
        }

        $ret_val = $dnsRecord->edit_record($postData);
        if ($ret_val) {
            $dnsRecord = new DnsRecord($this->db, $this->getConfig());
            $dnsRecord->update_soa_serial($zid);

            $new_record_info = DnsRecord::get_record_from_id($this->db, $_POST["rid"]);
            $this->logger->log_info(sprintf('client_ip:%s user:%s operation:edit_record'
                . ' old_record_type:%s old_record:%s old_content:%s old_ttl:%s old_priority:%s'
                . ' record_type:%s record:%s content:%s ttl:%s priority:%s',
                $_SERVER['REMOTE_ADDR'], $_SESSION["userlogin"],
                $old_record_info['type'], $old_record_info['name'], $old_record_info['content'], $old_record_info['ttl'], $old_record_info['prio'],
                $new_record_info['type'], $new_record_info['name'], $new_record_info['content'], $new_record_info['ttl'], $new_record_info['prio']),
                $zid);

            if ($this->config('pdnssec_use')) {
                $zone_name = DnsRecord::get_domain_name_by_id($this->db, $zid);
                $dnssecProvider = DnssecProviderFactory::create($this->db, $this->getConfig());
                $dnssecProvider->rectifyZone($zone_name);
            }

            $this->setMessage('edit', 'success', _('The record has been updated successfully.'));
            $this->redirect('edit.php', ['id' => $zid]);
        }
    }
}

$controller = new EditRecordController();
$controller->run();
