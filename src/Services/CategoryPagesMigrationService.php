<?php

namespace IO\Services;

use Plenty\Modules\Category\Contracts\CategoryRepositoryContract;
use Plenty\Modules\Category\Contracts\CategoryTemplateRepositoryContract;
use Plenty\Modules\Plugin\Repositories\ConfigurationRepository;
use Plenty\Plugin\Application;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Templates\Twig;

class CategoryPagesMigrationService
{
    public function createCategoryFromTwig($templateNames, $categoryNames, $parentCategoryId = null, $configKey = null, $additionalDetails = array() )
    {
        $plentyId = pluginApp( Application::class )->getPlentyId();

        /** @var CategoryRepositoryContract $categoryRepository */
        $categoryRepository = pluginApp( CategoryRepositoryContract::class );

        $categoryLevel = 0;
        if ( $parentCategoryId !== null )
        {
            $parentCategory = $categoryRepository->get( $parentCategoryId );
            $categoryLevel = $parentCategory->level + 1;
        }

        $details = [];
        foreach ($categoryNames as $lang => $name)
        {
            $detailData = [
                'plentyId'  => $plentyId,
                'lang'      => $lang,
                'name'      => $name,
            ];

            if ( array_key_exists( $lang, $additionalDetails ) )
            {
                foreach( $additionalDetails[$lang] as $detailKey => $detailValue )
                {
                    $detailData[$detailKey] = $detailValue;
                }
            }

            $details[] = $detailData;
        }

        $categoryData = [
            'parentCategoryId'  => $parentCategoryId,
            'type'              => 'content',
            'level'             => $categoryLevel,
            'details'           => $details,
            'clients'           => [
                ['plentyId'  => $plentyId]
            ]
        ];
        $newCategory = $categoryRepository->createCategory( $categoryData );

        /** @var Twig $twig */
        $twig = pluginApp(Twig::class);

        if ( is_array($templateNames) )
        {
            foreach( $templateNames as $lang => $templateName )
            {
                $this->storeCategoryTemplate(
                    $twig->render( "IO::TwigSourceRenderer", ['template' => $templateName] ),
                    $newCategory->id,
                    $lang
                );
            }
        }
        else
        {
            foreach ($categoryNames as $lang => $name)
            {
                $this->storeCategoryTemplate(
                    $twig->render("IO::TwigSourceRenderer", ['template' => $templateNames]),
                    $newCategory->id,
                    $lang
                );
            }
        }

        if ( $configKey !== null )
        {
            $this->writeConfigValue( $configKey, $newCategory->id );
        }

    }

    private function storeCategoryTemplate( $template, $categoryId, $lang )
    {
        /** @var CategoryTemplateRepositoryContract $categoryTemplateRepository */
        $categoryTemplateRepository = pluginApp(CategoryTemplateRepositoryContract::class);

        /** @var WebstoreConfigurationService $webstoreConfigService */
        $webstoreConfigService = pluginApp(WebstoreConfigurationService::class);

        $categoryTemplateRepository->storeCategoryTemplateContent(
            $template,
            $categoryId,
            $lang,
            $webstoreConfigService->getWebstoreConfig()->webstoreId
        );
    }

    private function writeConfigValue( $key, $value )
    {
        // TODO: store config value
    }
}