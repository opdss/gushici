<?php
/**
 * Functions.php for wa_poker.
 * @author SamWu
 * @date 2017/6/2 15:29
 * @copyright istimer.com
 */

namespace App;

use Opdss\Docparser\Docparser;
use App\Libraries\File;
use Monolog;
use App\Libraries\Config;

class Functions
{
	public static function formatApiData($param = 0, $data = array(), $extra = array(), $json = false)
	{
		$errMap = array(
			-1 => 'process',
			0 => 'success',
			1 => '处理失败',
			40001 => '参数错误',
			40002 => '上传文件为空',
			40100 => 'token参数为空',
			40101 => 'token已经失效',
			//表单类错误
			40110 => '账号或者密码错误',
			40111 => '账号不存在',
			40112 => '密码错误',
			40113 => '账号已经存在',
			40114 => '验证码错误',
			40115 => '更新对象错误',

			40190 => '签名错误',
			40191 => '请求频繁',
			40192 => '请求超时',

			40300 => '需要登录',
			40301 => '没有权限修改',
			40400 => '访问资源不存在',
			40500 => '访问方法不允许',
			50000 => '内部服务器错误',
		);
		if (is_numeric($param) || is_string($param)) {
			$code = is_numeric($param) ? (isset($errMap[$param]) ? $param : 50000) : 1;
			$msg = $code == 1 && is_string($param) ? $param : $errMap[$code];
			if ($code != 0) {
				is_string($data) AND $msg = $data;
				is_array($data) AND $extra = array_merge($data, $extra);
				$data = array();
			}
		} else {
			$code = 0;
			$msg = $errMap[$code];
			$extra = empty($data) ? array() : $data;
			$data = $param;
		}
		$ret = array(
			'errCode' => $code,
			'errMsg' => $msg,
		);
		empty($data) || $ret['data'] = $data;
		empty($extra) || $ret['extra'] = $extra;
		return $json ? json_encode($ret) : $ret;
	}


	public static function encrypt($string, $key, $operation = 'E')
	{
		$key = md5($key);
		$key_length = strlen($key);
		$string = $operation == 'D' ? base64_decode($string) : substr(md5($string . $key), 0, 8) . $string;
		$string_length = strlen($string);
		$rndkey = $box = array();
		$result = '';
		for ($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($key[$i % $key_length]);
			$box[$i] = $i;
		}
		for ($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
		for ($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}
		if ($operation == 'D') {
			if (substr($result, 0, 8) == substr(md5(substr($result, 8) . $key), 0, 8)) {
				return substr($result, 8);
			} else {
				return '';
			}
		} else {
			return str_replace('=', '', base64_encode($result));
		}
	}

    /**
     * curl请求
     * @param $url
     * @param null $data 发送数据
     * @param string $method 请求方法
     * @param array $headers 请求头
     * @param null $cookies 携带cookie
     * @param array $options 其他标准curl选项
     * @param null $info 请求信息
     * @return mixed|null
     */
	public static function iCurl($url, $data = null, $method = 'get', array $headers = array(), $cookies = null, array $options = array(), &$info = null)
    {
        $method = strtoupper($method);
        if ($data) {
            if ($method == 'GET') {
                $data = is_array($data) ? http_build_query($data) : $data;
                $url = strpos($url, '?') !== false ? $url . '&' . $data : $url . '?' . $data;
                $curl = curl_init($url);
            } else {
                $curl = curl_init($url);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
        } else {
            $curl = curl_init($url);
        }
        //设置选项
        curl_setopt_array($curl, array(
            CURLOPT_TIMEOUT => 30, //超市时间
            CURLOPT_CUSTOMREQUEST => $method,// 请求方法
            CURLOPT_RETURNTRANSFER => true,// 返回内容
            CURLOPT_HEADER => false,// 返回header
            CURLOPT_FOLLOWLOCATION => true,// 自动重定向
            CURLOPT_SSL_VERIFYPEER => false,// 不校验证书
        ));

        //设置头信息
        if (!empty($headers)) {
            $_headers = [];
            foreach ($headers as $name => $value) { //处理成CURL可以识别的headers格式
                $_headers[] = $name . ':' . $value;
            }
            curl_setopt($curl, CURLOPT_HTTPHEADER, $_headers);
        }
        //设置cookie
        if (!empty($cookies)) {
            $_cookies = '';
            if (is_array($cookies)) {
                foreach ($cookies as $name => $value) {
                    $_cookies .= "{$name}={$value}; ";
                }
            } else {
                $_cookies = $cookies;
            }
            curl_setopt($curl, CURLOPT_COOKIE, $_cookies);
        }
        //其他特殊选项
        if (!empty($options)) {
            curl_setopt_array($curl, $options);
        }
        //执行请求
        $output = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        if ($info['http_code'] == 200) {
            return $output;
        }
        $info['output'] = $output;
        return null;
    }

	public static function getIP()
	{
		$ip = $_SERVER['REMOTE_ADDR'];
		if (isset($_SERVER['HTTP_CDN_SRC_IP'])) {
			$ip = $_SERVER['HTTP_CDN_SRC_IP'];
		} elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
			foreach ($matches[0] AS $xip) {
				if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
					$ip = $xip;
					break;
				}
			}
		}
		return $ip;
	}

	/**
	 * @return Redis
	 */
	public static function getRedis()
	{
		static $redis = null;
		if (!$redis) {
			$redis_conf = \App\Libraries\Config::get('redis');
			$redis = new \Redis();
			$redis->connect($redis_conf['host'], $redis_conf['port'], 5);
			$redis->auth($redis_conf['password']);
		}
		return $redis;
	}

	/**
	 * 匹配身份证号
	 * @param $test
	 * @return bool
	 */
	public static function isIC($test)
	{
		if (strlen($test) != 18) {
			return false;
		}
		$r = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
		$sum = 0;
		for ($i = 0; $i < 17; $i++) {
			$sum += $test[$i] * $r[$i];
		}
		$t = array(1, 0, 'x', 9, 8, 7, 6, 5, 4, 3, 2);
		return strtolower($test[17]) == $t[$sum % 11];
	}

	public static function getMicroTime()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}

	public static function isCli()
	{
		return PHP_SAPI == 'cli'  || defined('STDIN');
	}

	public static function runTime($name, $isGet = false)
	{
		static $_times = array();
		if ($isGet) {
			return isset($_times[$name]) ? (self::getMicroTime() - $_times[$name]) : 0;
		} else {
			$_times[$name] = self::getMicroTime();
			return $_times[$name];
		}
	}

	public static function sign(array $data, $appkey)
	{
		ksort($data);
		return md5(http_build_query($data).$appkey);
	}

	public static function verifySign($data, $appkey)
	{
		$sign = isset($data['sign']) ? $data['sign'] : '';
		if (!$sign) {
			return false;
		}
		unset($data['sign']);
		ksort($data);
		return md5(http_build_query($data).$appkey) == $sign;
	}

	public static function formatJson($json, $unescapeUnicode = false, $unescapeSlashes = false)
	{
	   $result = '';
	   $pos = 0;
	   $strLen = strlen($json);
	   $indentStr = '    ';
	   $newLine = "\n";
	   $outOfQuotes = true;
	   $buffer = '';
	   $noescape = true;

	   for ($i = 0; $i < $strLen; $i++) {

		  $char = substr($json, $i, 1);


		  if ('"' === $char && $noescape) {
			 $outOfQuotes = !$outOfQuotes;
		  }

	if (!$outOfQuotes) {
		$buffer .= $char;
		$noescape = '\\' === $char ? !$noescape : true;
		continue;
	} elseif ('' !== $buffer) {
		if ($unescapeSlashes) {
			$buffer = str_replace('\\/', '/', $buffer);
		}

		if ($unescapeUnicode && function_exists('mb_convert_encoding')) {

			$buffer = preg_replace_callback('/(\\\\+)u([0-9a-f]{4})/i', function ($match) {
				$l = strlen($match[1]);

				if ($l % 2) {
					return str_repeat('\\', $l - 1) . mb_convert_encoding(
							pack('H*', $match[2]),
							'UTF-8',
							'UCS-2BE'
						);
				}

				return $match[0];
			}, $buffer);
		}

		$result .= $buffer.$char;
		$buffer = '';
		continue;
	}

	if (':' === $char) {

		$char .= ' ';
	} elseif (('}' === $char || ']' === $char)) {
		$pos--;
		$prevChar = substr($json, $i - 1, 1);

		if ('{' !== $prevChar && '[' !== $prevChar) {


			$result .= $newLine;
			for ($j = 0; $j < $pos; $j++) {
				$result .= $indentStr;
			}
		} else {

			$result = rtrim($result);
		}
	}

	$result .= $char;



	if (',' === $char || '{' === $char || '[' === $char) {
		$result .= $newLine;

		if ('{' === $char || '[' === $char) {
			$pos++;
		}

		for ($j = 0; $j < $pos; $j++) {
			$result .= $indentStr;
		}
	}
	}

	return $result;
	}

}
