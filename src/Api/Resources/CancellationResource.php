<?php

namespace IO\Api\Resources;

use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Helper\Utils;
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
            $requestData['lang'] = Utils::getLang();

            $responseData = $this->cancellationRepository->submitCancellationRequest($requestData);

            return $this->response->create($responseData, ResponseCode::OK);
        } catch (StorefrontException $exception) {
            $code = $exception->getCode() ?: ResponseCode::INTERNAL_SERVER_ERROR;

            return $this->response->create([
                'key' => $exception->getKey(),
                'message' => $exception->getMessage(),
                'errors' => $exception->getErrors()
            ], $code);
        } catch (\Throwable $exception) {
            $code = $exception->getCode() ?: ResponseCode::INTERNAL_SERVER_ERROR;

            return $this->response->create([
                'key' => 'unknownError',
                'message' => $exception->getMessage(),
                'errors' => []
            ], $code);
        }
    }
}
