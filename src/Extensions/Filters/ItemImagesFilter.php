<?php //strict

namespace IO\Extensions\Filters;
use IO\Extensions\AbstractFilter;
use IO\Helper\Utils;
use Plenty\Modules\Item\ItemImage\Contracts\ItemImageAvailabilityRepositoryContract;
use Plenty\Modules\Authorization\Services\AuthHelper;

/**
 * Class ItemImagesFilter
 *
 * Contains item image helper twig filters.
 *
 * @package IO\Extensions\Filters
 */
class ItemImagesFilter extends AbstractFilter
{

    /** @var AuthHelper $authHelper */
    private $authHelper;

    /**
     * ItemImagesFilter constructor.
     */
    public function __construct(
        AuthHelper $authHelper
    ) {
        parent::__construct();

        $this->authHelper = $authHelper;
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
            'firstItemImageUrl' => 'getFirstItemImageUrl',
            'firstItemImageAlt' => 'getFirstItemImageAlt'
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

        // Get current plentyId 
        $currentPlentyId = Utils::getPlentyId() ?? 17831;
        $imageAvailabilityRepo = pluginApp(ItemImageAvailabilityRepositoryContract::class);

        foreach ($images[$imageObject] as $image) {
            $isAvailable = false;

            // The image doesnt have availability present, so we request it
            if(!isset($image['availabilities']))
            {
                $imageId = $image['id'] ?? null;
                if(!$imageId)
                {
                    continue;
                }
                
                $availabilities = $this->authHelper->processUnguarded(function() use ($imageId, $imageAvailabilityRepo)
                {
                    return $imageAvailabilityRepo->findByImageId($imageId);
                });


                foreach($availabilities as $availability)
                {
                    if($availability->type == "mandant" && ($availability->value == -1 || $availability->value == $currentPlentyId))
                    {
                        $isAvailable = true;
                        break;
                    }
                }
            }

            $mandanten = $image['availabilities']['mandant'] ?? [];
            if(count($mandanten) == 0 && !$isAvailable)
                continue;

            foreach($mandanten as $mandant)
            {
                if($mandant == $currentPlentyId || $mandant == -1)
                {
                    $isAvailable = true;
                }
            }

            if(!$isAvailable)
                continue;
            
            $imageUrls[] = [
                'url' => $image[$imageAccessor],
                'position' => $image['position'],
                'alternate' => $image['names']['alternate'] ?? "",
                'name' => $image['names']['name'] ?? ""
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


    public function getFirstItemImageAlt($images, $imageAccessor = 'url'): string
    {
        $itemImage = $this->getFirstItemImage($images, $imageAccessor);
        if ($itemImage !== null && $itemImage['alternate'] !== null) {
            return $itemImage['alternate'];
        };

        return '';
    }
}
