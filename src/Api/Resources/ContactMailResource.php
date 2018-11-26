<?php

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\ContactMailService;
use Plenty\Plugin\Log\Loggable;


/**
 * Class ContactMailResource
 * @package IO\Api\Resources
 */
class ContactMailResource extends ApiResource
{
    use Loggable;
    private $contactMailService;

    /**
     * ContactMailResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     */
    public function __construct(Request $request, ApiResponse $response, ContactMailService $contactMailService)
    {
        parent::__construct($request, $response);
        $this->contactMailService = $contactMailService;
    }

    public function store():Response
    {
        $mailTemplate = $this->request->get('template', '');
        $contactData = $this->request->get('contactData',[]);
        $this->getLogger(__METHOD__)->error("contactData:", $contactData);

        $response = $this->contactMailService->sendMail($mailTemplate, $contactData);

        if($response)
        {
            return $this->response->create($response, ResponseCode::CREATED);
        }
        else
        {
            return $this->response->create($response, ResponseCode::BAD_REQUEST);
        }

    }
}
