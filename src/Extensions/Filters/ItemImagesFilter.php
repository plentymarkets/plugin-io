<?php //strict

namespace IO\Extensions\Filters;

use IO\Extensions\AbstractFilter;

/**
 * Class ItemImagesFilter
 * @package IO\Extensions\Filters
 */
class ItemImagesFilter extends AbstractFilter
{
    /**
     * ItemImagesFilter constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return array
     */
    public function getFilters():array
    {
        return [
            'itemImages' => 'getItemImages'
        ];
    }

    /**
     * @param $images
     * @param string $imageAccessor
     * @return array
     */
    public function getItemImages( $images, string $imageAccessor = 'url' ):array
    {
        $imageUrls = [];
        $imageObject = (empty( $images['variation'] ) ? 'all' : 'variation');

        foreach ($images[$imageObject] as $image)
        {
            $imageUrls[] = [
                "url" => $image[$imageAccessor],
                "position" => $image["position"]
            ];
        }

        return $imageUrls;
    }
}
