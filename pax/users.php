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
 * Script that handles requests to update and list users
 *
 * @package     Poweradmin
 * @copyright   2007-2010 Rejo Zenger <rejo@zenger.nl>
 * @copyright   2010-2023 Poweradmin Development Team
 * @license     https://opensource.org/licenses/GPL-3.0 GPL
 */

use Poweradmin\BaseController;
use Poweradmin\LegacyUsers;
use Poweradmin\Permission;

require_once __DIR__ . '/vendor/autoload.php';

class UsersController extends BaseController
{

    public function run(): void
    {
        if ($this->isPost()) {
            $this->updateUsers();
        }
        $this->showUsers();
    }

    private function updateUsers(): void
    {
        $success = false;
        foreach ($_POST['user'] as $user) {
            $legacyUsers = new LegacyUsers($this->db, $this->getConfig());
            $result = $legacyUsers->update_user_details($user);
            if ($result) {
                $success = true;
            }
        }
        if ($success) {
            $this->setMessage('users', 'success', _('User details updated'));
        }
    }

    private function showUsers(): void
    {
        $this->render('users.html', [
            'permissions' => Permission::getPermissions(
                $this->db,
                [
                    'user_view_others',
                    'user_edit_own',
                    'user_edit_others',
                    'user_edit_templ_perm',
                    'user_is_ueberuser'
                ]
            ),
            'perm_templates' => LegacyUsers::list_permission_templates($this->db),
            'users' => LegacyUsers::get_user_detail_list($this->db, $this->config('ldap_use')),
            'ldap_use' => $this->config('ldap_use'),
            'session_userid' => $_SESSION["userid"],
            'perm_add_new' => LegacyUsers::verify_permission($this->db, 'user_add_new'),
        ]);
    }
}

$controller = new UsersController();
$controller->run();
