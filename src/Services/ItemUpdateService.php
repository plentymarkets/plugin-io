<?php

namespace IO\Services;

use IO\Helper\Utils;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Item\Variation\Contracts\VariationRepositoryContract;
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
            /** @var AuthHelper $authHelper */
            $authHelper = pluginApp(AuthHelper::class);
            $variationRepo = pluginApp(VariationRepositoryContract::class);

            return $authHelper->processUnguarded(
                function () use ($variationRepo, $data, $variationId) {
                    return $variationRepo->update($data, $variationId);
                }
            );
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
