<?php

namespace IO\Services;

use Plenty\Modules\Category\Contracts\CategoryRepositoryContract;
use Plenty\Modules\Category\Contracts\CategoryTemplateRepositoryContract;
use Plenty\Plugin\Application;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Templates\Twig;

class CategoryPagesMigrationService
{
    private $pages = [
        'CancellationForm' => [
            'de' => 'Widerrufsformular',
            'en' => 'Cancellation form'
        ],
        'CancellationRights' => [
            'de' => 'Widerrufsrecht',
            'en' => 'Cancellation rights'
        ],
        'LegalDisclosure' => [
            'de' => 'Impressum',
            'en' => 'Legal disclosure'
        ],
        'PrivacyPolicy' => [
            'de' => 'Datenschutzerklärung',
            'en' => 'Privacy policy'
        ],
        'TermsAndConditions' => [
            'de' => 'AGB',
            'en' => 'Terms and conditions'
        ],
    ];
    
    public function __construct()
    {
    }

    /**
     * @param $template
     * @param $categoryNames
     * @param null $parentCategoryId
     * @throws \ErrorException
     * @throws \Plenty\Exceptions\ValidationException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function createContentCategoryFromTwig($templateNames, $categoryNames, $parentCategoryId = null  )
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
            $details[] = [
                'plentyId'  => $plentyId,
                'lang'      => $lang,
                'name'      => $name,
            ];
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

    public function createCategoryPages($version)
    {
        /** @var Application $app */
        $app = pluginApp(Application::class);

        /** @var WebstoreConfigurationService $webstoreConfigService */
        $webstoreConfigService = pluginApp(WebstoreConfigurationService::class);

        /** @var CategoryRepositoryContract $categoryRepo */
        $categoryRepo = pluginApp(CategoryRepositoryContract::class);

        /** @var CategoryTemplateRepositoryContract $categoryTemplateRepo */
        $categoryTemplateRepo = pluginApp(CategoryTemplateRepositoryContract::class);

        /** @var Twig $twig */
        $twig = pluginApp(Twig::class);

        /** @var ConfigRepository $configRepository */
        $configRepository = pluginApp(ConfigRepository::class);
        
        $templateName = $configRepository->get('IO.template.template_plugin_name', null);
        
        if(!is_null($templateName))
        {
            $activeLangs = $webstoreConfigService->getActiveLanguageList();
    
            $clients = [
                [
                    'plentyId' => $app->getPlentyId()
                ]
            ];
    
            $parentDetails = [];
            foreach ($activeLangs as $lang)
            {
                $parentDetails[] = [
                    'plentyId' => $app->getPlentyId(),
                    'lang' => $lang,
                    'name' => $templateName . '_' . $version,
                ];
            }
    
            $parentCategoryData = [
                'type' => 'content',
                'level' => 0,
                'details' => $parentDetails,
                'clients' => $clients
            ];
    
            $parentCategory = $categoryRepo->createCategory($parentCategoryData);
    
            $categories = [];
            foreach ($this->pages as $templateKey => $pageNames)
            {
                $details = [];
                foreach ($activeLangs as $lang)
                {
                    if (isset($pageNames[$lang]))
                    {
                        $name = $pageNames[$lang];
                    }
                    else
                    {
                        $name = $pageNames['en'];
                    }
            
                    $details[] = [
                        'plentyId' => $app->getPlentyId(),
                        'lang' => $lang,
                        'name' => $name,
                    ];
                }
        
                $category = [
                    'parentCategoryId' => $parentCategory->id,
                    'type' => 'content',
                    'level' => 1,
                    'details' => $details,
                    'clients' => $clients
                ];
        
                $newCategory = $categoryRepo->createCategory($category);
        
                foreach ($activeLangs as $lang)
                {
                    $categoryTemplateRepo->storeCategoryTemplateContent($twig->render($templateName . '::StaticPages.StaticPagesMigrationWrapper', ['templateKey' => $templateKey]), $newCategory->id, $lang, $webstoreConfigService->getWebstoreConfig()->webstoreId);
                }
            }
        }
    }
}