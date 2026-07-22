<?php

namespace IO\Api\Resources;

use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Constants\LogLevel;
use IO\Helper\ReCaptcha;
use IO\Helper\Utils;
use IO\Services\NotificationService;
use Plenty\Modules\Webshop\Storefront\Contracts\CancellationRepositoryContract;
use Plenty\Modules\Webshop\Storefront\Exceptions\StorefrontException;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Log\Loggable;

/**
 * Class CancellationResource
 *
 * Resource class for the route `io/cancellation`.
 * @package IO\Api\Resources
 */
class CancellationResource extends ApiResource
{
    use Loggable;

    /**
     * @var CancellationRepositoryContract
     */
    private $cancellationRepository;

    /**
     * CancellationResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param CancellationRepositoryContract $cancellationRepository
     */
    public function __construct(Request $request, ApiResponse $response, CancellationRepositoryContract $cancellationRepository)
    {
        parent::__construct($request, $response);
        $this->cancellationRepository = $cancellationRepository;
    }

    /**
     * Submit a cancellation request.
     * @return Response
     */
    public function store(): Response
    {
        try {
            $requestData = $this->request->all();
            $formData = $requestData['data'] ?? [];
            $errors = [];

            if(empty($formData)){
                return $this->response->create(false, ResponseCode::BAD_REQUEST);
            }

            $formData = array_change_key_case($formData);
            foreach (['email', 'name'] as $field) {
                if(empty($formData[$field]['value'])) {
                    $errors[] = $field;
                }
            }

            if(!empty($errors)) {
                $message = 'Keys "' . implode('", "', $errors) . '" of the contract withdrawal form couldn\'t be mapped to the email template.';

                $this->getLogger(self::class)->error(
                    "IO::Debug.CancellationResource_missingFields",
                    [
                        "code" => ResponseCode::BAD_REQUEST,
                        "message" => $message
                    ]
                );

                return $this->response->create(false, ResponseCode::BAD_REQUEST);
            }

            $this->cancellationRepository->submitCancellationRequest([
                'email' => $formData['email']['value'],
                'name' => $formData['name']['value'],
                'lang' => Utils::getLang(),
                'orderId' => $formData['order']['value'],
                'reason' => $formData['reason']['value'] ?? '',
                'recipient' => $requestData['recipient'] ?? '',
            ]);

            return $this->response->create(true, ResponseCode::OK);
        } catch (\Exception $exception) {
            $code = $exception->getCode() ?: ResponseCode::INTERNAL_SERVER_ERROR;
            return $this->response->create(false, $code);
        }
    }
}
