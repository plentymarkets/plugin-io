<?php

namespace IO\Middlewares;

use Plenty\Modules\Authentication\Contracts\ContactAuthenticationRepositoryContract;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Middleware;

/**
 * Class AuthenticateWithToken
 *
 * Authenticate a contact if a token is given in the request.
 *
 * @package IO\Middlewares
 */
class AuthenticateWithToken extends Middleware
{
    /**
     * Before the request is processed, the contact is authenticated first, if necessary.
     *
     * Example request: ?token=TOKENVALUE
     *
     * @param Request $request
     */
    public function before(Request $request)
    {
        $loginToken = $request->get('token', '');
        if (strlen($loginToken)) {
            /** @var ContactAuthenticationRepositoryContract $authRepo */
            $authRepo = pluginApp(ContactAuthenticationRepositoryContract::class);
            $authRepo->authenticateWithToken($loginToken);
        }
    }

    /**
     * After the request is processed, do nothing here.
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function after(Request $request, Response $response)
    {
        return $response;
    }
}
