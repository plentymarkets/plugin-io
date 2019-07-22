<?php

namespace IO\Controllers;

use IO\Api\ResponseCode;
use IO\Helper\VDIToElasticSearchMapper;
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
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationAttributeValueAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationCategoryAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationImageAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationSalesPriceAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationUnitAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Variation;
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


        $start = microtime(true);

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


        $end = microtime(true);
        $executionTime = $end - $start;
        $this->getLogger('Performance')->error('ES: '. $executionTime . ' Sekunden');


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

        $vdiResult = $this->loadItemDataVdi($itemId, $variationId);

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

    private function loadItemDataVdi($itemId, $variationId)
    {
        $start = microtime(true);

        /** @var VariationDataInterfaceContract $vdi */
        $vdi = app(VariationDataInterfaceContract::class);

        /** @var VariationBaseAttribute $basePart */
        $basePart = app(VariationBaseAttribute::class);
        $basePart->addLazyLoadParts(
            VariationBaseAttribute::DESCRIPTION,
            VariationBaseAttribute::AVAILABILITY,
            VariationBaseAttribute::CROSS_SELLING,
            VariationBaseAttribute::IMAGE,
            VariationBaseAttribute::ITEM,
            VariationBaseAttribute::PROPERTY,
            VariationBaseAttribute::SERIAL_NUMBER,
            VariationBaseAttribute::STOCK
        );

        /** @var VariationSalesPriceAttribute $pricePart */
        $pricePart = app(VariationSalesPriceAttribute::class);
        $pricePart->addLazyLoadParts(VariationSalesPriceAttribute::SALES_PRICE);
        
        /** @var VariationUnitAttribute $unitPart */
        $unitPart = app(VariationUnitAttribute::class);
        $unitPart->addLazyLoadParts(VariationUnitAttribute::UNIT);
        
        /** @var VariationImageAttribute $imagePart */
        $imagePart = app(VariationImageAttribute::class);
        
        /** @var VariationAttributeValueAttribute $attriuteValuePart */
        $attributeValuePart = app(VariationAttributeValueAttribute::class);
        $attributeValuePart->addLazyLoadParts(
            VariationAttributeValueAttribute::ATTRIBUTE,
            VariationAttributeValueAttribute::VALUE
        );

        /** @var VariationDataInterfaceContext $vdiContext */
        $vdiContext = app(VariationDataInterfaceContext::class);
        $vdiContext->setParts([
            $basePart,
            $pricePart,
            $unitPart,
            $imagePart,
            $attributeValuePart
        ]);

        /** @var ClientFilter $clientFilter */
        $clientFilter = app(ClientFilter::class);
        $clientFilter->isVisibleForClient(pluginApp(Application::class)->getPlentyId());

        /** @var VariationBaseFilter $variationFilter */
        $variationFilter = app(VariationBaseFilter::class);
        $variationFilter->isActive();
        $variationFilter->hasItemId($itemId);
        if($variationId > 0)
        {
            $variationFilter->hasId($variationId);
        }

        $lang = pluginApp(SessionStorageService::class)->getLang();

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

        /**
         * @var VDIToElasticSearchMapper $mappingHelper
         */
        $mappingHelper = pluginApp(VDIToElasticSearchMapper::class);
        $mappedData = $mappingHelper->map($vdiResult, ResultFieldTemplate::load(ResultFieldTemplate::TEMPLATE_SINGLE_ITEM));

        $end = microtime(true);
        $executionTime = $end - $start;
        $this->getLogger('Performance')->error('VDI: '. $executionTime . ' Sekunden');
        
        return $mappedData;
    }
}
