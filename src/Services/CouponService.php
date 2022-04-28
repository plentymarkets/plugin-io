<?php //strict

namespace IO\Services;

use Illuminate\Database\Eloquent\Collection;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Modules\Item\VariationCategory\Contracts\VariationCategoryRepositoryContract;
use Plenty\Modules\Order\Coupon\Campaign\Contracts\CouponCampaignRepositoryContract;
use Plenty\Modules\Order\Coupon\Campaign\Models\CouponCampaign;


/**
 * Service Class CouponService
 *
 * This service class contains functions related to coupons in the basket.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 */
class CouponService
{
    /**
     * @var CouponCampaignRepositoryContract
     */
    private $couponCampaignRepository;

    /**
     * @var BasketRepositoryContract $basketRepository
     */
    private $basketRepository;

    /**
     * @var VariationCategoryRepositoryContract $variationCategoryRepository
     */
    private $variationCategoryRepository;

    /**
     * @var AuthHelper $authHelper
     */
    private $authHelper;

    // Info constants
    const BELOW_MINIMAL_ORDER_VALUE = 301;
    const NO_VALID_ITEM_IN_BASKET = 302;

    /**
     * CouponService constructor.
     * @param CouponCampaignRepositoryContract $couponCampaignRepository
     * @param BasketRepositoryContract $basketRepository
     * @param VariationCategoryRepositoryContract $variationCategoryRepository
     * @param AuthHelper $authHelper
     */
    public function __construct(
        CouponCampaignRepositoryContract $couponCampaignRepository,
        BasketRepositoryContract $basketRepository,
        VariationCategoryRepositoryContract $variationCategoryRepository,
        AuthHelper $authHelper
    )
    {
        $this->couponCampaignRepository = $couponCampaignRepository;
        $this->basketRepository = $basketRepository;
        $this->variationCategoryRepository = $variationCategoryRepository;
        $this->authHelper = $authHelper;
    }

    /**
     * Setter for couponCode
     * @param string $couponCode Code representing a coupon
     * @return Basket
     */
    public function setCoupon(string $couponCode)
    {
        return $this->basketRepository->setCouponCode($couponCode);
    }

    /**
     * Remove the coupon code from the basket and optionally throw a notification with a code.
     * @param int|null $code Optional: Error code for notification
     * @return Basket
     */
    public function removeCoupon($code = null)
    {
        $response = $this->basketRepository->removeCouponCode();

        if ($code != null) {
            /** @var NotificationService $notificationService */
            $notificationService = pluginApp(NotificationService::class);
            $notificationService->info('CouponValidation', $code);
        }

        return $response;
    }

    /**
     * Get a basket with applied coupon discounts
     * @param array $basket The basket
     * @return array
     */
    public function checkCoupon($basket): array
    {
        if (isset($basket['couponCode']) && strlen($basket['couponCode']) > 0) {
            $campaign = $this->couponCampaignRepository->findByCouponCode($basket['couponCode']);

            if ($campaign instanceof CouponCampaign) {
                if ($campaign->couponType == CouponCampaign::COUPON_TYPE_SALES) {
                    $basket['openAmount'] = $basket['basketAmount'];
                    $basket['basketAmount'] -= $basket['couponDiscount'];
                    $basket['basketAmountNet'] -= $basket['couponDiscount'];
                }
                $basket['couponCampaignType'] = $campaign->couponType;
            }
        }
        return $basket;
    }

    /**
     * Validate the basket for the coupon, and remove the coupon if invalid
     * @param Basket $basket The basket
     * @param array $basketItem Current basketItem
     * @deprecated since 5.0.9. Validation is handled b the plentymarkets core from now.
     */
    public function validateBasketItemDelete($basket, $basketItem)
    {
        if (strlen($basket->couponCode) > 0) {
            $campaign = $this->couponCampaignRepository->findByCouponCode($basket->couponCode);

            if ($campaign instanceof CouponCampaign) {
                if ($this->isCouponMinimalOrderOnDelete($basket, $basketItem, $campaign)) {
                    // Check if the minimal order value is not met
                    $this->removeCoupon(CouponService::BELOW_MINIMAL_ORDER_VALUE);
                } elseif ($this->isCouponValidForBasketItems($basket, $basketItem, $campaign)) {
                    // Check if the coupon is still valid with the new basket (only coupon item removed?)
                    $this->removeCoupon(CouponService::NO_VALID_ITEM_IN_BASKET);
                }
            }
        }
    }

    /**
     * Validate the basket for the coupon, and remove the coupon if invalid
     * @param Basket $basket The Basket
     * @param array $data New basketItem
     * @param array $basketItem Current basketItem
     * @deprecated since 5.0.9. Validation is handled b the plentymarkets core from now.
     */
    public function validateBasketItemUpdate($basket, $data, $basketItem)
    {
        if (strlen($basket->couponCode) > 0) {
            $campaign = $this->couponCampaignRepository->findByCouponCode($basket->couponCode);

            if ($campaign instanceof CouponCampaign &&
                $this->isCouponMinimalOrderOnUpdate($basket, $data, $basketItem, $campaign)
            ) {
                // Check if the minimal order value is not met
                $this->removeCoupon(CouponService::BELOW_MINIMAL_ORDER_VALUE);
            }
        }
    }

    /**
     * Checks, if the coupon's discount changes shipping costs.
     * @param CouponCampaign $campaign Contains information about the campaign
     * @return bool
     */
    public function effectsOnShippingCosts(CouponCampaign $campaign)
    {
        if ($campaign->couponType != CouponCampaign::COUPON_TYPE_SALES && (($campaign->includeShipping && $campaign->discountType == CouponCampaign::DISCOUNT_TYPE_FIXED) ||
            $campaign->discountType == CouponCampaign::DISCOUNT_TYPE_SHIPPING)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks, if the minimal order value is still reached by the new basket
     * @param Basket $basket The Basket
     * @param array $data Pseudo-BasketItem
     * @param array $basketItem Current BasketItem
     * @param CouponCampaign $campaign Contains information about the campaign
     * @return bool
     */
    private function isCouponMinimalOrderOnUpdate($basket, $data, $basketItem, $campaign): bool
    {
        // Early exit if the quantity was raised ( careful: quantityChange is negative when raised )
        $quantityChange = $basketItem['quantity'] - $data['quantity'];
        if ($quantityChange <= 0) {
            return false;
        }

        // Get the normal basket items (not associated with the coupon)
        $normalBasketItems = $this->getNormalBasketItems($basket, $campaign);
        // Early exit if itemChanged ($data) is a normal item
        foreach ($normalBasketItems as $item) {
            if ($item->itemId == $data['itemId']) {
                return false;
            }
        }

        // Remove couponDiscount and normal items from basketAmount
        $basketAmountWithoutCoupon = $basket->basketAmount - $basket->couponDiscount;
        $basketAmountWithoutNormal = $basketAmountWithoutCoupon;

        foreach ($normalBasketItems as $item) {
            $basketAmountWithoutNormal -= $item->price * $item->quantity;
        }

        // Subtract the changed quantity from the basketAmountWithoutNormal
        $basketAmountFinal = $basketAmountWithoutNormal - ($basketItem['price'] * $quantityChange);

        return $campaign->minOrderValue >= $basketAmountFinal;
    }

    /**
     * Checks, if the minimal order value is still reached by the new basket
     * @param Basket $basket The basket
     * @param array $basketItem Current BasketItem
     * @param CouponCampaign $campaign Contains information about the campaign
     * @return bool
     */
    private function isCouponMinimalOrderOnDelete($basket, $basketItem, $campaign): bool
    {
        // $basket->basketAmount is basket amount minus coupon value
        // $basket->couponDiscount is negative
        return $campaign->minOrderValue > (($basket->basketAmount - $basket->couponDiscount) - ($basketItem['price'] * $basketItem['quantity']));
    }

    /**
     * Checks if at least one more item in the basket is valid for the coupon
     * Validity requires there to be > 1 valid items
     * @param Basket $basket The basket array
     * @param array $basketItem Current BasketItem
     * @param CouponCampaign $campaign Contains information about the campaign
     * @return bool
     */
    private function isCouponValidForBasketItems($basket, $basketItem, $campaign): bool
    {
        $variationId = $basketItem['variationId'];
        $categoryIds = $this->getCategoryIds($variationId);

        // Get items associated with the campaign via categoryid
        $campaignItems = $campaign->references->where('referenceType', 'category')->whereIn('value', $categoryIds);
        if (count($campaignItems) == 0) {
            // In case no category was linked, use item refs directly
            $campaignItems = $campaign->references->where('referenceType', 'item')->where(
                'value',
                $basketItem['itemId']
            );
        }

        // If this block is not entered, the coupon has no items associated
        if (count($campaignItems)) {
            $matchingBasketItems = $basket->basketItems->where('itemId', $basketItem['itemId']);

            $basketItems = $basket->basketItems->where('itemId', '!=', $basketItem['itemId']);
            $noOtherCouponBasketItemsExists = true;

            $basketItems->each(
                function ($item) use (
                    &$noOtherCouponBasketItemsExists,
                    $campaign
                ) {
                    $variationId = $item->variationId;
                    $categoryIds = $this->getCategoryIds($variationId);

                    $campaignItems = $campaign->references->where('referenceType', 'category')->whereIn(
                        'value',
                        $categoryIds
                    );
                    if (count($campaignItems) == 0) {
                        $campaignItems = $campaign->references->where('referenceType', 'item')->where(
                            'value',
                            $item->itemId
                        );
                    }

                    if (count($campaignItems)) {
                        $noOtherCouponBasketItemsExists = false;
                        return false;
                    }
                }
            );

            return count($matchingBasketItems) <= 1 && $noOtherCouponBasketItemsExists;
        }

        return false;
    }

    /**
     * Get all items in basket, which are NOT associated with the current campaign
     * @param Basket $basket The Basket
     * @param CouponCampaign $campaign Contains information about the campaign
     * @return array
     */
    private function getNormalBasketItems($basket, $campaign)
    {
        /**
         * @var Collection $basketItems
         */
        $basketItems = $basket->basketItems;
        $campaignBasketItems = [];

        $basketItems->each(
            function ($item) use (
                &$campaignBasketItems,
                $campaign
            ) {
                $variationId = $item->variationId;
                $categoryIds = $this->getCategoryIds($variationId);

                $campaignItems = $campaign->references->where('referenceType', 'category')->whereIn(
                    'value',
                    $categoryIds
                );
                if (count($campaignItems) === 0) {
                    $campaignItems = $campaign->references->where('referenceType', 'item')->where(
                        'value',
                        $item->itemId
                    );
                }
                if (count($campaignItems) === 0) {
                    $campaignBasketItems[] = $item;
                }
            }
        );

        return $campaignBasketItems;
    }

    /**
     * Get the categoryIds of an variationId
     * @param int $variationId
     * @return array
     * @throws \Throwable
     */
    private function getCategoryIds($variationId)
    {
        $variationCategories = $this->authHelper->processUnguarded(
            function () use ($variationId) {
                return $this->variationCategoryRepository->findByVariationIdWithInheritance($variationId);
            }
        );

        // Transform categories in an array of category ids
        $variationCategoryIds = [];
        $variationCategories->each(
            function ($category) use (&$variationCategoryIds) {
                $variationCategoryIds[] = $category->categoryId;
            }
        );

        // Collect sub-category ids
        /** @var CategoryService $categoryService */
        $categoryService = pluginApp(CategoryService::class);
        $categoryIds = $variationCategoryIds;

        foreach ($variationCategoryIds as $categoryId) {
            $category = $categoryService->get($categoryId);
            if ($category->branch !== null) {
                $branchData = $category->branch->toArray();

                for ($i = 6; $i > 0; $i--) {
                    if ($branchData['category' . $i . 'Id'] !== null && $branchData['category' . $i . 'Id'] > 0) {
                        $categoryIds[] = $branchData['category' . $i . 'Id'];
                    }
                }
            }
        }

        return array_unique($categoryIds);
    }
}
