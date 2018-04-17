<?php
/**
 * Index.php for mySSO.
 * @author SamWu
 * @date 2017/7/4 14:08
 * @copyright istimer.com
 */

namespace App\Controllers;

use App\Functions;
use App\Models\Articles;
use App\Models\Authors;
use Slim\Http\Request;
use Slim\Http\Response;

class Page extends Base
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
		return $this->articles($request, $response, $args);
	}

	/**
	 *
	 * @pattern /articles.html
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function articles(Request $request, Response $response, array $args)
	{
		$page = (int)$request->getParam('p') ?: 1;
		$number = (int)$request->getParam('number') ?: 10;
		$kw = trim($request->getParam('kw')) ?: '';
		$author_id = intval($request->getParam('author_id')) ?: '';

		$builder = $kw ? Articles::where('title', 'like', '%'.$kw.'%') : new Articles();
		//$builder = $kw ? Articles::where('title', 'like', '%'.$kw.'%') : Articles::where('author_id', $author_id);
		if ($author_id) {
			$builder = $builder->where('author_id', '=', $author_id);
		}

		$res = $builder->limit($number)->skip(($page-1)*$number)->get();
		$data['articles'] = $res ? $res->toArray() : array();
		$data['pagination'] = $this->pagination($builder->count(), $number);
		return $this->view('index.twig', $data);
	}

	/**
	 *
	 * @pattern /authors.html
	 * @name index
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function authors(Request $request, Response $response, array $args)
	{
		$page = (int)$request->getParam('p') ?: 1;
		$number = (int)$request->getParam('number') ?: 10;
		$kw = trim($request->getParam('kw')) ?: '';

		$builder = $kw ? Authors::where('name', 'like', '%'.$kw.'%') : new Authors;

		$res = $builder->limit($number)->skip(($page-1)*$number)->get();
		$data['authors'] = $res ? $res->toArray() : array();
		$data['pagination'] = $this->pagination($builder->count(), $number);
		return $this->view('authors.twig', $data);
	}

	/**
	 * @pattern /article/{id}.html
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return mixed
	 */
	public function article_detail(Request $request, Response $response, array $args)
	{
		$id = intval($args['id']);
		if (!$id || !$detail = Articles::find($id)) {
			return $response->withStatus(404);
		}
		$detail->documents;
		$detail->author;
		$data['title'] = $detail->title;
		$data['detail'] = $detail->toArray();
		return $this->view('article_detail.twig', $data);
	}


	/**
	 * @pattern /author/{id}.html
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return mixed
	 */
	public function author_detail(Request $request, Response $response, array $args)
	{
		$id = intval($args['id']);
		if (!$id || !$detail = Authors::find($id)) {
			return $response->withStatus(404);
		}
		$data['title'] = $detail->name;
		$data['detail'] = $detail->toArray();
		return $this->view('author_detail.twig', $data);
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

}