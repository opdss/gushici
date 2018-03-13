<?php
/**
 * AuthMiddleware.php for wa_poker.
 * 检测登录
 * @author SamWu
 * @date 2017/4/25 16:26
 * @copyright istimer.com
 */

namespace App\Middleware;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

use \App\Functions;

class Auth
{
	/**
	 * @var Container
	 */
	protected $ci;

	/**
	 * Auth constructor.
	 * @param Container $ci
	 */
	public function __construct(Container $ci)
	{
		$this->ci = $ci;
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param $next
	 * @return Response
	 */
	public function __invoke(Request $request, Response $response, $next)
	{

		$ajax = $this->ci->request->getHeaderLine('HTTP_X_REQUESTED_WITH');
		$isAjax = $ajax && strtolower($ajax) == 'xmlhttprequest';

		$userInfo = $this->ci->session->userInfo;

		if (empty($userInfo)) {
			if ($isAjax) {
				return $response->withJson(Functions::formatApiData(40300));
			}
			return $this->ci->response->withRedirect($this->ci->router->pathFor('login'));
		}
		//注入当前用户信息
		$this->ci->offsetSet('userInfo', $userInfo);
		//$this->ci->logger->debug(__METHOD__);
		$response = $next($request, $response);
		return $response;
	}

}