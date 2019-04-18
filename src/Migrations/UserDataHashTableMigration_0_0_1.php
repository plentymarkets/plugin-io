<?php

namespace IO\Migrations;

use IO\DBModels\UserDataHash;
use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;

class UserDataHashTableMigration_0_0_1
{
    public function run(Migrate $migrate)
    {
        $migrate->createTable(UserDataHash::class);
    }
}
