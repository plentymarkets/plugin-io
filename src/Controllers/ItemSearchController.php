<?php //strict

namespace IO\Controllers;

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
            ]
        );
    }
}