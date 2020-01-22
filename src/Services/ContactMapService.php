<?php //strict

namespace IO\Services;

use Plenty\Plugin\CachingRepository;
use Plenty\Plugin\Log\Loggable;

class ContactMapService
{
    use Loggable;

    /** @var CachingRepository $cachingRepository */
    private $cachingRepository;

    const COORD_CACHE_KEY = 'ceresMapCoordinates';

    public function __construct(CachingRepository $cachingRepository)
    {
        $this->cachingRepository = $cachingRepository;
    }

    public function getMapCoordinates($apiKey, $street, $zip = null)
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
