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

class StudipadConfig extends Migration
{
    public function description()
    {
        return 'config entries for Stud.IPad plugin';
    }

    public function up()
    {
        Config::get()->create(
            'STUDIPAD_APIKEY',
            [
                'description' => 'API Key für etherpad lite.'
            ]
        );
        Config::get()->create(
            'STUDIPAD_PADBASEURL',
            [
                'description' => 'Basis URL für etherpad lite Pads.'
            ]
        );
        Config::get()->create(
            'STUDIPAD_APIURL',
            [
                'description' => 'URL to Etherpad Lite API.'
            ]
        );
        Config::get()->create(
            'STUDIPAD_INITEXT',
            [
                'description' => "Welcome to Etherpad!\r\n\r\nThis pad text is synchronized as you type, so that everyone viewing this page sees the same text.  This allows you to collaborate seamlessly on documents!\r\n"
            ]
        );
        Config::get()->create(
            'STUDIPAD_COOKIE_DOMAIN',
            [
                'description' => 'Domain für etherpad lite Session Cookies.'
            ]
        );
    }

    public function down()
    {
        Config::get()->delete('STUDIPAD_APIKEY');
        Config::get()->delete('STUDIPAD_PADBASEURL');
        Config::get()->delete('STUDIPAD_APIURL');
        Config::get()->delete('STUDIPAD_INITEXT');
        Config::get()->delete('STUDIPAD_COOKIE_DOMAIN');
    }
}
