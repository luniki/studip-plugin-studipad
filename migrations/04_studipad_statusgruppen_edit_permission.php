<?php

class StudipadStatusgruppenEditPermission extends Migration
{
    public function description()
    {
        return 'Konfigurationseintrag zur Verwaltungsberechtigung von Gruppen-Pads';
    }

    public function up()
    {
        $query = 'INSERT IGNORE INTO `config` (`field`, `value`, `type`, `range`, `mkdate`, `chdate`, `description`)
                  VALUES (:name, :value, :type, :range, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)';
        $statement = \DBManager::get()->prepare($query);

        $statement->execute([
            ':name' => 'STUDIPAD_STATUSGRUPPEN_ADMIN_PERMISSION',
            ':description' =>
                'Mit dieser Konfigurationseinstellung wird in einer Veranstaltung festgelegt, wer Gruppen-Pads verwalten darf.',
            ':range' => 'course',
            ':type' => 'string',
            ':value' => 'tutor',
        ]);
    }

    public function down()
    {
        $query = "DELETE `config`, `config_values`
                FROM `config`
                LEFT JOIN `config_values` USING (`field`)
                WHERE `field` = :field";
        $statement = \DBManager::get()->prepare($query);

        $statement->execute([':field' => 'STUDIPAD_STATUSGRUPPEN_ADMIN_PERMISSION']);
    }
}
