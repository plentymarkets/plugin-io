<?php //strict

namespace IO\Services;

use Plenty\Modules\Item\Availability\Contracts\AvailabilityRepositoryContract;
use Plenty\Modules\Item\Availability\Models\Availability;

/**
 * Service Class AvailabilityService
 *
 * This service class contains various methods for getting item availabilities.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 */
class AvailabilityService
{
    /**
     * @var AvailabilityRepositoryContract This repository is used for fetching and updating availabilities.
     * @see \Plenty\Modules\Item\Availability\Models\Availability
     */
    private $availabilityRepository;

    /**
     * AvailabilityService constructor.
     *
     * @param AvailabilityRepositoryContract $availabilityRepository This repository is used for fetching and updating availabilities.
     */
    public function __construct(AvailabilityRepositoryContract $availabilityRepository)
    {
        $this->availabilityRepository = $availabilityRepository;
    }

    /**
     * Get the item availability by id
     *
     * @param int $availabilityId The id of the availability, between 1 and 10
     * @return Availability|null
     */
    public function getAvailabilityById(int $availabilityId = 0)
    {
        return $this->availabilityRepository->findAvailability($availabilityId);
    }

    /**
     * Get all item availabilities
     *
     * @return array
     */
    public function getAvailabilities(): array
    {
        $availabilities = array();
        for ($i = 1; $i <= 10; $i++) {
            $availability = $this->getAvailabilityById($i);
            if ($availability instanceof Availability) {
                array_push($availabilities, $this->getAvailabilityById($i));
            }
        }
        return $availabilities;
    }
}
