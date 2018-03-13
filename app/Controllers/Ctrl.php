<?php
/**
 * Ctrl.php for mySSO.
 * @author SamWu
 * @date 2017/7/4 14:08
 * @copyright istimer.com
 */

namespace App\Controllers;

use App\Functions;
use Opdss\Cisession\SessionInterface;
use Slim\Container;

class Ctrl
{
	/**
	 * @var Container
	 */
	protected $ci;

	/**
	 * @var SessionInterface
	 */
	protected $session;

	protected $settings;

	/**
	 * Ctrl constructor.
	 * @param Container $ci
	 */
	public function __construct(Container $ci)
	{
		$this->ci = $ci;
		$this->settings = $this->ci->get('settings');
		$this->session = $this->ci->session;
	}

	/**
	 * 返回json
	 * @param $param
	 * @param array $data
	 * @return mixed
	 */
	protected function json($param, $data = array())
	{
		$extra = array();
		$extra['runTime'] = Functions::runTime('run', 1);
		if (func_num_args() == 1) {
			$data = $extra;
		}
		return $this->ci->get('response')->withJson(Functions::formatApiData($param, $data, $extra));
	}

	protected function tokenCache($token, $value = null, $timeOut = 60)
	{
		$key = 'token:'.$token;
		if ($value === 'delete') {
			return $this->ci->cache->delete($key);
		}
		if ($value === null) {
			return $this->ci->cache->get($key);
		} else {
			return $this->ci->cache->save($key, $value, $timeOut);
		}
	}

}