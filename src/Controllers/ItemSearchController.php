<?php //strict

namespace IO\Controllers;

use IO\Extensions\Constants\ShopUrls;
use IO\Guards\AuthGuard;
use IO\Services\SessionStorageService;
use IO\Services\WebstoreConfigurationService;
use Plenty\Plugin\Http\Request;

class ItemSearchController extends LayoutController
{
    public function showSearch( $category = null ):string
    {
        /** @var Request $request */
        $request = pluginApp(Request::class);

        return $this->renderTemplate(
            "tpl.search",
            [
                'category'      => $category,
                'page'          => $request->get('page', null),
                'itemsPerPage'  => $request->get('items', null),
                'query'         => $request->get('query', null),
                'sorting'       => $request->get('sorting', null),
                'facets'        => $request->get('facets', '' )
            ],
            false
        );
    }

    /**
     * Redirect to new search url from category when search route
     * is enabled and called.
     *
     * @return void
     */
    public function redirectToSearch(): void
    {
        /** @var Request $request */
        $request = pluginApp(Request::class);
        /** @var ShopUrls $shopUrl */
        $shopUrl = pluginApp(ShopUrls::class);

        AuthGuard::redirect($shopUrl->search, ['query' => $request->get('query', null)]);
    }
}
