<?php
/**
 * @author               Oliver Oster <oster@zmml.uni-bremen.de>
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
include 'etherpad-lite-client.php';
class StudipadControlsConfig extends Migration
{
    public function description()
    {
        return 'config entries for Stud.IPad plugin';
    }

    public function up()
    {
        Config::get()->create(
            'STUDIPAD_CONTROLS_DEFAULT',
            [
                'description' => 'Control Elemente in Pads initial alle aktivieren.',
                'type' => 'boolean',
                'value' => 1,
            ]
        );
    }

    public function down()
    {
        Config::get()->delete('STUDIPAD_CONTROLS_DEFAULT');
    }
}
