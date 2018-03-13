<?php
/**
 * Created by PhpStorm.
 * User: wuxin
 * Date: 2018/1/29
 * Time: 00:16
 */
return array(
    'determineRouteBeforeAppMiddleware' => false,
    'displayErrorDetails' => true, // set to false in production
    'addContentLengthHeader' => false, // Allow the web server to send the content-length header
    'routerCache' => CACHE_DIR.'slim_router_cache.php', //slim router 路由缓存文件
    'encryptKey' => 'gae2kjfuag342p3okfg0bsiekawge2fa', // 密码加密秘钥
    'httpRequestTimeOut' => 6000, //接口请求超时时间
    //'httpRequestSignKey' => '23e112310af3b657b0c88c5f35e94189', //接口请求签名字符串
    //'accessTokenTimeOut' => 600, //接口请求签名字符串

	'forceUseLoginCaptcha' => true,
	'loginTryNumber' => 5,
	'needCaptchaTry' => 3,

	'renderer' => [
		'template_path' => TPL_DIR,
	],

    'cache' => array(
        'handler' => 'redis',
        'host' => '127.0.0.1',
        'password' => 'XIN~!@#$%^&*123',
        'port' => 6379,
        'timeout' => 0,
		'prefix' => 'mySSO_Cache:',
    ),

    'mysql' => array(
		'driver' => 'mysql',
		'host' => 'localhost:3306',
		'database' => 'gushici',
		'username' => 'prod',
		'password' => 'Wx24Fce&!3gHcnD',
		'charset' => 'utf8',
		'collation' => 'utf8_general_ci',
		'prefix' => '',
    ),

	'session' => array(
		//'sessionDriver' => 'redis',
		'sessionDriver' => 'file',
		'sessionCookieName' => 'gscsess',
		'sessionExpiration' => 7200,
		'sessionSavePath' => CACHE_DIR.'session',
		//'sessionSavePath' => 'tcp://127.0.0.1:6379?auth=XIN~!%40%23%24%25%5E%26*123',
		'sessionMatchIP' => true,
		'sessionTimeToUpdate' => 300,
		'sessionRegenerateDestroy' => true,
		'sessionKeyPrefix' => 'mySSO_Session:',

		'cookieDomain' => '',
		'cookiePath' => '/',
		'cookieSecure' => false,
		'cookieHTTPOnly' => false,
	),
	'sso' => array(
		'test' => array(
			'appid' => 'test',
			'appkey' => 'agawefawehgawehawef',
			'redirect' => 'http://47.93.255.190:8887/',
		),
		'test1' => array(
			'appid' => 'test',
			'appkey' => 'bbbafwefawegawegwe',
			'redirect' => 'http://47.93.255.190:8887/',
		)
	)

);