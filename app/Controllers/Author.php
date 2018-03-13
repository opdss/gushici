<?php
/**
 * Author.php for gushici.
 * @author SamWu
 * @date 2018/3/13 18:29
 * @copyright boyaa.com
 */
namespace App\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;

class Author extends Api
{
	/**
	 * 古诗人列表
	 * @pattern /authors
	 * @param Request $request
	 * @param Response $response
	 * @param $args
	 */
	public function lists(Request $request, Response $response, $args)
	{

	}

	/**
	 * 古诗人详情
	 * @pattern /author/{id}
	 * @param Request $request
	 * @param Response $response
	 * @param $args
	 */
	public function detail(Request $request, Response $response, $args)
	{

	}
}