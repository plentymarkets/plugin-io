<?php

namespace IO\Migrations;

use IO\DBModels\UserDataHash;
use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;

class UserDataHashTableMigration_0_0_2
{
    public function run(Migrate $migrate)
    {
        $migrate->updateTable(UserDataHash::class);
    }
}
