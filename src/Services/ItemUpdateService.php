<?php
/**
 * UNUSED
 * UNUSED
 * UNUSED
 * UNUSED
 * UNUSED
 * UNUSED
 * UNUSED
 * UNUSED
 * UNUSED
 * UNUSED
 * UNUSED
 * Did a test with this on Jul 16th, but dodnt work as i wanted.
 * Now using internal servers + rest api for the updates... 
 * RS Jul 17th, 25
 * UNUSED
 * UNUSED
 * UNUSED
 * UNUSED
 * UNUSED
 * UNUSED
 * UNUSED
 * UNUSED
 * UNUSED
 * UNUSED
 * UNUSED
 * UNUSED
 * UNUSED
 * UNUSED
 * UNUSED
 * UNUSED
 * UNUSED
 */
namespace IO\Services;

use IO\Helper\Utils;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Item\Variation\Contracts\VariationRepositoryContract;
use Plenty\Modules\Pim\VariationDataInterface\Contracts\VariationDataInterfaceContract;
use Plenty\Modules\Pim\VariationDataInterface\Model\DecoratedAttributes\Property;
use Plenty\Modules\Pim\VariationDataInterface\Model\DecoratedAttributes\Base;
use Plenty\Plugin\Log\Loggable;

/**
 * Service Class ItemUpdateService
 *
 * This service class contains functions related to common item tasks.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 */
class ItemUpdateService
{
     use Loggable;

    private $isAdminPreview;


    public function __construct()
    {
        $this->isAdminPreview = Utils::isAdminPreview();
    }

    public function updateVariation($data, $variationId)
    {
        $updatedVariation = [];

        if(!is_array($data) || !is_int($variationId))
        {
            $this->getLogger(__CLASS__)
                ->error('IO::Debug.updateService_missingParameter',
                    [
                        'data' => $data,
                        'variationId' => $variationId
                    ]
                );
            return $updatedVariation;
        }

        if(!$this->isAdminPreview)
        {
            $this->getLogger(__CLASS__)
                ->addReference('variationId', $variationId)
                ->error('IO::Debug.updateService_notAnAdmin',
                    [
                        'data' => $data,
                        'variationId' => $variationId,
                        'isAdminPreview' => $this->isAdminPreview
                    ]
                );
            return $updatedVariation;
        }

        try {
            // /** @var AuthHelper $authHelper */
            // $variationPimContract = pluginApp(VariationDataInterfaceContract::class);
            // $base = pluginApp(Base::class);
            // $property = pluginApp(Property::class);
            // $authHelper = pluginApp(AuthHelper::class);

            // $this->getLogger(__CLASS__)
            //     ->addReference('variationId', $variationId)
            //     ->error('Trying new update.',
            //         [
            //             'data' => $data,
            //             'variationId' => $variationId,
            //             'isAdminPreview' => $this->isAdminPreview
            //         ]
            //     );
            
            // return $authHelper->processUnguarded(
            //     function () use ($variationPimContract, $base, $property, $variationId) {
            //         $createOrUpdate = $variationPimContract->createOrUpdate();
            //         $base->setVariationId($variationId);

            //         $property->propertyId = 22;
            //         $property->groupId = 2;
            //         $property->values = "321";

            //         $createOrUpdate->add($property);
            //         return $createOrUpdate->save();
            //     }
            // );

        } catch (\Exception $e) {
             $this->getLogger(__CLASS__)
                ->addReference('variationId', $variationId)
                ->error('IO::Debug.updateService_updateError',
                    [
                        'data' => $data,
                        'variationId' => $variationId,
                        'isAdminPreview' => $this->isAdminPreview,
                        'errorMessage' => $e->getMessage()
                    ]
                );
        }
        return $updatedVariation;
    }

   
}
