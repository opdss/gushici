<?php
/**
 * Base.php for tools.
 * @author SamWu
 * @date 2017/7/4 14:08
 * @copyright istimer.com
 */

namespace App\Controllers;

use App\Functions;
use App\Libraries\Bootstrap;
use App\Libraries\Config;
use App\Libraries\File;
use App\Libraries\Pagination;
use Slim\Container;
use Slim\Http\Request;

class Base
{
	/**
	 * @var Container
	 */
	protected $ci;

	protected $settings;

	protected $css = array();
	protected $js = array();

	/**
	 * Ctrl constructor.
	 * @param Container $ci
	 */
	public function __construct(Container $ci)
	{
		$this->ci = $ci;
		$this->settings = $this->ci->get('settings');

		//$this->addJs('/statics/js/main.js');
		$this->addCss('/statics/css/layout.css');
		//$this->addJs('/statics/js/utils.js');
	}


	protected function addJs($file, $version = 0)
	{
		array_push($this->js, $version ? $file . '?' . $version : $file);
		return $this->js;
	}

	protected function addCss($file, $version = 0)
	{
		array_push($this->css, $version ? $file . '?' . $version : $file);
		return $this->css;
	}

	protected function addStaticsDir($dir, $dep = 1, $version = 0)
	{
		$path = PUBLIC_DIR . 'statics/'.ltrim($dir, '/');
		$files = File::getFileNames($path, 1, $dep);
		if ($files) {
			foreach ($files as $item) {
				if (substr($item, -3) == '.js') {
					$f = str_replace(PUBLIC_DIR, '/', $item);
					$this->addJs($f, $version);
				} elseif (substr($item, -4) == '.css') {
					$f = str_replace(PUBLIC_DIR, '/', $item);
					$this->addCss($f, $version);
				}
			}
		}
		return $files;
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
		//$extra['runTime'] = Functions::runTime('run', 1);
		if (func_num_args() == 1) {
			$data = $extra;
		}
		return $this->ci->get('response')->withJson(Functions::formatApiData($param, $data, $extra));
	}

	protected function view($tpl, $data = array())
	{
		$render_data['site'] = $this->ci->get('settings')['site'];
		$render_data['statics'] = array('css' => $this->css, 'js' => $this->js);
		$render_data['Functions'] = new Functions;
		$render_data = array_merge($render_data, $data);
		$render_data['title'] = isset($data['title']) ? $data['title'] .'-'.$render_data['site']['title'] : $render_data['site']['title'];
		$render_data['keyword'] = isset($data['title']) ? $data['title'].','.$render_data['site']['keyword'] : $render_data['site']['keyword'];
		$render_data['description'] = $render_data['keyword'];
		$render_data['runtime'] = round(\App\Functions::runTime('run', true), 6);
		return $this->ci->view->render($this->ci->response, $tpl, $render_data);
	}

	protected function pagination($total, $pageSize = 10)
	{
		$temp = array(
			0 => '<li><span>共有<b>{#total}</b>个记录</span></li>',
			1 => '<li><span>每页显示<b>{#pageSize}</b>条，本页<b>{#start}-{#end}</b>条</span></li>',
			2 => '<li><span><b>{#page}/{#pageTotal}</b>页</span></li>',
			'go_page' => '<li><input type="text" onkeydown="javascript:if(event.keyCode==13){var page=(this.value>{#pageTotal})?{#pageTotal}:this.value;location=\'{#baseUrl}&{#pageName}=\'+page;}" value="{#page}" style="width:25px"><input type="button" value="GO" onclick="javascript:var page=(this.previousSibling.value>{#pageTotal})?{#pageTotal}:this.previousSibling.value;location=\'{#baseUrl}&{#pageName}=\'+page;"></li>',

			'fl_active' => '<li><a href="{#url}"><span aria-hidden="true">{#title}</span></a></li>', //首页尾页有链接模版
			'fl_not_active' => '<li><a href="#"><span aria-hidden="true">{#title}</span></a></li>', //首页尾页没有链接模版
			'pn_active' => '<li><a href="{#url}"><span aria-hidden="true">{#title}</span></a></li>',
			'pn_not_active' => '<li><a href="#"><span aria-hidden="true">{#title}</span></a></li>',
			'list_active' => '<li><a href="#">{#page}</a></li>', //分页列表没有链接模版
			'list_not_active' => '<li><a href="{#url}">{#page}</a></li>',//分页列表有链接模版
		);
		$config = array(
			"prev" => "上一页",
			"next" => "下一页",
			"first" => "首 页",
			"last" => "尾 页",
			'div_prev' => '<nav aria-label="Page navigation"><ul class="pagination">',
			'div_next' => '</ul></nav>'
		);


		$pagination = new Pagination($total, $pageSize);
		return $pagination->setTemp($temp)->setConfig($config)->fpage(array(3, 4, 5, 6, 7));
	}
}