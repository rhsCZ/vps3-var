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
 * Script that handles editing of permission templates
 *
 * @package     Poweradmin
 * @copyright   2007-2010 Rejo Zenger <rejo@zenger.nl>
 * @copyright   2010-2023 Poweradmin Development Team
 * @license     https://opensource.org/licenses/GPL-3.0 GPL
 */

use Poweradmin\BaseController;
use Poweradmin\LegacyUsers;

require_once __DIR__ . '/vendor/autoload.php';

class EditPermTemplController extends BaseController
{

    public function run(): void
    {
        $this->checkPermission('templ_perm_edit', _("You do not have the permission to edit permission templates."));

        $v = new Valitron\Validator($_GET);
        $v->rules([
            'required' => ['id'],
            'integer' => ['id'],
        ]);

        if (!$v->validate()) {
            $this->showFirstError($v->errors());
        }

        if ($this->isPost()) {
            $this->editPermTempl();
        } else {
            $this->showEditPermTempl();
        }
    }

    private function editPermTempl(): void
    {
        $v = new Valitron\Validator($_POST);
        $v->rules([
            'required' => ['templ_name'],
        ]);

        if ($v->validate()) {
            LegacyUsers::update_perm_templ_details($this->db, $_POST);

            $this->setMessage('list_perm_templ', 'success', _('The permission template has been updated successfully.'));
            $this->redirect('list_perm_templ.php');
        } else {
            $this->showFirstError($v->errors());
        }
    }

    private function showEditPermTempl(): void
    {
        $id = htmlspecialchars($_GET['id']);
        $this->render('edit_perm_templ.html', [
            'id' => $id,
            'templ' => LegacyUsers::get_permission_template_details($this->db, $id),
            'perms_templ' => LegacyUsers::get_permissions_by_template_id($this->db, $id),
            'perms_avail' => LegacyUsers::get_permissions_by_template_id($this->db),
        ]);
    }
}

$controller = new EditPermTemplController();
$controller->run();
