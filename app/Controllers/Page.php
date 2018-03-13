<?php
/**
 * Index.php for mySSO.
 * @author SamWu
 * @date 2017/7/4 14:08
 * @copyright istimer.com
 */

namespace App\Controllers;

use App\Functions;
use App\Models\Users;
use Slim\Http\Request;
use Slim\Http\Response;

class Page extends Ctrl
{
	/**
	 * 首页
	 *
	 * @pattern [/]
	 * @name index
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function index(Request $request, Response $response, array $args)
	{
		if (!$this->session->userInfo) {
			return $response->withRedirect($this->ci->router->pathFor('login'));
		}
		return var_export($this->session->get(), true);
	}

	/**
	 * 登录页面
	 * @pattern /login[/]
	 * @method POST|GET
	 * @name login
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 */
	public function login(Request $request, Response $response, array $args)
	{
		//已经登陆
		if ($this->session->token) {
			return $response->withRedirect($this->returnUrl($request->getQueryParam('appid')));
		}

		$data = array(
			'error' => '',
			'need_captcha' =>  intval($this->session->get('try_login')) > 5 ? 1 : 0,
		);
		if ($request->getMethod() == 'POST') {
			$username = $request->getParsedBodyParam('username');
			$email = $request->getParsedBodyParam('email');
			$password = $request->getParsedBodyParam('password');
			$captcha = $request->getParsedBodyParam('captcha');
			$verifyCaptcha = true;
			if ($data['need_captcha']) {
				if (empty($captcha) || $this->session->getFlashdata('captcha') != strtolower($captcha)) {
					$data['error'] = '验证码输入错误';
					$verifyCaptcha = false;
				}
			}
			if ($verifyCaptcha) {
				$user = Users::login(($username ?: $email), $password, !$username);
				if ($user) {
					//登陆成功之后的操作
					$this->session->set('try_login', 0);
					$this->session->token = $user['token'];
					$this->session->userInfo = $user;
					$this->tokenCache($user['token'], $user, 7200);
					return $response->withRedirect($this->returnUrl($request->getQueryParam('appid')));
				}
				$try_login = intval($this->session->get('try_login'));
				$this->session->set('try_login', $try_login + 1);
				$data['error'] = '用户名或者密码错误';
				$data['need_captcha'] = $try_login >= 5 ? 1 : 0;
			}
			$data = array_merge($data, $request->getParams());
		}
		return $this->ci->renderer->render($response, 'login.phtml', $data);
	}

	private function returnUrl($appid = '')
	{
		$url = '/';
		if ($appid && isset($this->settings['sso'][$appid])) {
			$appConf = $this->settings['sso'][$appid];
			$user = $this->tokenCache($this->session->token);
			$data = array(
				't' => time(),
				'token' => $user['token'],
				'uid' => $user['uid'],
				'username' => $user['username'],
			);
			$data['sign'] = Functions::sign($data, $appConf['appkey']);
			$and = strpos($appConf['redirect'], '?') === false ? '?' : '&';
			$url = $appConf['redirect'].$and.http_build_query($data);
		}
		return $url;
	}

	/**
	 * 退出页面
	 * @pattern /logout[/]
	 * @name logout
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 */
	public function logout(Request $request, Response $response, array $args)
	{
		if ($token = $request->getQueryParam('token')) {
			$this->tokenCache($token, 'delete');
		}
		$this->session->destroy();
		$url = $this->ci->router->pathFor('login');
		if ($appid = $request->getQueryParam('appid')) {
			$url.'?appid='.$appid;
		}
		return $response->withRedirect($url);
	}

	/**
	 * 退出页面
	 * @pattern /register[/]
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 */
	public function register(Request $request, Response $response, array $args)
	{
		$data = array();
		return $this->ci->renderer->render($response, 'register.phtml', $data);
	}

	/**
	 * 退出页面
	 * @pattern /forget[/]
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 */
	public function forget(Request $request, Response $response, array $args)
	{
	}

	/**
	 * 路由列表
	 *
	 * 本站所有的路由信息
	 * 需要查看的话在这里看就行了
	 * @pattern /routes[/]
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return mixed
	 */
	public function routes(Request $request, Response $response, array $args)
	{
		$type = $request->getParam('type');
		$routes = $this->ci->offsetGet('routes');
		if ($type == 'api') {
			foreach ($routes as $k => $v) {
				if (substr($v['pattern'], 0, 4) != '/api') {
					unset($routes[$k]);
				}
			}
		}
		/*if ($this->isAjax()) {
			return $this->json($routes);
		}*/
		return $this->json($routes);
	}

	/**
	 * @pattern /debug
	 * @method GET|POST
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return mixed
	 */
	public function debug(Request $request, Response $response, array $args)
	{
		$data = [
			'app' => $this->settings['sso'],
			'api' => array_reduce($this->ci->routes, function($a, $b) {
				if (strpos($b['pattern'], '/api') === 0) {
					$a[] = [
						'title' => $b['title'],
						'api' => $b['pattern']
					];
				}
				return $a;
			}),
			'response' => '',
			'method' => ['GET', 'POST', 'PUT', 'DELETE'],
		];

		if ($request->getMethod() == 'POST') {
			$params = $request->getParsedBody();
			$appConf = isset($this->settings['sso'][$params['appid']]) ? $this->settings['sso'][$params['appid']] : '';
			if (empty($appConf) || !in_array($params['method'], $data['method'])) {
				$data['response'] = '';
			} else {
				$toParams = array();
				if (isset($params['name'])) {
					$toParams = array_combine($params['name'], $params['value']);
				}
				$toParams['appid'] = $params['appid'];
				$toParams['t'] = time();
				$toParams['sign'] = Functions::sign($toParams, $appConf['appkey']);
				$method = strtolower($params['method']);
				$api = $params['url'] . '/' . trim($params['api'], '/');
				if ($method == 'get') {
					$api .= '?' . http_build_query($toParams);
					$res = \Opdss\Http\Request::get($api, $toParams);
				} else {
					$res = call_user_func(array('\Opdss\Http\Request', $method), $api, $toParams);
				}
				$body = $res->getBody();
				$ret = [
					'request_header' => $res->getCurlInfo('request_header'),
					//'params' => http_build_query($toParams),
					'http_code' => $res->httpCode(),
					'response_header' => $res->getHeaderString(),
					'body' => $body,
					'json' => json_decode($body, true) ? Functions::formatJson($body) : ''
				];
				if ($request->isXhr()) {
					return $this->json($ret);
				}
				$data['response'] = $ret;
			}
		}
		return $this->ci->renderer->render($response, 'debug.phtml', $data);
	}

	public function signups(Request $request, Response $response, array $args)
	{
		$captcha = $request->getParsedBodyParam('captcha', '');
		if (empty($captcha) || !isset($_SESSION['captcha']) || $_SESSION['captcha'] != strtolower($captcha)) {
			return $this->json(40114);
		}
		unset($_SESSION['captcha']);
		$validator = new Validator($request->getParsedBody());
		$validator
			->required('%s不能为空')
			->betweenlength(1, 32, '%s最大长度为32个字符')
			->callback(function ($val) {
				return !(bool)Users::where('username', $val)->count();
			}, '此用户名已经存在')
			->validate('username', '用户名');
		$validator
			->required('%s不能为空')
			->betweenlength(6, 32, '%s长度6-32个字符')
			->validate('password', '用户密码');
		$validator
			->required('%s不能为空')
			->validate('mobile', '手机号');
		$validator
			->matches('password', 0, '两次密码不一致')
			->validate('repassword', '用户密码');
		$validator
			->email('%s输出错误')
			->validate('email', '邮箱');
		// check for errors
		if ($validator->hasErrors()) {
			return $this->json(40001, $validator->getAllErrors());
		}
		$user = new Users();
		foreach ($validator->getValidData() as $k => $v) {
			if ($k == 'repassword') continue;
			$user->$k = trim($v);
		}
		$user->password = password_hash($user->password, PASSWORD_DEFAULT);

		if ($user->save()) {
			return $this->json($user);
		}
		return $this->json(50000);
	}

}