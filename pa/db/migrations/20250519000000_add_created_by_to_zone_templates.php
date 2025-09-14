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

use Phinx\Migration\AbstractMigration;

final class AddCreatedByToZoneTemplates extends AbstractMigration
{
    public function change(): void
    {
        $adapter = $this->getAdapter();
        $adapterType = $adapter->getAdapterType();

        // Add created_by column
        $this->table('zone_templ')
            ->addColumn('created_by', 'integer', ['null' => true])
            ->save();

        // Set created_by to match owner for existing templates
        $this->execute('UPDATE zone_templ SET created_by = owner WHERE owner != 0');

        // Add foreign key to users table if supported
        if (in_array($adapterType, ['mysql', 'pgsql'])) {
            $this->table('zone_templ')
                ->addForeignKey('created_by', 'users', 'id', [
                    'delete' => 'SET NULL',
                    'update' => 'CASCADE'
                ])
                ->save();
        }
    }
}
