<?php //strict

namespace IO\Services;

use Plenty\Modules\Item\Availability\Contracts\AvailabilityRepositoryContract;
use Plenty\Modules\Item\Availability\Models\Availability;

/**
 * Class AvailabilityService
 * @package IO\Services
 */
class AvailabilityService
{
	/**
	 * @var AvailabilityRepositoryContract
	 */
	private $availabilityRepository;

    /**
     * AvailabilityService constructor.
     * @param AvailabilityRepositoryContract $availabilityRepository
     */
	public function __construct(AvailabilityRepositoryContract $availabilityRepository)
	{
		$this->availabilityRepository = $availabilityRepository;
	}

    /**
     * Get the item availability by ID
     * @param int $availabilityId
     * @return Availability|null
     */
	public function getAvailabilityById( int $availabilityId = 0 )
    {
        return $this->availabilityRepository->findAvailability( $availabilityId );
    }

    /**
     *
     * @return array
     */
    public function getAvailabilities():array
    {
        $availabilities = array();
        for( $i = 1; $i <= 10; $i++ )
        {
	        $availability = $this->getAvailabilityById( $i );
	        if($availability instanceof Availability)
	        {
	            array_push( $availabilities, $this->getAvailabilityById( $i ) );
	        }
        }
        return $availabilities;
    }
}
