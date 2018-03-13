<?php
/**
 * Api.php for mySSO.
 * @author SamWu
 * @date 2018/2/6 18:06
 * @copyright boyaa.com
 */

namespace App\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Api
 * @middleware App\Middleware\ApiSign
 * @package App\Controllers
 */
class Api extends Ctrl
{
	/**
	 * 校验token返回用户信息
	 * @pattern /api/user/token
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return mixed
	 */
	public function getUserByToken(Request $request, Response $response, array $args)
	{
		$token = $request->getQueryParam('token');
		if ($token && ($user = $this->tokenCache($token))) {
			return $this->json($user);
		}
		return $this->json(1);
	}
}