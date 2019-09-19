<?php //strict

namespace IO\Services;

use IO\Services\Basket\Factories\BasketResultFactory;

class FakerService
{
    /**
     * Get a faked basket for shopbuilder basket templates
     */
    public function getBasket()
    {
        /** @var BasketResultFactory $basketResultFactory */
        $basketResultFactory = pluginApp(BasketResultFactory::class);
        return $basketResultFactory->fillBasketResult();
    }
}
