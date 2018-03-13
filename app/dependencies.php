<?php
// DIC configuration

$container = $app->getContainer();

// Service factory for the ORM
$capsule = new \Illuminate\Database\Capsule\Manager;

$capsule->addConnection($container->get('settings')['mysql']);
$capsule->setAsGlobal();
$capsule->bootEloquent();
$container['db'] = $capsule;

/*$container['view'] = function ($c) {
	$settings = \App\Libraries\Config::get('twig');
	$view = new \Slim\Views\Twig($settings['template_path'], $settings['options']);
	//$basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
	$baseUrl = $c['request']->getUri()->getBaseUrl();
	$view->addExtension(new Slim\Views\TwigExtension($c['router'], $baseUrl));
	return $view;
};*/

// monolog
$container['logger'] = function ($c) {
	//初始化日志类
	$lineFormatter = new Monolog\Formatter\LineFormatter("[%datetime%] %channel%.%level_name% => %message% %context% %extra%\n", "Y-m-d H:i:s.u");
	$uidProcessor = new Monolog\Processor\UidProcessor();
	$memoryUsageProcessor = new Monolog\Processor\MemoryUsageProcessor();
	$processIdProcessor = new Monolog\Processor\ProcessIdProcessor();
	$streamHandler = new Monolog\Handler\StreamHandler(LOG_DIR .date('Y-m-d').'.log', Monolog\Logger::DEBUG);
	$streamHandler->setFormatter($lineFormatter);
	$_logger = new Monolog\Logger('mySSO');
	$_logger->pushProcessor($uidProcessor)
		->pushProcessor($memoryUsageProcessor)
		->pushProcessor($processIdProcessor);
	$_logger->pushHandler($streamHandler);
	return $_logger;
};

$container['renderer'] = function ($c) {
	$settings = $c->get('settings')['renderer'];
	return new Slim\Views\PhpRenderer($settings['template_path']);
};

$container['cache'] = function ($c) {
	return \Opdss\Cicache\Cache::factory($c->get('settings')['cache']);
};

//设置session

/*$container['session'] = function ($c) {
	\App\Libraries\File::mkDir(CACHE_DIR.'session');
	$session = \Opdss\Cisession\Session::getInstance($c->get('settings')['session']);
	$session->setLogger($c->logger);
	return $session;
};*/

//设置session
\App\Libraries\File::mkDir(CACHE_DIR.'session');
$session = \Opdss\Cisession\Session::getInstance($container->get('settings')['session']);
$session->setLogger($container->logger);
$session->start();
$container['session'] = $session;

//if (RUN_ENV == 'production') {
//500错误处理
/*$container['errorHandler'] = function ($c) {
	return function ($request, $response, $exception) use ($c) {
		$path = $request->getUri()->getPath();
		$res = array('errCode' => $exception->getCode(), 'errMsg' => $exception->getMessage());
		$c->logger->error($exception->getMessage());
			return $response->withStatus(500)
				->withJson($res);

	};
};*/
//}

//404
$container['notFoundHandler'] = function ($c) {
	return function ($request, $response) use ($c) {
		$path = $request->getUri()->getPath();
			return $response
				->withStatus(404)
				->withJson(\App\Functions::formatApiData(40400));
	};
};

//405
$container['notAllowedHandler'] = function ($c) {
	return function ($request, $response, $methods) use ($c) {
		$return['errMsg'] = 'Method must be one of: ' . implode(', ', $methods);
		return $response
			->withStatus(405)
			->withHeader('Allow', implode(', ', $methods))
			->withJson($return);
	};
};
