<?php

namespace IO\Services\ItemSearch\Factories\Faker;

use IO\Services\CategoryService;

class DefaultCategoryFaker extends AbstractFaker
{
    public $isList = true;
    
    public function fill($data)
    {
        /** @var CategoryService $categoryService */
        $categoryService = pluginApp(CategoryService::class);
        $currentCategory = $categoryService->getCurrentCategory();
        $default = [
            "id"                    => !is_null($currentCategory) ? $currentCategory->id : $this->number(),
            "plentyId"              => $this->index === 0 ? $this->plentyId : $this->number(),
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