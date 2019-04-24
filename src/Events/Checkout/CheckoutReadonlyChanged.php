<?php

namespace IO\Events\Checkout;

class CheckoutReadonlyChanged
{
    private $isReadonly = false;

    public function __construct($isReadonly)
    {
        $this->isReadonly = $isReadonly;
    }

    public function isReadOnlyCheckout()
    {
        return $this->isReadonly;
    }
}