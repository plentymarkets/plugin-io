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
        //TODO this will be uncommented after testing
//        if (!ReCaptcha::verify($this->request->get('recaptchaToken', null), true)) {
//            /** @var NotificationService $notificationService */
//            $notificationService = pluginApp(NotificationService::class);
//            $notificationService->addNotificationCode(LogLevel::ERROR, 13);
//
//            return $this->response->create('', ResponseCode::BAD_REQUEST);
//        }

        try {
            $formData = $this->request->all()['data'] ?? [];
            $errors = [];

            foreach (['email', 'name', 'order'] as $value) {
                if(empty($formData[$value])) {
                    $errors[] = $value;
                }
            }

            if(!empty($errors)) {
                $this->getLogger(self::class)->warning(
                    "IO::Debug.CancellationResource_missingFields",
                    [
                        "code" => ResponseCode::BAD_REQUEST,
                        "message" => 'Keys ' . implode(', ', $errors) . " couldn't be mapped to the email template"
                    ]
                );

                return $this->response->create('Missing required fields', ResponseCode::BAD_REQUEST);
            }

            $successMessage = $this->cancellationRepository->submitCancellationRequest([
                'email' => $formData['email']['value'],
                'name' => $formData['name']['value'],
                'lang' => Utils::getLang(),
                'orderId' => (int) ($formData['order']['value']),
                'reason' => $formData['reason']['value'] ?? '',
                'recipient' => $requestData['recipient'] ?? '',
            ]);

            return $this->response->create($successMessage);
        } catch (\Exception $exception) {
            $code = $exception->getCode() ?: ResponseCode::INTERNAL_SERVER_ERROR;
            $this->response->error($code, $exception->getMessage());
            return $this->response->create($exception->getMessage(), $code);
        }
    }
}
