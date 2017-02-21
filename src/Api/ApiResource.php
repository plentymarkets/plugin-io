<?php //strict

namespace IO\Api;

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
     * @return Response
     */
	public function index():Response
	{
		return $this->response->create(null, $this->defaultCode);
	}

	// Post
    /**
     * @return Response
     */
	public function store():Response
	{
		return $this->response->create(null, $this->defaultCode);
	}

	// Get
    /**
     * @param string $selector
     * @return Response
     */
	public function show(string $selector):Response
	{
		return $this->response->create(null, $this->defaultCode);
	}

	// Put/patch
    /**
     * @param string $selector
     * @return Response
     */
	public function update(string $selector):Response
	{
		return $this->response->create(null, $this->defaultCode);
	}

	// Delete
    /**
     * @param string $selector
     * @return Response
     */
	public function destroy(string $selector):Response
	{
		return $this->response->create(null, $this->defaultCode);
	}
}
