<?php

namespace IO\Migrations;

use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;
use IO\DBModels\PasswordReset;

class PasswordResetTableMigration_0_0_1
{
    public function run(Migrate $migrate)
    {
        $migrate->createTable(PasswordReset::class);
    }
}
