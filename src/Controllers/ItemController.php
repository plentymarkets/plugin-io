<?php

namespace IO\Controllers;

use IO\Api\ResponseCode;
use IO\Services\CategoryService;
use IO\Services\ItemListService;
use IO\Services\ItemSearch\Factories\VariationSearchResultFactory;
use IO\Services\ItemSearch\Helper\ResultFieldTemplate;
use IO\Services\ItemSearch\SearchPresets\CrossSellingItems;
use IO\Services\ItemSearch\SearchPresets\SingleItem;
use IO\Services\ItemSearch\SearchPresets\VariationAttributeMap;
use IO\Services\ItemSearch\Services\ItemSearchService;
use IO\Services\PriceDetectService;
use IO\Services\SessionStorageService;
use Plenty\Modules\Category\Models\Category;
use Plenty\Modules\Pim\SearchService\Filter\ClientFilter;
use Plenty\Modules\Pim\SearchService\Filter\SalesPriceFilter;
use Plenty\Modules\Pim\SearchService\Filter\TextFilter;
use Plenty\Modules\Pim\SearchService\Filter\VariationBaseFilter;
use Plenty\Modules\Pim\VariationDataInterface\Contracts\VariationDataInterfaceContract;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationCategoryAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationSalesPriceAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\VariationDataInterfaceContext;
use Plenty\Modules\ShopBuilder\Helper\ShopBuilderRequest;
use Plenty\Plugin\Application;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Log\Loggable;

/**
 * Class ItemController
 * @package IO\Controllers
 */
class ItemController extends LayoutController
{
    use Loggable;

    /**
     * Prepare and render the item data.
     * @param string    $slug
     * @param int       $itemId         The itemId read from current request url. Will be null if item url does not contain a slug.
     * @param int       $variationId
     * @param Category  $category
     * @return string
     * @throws \ErrorException
     */
    public function showItem(
        string $slug = "",
        int $itemId = 0,
        int $variationId = 0,
        $category = null
    )
    {
        if (!is_null($category))
        {
            pluginApp(CategoryService::class)->setCurrentCategory($category);
        }
        
        
        $itemSearchOptions = [
            'itemId'        => $itemId,
            'variationId'   => $variationId,
            'setCategory'   => is_null($category)
        ];
        /** @var ItemSearchService $itemSearchService */
        $itemSearchService = pluginApp( ItemSearchService::class );
        $itemResult = $itemSearchService->getResults([
            'item' => SingleItem::getSearchFactory( $itemSearchOptions ),
            'variationAttributeMap' => VariationAttributeMap::getSearchFactory( $itemSearchOptions )
        ]);
        
        /** @var ShopBuilderRequest $shopBuilderRequest */
        $shopBuilderRequest = pluginApp(ShopBuilderRequest::class);
        if ($shopBuilderRequest->isShopBuilder())
        {
            /** @var VariationSearchResultFactory $searchResultFactory */
            $searchResultFactory = pluginApp(VariationSearchResultFactory::class);
            $itemResult['item'] = $searchResultFactory->fillSearchResults(
                $itemResult['item'],
                ResultFieldTemplate::get(ResultFieldTemplate::TEMPLATE_SINGLE_ITEM)
            );
        }
        
        /** @var VariationDataInterfaceContract $vdi */
        $vdi = app(VariationDataInterfaceContract::class);
        
        /** @var VariationDataInterfaceContext $vdiContext */
        $vdiContext = app(VariationDataInterfaceContext::class);
        $vdiContext->setParts([
            app(VariationBaseAttribute::class)
        ]);
    
        /** @var ClientFilter $clientFilter */
        $clientFilter = app(ClientFilter::class);
        $clientFilter->isVisibleForClient(pluginApp(Application::class)->getPlentyId());
    
        /** @var VariationBaseFilter $variationFilter */
        $variationFilter = app(VariationBaseFilter::class);
        $variationFilter->isActive();
        $variationFilter->hasItemId($itemId);
        $variationFilter->hasId($variationId);
    
        $lang = pluginApp(SessionStorageService::class)->getLang();
        if ( $lang === null )
        {
            $lang = pluginApp(SessionStorageService::class)->getLang();
        }
    
        $langMap = [
            'de' => 'german',
            'en' => 'english',
            'fr' => 'french',
            'bg' => 'bulgarian',
            'it' => 'italian',
            'es' => 'spanish',
            'tr' => 'turkish',
            'nl' => 'dutch',
            'pt' => 'portuguese',
            'nn' => 'norwegian',
            'ro' => 'romanian',
            'da' => 'danish',
            'se' => 'swedish',
            'cz' => 'czech',
            'ru' => 'russian',
        ];
    
        if ( array_key_exists( $lang, $langMap ) )
        {
            $lang = $langMap[$lang];
        }
        else
        {
            $lang = 'de';
        }
        /** @var TextFilter $textFilter */
        $textFilter = app(TextFilter::class);
        $textFilter->hasNameInLanguage( $lang, TextFilter::FILTER_ANY_NAME );
    
        /** @var PriceDetectService $priceDetectService */
        $priceDetectService = pluginApp( PriceDetectService::class );
        /** @var SalesPriceFilter $priceFilter */
        $priceFilter = app(SalesPriceFilter::class);
        $priceFilter->hasAtLeastOnePrice( $priceDetectService->getPriceIdsForCustomer() );
        
        $vdiContext
            ->addFilter($clientFilter)
            ->addFilter($variationFilter)
            ->addFilter($textFilter)
            ->addFilter($priceFilter);
        
        $vdiResult = $vdi->getResult($vdiContext);
        foreach($vdiResult->get() as $vdiVariation)
        {
            $test = true;
        }
        
        if(empty($itemResult['item']['documents']))
        {
            $this->getLogger(__CLASS__)->info(
                "IO::Debug.ItemController_itemNotFound",
                [
                    "slug"          => $slug,
                    "itemId"        => $itemId,
                    "variationId"   => $variationId
                ]
            );
            /** @var Response $response */
            $response = pluginApp(Response::class);
            $response->forceStatus(ResponseCode::NOT_FOUND);

            return $response;
        }
        else
        {
            return $this->renderTemplate(
                'tpl.item',
                $itemResult
            );
        }
    }

    /**
     * @param int $itemId
     * @param int $variationId
     * @return string
     */
    public function showItemWithoutName(int $itemId, $variationId = 0)
    {
        return $this->showItem("", $itemId, $variationId);
    }

    /**
     * @param int $itemId
     * @return string
     */
    public function showItemFromAdmin(int $itemId)
    {
        return $this->showItem("", $itemId, 0);
    }

    public function showItemOld($name = null, $itemId = null)
    {
        if(is_null($itemId))
        {
            $itemId = $name;
        }

        return $this->showItem("", (int)$itemId, 0);
    }

    public function showItemForCategory($category)
    {
        /** @var ItemListService $itemListService */
        $itemListService = pluginApp(ItemListService::class);
        $itemList = $itemListService->getItemList(ItemListService::TYPE_CATEGORY, $category->id, null, 1);
        if (count($itemList['documents']))
        {
            return $this->showItem(
                '',
                $itemList['documents'][0]['data']['item']['id'],
                $itemList['documents'][0]['data']['variation']['id'],
                $category
            );
        }

        return $this->showItem(
            '',
            0,
            0,
            $category
        );
    }
}
