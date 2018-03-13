<?php
/**
 * CmdColor.php for mySSO.
 * @author SamWu
 * @link http://www.neatstudio.com/show-2568-1.shtml
 * @date 2017/11/2 11:07
 * @copyright boyaa.com
 */

namespace App\Libraries;

/**
 * Class CmdColor
 * @method highlight
 * @method underline
 * @method redisplay
 * @method colorize
 * @package App\Libraries
 */
class ColorNew
{
	const REP = '{$text}';

	private $text = '';
	private $format = self::REP;
	/**
	 * 背景色
	 * @var array
	 */
	private static $colorize = array(
		'black' => '40m', //黑色
		'red' => '41m', //红色
		'green' => '42m', //绿色
		'yellow' => '43m', //黄色
		'blue' => '44m', //蓝色
		'violet' => '45m', //紫色
		'cyan' => '46m', //青色
		'white' => '47m', //白色
	);

	/**
	 * 字体色
	 * @var array
	 */
	private static $colorfont = array(
		'black' => '30m',
		'red' => '31m',
		'green' => '32m',
		'yellow' => '33m',
		'blue' => '34m',
		'violet' => '35m',
		'cyan' => '36m',
		'white' => '37m',
	);

	/**
	 * 控制属性
	 * @var array
	 */
	private static $console = array(
		'close' => '0m', //关闭所有属性
		'highlight' => '1m', //高亮
		'underline' => '4m', //下划线
		'twinkle' => '5m', //闪烁
		'redisplay' => '7m', //反显
		'blanking' => '8m', //消隐
		'cursorUp' => '{0}A', //关闭上移n行
		'cursorDown' => '{0}B', //关闭下移n行
		'cursorRight' => '{0}C', //关闭右移n行
		'cursorLeft' => '{0}D', //关闭左移n行
		'clear' => '2J', //清屏
		'hideCursor' => '?25l', //隐藏光标
		'displayCursor' => '?25h', //显示光标
		'setCursor' => '{0};{1}H', //设置光标位置(左上角起第n行，第n列)
		'clearCursorContent' => 'K', //清除从光标到行尾的内容
		'saveCursorOffset' => 's', // 保存光标位置
		'reCursorOffset' => 'u', //恢复光标位置
	);

	public function __construct($text = '')
	{
		$this->setText($text);
	}

	/**
	 * @param string $text
	 * @return static
	 */
	public static function factory($text = '')
	{
		return new static($text);
	}

	/**
	 * @param $text
	 * @return $this
	 */
	public function setText($text)
	{
		$this->text = $text;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getContent()
	{
		return str_replace(self::REP, $this->text, $this->format);
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->getContent();
	}

	/**
	 * @param $name
	 * @param $arguments
	 * @return $this
	 */
	public function __call($name, $arguments)
	{
		if ($name == 'colorize') {
			$color = isset($arguments[0]) ? $arguments[0] : null;
			$cmd = $color && isset(static::$colorize[$color]) ? static::$colorize[$color] : null;
			$this->format = $cmd ? static::genStr($this->format, $cmd) : $this->format;
		} elseif ($name == 'colorfont') {
			$color = isset($arguments[0]) ? $arguments[0] : null;
			$cmd = $color && isset(static::$colorfont[$color]) ? static::$colorfont[$color] : null;
			$this->format = $cmd ? static::genStr($this->format, $cmd) : $this->format;
		} else {
			list($format, $text) = static::genConsole($name, $arguments, $this->format);
			$this->format = $format;
			if ($text) {
				$this->text = $text;
			}
		}
		return $this;
	}

	/**
	 * @param $name
	 * @param $arguments
	 * @return $this
	 */
	public static function __callStatic($name, $arguments)
	{
		if ($name == 'colorize' || $name == 'colorfont') {
			$text = isset($arguments[0]) ? $arguments[0] : '';
			$color = isset($arguments[1]) ? $arguments[1] : '';
			return static::factory($text)->{$name}($color);
		} else {
			list($format, $text) = static::genConsole($name, $arguments);
			return static::factory($text)->setFormat($format);
		}
	}

	/**
	 * @param $format
	 * @return $this
	 */
	private function setFormat($format)
	{
		$this->format = $format;
		return $this;
	}

	/**
	 * 生成命令字符串
	 * @param $text
	 * @param $cmd
	 * @return string
	 */
	private static function genStr($text, $cmd)
	{
		return chr(27) . "[" . $cmd . "$text" . chr(27) . "[0m";
	}

	/**
	 * 控制命令相关 static::$console 里的属性方法
	 * @param $name
	 * @param $arguments
	 * @param string $format
	 * @return array
	 */
	private static function genConsole($name, $arguments, $format = self::REP)
	{
		$cmd = isset(static::$console[$name]) ? static::$console[$name] : null;
		$text = '';
		if ($cmd) {
			if (!empty($arguments)) {
				if (strpos($cmd, '{') === false) {
					$text = $arguments[0];
				}
				$ks = array_map(function ($str) {
					return '{' . $str . '}';
				}, array_keys($arguments));

				$cmd = str_replace($ks, $arguments, $cmd);
			}
			$format = static::genStr($format, $cmd);
		}
		return array($format, $text);
	}
}

$a = ColorNew::colorize('测试背景1', 'red')->colorfont('green');
$a->underline();
echo $a.PHP_EOL;

$b = ColorNew::factory('测试2')->colorize('yellow')->colorfont('black');
echo $b.PHP_EOL;

$c = ColorNew::colorfont('测试字体3', 'blue')->colorize('white');
echo $c.PHP_EOL;

$c->twinkle('修改文字1')->colorize('white') ;
echo $c.PHP_EOL;

$b->redisplay();
echo $b.PHP_EOL;

echo ColorNew::redisplay('\"\"')->underline();