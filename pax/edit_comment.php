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
 * Script that handles editing of zone comments
 *
 * @package     Poweradmin
 * @copyright   2007-2010 Rejo Zenger <rejo@zenger.nl>
 * @copyright   2010-2023 Poweradmin Development Team
 * @license     https://opensource.org/licenses/GPL-3.0 GPL
 */

use Poweradmin\Application\Presenter\ErrorPresenter;
use Poweradmin\BaseController;
use Poweradmin\DnsRecord;
use Poweradmin\Domain\Error\ErrorMessage;
use Poweradmin\LegacyUsers;
use Poweradmin\Permission;
use Poweradmin\Validation;

require_once __DIR__ . '/vendor/autoload.php';

class EditCommentController extends BaseController {

    public function run(): void
    {
        $iface_zone_comments = $this->config('iface_zone_comments');

        if (!$iface_zone_comments) {
            $this->showError(_("You do not have the permission to edit this comment."));
        }

        $perm_view = Permission::getViewPermission($this->db);
        $perm_edit = Permission::getEditPermission($this->db);

        if (!isset($_GET['id']) || !Validation::is_number($_GET['id'])) {
            $this->showError(_('Invalid or unexpected input given.'));
        }
        $zone_id = htmlspecialchars($_GET['id']);

        $user_is_zone_owner = LegacyUsers::verify_user_is_owner_zoneid($this->db, $zone_id);
        if ($perm_view == "none" || $perm_view == "own" && $user_is_zone_owner == "0") {
            $this->showError(_("You do not have the permission to view this comment."));
        }

        $zone_type = DnsRecord::get_domain_type($this->db, $zone_id);
        $perm_edit_comment = $zone_type == "SLAVE" || $perm_edit == "none" || ($perm_edit == "own" || $perm_edit == "own_as_client") && $user_is_zone_owner == "0";

        if (isset($_POST["commit"])) {
            if ($perm_edit_comment) {
                $error = new ErrorMessage(_("You do not have the permission to edit this comment."));
                $errorPresenter = new ErrorPresenter();
                $errorPresenter->present($error);
            } else {
                DnsRecord::edit_zone_comment($this->db, $zone_id, $_POST['comment']);
                $this->setMessage('edit', 'success', _('The comment has been updated successfully.'));
                $this->redirect('edit.php', ['id' => $zone_id]);
            }
        }

        $this->showCommentForm($zone_id, $perm_edit_comment);
    }

    public function showCommentForm(string $zone_id, bool $perm_edit_comment): void
    {
        $zone_name = DnsRecord::get_domain_name_by_id($this->db, $zone_id);

        if (str_starts_with($zone_name, "xn--")) {
            $idn_zone_name = idn_to_utf8($zone_name, IDNA_NONTRANSITIONAL_TO_ASCII);
        } else {
            $idn_zone_name = "";
        }

        $this->render('edit_comment.html', [
            'zone_id' => $zone_id,
            'comment' => DnsRecord::get_zone_comment($this->db, $zone_id),
            'disabled' => $perm_edit_comment,
            'zone_name' => $zone_name,
            'idn_zone_name' => $idn_zone_name,
        ]);
    }
}

$controller = new EditCommentController();
$controller->run();
