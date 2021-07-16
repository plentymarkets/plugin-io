<?php //strict

namespace IO\Extensions\Filters;

use IO\Extensions\AbstractFilter;

/**
 * Class ItemImagesFilter
 *
 * Contains item image helper twig filters.
 *
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
     * Get the twig filter to method name mapping. (twig filter => method name)
     *
     * @return array
     */
    public function getFilters(): array
    {
        return [
            'itemImages' => 'getItemImages',
            'firstItemImage' => 'getFirstItemImage',
            'firstItemImageUrl' => 'getFirstItemImageUrl'
        ];
    }

    /**
     * Get the item images for the given accessor.
     *
     * @param array $images Item image object to get the images from.
     * @param string $imageAccessor Accessor to get the image data from.
     * @return array
     */
    public function getItemImages($images, string $imageAccessor = 'url'): array
    {
        $imageUrls = [];
        $imageObject = (empty($images['variation']) ? 'all' : 'variation');

        foreach ($images[$imageObject] as $image) {
            $imageUrls[] = [
                'url' => $image[$imageAccessor],
                'position' => $image['position'],
                'alternate' => $image['names']['alternate'],
                'name' => $image['names']['name']
            ];
        }

        return $imageUrls;
    }

    /**
     * Gets the first item image for the given accessor.
     *
     * @param array|object $images Item image object from which the image gets returned.
     * @param string $imageAccessor Accessor to get the image data from.
     * @return array
     */
    public function getFirstItemImage($images, $imageAccessor = 'url'): array
    {
        $images = $this->getItemImages($images, $imageAccessor);
        $itemImage = [];
        foreach ($images as $image) {
            if (!count($itemImage) || $itemImage['position'] > $image['position']) {
                $itemImage = $image;
            }
        }

        return $itemImage;
    }

    /**
     * Gets the first item image url for the given accessor.
     *
     * @param array|object $images Item image object from which the url gets returned.
     * @param string $imageAccessor Accessor to get the url from.
     * @return string
     */
    public function getFirstItemImageUrl($images, $imageAccessor = 'url'): string
    {
        $itemImage = $this->getFirstItemImage($images, $imageAccessor);
        if ($itemImage !== null && $itemImage['url'] !== null) {
            return $itemImage['url'];
        };

        return '';
    }
}
