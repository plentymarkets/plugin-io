<?php

namespace IO\Helper;

class ContentCacheHelper
{
    private $blackList = [
        '/basket',
        '/checkout',
        '/my-account',
        '/place-order',
        '/execute-payment',
        '/search',
        '/wish-list',
        '/order-property-file',
    ];
    
    public function __construct()
    {
    
    }
    
    public function isBlacklisted($route)
    {
        if(in_array($route, $this->blackList))
        {
            return true;
        }
        
        return false;
    }
}