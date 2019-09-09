<?php

namespace IO\Services\ItemSearch\Factories\Faker\Traits;

trait FakeImage
{
    protected function image($width = 100, $height = 100, $text = "", $background="CCCCCC", $color = "9C9C9C", $type = "png")
    {
        $imageUrl = sprintf(
            "https://via.placeholder.com/%dx%d/%s/%s.%s",
            $width,
            $height,
            $background,
            $color,
            $type
        );

        if (strlen($text))
        {
            $imageUrl .= "?text=" . urlencode($text);
        }

        return $imageUrl;
    }
}