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
 * Script that displays list of zone templates
 *
 * @package     Poweradmin
 * @copyright   2007-2010 Rejo Zenger <rejo@zenger.nl>
 * @copyright   2010-2023 Poweradmin Development Team
 * @license     https://opensource.org/licenses/GPL-3.0 GPL
 */

use Poweradmin\BaseController;
use Poweradmin\LegacyUsers;
use Poweradmin\ZoneTemplate;

require_once __DIR__ . '/vendor/autoload.php';

class ListZoneTemplController extends BaseController {

    public function run(): void
    {
        $this->checkPermission('zone_master_add', _("You do not have the permission to edit zone templates."));

        $this->showListZoneTempl();
    }

    private function showListZoneTempl(): void
    {
        $perm_zone_master_add = LegacyUsers::verify_permission($this->db, 'zone_master_add');

        $this->render('list_zone_templ.html', [
            'perm_zone_master_add' => $perm_zone_master_add,
            'user_name' => LegacyUsers::get_fullname_from_userid($this->db, $_SESSION['userid']) ?: $_SESSION['userlogin'],
            'zone_templates' => ZoneTemplate::get_list_zone_templ($this->db, $_SESSION['userid']),
            'perm_is_godlike' => LegacyUsers::verify_permission($this->db, 'user_is_ueberuser'),
        ]);
    }
}

$controller = new ListZoneTemplController();
$controller->run();
