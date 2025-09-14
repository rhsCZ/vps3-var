<?php

/*  Poweradmin, a friendly web-based admin tool for PowerDNS.
 *  See <https://www.poweradmin.org> for more details.
 *
 *  Copyright 2007-2010 Rejo Zenger <rejo@zenger.nl>
 *  Copyright 2010-2025 Poweradmin Development Team
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

class TwigEnvironmentInitializer
{
    private LocaleHandler $localeHandler;

    public function __construct(LocaleHandler $localeHandler)
    {
        $this->localeHandler = $localeHandler;
    }

    public function initialize($language): Environment
    {
        $fsLoader = new FilesystemLoader('templates');
        $twigEnvironment = new Environment($fsLoader);

        $translator = new Translator($language);
        $translator->addLoader('po', new PoFileLoader());
        $translator->addResource('po', $this->localeHandler->getLocaleFile($language), $language);

        $twigEnvironment->addExtension(new TranslationExtension($translator));

        return $twigEnvironment;
    }
}
