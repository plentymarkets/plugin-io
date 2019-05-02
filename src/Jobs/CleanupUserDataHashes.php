<?php

namespace IO\Jobs;

use IO\DBModels\UserDataHash;
use Plenty\Modules\Cron\Contracts\CronHandler;
use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;

class CleanupUserDataHashes extends CronHandler
{
    public function handle()
    {
        /** @var DataBase $db */
        $db = pluginApp(DataBase::class);
        $db->query(UserDataHash::class)
            ->where('expiresAt', '<', date("Y-m-d H:i:s"))
            ->where('expiresAt', '<>', '')
            ->delete();
    }
}