<?php
/**
 * FileQueue.php for mySSO.
 * 以文件形式的队列，效率很低，但是够用就好
 * @author SamWu
 * @date 2017/11/30 15:31
 * @copyright boyaa.com
 */

namespace App\Libraries;

class FileQueue
{
	/**
	 * 实例容器
	 * @var array
	 */
	private static $ins = [];

	/**
	 * 文件
	 * @var string
	 */
	private $queueFile;

	private $queueId;

	/**
	 * FileQueue constructor.
	 * @param $queueId
	 */
	private function __construct($queueId)
	{
		$this->queueId = $queueId;
		$dir = DATA_DIR . 'file_queue' . DIRECTORY_SEPARATOR;
		if (!File::mkDir($dir)) {
			throw new \Exception(__METHOD__.' -> '.$dir.'目录没有操作权限！');
		}
		$this->queueFile = $dir . md5($queueId) . '.queue';
	}

	/**
	 * @param $file
	 * @return FileQueue
	 */
	public static function getInstance($queueId = '')
	{
		$queueId = $queueId ?: self::genQueueId();
		if (!isset(self::$ins[$queueId]) || !(self::$ins[$queueId] instanceof self)) {
			self::$ins[$queueId] = new self($queueId);
		}
		return self::$ins[$queueId];
	}

	public static function genQueueId($pre = '')
	{
		$id = md5(uniqid());
		return $pre ? $pre . $id : $id;
	}

	/**
	 * （头部）入队
	 */
	public function unshift($data)
	{
		$arr = $this->_readData();
		array_unshift($arr, $data);
		$this->_writeData($arr);
		return count($arr);
	}

	/**
	 * （头部）出队
	 */
	public function shift()
	{
		$arr = $this->_readData();
		$data = array_shift($arr);
		$this->_writeData($arr);
		return $data;
	}

	/**
	 * （尾部）入队
	 */
	public function push($data)
	{
		$arr = $this->_readData();
		array_push($arr, $data);
		$this->_writeData($arr);
		return count($arr);
	}

	/**
	 * （尾部）出队
	 */
	public function pop()
	{
		$arr = $this->_readData();
		$data = array_pop($arr);
		$this->_writeData($arr);
		return $data;
	}

	/**
	 * 长度
	 */
	public function size()
	{
		$arr = $this->_readData();
		return count($arr);
	}

	/**
	 * 清空
	 * @return bool
	 */
	public function clear()
	{
		return @unlink($this->queueFile);
	}

	public function exists()
	{
		return file_exists($this->queueFile);
	}

	/**
	 * 初始化
	 * @return bool
	 */
	public function init()
	{
		return (bool)$this->_writeData(array());
	}

	public function getQueueId()
	{
		return $this->queueId;
	}

	public function getQueueFile()
	{
		return $this->queueFile;
	}

	/**
	 * 禁止clone对象
	 * @return FileQueue
	 */
	private function __clone()
	{
	}

	/**
	 * 读取文件
	 * @return array
	 */
	private function _readData()
	{
		$data = is_readable($this->queueFile) ? file_get_contents($this->queueFile) : array();
		return $data ? unserialize($data) : array();
	}

	/**
	 * 写入文件
	 * @param $data
	 * @return bool|int
	 */
	private function _writeData($data)
	{
		$data = serialize($data);
		return file_put_contents($this->queueFile, $data, LOCK_EX);
	}
}