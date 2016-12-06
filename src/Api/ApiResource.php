<?php //strict

namespace IO\Api;

use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;

/**
 * Class ApiResource
 * @package IO\Api
 */
class ApiResource extends Controller
{
    /**
     * @var \IO\Api\ApiResponse
     */
	protected $response;

    /**
     * @var Request
     */
	protected $request;

	/**
	 * @var ResponseCode
	 */
	private $defaultCode = ResponseCode::NOT_IMPLEMENTED;

    /**
     * ApiResource constructor.
     * @param Request $request
     * @param \IO\Api\ApiResponse $response
     */
	public function __construct(Request $request, ApiResponse $response)
	{
		$this->response = $response;
		$this->request  = $request;
	}

	// Get all
    /**
     * @return BaseResponse
     */
	public function index():BaseResponse
	{
		return $this->response->create(null, $this->defaultCode);
	}

	// Post
    /**
     * @return BaseResponse
     */
	public function store():BaseResponse
	{
		return $this->response->create(null, $this->defaultCode);
	}

	// Get
    /**
     * @param string $selector
     * @return BaseResponse
     */
	public function show(string $selector):BaseResponse
	{
		return $this->response->create(null, $this->defaultCode);
	}

	// Put/patch
    /**
     * @param string $selector
     * @return BaseResponse
     */
	public function update(string $selector):BaseResponse
	{
		return $this->response->create(null, $this->defaultCode);
	}

	// Delete
    /**
     * @param string $selector
     * @return BaseResponse
     */
	public function destroy(string $selector):BaseResponse
	{
		return $this->response->create(null, $this->defaultCode);
	}
}
