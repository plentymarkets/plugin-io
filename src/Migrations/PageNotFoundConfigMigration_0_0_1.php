<?php

namespace IO\Migrations;

use Plenty\Modules\Plugin\Contracts\ConfigurationRepositoryContract;
use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;
use Plenty\Modules\Plugin\PluginSet\Contracts\PluginSetRepositoryContract;
use Plenty\Modules\Plugin\PluginSet\Models\PluginSet;
use Plenty\Modules\Plugin\PluginSet\Models\PluginSetEntry;


class PageNotFoundConfigMigration_0_0_1
{
    public function run(Migrate $migrate)
    {
        /** @var ConfigurationRepositoryContract $configRepo */
        $configRepo = pluginApp(ConfigurationRepositoryContract::class);
        
        /** @var PluginSetRepositoryContract $pluginSetRepo */
        $pluginSetRepo = pluginApp(PluginSetRepositoryContract::class);
        $pluginSets = $pluginSetRepo->list();
    
        /** @var PluginSet $pluginSet */
        foreach($pluginSets as $pluginSet)
        {
            foreach ($pluginSet->pluginSetEntries as $pluginSetEntry)
            {
                if ($pluginSetEntry instanceof PluginSetEntry && $pluginSetEntry->plugin->name === 'IO')
                {
                    $pluginSetId = $pluginSetEntry->pluginSetId;
                    $config      = $pluginSetEntry->configurations()->getResults();
                    
                    $routeSettings = $config->where('key', 'routing.enabled_routes')->first();
                    if(!is_null($routeSettings))
                    {
                        $routeSettings = $routeSettings->toArray();
                        if(count($routeSettings))
                        {
                            $routeSettingsValues = explode(', ', $routeSettings['value']);
                            if(count($routeSettingsValues) && !in_array('page-not-found', $routeSettingsValues) && (in_array('category', $routeSettingsValues) || in_array('all', $routeSettingsValues)))
                            {
                                $routeSettingsValues[] = 'page-not-found';
                                $newConfigValues = [
                                    [
                                        'key' => 'routing.enabled_routes',
                                        'value' => implode(', ', $routeSettingsValues)
                                    ]
                                ];
                                
                                $configRepo->saveConfiguration($pluginSetEntry->plugin->id, $newConfigValues, $pluginSetId);
                            }
                        }
                    }
                }
            }
        }
    }
}
