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

namespace PoweradminInstall;

use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\Loader\PoFileLoader;
use Symfony\Component\Translation\Translator;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TemplateUtils
{

    const MIN_STEP_VALUE = 1;
    const MAX_STEP_VALUE = 7;

    public static function initializeTwigEnvironment($language): Environment
    {
        $loader = new FilesystemLoader('templates');
        $twig = new Environment($loader);

        $translator = new Translator($language);
        $translator->addLoader('po', new PoFileLoader());
        $translator->addResource('po', getLocaleFile($language), $language);

        $twig->addExtension(new TranslationExtension($translator));

        return $twig;
    }

    public static function getCurrentStep(array $postData): int
    {
        $sanitizedData = filter_var_array($postData, [
            'step' => ['filter' => FILTER_VALIDATE_INT, 'options' => ['default' => 1]]
        ]);

        $step = $sanitizedData['step'];

        if ($step < self::MIN_STEP_VALUE || $step > self::MAX_STEP_VALUE) {
            return 1;
        }

        return ($step !== false && $step !== null) ? $step : 1;
    }

    public static function renderHeader($twig, $current_step): void
    {
        echo $twig->render('header.html', array(
            'current_step' => htmlspecialchars($current_step),
            'file_version' => time()
        ));
    }

    public static function renderFooter($twig): void
    {
        echo $twig->render('footer.html');
    }
}