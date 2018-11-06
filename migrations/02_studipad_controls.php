<?php
/**
 * @author               Eric Laubmeyer <eric.laubmeyer@hs-rm.de>
 */

// +---------------------------------------------------------------------------+
// This file is NOT part of Stud.IP
// Copyright (C) 2011 Oliver Oster <oster@zmml.uni-bremen.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

class StudipadControls extends Migration
{
    public function description()
    {
        return 'added control table for Stud.IPad plugin';
    }

    public function up()
    {
        $conn = DBManager::get();
        $conn->query('
  CREATE TABLE IF NOT EXISTS `plugin_StudIPad_controls` (
    `pad_id` varchar(100) NOT NULL,
    `controls` varchar(20) NOT NULL,
	`readonly` tinyint(1) NOT NULL,
    PRIMARY KEY  (`pad_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;');
    }

    public function down()
    {
        $conn = DBManager::get();
        $conn->query('DROP TABLE plugin_StudIPad_controls');
    }
}
