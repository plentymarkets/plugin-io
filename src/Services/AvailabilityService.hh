<?hh //strict

namespace LayoutCore\Services;

use Plenty\Modules\Item\Availability\Contracts\AvailabilityRepositoryContract;
use Plenty\Modules\Item\Availability\Models\Availability;

class AvailabilityService
{
    private AvailabilityRepositoryContract $availabilityRepository;

    public function __construct( AvailabilityRepositoryContract $availabilityRepository )
    {
        $this->availabilityRepository = $availabilityRepository;
    }

    public function getAvailabilityById(int $availabilityId = 0) : ?Availability
    {
        return $this->availabilityRepository->findAvailability($availabilityId);
    }

    public function getAvailabilities():array<Availability>
    {
        $availabilities = array();
        for( $i = 1; $i <= 10; $i++ )
        {
            array_push( $availabilities, $this->getAvailabilityById( $i ) );
        }
        return $availabilities;
    }
}
