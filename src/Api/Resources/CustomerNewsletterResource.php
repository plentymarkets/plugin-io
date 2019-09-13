<?php

namespace IO\Api\Resources;

use IO\Services\CustomerNewsletterService;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;

class CustomerNewsletterResource extends ApiResource
{
    /** @var CustomerNewsletterService */
    private $newsletterService;

    public function __construct(Request $request, ApiResponse $response, CustomerNewsletterService $newsletterService)
    {
        parent::__construct($request, $response);

        $this->newsletterService = $newsletterService;
    }

    /**
     * Add an email to a newsletter
     * @return Response
     */
    public function store(): Response
    {
        $email = $this->request->get('email', '');
        $firstName = $this->request->get('firstName', '');
        $lastName = $this->request->get('lastName', '');
        $emailFolder = $this->request->get('emailFolder', 0);

        $this->newsletterService->saveNewsletterData($email, $emailFolder, $firstName, $lastName);

        return $this->response->create($email, ResponseCode::OK);
    }

    /**
     * Remove an email from the newsletter
     * @param string $selector the email to be removed
     * @return Response
     * @throws \Throwable
     */
    public function destroy(string $selector): Response
    {
        $emailFolder = $this->request->get('emailFolder', 0);

        $success = $this->newsletterService->deleteNewsletterDataByEmail($selector, (int) $emailFolder);

        return $this->response->create($success, ResponseCode::OK);
    }
}
