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
 * Script that handles user password changes
 *
 * @package     Poweradmin
 * @copyright   2007-2010 Rejo Zenger <rejo@zenger.nl>
 * @copyright   2010-2023 Poweradmin Development Team
 * @license     https://opensource.org/licenses/GPL-3.0 GPL
 */

use Poweradmin\BaseController;
use Poweradmin\Domain\Model\SessionEntity;
use Poweradmin\Domain\Service\AuthenticationService;
use Poweradmin\Domain\Service\SessionService;
use Poweradmin\Infrastructure\Service\RedirectService;
use Poweradmin\LegacyUsers;
use Valitron\Validator;

require_once __DIR__ . '/vendor/autoload.php';

class ChangePasswordController extends BaseController {
    private AuthenticationService $authService;

    public function __construct() {
        parent::__construct();

        $sessionService = new SessionService();
        $redirectService = new RedirectService();
        $this->authService = new AuthenticationService($sessionService, $redirectService);
    }

    public function run(): void
    {
        if ($this->isPost()) {
            $v = new Validator($_POST);
            $v->rules([
                'required' => [
                    ['old_password'],
                    ['new_password'],
                    ['new_password2'],
                ]
            ]);

            if ($v->validate()) {
                $result = LegacyUsers::change_user_pass($this->db, $_POST);
                if ($result === true) {
                    $sessionEntity = new SessionEntity(_('Password has been changed, please login.'), 'success');
                    $this->authService->logout($sessionEntity);
                }
            } else {
                $this->showFirstError($v->errors());
            }
        }

        $this->render('change_password.html', []);
    }
}

$controller = new ChangePasswordController();
$controller->run();
