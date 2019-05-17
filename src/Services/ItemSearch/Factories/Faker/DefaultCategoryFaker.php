<?php

namespace IO\Services\ItemSearch\Factories\Faker;

use IO\Services\CategoryService;

class DefaultCategoryFaker extends AbstractFaker
{
    public $isList = true;
    
    public function fill($data)
    {
        $currentCategory = pluginApp(CategoryService::class)->getCurrentCategory();
        $default = [
            "id"                    => !is_null($currentCategory) ? $currentCategory->id : $this->number(),
            "plentyId"              => $this->plentyId,
            "parentCategoryId"      => $this->number(),
            "level"                 => $this->number(0, 5),
            "type"                  => 'item',
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