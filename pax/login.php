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

use Poweradmin\Application\Presenter\LocalePresenter;
use Poweradmin\Application\Service\LocaleService;
use Poweradmin\BaseController;
use Poweradmin\Infrastructure\Repository\LocaleRepository;

require_once __DIR__ . '/vendor/autoload.php';

include_once('inc/config-defaults.inc.php');
@include_once('inc/config.inc.php');

class LoginController extends BaseController {

    private LocaleRepository $localeRepository;
    private LocaleService $localeService;
    private LocalePresenter $localePresenter;

    public function __construct() {
        parent::__construct($authenticate = false);

        $this->localeRepository = new LocaleRepository();
        $this->localeService = new LocaleService();
        $this->localePresenter = new LocalePresenter();
    }

    public function run(): void
    {
        $localesData = $this->localeRepository->getLocales();
        $preparedLocales = $this->localeService->prepareLocales($localesData, $this->config('iface_lang'));

        $this->initializeSession();
        list($msg, $type) = $this->getSessionMessages();

        if (!$this->config('ignore_install_dir') && file_exists('install')) {
            $this->render('empty.html', []);
        } else {
            $this->renderLogin($preparedLocales, $msg, $type);
        }
    }

    public function initializeSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function getSessionMessages(): array
    {
        $msg = $_SESSION['message'] ?? '';
        $type = $_SESSION['type'] ?? '';
        unset($_SESSION['message'], $_SESSION['type']);
        return [$msg, $type];
    }

    public function renderLogin(array $preparedLocales, string $msg, string $type): void
    {
        $this->render('login.html', [
            'query_string' => $_SERVER['QUERY_STRING'] ?? '',
            'locale_options' => $this->localePresenter->generateLocaleOptions($preparedLocales),
            'msg' => $msg,
            'type' => $type,
        ]);
    }
}

$controller = new LoginController();
$controller->run();
