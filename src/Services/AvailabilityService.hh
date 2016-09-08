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
}
