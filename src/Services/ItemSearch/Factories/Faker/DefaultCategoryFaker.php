<?php

namespace IO\Services\ItemSearch\Factories\Faker;

class DefaultCategoryFaker extends AbstractFaker
{
    public $isList = true;
    
    public function fill($data)
    {
        $default = [
            "id"                    => $this->number(),
            "plentyId"              => $this->plentyId,
            "parentCategoryId"      => $this->number(),
            "level"                 => $this->number(0, 5),
            "type"                  => $this->rand(['content', 'item', 'container', 'blog']),
            "linklist"              => $this->boolean(),
            "right"                 => $this->rand(['all', 'customer']),
            "sitemap"               => $this->boolean(),
            "position"              => $this->number(0, 100),
            "isNeckermannPrimary"   => $this->boolean(),
            "manually"              => $this->boolean()
        ];

        $this->merge($data, $default);
        return $data;
    }
}