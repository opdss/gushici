<?php
/**
 * Article.php for gushici.
 * @author SamWu
 * @date 2018/3/13 18:29
 * @copyright boyaa.com
 */
namespace App\Controllers;

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