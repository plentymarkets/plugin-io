<?php //strict

namespace IO\Controllers;

use IO\Guards\AuthGuard;
use IO\Services\SessionStorageService;
use IO\Services\WebstoreConfigurationService;
use Plenty\Plugin\Http\Request;

class ItemSearchController extends LayoutController
{
    public function showSearch():string
    {
        /** @var Request $request */
        $request = pluginApp(Request::class);
        
        return $this->renderTemplate(
            "tpl.search",
            [
                'page'          => $request->get('page', null),
                'itemsPerPage'  => $request->get('items', null),
                'query'         => $request->get('query', null),
                'sorting'       => $request->get('sorting', null),
                'facets'        => $request->get('facets', '' )
            ],
            false
        );
    }

    public function redirectToSearch($query):string
    {
        $url = '/search';
        $webstoreConfigurationService = pluginApp(WebstoreConfigurationService::class);
        $sessionStorage = pluginApp(SessionStorageService::class);
        if($webstoreConfigurationService->getDefaultLanguage() !== $sessionStorage->getLang())
        {
            $url = '/'.$sessionStorage->getLang() .$url;
        }
        AuthGuard::redirect($url, ['query' => $query]);
        return "";
    }
}
