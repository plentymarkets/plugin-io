<?php

namespace IO\Api\Resources;

use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Constants\LogLevel;
use IO\Helper\ReCaptcha;
use IO\Services\NotificationService;
use Plenty\Modules\Webshop\Storefront\Contracts\CancellationRepositoryContract;
use Plenty\Modules\Webshop\Storefront\DTOs\CancellationFormDTO;
use Plenty\Modules\Webshop\Storefront\Exceptions\StorefrontException;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;

/**
 * Class CancellationResource
 *
 * Resource class for the route `io/cancellation`.
 * @package IO\Api\Resources
 */
class CancellationResource extends ApiResource
{
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
        if (!ReCaptcha::verify($this->request->get('recaptchaToken', null), true)) {
            /** @var NotificationService $notificationService */
            $notificationService = pluginApp(NotificationService::class);
            $notificationService->addNotificationCode(LogLevel::ERROR, 13);

            return $this->response->create('', ResponseCode::BAD_REQUEST);
        }

        try {
            $cancellationFormDTO = new CancellationFormDTO(
                $this->request->get('email', ''),
                $this->request->get('name', ''),
                $this->request->get('lang', ''),
                (int) $this->request->get('orderId', 0),
                $this->request->get('reason', '')
            );

            $successMessage = $this->cancellationRepository->submitCancellationRequest($cancellationFormDTO);

            return $this->response->create($successMessage, ResponseCode::OK);
        } catch (StorefrontException $exception) {
            $code = $exception->getCode() ?: ResponseCode::INTERNAL_SERVER_ERROR;
            return $this->response->create($exception->getKey(), $code);
        } catch (\Exception $exception) {
            $code = $exception->getCode() ?: ResponseCode::INTERNAL_SERVER_ERROR;
            $this->response->error($code, $exception->getMessage());
            return $this->response->create($exception->getMessage(), $code);
        }
    }
}
