<?php //strict

namespace IO\Controllers;

use Plenty\Plugin\Controller;
use Plenty\Plugin\Data\Contracts\Resources;
use Plenty\Plugin\Http\Request;

/**
 * Class MyAccountController
 * @package IO\Controllers
 */
class TranslationController extends Controller
{
    public function loadTranslations( $namespace, $group, $lang )
    {
        /** @var Resources $resource */
        $resource = pluginApp( Resources::class );

        return $resource->load( "$namespace::lang/$lang/$group" )->getData();
    }
}
