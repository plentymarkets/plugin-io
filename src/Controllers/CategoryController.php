<?php //strict

namespace IO\Controllers;

use IO\Api\ResponseCode;
use IO\Helper\RouteConfig;
use IO\Guards\AuthGuard;
use IO\Services\SessionStorageService;
use IO\Services\UrlService;
use Plenty\Modules\ShopBuilder\Helper\ShopBuilderRequest;
use Plenty\Plugin\Application;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Log\Loggable;

/**
 * Class CategoryController
 * @package IO\Controllers
 */
class CategoryController extends LayoutController
{
    use Loggable;

    /**
     * Prepare and render the data for categories
     * @param string $lvl1 Level 1 of category url. Will be null at root page
     * @param string $lvl2 Level 2 of category url.
     * @param string $lvl3 Level 3 of category url.
     * @param string $lvl4 Level 4 of category url.
     * @param string $lvl5 Level 5 of category url.
     * @param string $lvl6 Level 6 of category url.
     * @return string
     */
    public function showCategory(
        $lvl1 = null,
        $lvl2 = null,
        $lvl3 = null,
        $lvl4 = null,
        $lvl5 = null,
        $lvl6 = null)
    {
        /** @var SessionStorageService $sessionService */
        $sessionService  = pluginApp(SessionStorageService::class);
        $lang = $sessionService->getLang();
        $webstoreId = pluginApp(Application::class)->getWebstoreId();

        /** @var Request $request */
        $request = pluginApp(Request::class);
        if ($request->get('isContentBuilder', false) && $request->get('contentType', '') === 'singleitem')
        {
            return $this->renderTemplate(
                'tpl.item',
                [
                    'item' => json_decode('{"took":7,"total":1,"maxScore":0,"documents":[{"score":0,"id":"1007","data":{"filter":{"hasActiveChildren":false,"hasManufacturer":true,"isSalable":true},"images":{"all":[{"path":"documents\/image\/i10\/109-Barhocker-White-SanFrancisco.jpg","urlPreview":"http:\/\/master.plentymarkets.com\/documents\/image\/i10\/109-Barhocker-White-SanFrancisco.jpg","position":0,"urlMiddle":"http:\/\/master.plentymarkets.com\/documents\/image\/i10\/109-Barhocker-White-SanFrancisco.jpg","url":"http:\/\/master.plentymarkets.com\/documents\/image\/i10\/109-Barhocker-White-SanFrancisco.jpg","urlSecondPreview":"http:\/\/master.plentymarkets.com\/documents\/image\/i10\/109-Barhocker-White-SanFrancisco.jpg"}],"variation":[]},"item":{"storeSpecial":null,"customsTariffNumber":"","add_cms_page":"0","condition":{"names":{"name":"Neu","lang":"de"}},"producingCountry":{"names":{"name":"Deutschland","lang":"de"}},"ageRestriction":50,"id":109,"manufacturer":{"externalName":"A & C Design"}},"unit":{"unitOfMeasurement":"C62","names":{"name":"St\u00fcck","lang":"de"},"content":1},"texts":{"name3":"N \/ A","keywords":"","technicalData":"","description":"<p>Der Barhocker White SanFrancisco zeichnet sich durch seine besondere Optik in Hochglanz aus. Durch seine stufenlos einstellbare Gasdruckfeder ist er f\u00fcr jede K\u00f6rpergr\u00f6\u00dfe geeignet. Ein weiteres Ausstattungsmerkmal ist die sehr stabile Fu\u00dfbank. Der Chrom-Fu\u00df rundet das Gesamtbild des Barhockers stimmig ab.<\/p>\n<ul>\t<li>Farbe Sitzfl\u00e4che: Wei\u00df<\/li>\t<li>Gestell: Chrom<\/li>\t<li>Funktion: Stufenlos h\u00f6henverstellbar, durch Gasdruckfeder 360\u00b0 drehbar<\/li><\/ul>","shortDescription":"","lang":"de","name2":"N \/ A","name1":"Barhocker White SanFrancisco","metaDescription":"","urlPath":"wohnzimmer\/sessel-hocker\/barhocker-white-sanfrancisco"},"defaultCategories":[{"linklist":true,"manually":false,"level":2,"plentyId":134,"parentCategoryId":16,"id":17,"right":"all","type":"item","sitemap":true}],"variation":{"intervalOrderQuantity":1,"releasedAt":null,"weightNetG":0,"minimumOrderQuantity":1,"externalId":"","availability":{"names":{"name":"Sofort versandfertig, Lieferzeit 48h","lang":"de"},"id":1},"bundleType":null,"lengthMM":0,"widthMM":0,"number":"103","maximumOrderQuantity":null,"weightG":0,"name":"","model":"","id":1007,"mayShowUnitPrice":true,"unitCombinationId":1,"heightMM":0},"properties":[{"property":{"surcharge":2.5210084033613,"names":{"name":"Druck R\u00fcckennummer","description":"Bitte teilen Sie uns NACH dem Kauf die Spielernummern mit. Geben Sie bitte auch immer an welche Nummer f\u00fcr welche Gr\u00f6\u00dfe bestimmt ist. ","lang":"de"},"isShownAtCheckout":true,"isShownOnItemList":true,"valueType":"empty","isOderProperty":true,"isShownOnItemPage":true,"id":1},"group":{"names":{"name":"Druckoptionen","description":"Wenn sie verschiedene Gr\u00f6\u00dfen mit Druck bestellen wollen, w\u00e4hlen Sie bitte zuerst blablabla.","lang":"de"},"id":1,"orderPropertyGroupingType":"none"},"surcharge":0},{"property":{"surcharge":3.3613445378151,"names":{"name":"Mein Radio","description":"Hier ist die Beschreibung.","lang":"de"},"isShownAtCheckout":true,"isShownOnItemList":true,"valueType":"empty","isOderProperty":true,"isShownOnItemPage":true,"id":4},"group":{"names":{"name":"Druckoptionen","description":"Wenn sie verschiedene Gr\u00f6\u00dfen mit Druck bestellen wollen, w\u00e4hlen Sie bitte zuerst blablabla.","lang":"de"},"id":1,"orderPropertyGroupingType":"none"},"surcharge":0},{"property":{"surcharge":4.1596638655462,"names":{"name":"Verpackung","description":"","lang":"de"},"isShownAtCheckout":true,"isShownOnItemList":true,"valueType":"selection","isOderProperty":true,"isShownOnItemPage":true,"id":7,"selectionValues":{"3":{"name":"Karton","description":""},"4":{"name":"Plastik lol","description":""}}},"surcharge":0}],"variationPropertyGroups":[],"prices":{"default":{"price":{"value":66.386554621849,"formatted":"66,39\u00a0EUR"},"unitPrice":{"value":66.386554621849,"formatted":"66,39\u00a0EUR"},"basePrice":"66,39\u00a0EUR \/ St\u00fcck","baseLot":1,"baseUnit":"C62","baseSinglePrice":66.386554621849,"minimumOrderQuantity":1,"contactClassDiscount":{"percent":0,"amount":0},"categoryDiscount":{"percent":0,"amount":0},"currency":"EUR","vat":{"id":0,"value":19},"isNet":true,"data":{"salesPriceId":1,"price":79,"priceNet":66.386554621849,"basePrice":79,"basePriceNet":66.386554621849,"unitPrice":79,"unitPriceNet":66.386554621849,"customerClassDiscountPercent":0,"customerClassDiscount":0,"customerClassDiscountNet":0,"categoryDiscountPercent":0,"categoryDiscount":0,"categoryDiscountNet":0,"vatId":0,"vatValue":19,"currency":"EUR","interval":"none","conversionFactor":1,"minimumOrderQuantity":"1.00","updatedAt":"2016-09-05 13:25:20"}},"rrp":{"price":{"value":84.033613445378,"formatted":"84,03\u00a0EUR"},"unitPrice":{"value":84.033613445378,"formatted":"84,03\u00a0EUR"},"basePrice":"84,03\u00a0EUR \/ St\u00fcck","baseLot":1,"baseUnit":"C62","baseSinglePrice":84.033613445378,"minimumOrderQuantity":0,"contactClassDiscount":{"percent":0,"amount":0},"categoryDiscount":{"percent":0,"amount":0},"currency":"EUR","vat":{"id":0,"value":19},"isNet":true,"data":{"salesPriceId":2,"price":100,"priceNet":84.033613445378,"basePrice":100,"basePriceNet":84.033613445378,"unitPrice":100,"unitPriceNet":84.033613445378,"customerClassDiscountPercent":0,"customerClassDiscount":0,"customerClassDiscountNet":0,"categoryDiscountPercent":0,"categoryDiscount":0,"categoryDiscountNet":0,"vatId":0,"vatValue":19,"currency":"EUR","interval":"none","conversionFactor":1,"minimumOrderQuantity":"0.00","updatedAt":"2016-09-05 13:25:20"}},"specialOffer":null,"graduatedPrices":[{"price":{"value":66.386554621849,"formatted":"66,39\u00a0EUR"},"unitPrice":{"value":66.386554621849,"formatted":"66,39\u00a0EUR"},"basePrice":"66,39\u00a0EUR \/ St\u00fcck","baseLot":1,"baseUnit":"C62","baseSinglePrice":66.386554621849,"minimumOrderQuantity":1,"contactClassDiscount":{"percent":0,"amount":0},"categoryDiscount":{"percent":0,"amount":0},"currency":"EUR","vat":{"id":0,"value":19},"isNet":true,"data":{"salesPriceId":1,"price":79,"priceNet":66.386554621849,"basePrice":79,"basePriceNet":66.386554621849,"unitPrice":79,"unitPriceNet":66.386554621849,"customerClassDiscountPercent":0,"customerClassDiscount":0,"customerClassDiscountNet":0,"categoryDiscountPercent":0,"categoryDiscount":0,"categoryDiscountNet":0,"vatId":0,"vatValue":19,"currency":"EUR","interval":"none","conversionFactor":1,"minimumOrderQuantity":"1.00","updatedAt":"2016-09-05 13:25:20"}}]},"facets":[],"attributes":[]}}],"success":true,"error":null}', true),
                    'shopBuilderCategory' => $this->categoryRepo->findCategoryByUrl($lvl1, $lvl2, $lvl3, $lvl4, $lvl5, $lvl6, $webstoreId, $lang)
                ]
            );
        }

        return $this->renderCategory(
            $this->categoryRepo->findCategoryByUrl($lvl1, $lvl2, $lvl3, $lvl4, $lvl5, $lvl6, $webstoreId, $lang)
        );
	}

	public function showCategoryById($categoryId)
    {
        /** @var SessionStorageService $sessionService */
        $sessionService  = pluginApp(SessionStorageService::class);
        $lang = $sessionService->getLang();

        return $this->renderCategory(
            $this->categoryRepo->get( $categoryId, $lang )
        );
    }

    public function redirectToCategory( $categoryUrl )
    {
        // Check if category can be displayed
        $categoryLevels = array_filter(
            explode("/", $categoryUrl),
            function($lvl)
            {
                return strlen($lvl);
            }
        );
        $categoryResponse = $this->showCategory(
            $categoryLevels[0],
            $categoryLevels[1],
            $categoryLevels[2],
            $categoryLevels[3],
            $categoryLevels[4],
            $categoryLevels[5]
        );
        if (!($categoryResponse instanceof Response && $categoryResponse->status() == ResponseCode::NOT_FOUND))
        {
            // category cannot be displayed. Return 404
            return $categoryResponse;
        }

        /** @var UrlService $urlService */
        $urlService = pluginApp(UrlService::class);
        return $urlService->redirectTo($categoryUrl);
    }

	private function renderCategory($category)
    {
        /** @var Request $request */
        $request = pluginApp(Request::class);

        if ($category === null || (($category->clients->count() == 0 || $category->details->count() == 0) && !$this->app->isAdminPreview()))
        {
            $this->getLogger(__CLASS__)->warning(
                "IO::Debug.CategoryController_cannotDisplayCategory",
                [
                    "category" => $category,
                    "clientCount" => ($category !== null ? $category->clients->count() : 0),
                    "detailCount" => ($category !== null ? $category->details->count() : 0),
                    "isAdminPreview" => $this->app->isAdminPreview()
                ]
            );

            /** @var Response $response */
            $response = pluginApp(Response::class);
            $response->forceStatus(ResponseCode::NOT_FOUND);

            return $response;
        }

        $this->categoryService->setCurrentCategory($category);
        if ($this->categoryService->isHidden($category->id)) {
            $guard = pluginApp(AuthGuard::class);
            $guard->assertOrRedirect( true, '/login');
        }

        /** @var ShopBuilderRequest $shopBuilderRequest */
        $shopBuilderRequest = pluginApp(ShopBuilderRequest::class);

        if ( RouteConfig::getCategoryId( RouteConfig::CHECKOUT ) === $category->id || $shopBuilderRequest->getPreviewContentType() === 'checkout')
        {
            $this->getLogger(__CLASS__)->info(
                "IO::Debug.CategoryController_showCheckoutCategory",
                [
                    "category" => $category,
                    "previewContentType" => $shopBuilderRequest->getPreviewContentType()
                ]
            );
            RouteConfig::overrideCategoryId(RouteConfig::CHECKOUT, $category->id);

            /** @var CheckoutController $checkoutController */
            $checkoutController = pluginApp(CheckoutController::class);
            return $checkoutController->showCheckout( $category );
        }

        if ( RouteConfig::getCategoryId( RouteConfig::MY_ACCOUNT ) === $category->id || $shopBuilderRequest->getPreviewContentType() === 'myaccount')
        {
            $this->getLogger(__CLASS__)->info(
                "IO::Debug.CategoryController_showMyAccountCategory",
                [
                    "category" => $category,
                    "previewContentType" => $shopBuilderRequest->getPreviewContentType()
                ]
            );
            RouteConfig::overrideCategoryId(RouteConfig::MY_ACCOUNT, $category->id);

            /** @var MyAccountController $myAccountController */
            $myAccountController = pluginApp(MyAccountController::class);
            return $myAccountController->showMyAccount( $category );
        }

        return $this->renderTemplate(
            "tpl.category." . $category->type,
            [
                'category'      => $category,
                'sorting'       => $request->get('sorting', null),
                'itemsPerPage'  => $request->get('items', null),
                'page'          => $request->get('page', null),
                'facets'        => $request->get('facets', '')
            ]
        );
    }
}
