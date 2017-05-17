<?php //strict

namespace IO\Extensions\Filters;

use IO\Extensions\AbstractFilter;
use Plenty\Modules\Item\DataLayer\Models\ItemDescription;

class ItemImagesFilter extends AbstractFilter
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getFilters():array
    {
        return [
            'itemImages' => 'getItemImages'
        ];
    }

    public function getItemImages( $images, string $imageAccessor = 'url' )
    {
        $imageUrls = [];
        $imageObject = (empty( $images['variation'] ) ? 'all' : 'variation');

        foreach ($images[$imageObject] as $image)
        {
            $imageUrls[] = $image[$imageAccessor];
        }

        return $imageUrls;
    }
}
