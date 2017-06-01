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
class StudipadConfig extends DBMigration {
       
  function description() {
    return 'config entries for Stud.IPad plugin';
  }

  function up() {
    $this->db->query("INSERT IGNORE INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('27950286f8bdeb8e6facd400f80e56fc', '', 'STUDIPAD_APIKEY', '', 0, 'string', 'global', 'studipad', 0, 0, 0, 'API Key für etherpad lite', '', '')");
    $this->db->query("INSERT IGNORE INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('3d67adadf067d4a31da5d5ac8bdf7125', '', 'STUDIPAD_PADBASEURL', 'http://pad2.elearning.uni-bremen.de/p', 0, 'string', 'global', 'studipad', 0, 0, 0, 'Basis URL für etherpad lite Pads', '', '')");
    $this->db->query("INSERT IGNORE INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('4ba8cfb4e2842d8430e868ad81c97689', '', 'STUDIPAD_APIURL', 'http://pad2.elearning.uni-bremen.de/api', 0, 'string', 'global', 'studipad', 0, 0, 0, 'URL to Etherpad Lite API', '', '')");
    $this->db->query("INSERT IGNORE INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('4ee5195445a80556bf4eb24f805ea8be', '', 'STUDIPAD_INITEXT', 'Welcome to Stud.IPad!\r\n\r\nThis pad text is synchronized as you type, so that everyone viewing this page sees the same text.  This allows you to collaborate seamlessly on documents!\r\n', 0, 'string', 'global', 'studipad', 0, 0, 0, 'Default Text für neue Pads ', '', '')");
    $this->db->query("INSERT IGNORE INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('ad5b21a83a03aaf963a657a66fbe91f6', '', 'STUDIPAD_COOKIE_DOMAIN', '.elearning.uni-bremen.de', 0, 'string', 'global', 'studipad', 0, 0, 0, 'Domain für etherpad lite Session Cookies', '', '')");
  }

  function down() {
      $this->db->query("DELETE FROM `config` WHERE `config_id`='27950286f8bdeb8e6facd400f80e56fc'");
      $this->db->query("DELETE FROM `config` WHERE `config_id`='3d67adadf067d4a31da5d5ac8bdf7125'");
      $this->db->query("DELETE FROM `config` WHERE `config_id`='4ba8cfb4e2842d8430e868ad81c97689'");
      $this->db->query("DELETE FROM `config` WHERE `config_id`='4ee5195445a80556bf4eb24f805ea8be'");
      $this->db->query("DELETE FROM `config` WHERE `config_id`='ad5b21a83a03aaf963a657a66fbe91f6'");
  }
		    
}

?>
