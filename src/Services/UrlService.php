<?php

namespace IO\Services;

use IO\Helper\ShopUrl;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Item\VariationDescription\Contracts\VariationDescriptionRepositoryContract;
use Plenty\Plugin\Application;

class UrlService
{
    private $activeLanguages = [];
    private $defautLang = "";

    /** @var CategoryService $categoryService */
    private $categoryService;

    /** @var ItemService $itemService */
    private $itemService;

    private $usedItemName = "";

    private $enableOldUrlPattern = false;

    private static $itemUrlMap = [];

    public function __construct(
        WebstoreConfigurationService $webstoreConfigurationService,
        SessionStorageService $sessionStorageService,
        CategoryService $categoryService,
        ItemService $itemService,
        TemplateConfigService $templateConfigService )
    {
        $this->activeLanguages = $webstoreConfigurationService->getActiveLanguageList();
        $this->defautLang = $sessionStorageService->getLang();
        $this->categoryService = $categoryService;
        $this->itemService = $itemService;
        $this->enableOldUrlPattern = $templateConfigService->get('global.enableOldUrlPattern') === "true";

        $usedItemName = $templateConfigService->get('item.name');
        if ( strlen( $usedItemName ) <= 0 )
        {
            $usedItemName = '0';
        }

        $itemNameFields = ['name1', 'name2', 'name3'];

        $this->usedItemName = $itemNameFields[$usedItemName];
    }

    /**
     * Get canonical url for a category
     * @param int           $categoryId
     * @param string|null   $lang
     * @return ShopUrl
     */
    public function getCategoryURL( $categoryId, $lang = null )
    {
        $activeLang = $lang;
        if ( $activeLang === null )
        {
            $activeLang = $this->defautLang;
        }

        $category = null;
        if ( $this->categoryService->getCurrentCategory() !== null && $categoryId === $this->categoryService->getCurrentCategory()->id )
        {
            $category = $this->categoryService->getCurrentCategory();
        }
        else
        {
            $category = $this->categoryService->get( $categoryId, $activeLang );
        }

        if ( $category !== null )
        {
            $categoryDetails = $this->categoryService->getDetails( $category, $activeLang );
            if ( $categoryDetails !== null && strlen( $categoryDetails->canonicalLink ) > 0 )
            {
                return $this->getURL( $categoryDetails->canonicalLink, $lang );
            }

            return $this->getURL(
                $this->categoryService->getURL( $category, $activeLang ),
                $lang
            );
        }

        return pluginApp( ShopUrl::class );
    }

    /**
     * Get canonical url for a variation
     * @param int           $itemId
     * @param int           $variationId
     * @param string|null   $lang
     * @return ShopUrl
     */
    public function getVariationURL( $itemId, $variationId, $lang = null )
    {
        $activeLang = $lang;
        if ( $activeLang === null )
        {
            $activeLang = $this->defautLang;
        }

        $itemData = self::$itemUrlMap[$itemId][$variationId];
        if ( $itemData === null )
        {
            return pluginApp( ShopUrl::class );
        }

        if ( array_key_exists( $activeLang, $itemData['texts'] ) )
        {
            $urlPath = $itemData['texts'][$activeLang]['urlPath'];
            $suffix = $this->enableOldUrlPattern ? "/a-" . $itemId : "_" . $itemId . "_" . $variationId;
            if ( $urlPath !== null && strlen( $urlPath ) > 0 )
            {
                return $this->getURL( $urlPath . $suffix, $lang );
            }

            $itemUrlPath = $this->generateItemUrlPath( $itemId, $variationId, $activeLang );


            return $this->getURL( $itemUrlPath . $suffix, $lang );
        }

        return pluginApp( ShopUrl::class );
    }

    private function generateItemUrlPath( $itemId, $variationId, $lang )
    {
        $itemData = self::$itemUrlMap[$itemId][$variationId];
        $itemName4Url = $this->string4URL( $itemData['texts'][$lang][$this->usedItemName] );
        $prefix = "";

        if ( $itemData['defaultCategoryId'] > 0 )
        {
            $prefix = substr($this->getCategoryURL( $itemData['defaultCategoryId'], $lang )->toRelativeUrl(), strlen($lang) + 1 ) . "/";
        }

        $itemUrlPath = $prefix . $itemName4Url;

        // write itemUrl to database
        /** @var AuthHelper $authHelper */
        $authHelper = pluginApp( AuthHelper::class );

        $authHelper->processUnguarded(
            function() use ( $itemUrlPath, $variationId, $lang) {
                /** @var VariationDescriptionRepositoryContract $variationDescriptionRepository */
                $variationDescriptionRepository = pluginApp( VariationDescriptionRepositoryContract::class );

                $variationDescriptionRepository->update(
                    [
                        'urlPath' => $itemUrlPath
                    ],
                    $variationId,
                    $lang
                );
            }
        );
        self::$itemUrlMap[$itemId][$variationId]['texts'][$lang]['urlPath'] = $itemUrlPath;

        return $prefix . $itemName4Url;
    }

    /**
     * Get canonical url for current page
     * @param string|null   $lang
     * @return ShopUrl
     */
    public function getCanonicalURL( $lang = null )
    {
        if ( TemplateService::$currentTemplate === 'tpl.item' )
        {
            $currentItem = $this->categoryService->getCurrentItem();
            if ( count($currentItem) > 0 )
            {
                return $this->getVariationURL( $currentItem['item']['id'], $currentItem['variation']['id'], $lang );
            }

            return pluginApp( ShopUrl::class );
        }

        if ( substr(TemplateService::$currentTemplate,0, 12) === 'tpl.category' )
        {
            $currentCategory = $this->categoryService->getCurrentCategory();

            if ( $currentCategory !== null )
            {
                return $this->getCategoryURL( $currentCategory->id, $lang );
            }
            return pluginApp( ShopUrl::class );
        }

        if ( TemplateService::$currentTemplate === 'tpl.home' )
        {
            return $this->getURL("", $lang );
        }

        return pluginApp( ShopUrl::class );
    }

    /**
     * Get equivalent canonical urls for each active language
     * @return array
     */
    public function getLanguageURLs()
    {
        $result = [];
        $defaultUrl = $this->getCanonicalURL()->toAbsoluteUrl();

        if ( $defaultUrl !== null )
        {
            $result["x-default"] = $defaultUrl;
        }

        foreach( $this->activeLanguages as $language )
        {
            $url = $this->getCanonicalURL( $language )->toAbsoluteUrl();
            if ( $url !== null )
            {
                $result[$language] = $url;
            }
        }

        return $result;
    }


    public static function prepareItemUrlMap( $itemData )
    {
        $itemId = $itemData['item']['id'];
        $variationId = $itemData['variation']['id'];
        $plentyId = pluginApp( Application::class )->getPlentyId();

        $defaultCategoryId = 0;
        if ( count($itemData['defaultCategories']) )
        {
            foreach ($itemData['defaultCategories'] as $category)
            {
                if ((int)$category['plentyId'] === (int)$plentyId)
                {
                    $defaultCategoryId = (int)$category['id'];
                    break;
                }
            }
        }

        $itemTextsMap = $itemData['texts'];
        if ( array_key_exists( 'lang', $itemTextsMap ) )
        {
            $itemTextsMap = [ $itemData['texts']['lang'] => $itemData['texts'] ];
        }

        self::$itemUrlMap[$itemId][$variationId] = [
            "texts" => $itemTextsMap,
            "defaultCategoryId" => $defaultCategoryId
        ];
    }


    private function string4URL( $n )
    {
        $n = strtolower( $n );
        /**
         * & => "und" wurde geändert in & => " "
         * NIE wieder ändern!!!
         */

        // replace "Basic Latin" and "Latin-1 Supplement" characters to a-z
        $regex = array(
            '/&(a|o|u|A|O|U)ml;/u',				// &aml; => ae, &oml; => oe, &uml; => ue, &Aml; => Ae, &Oml; => Oe, &Uml; =>Ue,
            '/[äÄ\xC6\xE6]/u',					// ä,Ä,Æ,æ => ae
            '/[öÖ]/u',							// ö,Ö => oe
            '/[üÜ]/u',							// ü,Ü => ue
            '/([ß]|&szlig;)/u',					// ß,&szlig; => ss
            '/[\xC0-\xC5\xE0-\xE5]/u',			// À-Å,à-å => a
            '/[\xC7\xE7]/u',					// Ç,ç => c
            '/[\xC8-\xCB\xE8-\xEB]/u', 			// È-Ë,è-ë => e
            '/[\xCC-\xCF\xEC-\xEF]/u',			// Ì-Ï,ì-ï => i
            '/[\xD1\xF1\x{0143}-\x{014B}]/u',	// Ñ,ñ,Ń-ŋ => n
            '/[\xD2-\xD5\xF2-\xF5]/u',			// Ò-Õ,ò-õ => o
            '/[\xD9-\xDC\xF9-\xFB]/u',			// Ù-Û,ù-û => u
            '/[\xDD\xFD\xFF]/u',				// Ý,ý,ÿ => y
            '/[\\\\]/',								// \ => empty string
        );

        $replace = array('$1e','ae','oe','ue','ss','a','c','e','i','n','o','u','y','');

        // if there are UTF-8 characters with Unicode above U+0077
        // add "Latin Extended-A"
        if(preg_replace('/^[\x00-\xFF]+$/u',FALSE,$n))
        {
            $regex = array_merge($regex, array(
                '/[\x{0152}-\x{0153}]/u',			// Œ-œ => oe
                '/[\x{0132}-\x{0133}]/u',			// Ĳ-ĳ => ij
                '/[\x{017F}]/u',					// ſ => ss
                '/[\x{0100}-\x{0105}]/u',			// Ā-ą => a
                '/[\x{0106}-\x{010D}]/u',			// Ć-č => c
                '/[\x{010E}-\x{0111}]/u',			// Ď-đ => d
                '/[\x{0112}-\x{011B}]/u', 			// Ē-ě => e
                '/[\x{011C}-\x{0123}]/u',			// Ĝ-ģ => g
                '/[\x{0124}-\x{0127}]/u',			// Ĥ-ħ => h
                '/[\x{0128}-\x{0131}]/u',			// Ĩ-ı => i
                '/[\x{0134}-\x{0135}]/u',			// Ĵ-ĵ => j
                '/[\x{0136}-\x{0138}]/u',			// Ķ-ĸ => k
                '/[\x{0139}-\x{0142}]/u',			// Ĺ-ł => l
                '/[\x{0143}-\x{014B}]/u',			// Ń-ŋ => n
                '/[\x{014C}-\x{0151}]/u',			// Ō-ő => o
                '/[\x{0154}-\x{0159}]/u',			// Ŕ-ř => r
                '/[\x{015A}-\x{0161}]/u',			// Ś-š => s
                '/[\x{0162}-\x{0167}]/u',			// Ţ-ŧ => t
                '/[\x{0168}-\x{0173}]/u',			// Ũ-ų => u
                '/[\x{0174}-\x{0175}]/u',			// Ŵ-ŵ => w
                '/[\x{0176}-\x{0178}]/u',			// Ŷ-Ÿ => y
                '/[\x{0179}-\x{017E}]/u'			// Ź-ž => z
            ));

            $replace = array_merge($replace, array('oe','ij','ss','a','c','d','e','g','h','i','j','k','l','n','o','r','s','t','u','w','y','z'));
        }

        $regex[] = '/[^a-zA-Z0-9]+/'; // convert all which match the reg_expr /[^a-zA-Z0-9]+/ => '-'
        $replace[] = '-';

        $s = preg_replace($regex,$replace,$n);

        $s = trim($s,'-');

        $t = substr($s,0,2);

        if($t=='a-' || $t=='c-' || $t=='b-' || $t=='f-')
        {
            return substr($s,2);
        }

        return $s;
    }

    /**
     * @param $url
     * @param null $lang
     * @return ShopUrl
     */
    private function getURL($url, $lang = null )
    {
        if ( substr( $url, 0, 1 ) !== "/" )
        {
            $url = "/" . $url;
        }

        if ( $lang !== null && strlen( $lang ) > 0 )
        {
            $url = "/" . $lang . $url;
        }

        return pluginApp( ShopUrl::class, ['path' => $url ] );
    }
}