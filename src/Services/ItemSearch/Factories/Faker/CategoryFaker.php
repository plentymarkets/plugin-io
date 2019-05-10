<?php

namespace IO\Services\ItemSearch\Factories\Faker;

class CategoryFaker extends AbstractFaker
{
    public function fill($data)
    {
        $default = [
            "paths" => "",
            "details" => $this->makeDetails()
        ];

        $this->merge($data, $default);
        return $data;
    }

    private function makeDetails()
    {
        $details = [];

        $details[] = $this->makeDetailsEntry();

        return $details;
    }

    private function makeDetailsEntry()
    {
        $categoryId = $this->number();
        return [
            "id"                    => $categoryId,
            "parentCategoryId"      => $this->number(),
            "level"                 => $this->number(0, 5),
            "type"                  => $this->rand(['content', 'item', 'container', 'blog']),
            "linklist"              => $this->boolean(),
            "right"                 => $this->rand(['all', 'customer']),
            "sitemap"               => $this->boolean(),
            "isNeckermannPrimary"   => $this->boolean(),
            "path"                  => $this->number(),
            "details"               => $this->makeCategoryDetails($categoryId)
        ];
    }

    private function makeCategoryDetails($categoryId)
    {
        return [
            [
                "categoryId"            => $categoryId,
                "plentyId"              => $this->plentyId,
                "lang"                  => $this->lang,
                "name"                  => $this->trans("IO::Faker.categoryName"),
                "description"           => $this->text(0, 20),
                "description2"          => $this->text(0, 20),
                "shortDescription"      => $this->text(0, 10),
                "metaKeywords"          => $this->text(0, 5),
                "metaDescription"       => $this->text(0, 10),
                "nameUrl"               => $this->word(),
                "metaTitle"             => $this->text(0, 3),
                "position"              => $this->number(),
                "fulltext"              => $this->boolean(),
                "placeholderTranslation"=> $this->boolean(),
                "webTemplateExists"     => $this->boolean(),
                "metaRobots"            => $this->rand(['ALL', 'INDEX', 'NOFOLLOW', 'NOINDEX', 'NOINDEX_NOFOLLOW']),
                "canonicalLink"         => ""
            ]
        ];
    }
}