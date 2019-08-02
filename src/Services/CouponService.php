<?php //strict

namespace IO\Services;

use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Modules\Item\VariationCategory\Contracts\VariationCategoryRepositoryContract;
use Plenty\Modules\Order\Coupon\Campaign\Contracts\CouponCampaignRepositoryContract;
use Plenty\Modules\Order\Coupon\Campaign\Models\CouponCampaign;

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
     * CouponService constructor.
     * @param CouponCampaignRepositoryContract $couponCampaignRepository
     */
    public function __construct(
        CouponCampaignRepositoryContract $couponCampaignRepository,
        BasketRepositoryContract $basketRepository
    )
    {
        $this->couponCampaignRepository = $couponCampaignRepository;
        $this->basketRepository = $basketRepository;
    }

    public function setCoupon(string $couponCode)
    {
        /**
         * @var BasketRepositoryContract $basket
         */
        $basket = pluginApp(BasketRepositoryContract::class);
        return $basket->setCouponCode($couponCode);
    }

    public function removeCoupon()
    {
        /**
         * @var BasketRepositoryContract $basket
         */
        $basket = pluginApp(BasketRepositoryContract::class);
        return $basket->removeCouponCode();
    }

    public function validateBasketItemDelete($basket, $basketItem) {
        if(strlen($basket->couponCode) > 0)
        {
            $campaign = $this->couponCampaignRepository->findByCouponCode($basket->couponCode);

            if($campaign instanceof CouponCampaign)
            {
                if($this->isCouponMinimalOrderOnDelete($basket, $basketItem, $campaign))
                {
                    // Check if the minimal order value is not met
                    $this->removeInvalidCoupon(301);
                }
                else if($this->isCouponValidForBasketItems($basket, $basketItem, $campaign))
                {
                    // Check if the coupon is still valid with the new basket (only coupon item removed?)
                    $this->removeInvalidCoupon(302);
                }
            }
        }
    }

    public function validateBasketItemUpdate($basket, $data, $basketItem) {
        if(strlen($basket->couponCode) > 0)
        {
            $campaign = $this->couponCampaignRepository->findByCouponCode($basket->couponCode);

            if($campaign instanceof CouponCampaign)
            {
                if($this->isCouponMinimalOrderOnUpdate($basket, $data, $basketItem, $campaign))
                {
                    // Check if the minimal order value is not met
                    $this->removeInvalidCoupon(301);
                }
            }
        }
    }

    /**
     * @param int $code Infocode for notification
     */
    private function removeInvalidCoupon($code)
    {
        $this->basketRepository->removeCouponCode();
        pluginApp(NotificationService::class)->info('CouponValidation', $code);
    }

    private function isCouponMinimalOrderOnUpdate($basket, $data, $basketItem, $campaign): bool
    {
        // $basket->basketAmount is basket amount minus coupon value
        // $basket->couponDiscount is negative
        $minimalOrder = $campaign->minOrderValue;
        $quantityChange = $basketItem['quantity'] - $data['quantity'];
        $newBasketAmount = (( $basket->basketAmount - $basket->couponDiscount ) - ($basketItem['price'] * $quantityChange));

        return $minimalOrder > $newBasketAmount;
    }

    private function isCouponMinimalOrderOnDelete($basket, $basketItem, $campaign): bool
    {
        // $basket->basketAmount is basket amount minus coupon value
        // $basket->couponDiscount is negative
        $minimalOrder = $campaign->minOrderValue;
        $newBasketAmount = (( $basket->basketAmount - $basket->couponDiscount ) - ($basketItem['price'] * $basketItem['quantity']));
        return $minimalOrder > $newBasketAmount;
    }

    private function isCouponValidForBasketItems($basket, $basketItem, $campaign): bool
    {
        /**
         * @var VariationCategoryRepositoryContract $variationCategoryRepository
         */
        $variationCategoryRepository = pluginApp(VariationCategoryRepositoryContract::class);

        $authHelper = pluginApp(AuthHelper::class);
        $variationId = $basketItem['variationId'];
        $categories = $authHelper->processUnguarded(function () use ($variationId, $variationCategoryRepository) {
            return $variationCategoryRepository->findByVariationIdWithInheritance($variationId);
        });

        $categoryIds = [];
        $categories->each(function ($category) use (&$categoryIds) {
            $categoryIds[] = $category->categoryId;
        });

        $campaignItems = $campaign->references->where('referenceType', 'category')->whereIn('value', $categoryIds);
        if (count($campaignItems) == 0) {
            $campaignItems = $campaign->references->where('referenceType', 'item')->where('value',
                $basketItem['itemId']);
        }

        if (count($campaignItems)) {
            $matchingBasketItems = $basket->basketItems->where('itemId', $basketItem['itemId']);

            $basketItems = $basket->basketItems->where('itemId', '!=', $basketItem['itemId']);
            $noOtherCouponBasketItemsExists = true;

            $basketItems->each(function ($item) use (
                &$noOtherCouponBasketItemsExists,
                $campaign,
                $authHelper,
                $variationCategoryRepository
            ) {
                $variationId = $item->variationId;
                $categories = $authHelper->processUnguarded(function () use (
                    $variationId,
                    $variationCategoryRepository
                ) {
                    return $variationCategoryRepository->findByVariationIdWithInheritance($variationId);
                });
                $categoryIds = [];
                $categories->each(function ($category) use (&$categoryIds) {
                    $categoryIds[] = $category->categoryId;
                });

                $campaignItems = $campaign->references->where('referenceType', 'category')->whereIn('value',
                    $categoryIds);
                if (count($campaignItems) == 0) {
                    $campaignItems = $campaign->references->where('referenceType', 'item')->where('value',
                        $item->itemId);
                }

                if (count($campaignItems)) {
                    $noOtherCouponBasketItemsExists = false;
                    return false;
                }
            });

            return count($matchingBasketItems) <= 1 && $noOtherCouponBasketItemsExists;
        }

        return false;
    }
}
