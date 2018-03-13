<?php
/**
 * AppSign.php for mySSO.
 * @author SamWu
 * @date 2018/2/6 18:12
 * @copyright boyaa.com
 */
namespace App\Middleware;

use Slim\Http\Request;
use Slim\Http\Response;
use \App\Functions;

class ApiSign
{
	/**
	 * @var \Slim\Container
	 */
	private $ci;

	/**
	 * http请求超时时间
	 * @var int
	 */
	private $timeOut = 60;

	/**
	 * @var \Opdss\Cicache\CacheInterface
	 */
	private $cache;

	/**
	 * 是否需要检查重复请求
	 * @var bool
	 */
	private $checkRepeatRet = true;

	private $ssoConf;

	/**
	 * 缓存前缀
	 * @var string
	 */
	private $cachePre = 'AppSign:';

	/**
	 * SignMiddleware constructor.
	 * @param \Slim\Container $ci
	 */
	public function __construct(\Slim\Container $ci)
	{
		$this->ci = $ci;
		$this->cache = $ci->cache;
		$setting = $ci->get('settings');
		$this->ssoConf = $setting['sso'];
		isset($setting['httpRequestTimeOut']) AND $this->timeOut = $setting['httpRequestTimeOut'];
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param $next
	 * @return Response
	 */
	public function __invoke(Request $request, Response $response, $next)
	{
		if ($request->getMethod() == 'GET') {
			$params = $request->getQueryParams();
		} else {
			$params = $request->getParsedBody();
		}

		$sign = isset($params['sign']) ? $params['sign'] : '';
		$t = isset($params['t']) ? intval($params['t']) : 0;
		if (!$sign || !$t) {
			return $response->withStatus(400)->withJson(Functions::formatApiData(40001));
		}
		//检查请求是否超时
		if (time()-$t > $this->timeOut) {
			return $response->withJson(Functions::formatApiData(40192));
		}
		$appid = isset($params['appid']) ? $params['appid'] : '';
		if (!$appid || !isset($this->ssoConf[$appid])) {
			return $response->withStatus(400)->withJson(Functions::formatApiData(40001));
		}
		$appConf = $this->ssoConf[$appid];
		//检查签名
		if (!$this->sign($params, $appConf['appkey'])) {
			return $response->withStatus(400)->withJson(Functions::formatApiData(40190));
		}
		//检查缓存有没有请求记录,过滤重复请求
		if ($this->checkRepeatRet) {
			$cacheKey = $this->cachePre.$sign;
			if ($this->cache->get($cacheKey)) {
				return $response->withJson(Functions::formatApiData(40191));
			}
			//插入到缓存记录
			$this->cache->save($cacheKey, $params, $this->timeOut);
		}

		$response = $next($request, $response);
		return $response;
	}

	/**
	 * 接口参数签名
	 * @param array $data
	 * @param $appkey
	 * @param bool $isVerify
	 * @return bool|string
	 */
	protected function sign(array $data, $appkey, $isVerify = true)
	{
		if (empty($data)) {
			return false;
		}
		ksort($data);
		$sign = '';
		if (isset($data['sign'])) {
			$sign = $data['sign'];
			unset($data['sign']);
		}
		$str = http_build_query($data) . $appkey;
		$new_sign = md5($str);

		$this->ci->logger->debug('签名参数 => '.var_export($data, true));
		$this->ci->logger->debug('签名字符串 => '.$str);
		$this->ci->logger->debug('签名值=> '.$new_sign);
		$this->ci->logger->debug('收到的签名值=> '.$sign);

		if ($isVerify) {
			return $sign === $new_sign;
		} else {
			return $new_sign;
		}
	}
}