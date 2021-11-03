<?php //strict

namespace IO\Extensions\Functions;

use IO\Extensions\AbstractFunction;
use Plenty\Modules\Webshop\Contracts\WebspaceRepositoryContract;

/**
 * Class Component
 *
 *
 *
 * @package IO\Extensions\Functions
 */
class GetCdnMetadata extends AbstractFunction
{

    /**
     * Get the twig function to internal method name mapping. (twig function => internal method)
     *
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            "cdn_metadata" => "getCdnMetadata"
        ];
    }

    /**
     * Get the metadata for a file stored on plentymarkets cdn.
     * @param string $imageUrl Resource URL to get metadata for.
     * @param string $key Metadata key to get value for.
     * @param mixed $default Default value to return if no value is stored in metadata.
     *
     * @return mixed
     */
    public function getCdnMetadata($imageUrl, $key = null, $default = null)
    {
        /** @var WebspaceRepositoryContract $metaDataRepository */
        $metaDataRepository = pluginApp(WebspaceRepositoryContract::class);
        return $metaDataRepository->getCdnMetadata($imageUrl, $key, $default);
    }

}
