<?php
// 定义运行环境
define("RUN_ENV", isset($_SERVER['RUN_ENV']) ? $_SERVER['RUN_ENV'] : 'development');
define("ROOT", realpath(dirname(__DIR__)) . DIRECTORY_SEPARATOR); //根目录
define("VENDOR_DIR", ROOT . "vendor" . DIRECTORY_SEPARATOR);
define("APP_DIR", ROOT . "app" . DIRECTORY_SEPARATOR); //项目目录
define("LOG_DIR", ROOT . "logs" . DIRECTORY_SEPARATOR); //日志目录 运行时需要读写权限
define("CACHE_DIR", ROOT . "cache" . DIRECTORY_SEPARATOR); //系统缓存目录 运行时需要读写权限
define("TPL_DIR", ROOT . "templates" . DIRECTORY_SEPARATOR); //系统缓存目录 运行时需要读写权限
define("PUBLIC_DIR", ROOT . "public" . DIRECTORY_SEPARATOR);  //web访问目录

// 自动载入类库
if (file_exists(VENDOR_DIR . "autoload.php")) {
    require_once VENDOR_DIR . "autoload.php";
} else {
    die("<pre>vendor目录不存在，请运行`composer install`</pre>");
}

/**
 * 设置时区
 */
date_default_timezone_set('ETC/GMT-8');

/**
 * 定义错误级别
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);


//运行时间打点
\App\Functions::runTime('run');

// 实例化App
$app = new \Slim\App(array('settings' => (require APP_DIR.'config.php')));

// 设置依赖
require APP_DIR . 'dependencies.php';


// 命令行模式
if (PHP_SAPI == 'cli') {
	exit();
}

// 根据注释注册路由
\Opdss\Nroute\Nroute::factory(array('cacheDir'=>CACHE_DIR))->register($app, array(APP_DIR . 'Controllers' => 'App\\Controllers'));

$app->run();