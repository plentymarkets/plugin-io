<?php

namespace IO\Services\ItemSearch\Factories\Faker;

class CategoryTreeFaker extends AbstractFaker
{
    public function fill($data)
    {
        /** @var CategoryFaker $categoryFaker */
        $categoryFaker        = pluginApp(CategoryFaker::class);
        $categoryFaker->types = ['item', 'content', 'item', 'container', 'item', 'blog'];
        
        for ($i = 1; $i <= 10; $i++) {
            $default[] = $categoryFaker->fill([]);
        }
        
        foreach ($default as $key => $category) {
            if ($key % $this->number(1, 3) == 0) {
                $default[$key]['children'] = $categoryFaker->fill([]);
            }
        }
        
        $this->merge($data, $default);
        
        return $data;
    }
}
