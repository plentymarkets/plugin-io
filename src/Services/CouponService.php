<?php //strict

namespace IO\Services;

use Illuminate\Database\Eloquent\Collection;
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
     * Remove a coupon and throw a notification
     * @param int $code Infocode for notification
     */
    private function removeInvalidCoupon($code)
    {
        $this->basketRepository->removeCouponCode();
        pluginApp(NotificationService::class)->info('CouponValidation', $code);
    }

    private function isCouponMinimalOrderOnUpdate($basket, $data, $basketItem, $campaign): bool
    {
        // Early exit if the quantity was raised ( careful: quantityChange is negative when raised )
        $quantityChange = $basketItem['quantity'] - $data['quantity'];
        if($quantityChange <= 0) {
            return false;
        }

        // Get the normal basket items (not associated with the coupon)
        $normalBasketItems = $this->getNormalBasketItems($basket, $campaign);
        // Early exit if itemChanged ($data) is a normal item
        foreach($normalBasketItems as $item) {
            if($item->itemId == $data['itemId']) {
                return false;
            }
        }

        // Remove couponDiscount and normal items from basketAmount
        $basketAmountWithoutCoupon = $basket->basketAmount - $basket->couponDiscount;
        $basketAmountWithoutNormal = $basketAmountWithoutCoupon;

        foreach($normalBasketItems as $item) {
            $basketAmountWithoutNormal -= $item->price * $item->quantity;
        }

        // Subtract the changed quantity from the basketAmountWithoutNormal
        $basketAmountFinal = $basketAmountWithoutNormal - ($data['price'] * $quantityChange);

        return $campaign->minOrderValue > $basketAmountFinal;
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

        // Get the categories of this variation
        $authHelper = pluginApp(AuthHelper::class);
        $variationId = $basketItem['variationId'];
        $categories = $authHelper->processUnguarded(function () use ($variationId, $variationCategoryRepository) {
            return $variationCategoryRepository->findByVariationIdWithInheritance($variationId);
        });

        // Transform categories in an array of category ids
        $categoryIds = [];
        $categories->each(function ($category) use (&$categoryIds) {
            $categoryIds[] = $category->categoryId;
        });

        // Get items associated with the campaign via categoryid
        $campaignItems = $campaign->references->where('referenceType', 'category')->whereIn('value', $categoryIds);
        if (count($campaignItems) == 0) {
            // In case no category was linked, use item refs directly
            $campaignItems = $campaign->references->where('referenceType', 'item')->where('value',
                $basketItem['itemId']);
        }

        // If this block is not entered, the coupon has no items associated
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

    /**
     * Get all items in basket, which are associated with the current campaign
     * @param $basket
     * @param $campaign
     * @return array
     */
    private function getNormalBasketItems($basket, $campaign)
    {
        /**
         * @var Collection $basketItems
         */
        $basketItems = $basket->basketItems;
        $campaignBasketItems = [];

        $variationCategoryRepository = pluginApp(VariationCategoryRepositoryContract::class);
        $authHelper = pluginApp(AuthHelper::class);

        $basketItems->each(function ($item) use (
            &$campaignBasketItems,
            $campaign,
            $authHelper,
            $variationCategoryRepository
        )
        {
            $variationId = $item->variationId;
            $categories = $authHelper->processUnguarded(function () use (
                $variationId,
                $variationCategoryRepository
            )
            {
                return $variationCategoryRepository->findByVariationIdWithInheritance($variationId);
            });

            $categoryIds = [];
            $categories->each(function ($category) use (&$categoryIds) {
                $categoryIds[] = $category->categoryId;
            });

            $campaignItems = $campaign->references->where('referenceType', 'category')->whereIn('value',
                $categoryIds);
            if (count($campaignItems) === 0) {
                $campaignItems = $campaign->references->where('referenceType', 'item')->where('value',
                    $item->itemId);
            }
            if (count($campaignItems) === 0) {
                $campaignBasketItems[] = $item;
            }
        });

        return $campaignBasketItems;
    }
}
