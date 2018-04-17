<?php
/**
 * icon.php for gushici
 * @author SamWu
 * @date 2018/4/17 10:06
 * @copyright boyaa.com
 */

$icons = include('cache/icon.php');

$cacheDir = './cache/';

$data = [];

foreach ($icons as $item) {
	$_icon = $item['_icon'];
	$_arr = explode('/', $_icon);
	$name = array_pop($_arr);
	$data[$item['id']] = 'http://img.isnoter.com/gsc/author/'.$name;
}


file_put_contents($cacheDir.'succ.php', "<?php \r\n return ".var_export($data, true).';');