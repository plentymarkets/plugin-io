<?php //strict

namespace LayoutCore\Api;

use Symfony\Component\HttpFoundation\Response as BaseReponse;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use LayoutCore\Api\ApiResponse;
use LayoutCore\Api\ResponseCode;

class ApiResource extends Controller
{
	protected $response;
	protected $request;
	
	/**
	 * @var ResponseCode
	 */
	private $defaultCode = ResponseCode::NOT_IMPLEMENTED;
	
	public function __construct(Request $request, ApiResponse $response)
	{
		$this->response = $response;
		$this->request  = $request;
	}
	
	// get all
	public function index():BaseReponse
	{
		return $this->response->create(null, $this->defaultCode);
	}
	
	// post
	public function store():BaseReponse
	{
		return $this->response->create(null, $this->defaultCode);
	}
	
	// get
	public function show(string $selector):BaseReponse
	{
		return $this->response->create(null, $this->defaultCode);
	}
	
	// put/patch
	public function update(string $selector):BaseReponse
	{
		return $this->response->create(null, $this->defaultCode);
	}
	
	// delete
	public function destroy(string $selector):BaseReponse
	{
		return $this->response->create(null, $this->defaultCode);
	}
}
