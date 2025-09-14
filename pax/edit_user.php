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
 * Script that handles user editing requests
 *
 * @package     Poweradmin
 * @copyright   2007-2010 Rejo Zenger <rejo@zenger.nl>
 * @copyright   2010-2023 Poweradmin Development Team
 * @license     https://opensource.org/licenses/GPL-3.0 GPL
 */

use Poweradmin\BaseController;
use Poweradmin\LegacyUsers;
use Poweradmin\Permission;
use Poweradmin\Validation;

require_once __DIR__ . '/vendor/autoload.php';

class EditUserController extends BaseController
{

    public function run(): void
    {
        $edit_id = "-1";
        if (isset($_GET['id']) && Validation::is_number($_GET['id'])) {
            $edit_id = $_GET['id'];
        }

        $perm_edit_own = LegacyUsers::verify_permission($this->db, 'user_edit_own');
        $perm_edit_others = LegacyUsers::verify_permission($this->db, 'user_edit_others');

        if ($edit_id == "-1") {
            $this->showError(_('Invalid or unexpected input given.'));
        }

        if (($edit_id != $_SESSION["userid"] || !$perm_edit_own) && ($edit_id == $_SESSION["userid"] || !$perm_edit_others)) {
            $this->showError(_("You do not have the permission to edit this user."));
        }

        if ($this->isPost()) {
            $this->saveUser($edit_id);
        }

        $this->showUserEditForm($edit_id);
    }

    public function saveUser($edit_id): void
    {
        $i_username = "-1";
        $i_fullname = "-1";
        $i_email = "-1";
        $i_description = "-1";
        $i_password = "";
        $i_perm_templ = "0";

        if (isset($_POST['username'])) {
            $i_username = htmlspecialchars($_POST['username']);
        }

        if (isset($_POST['fullname'])) {
            $i_fullname = htmlspecialchars($_POST['fullname']);
        }

        if (isset($_POST['email'])) {
            $i_email = htmlspecialchars($_POST['email']);
        }

        if (isset($_POST['description'])) {
            $i_description = htmlspecialchars($_POST['description']);
        }

        if (isset($_POST['password'])) {
            $i_password = $_POST['password'];
        }

        if (isset($_POST['perm_templ']) && Validation::is_number($_POST['perm_templ'])) {
            $i_perm_templ = $_POST['perm_templ'];
        } else {
            $user_details = LegacyUsers::get_user_detail_list($this->db, $this->config('ldap_use'), $edit_id);
            $i_perm_templ = $user_details[0]['tpl_id'];
        }

        $i_active = false;
        if (isset($_POST['active']) && Validation::is_number($_POST['active'])) {
            $i_active = true;
        }

        $i_use_ldap = false;
        if (isset($_POST['use_ldap']) && Validation::is_number($_POST['use_ldap'])) {
            $i_use_ldap = true;
        }

        if ($i_username == "-1" || $i_fullname == "-1" || $i_email < "1" || $i_description == "-1") {
            $this->showError(_('Invalid or unexpected input given.'));
        }

        if ($i_username != "" && $i_perm_templ > "0" && $i_fullname) {
            $legacyUsers = new LegacyUsers($this->db, $this->getConfig());
            if ($legacyUsers->edit_user($edit_id, $i_username, $i_fullname, $i_email, $i_perm_templ, $i_description, $i_active, $i_password, $i_use_ldap)) {
                $this->setMessage('users', 'success', _('The user has been updated successfully.'));
                $this->redirect('users.php');
            } else {
                $this->setMessage('edit_user', 'error', _('The user could not be updated.'));
            }
        } else {
            $this->setMessage('edit_user', 'error', _('The user could not be updated.'));
        }
    }

    public function showUserEditForm($edit_id): void
    {
        $users = LegacyUsers::get_user_detail_list($this->db, $this->config('ldap_use'), $edit_id);
        if (empty($users)) {
            $this->showError(_('User does not exist.'));
        }

        $user = $users[0];
        $edit_templ_perm = LegacyUsers::verify_permission($this->db, 'user_edit_templ_perm');
        $passwd_edit_others_perm = LegacyUsers::verify_permission($this->db, 'user_passwd_edit_others');
        $edit_own_perm = LegacyUsers::verify_permission($this->db, 'user_edit_own');
        $permission_templates = LegacyUsers::list_permission_templates($this->db);
        $user_permissions = LegacyUsers::get_permissions_by_template_id($this->db, $user['tpl_id']);

        ($user['active']) == "1" ? $check = " CHECKED" : $check = "";
        $name = $user['fullname'] ?: $user['username'];

        $use_ldap_checked = "";
        if ($user['use_ldap']) {
            $use_ldap_checked = "checked";
        }

        $permissions = Permission::getPermissions($this->db, ['user_is_ueberuser']);
        $currentUserAdmin = $permissions['user_is_ueberuser'] && $_SESSION['userid'] == $edit_id;

        $this->render('edit_user.html', [
            'edit_id' => $edit_id,
            'name' => $name,
            'user' => $user,
            'session_user_id' => $_SESSION['userid'],
            'check' => $check,
            'edit_templ_perm' => $edit_templ_perm,
            'edit_own_perm' => $edit_own_perm,
            'perm_passwd_edit_others' => $passwd_edit_others_perm,
            'permission_templates' => $permission_templates,
            'user_permissions' => $user_permissions,
            'ldap_use' => $this->config('ldap_use') && !$currentUserAdmin,
            'use_ldap_checked' => $use_ldap_checked,
        ]);
    }
}

$controller = new EditUserController();
$controller->run();
