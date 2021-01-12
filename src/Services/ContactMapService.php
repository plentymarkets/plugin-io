<?php //strict

namespace IO\Services;

use Plenty\Plugin\CachingRepository;
use Plenty\Plugin\Log\Loggable;

/**
 * Class ContactMapService
 *
 * This service class exposes a method used for getting map coordinates from the Google Maps API.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 */
class ContactMapService
{
    use Loggable;

    /**
     * @var CachingRepository $cachingRepository Repository for caching in Redis
     */
    private $cachingRepository;

    const COORD_CACHE_KEY = 'ceresMapCoordinates';

    /**
     * ContactMapService constructor.
     * @param CachingRepository $cachingRepository Repository for caching in Redis
     */
    public function __construct(CachingRepository $cachingRepository)
    {
        $this->cachingRepository = $cachingRepository;
    }

    /**
     * Get coordinates for a street at a specific zipcode from Google Maps API.
     * These coordinates are saved to the Redis cache and returned.
     *
     * @param string $apiKey Google Maps API Key
     * @param string $street Name of the street, optionally with house number
     * @param string|null $zip Optional: Zip code for address (Default: null)
     * @return object
     */
    public function getMapCoordinates(string $apiKey, string $street, $zip = null)
    {
        return $this->cachingRepository->remember(
            self::COORD_CACHE_KEY,
            24 * 60,
            function () use ($street, $zip, $apiKey) {
                $location = [
                    'location' => [
                        'lat' => 0,
                        'lng' => 0
                    ]
                ];

                if (empty($street) || empty($apiKey)) {
                    return $location;
                }

                // If zip is not empty, concat it to street
                if ($zip !== null) {
                    $street = $zip . ' ' . $street;
                }

                $address = urlencode($street);

                // google map geocode api url
                $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key={$apiKey}";

                $curl = curl_init();

                curl_setopt_array(
                    $curl,
                    array(
                        CURLOPT_RETURNTRANSFER => 1,
                        CURLOPT_URL => $url
                    )
                );

                $result_json = curl_exec($curl);
                $result = json_decode($result_json, true);

                curl_close($curl);

                $lat = $result['results'][0]['geometry']['location']['lat'] ?? '';
                $lng = $result['results'][0]['geometry']['location']['lng'] ?? '';

                if ($lat && $lng) {
                    $location['location']['lat'] = $lat;
                    $location['location']['lng'] = $lng;
                    return $location;
                }

                if (isset($result['error_message'])) {
                    $this->getLogger(__CLASS__)->error(
                        'Google Maps API error',
                        [
                            'status' => $result['status'],
                            'error' => $result['error_message']
                        ]
                    );
                }

                return $location;
            }
        );
    }
}
