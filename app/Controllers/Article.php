<?php
/**
 * Article.php for gushici.
 * @author SamWu
 * @date 2018/3/13 18:29
 * @copyright boyaa.com
 */
namespace App\Controllers;

use App\Models\Articles;
use Slim\Http\Request;
use Slim\Http\Response;

class Article extends Api
{
	/**
	 * 古诗文列表
	 * @pattern /articles
	 * @param Request $request
	 * @param Response $response
	 * @param $args
	 */
	public function lists(Request $request, Response $response, $args)
	{
	    $page = (int)$request->getParam('p') ?: 1;
	    $offset = (int)$request->getParam('offset') ?: 10;
	    $kw = trim($request->getParam('kw')) ?: '';

	    $json = array(
	        'errCode' => 0,
            'errMsg' => '',
            'data' => []
        );

	    $builder = Articles::where('title', 'like', '%'.$kw.'%');

	    $pageInfo = array(
	        'totalCount' => $builder->count(),
            'currentPage' => $page,
            'offset' => $offset,
			'kw' => $kw
        );

        $res = $builder->limit($offset)->skip(($page-1)*$offset)->get();
        $data = $res;

        $json['data'] = array(
            'pageInfo' => $pageInfo,
            'records' => $data
        );
        return $response->withJson($json);
	}

	/**
	 * 古诗文详情
	 * @pattern /article/{id}
	 * @param Request $request
	 * @param Response $response
	 * @param $args
	 */
	public function detail(Request $request, Response $response, $args)
	{

	}
}