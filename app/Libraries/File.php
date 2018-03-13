<?php
/**
 * 文件操作类
 * File.php for mySSO.
 * @author SamWu
 * @date 2017/7/27 13:56
 * @copyright istimer.com
 */

namespace App\Libraries;

class File
{
	/**
	 * 读取文件
	 * @param $file_name
	 * @return bool|string
	 */
	public static function read($file)
	{
		if (!is_readable($file)) {
			//return false;
		}
		return file_get_contents($file);
	}

	/**
	 * File::read 别名
	 * @param $file_name
	 * @return bool|string
	 */
	public static function get($file)
	{
		return self::read($file);
	}

	/**
	 * 写文件,file_put_content 别名
	 * @param $file_name
	 * @param $data
	 * @param int $try
	 * @return bool
	 */
	public static function write($file, $data, $flags = LOCK_EX, $context = null)
	{
		if (!self::mkDir(dirname($file))) {
			return false;
		}
		return file_put_contents($file, $data, $flags, $context);
	}

	/**
	 * 写入return返回值形式的php文件
	 * @param $file_name
	 * @param array|string $data
	 * @param array|string $ext 扩展信息
	 * @return bool
	 */
	public static function writeRetPhp($file_name, $data, $ext = '')
	{
		$str = '';
		if (is_array($data)) {
			$str = var_export($data, true);
		} elseif (is_string($data)) {
			$str = "'" . addslashes($data) . "'";
		}
		if (empty($str)) {
			return false;
		}
		$ext = empty($ext) ? null : (is_array($ext) ? json_encode($ext) : $ext);
		$ext = $ext ? "\r\n// {$ext}" : '';
		$str = "<?php\r\n// " . date('Y-m-d H:i:s') . $ext . "\r\n return {$str};\r\n";
		return self::write($file_name, $str);
	}

	/**
	 * 删除文件
	 * @param string $path 删除文件或者目录名
	 * @param bool $del_dir 是否删除目录,如果$path为目录，0:将删除该目录下所有文件，保留所有子目录，1:删除此目录下所有文件和子目录，保留此目录，-1:删除此目录下所有文件和子目录，包括此目录
	 * @param bool $htdocs 是否删除目录下特殊文件
	 * @param int $_level 内部递归调用时使用，外部不用管
	 * @return bool
	 */
	function delete($file, $del_dir = 1, $_level = 0)
	{
		if (!is_dir($file)) {
			return @unlink($file);
		}
		$path = rtrim($file, '/\\');
		if (!$current_dir = @opendir($path)) {
			return false;
		}
		while (false !== ($filename = @readdir($current_dir))) {
			if ($filename !== '.' && $filename !== '..') {
				$filepath = $path . DIRECTORY_SEPARATOR . $filename;
				if (is_dir($filepath) && $filename[0] !== '.' && !is_link($filepath)) {
					delete($filepath, $del_dir, $_level + 1);
				} else {
					@unlink($filepath);
				}
			}
		}
		closedir($current_dir);
		if ($del_dir == 1 && $_level > 0) {
			return @rmdir($path);
		} elseif ($del_dir == -1) {
			return @rmdir($path);
		}
		return true;
	}

	/**
	 * 获取目录包含文件列表
	 * @param $source_dir
	 * @param bool $full_path 是否返回全路径，0:只有文件名，1：全路径，2：相对于初始路径的相对路径
	 * @param int $depth 递归深度 默认0:所有
	 * @param bool $_recursion 内部递归调用参数，外部调用不用管
	 * @return array|bool
	 */
	public static function getFileNames($source_dir, $full_path = 0, $depth = 0, $_recursion = false)
	{
		static $_filedata = array();
		static $pre = '';
		static $_depth = 0;
		if ($fp = @opendir($source_dir)) {
			if ($_recursion === false) {
				$_filedata = array();
				$source_dir = rtrim(realpath($source_dir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
				$pre = $source_dir;
				$_depth = 0;
			}
			$_depth++;
			while (false !== ($file = readdir($fp))) {
				if (is_dir($source_dir . $file) && $file[0] !== '.') {
					if ($depth && $_depth >= $depth) {
						continue;
					}
					self::getFileNames($source_dir . $file . DIRECTORY_SEPARATOR, $full_path, $depth, TRUE);
				} elseif ($file[0] !== '.') {
					switch ((int)$full_path) {
						case 1:
							$_filedata[] = $source_dir . $file;
							break;
						case 2:
							$_filedata[] = str_replace($pre, '', $source_dir . $file);
							break;
						default:
							$_filedata[] = $file;
							break;
					}
				}
			}
			closedir($fp);
			return $_filedata;
		}
		return false;
	}

	public static function getFileList($source_dir, $full_path = 0, $depth = 0, $_recursion = false)
	{
		return self::getFileNames($source_dir, $full_path, $depth, $_recursion);
	}

	/**
	 * 获取目录下所有文件的信息
	 * @param $source_dir
	 * @param bool $top_level_only
	 * @param bool $_recursion
	 * @return array|bool
	 */
	public static function getDirFileInfo($source_dir, $top_level_only = true, $_recursion = false)
	{
		static $_filedata = array();
		$relative_path = $source_dir;
		if ($fp = @opendir($source_dir)) {
			if ($_recursion === FALSE) {
				$_filedata = array();
				$source_dir = rtrim(realpath($source_dir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
			}
			while (false !== ($file = readdir($fp))) {
				if (is_dir($source_dir . $file) && $file[0] !== '.' && $top_level_only === false) {
					self::getDirFileInfo($source_dir . $file . DIRECTORY_SEPARATOR, $top_level_only, true);
				} elseif ($file[0] !== '.') {
					$_filedata[$file] = self::getInfo($source_dir . $file);
					$_filedata[$file]['relative_path'] = $relative_path;
				}
			}
			closedir($fp);
			return $_filedata;
		}
		return false;
	}

	/**
	 * 获取文件信息
	 * @param $file
	 * @param array $returned_values
	 * @return bool
	 */
	public static function getInfo($file, $returned_values = array('name', 'server_path', 'size', 'date', 'pathinfo'))
	{
		if (!file_exists($file)) {
			return false;
		}
		if (is_string($returned_values)) {
			$returned_values = explode(',', $returned_values);
		}
		foreach ($returned_values as $key) {
			switch ($key) {
				case 'name':
					$fileinfo['name'] = basename($file);
					break;
				case 'server_path':
					$fileinfo['server_path'] = realpath($file);
					break;
				case 'size':
					$fileinfo['size'] = filesize($file);
					break;
				case 'date':
					$fileinfo['date'] = filemtime($file);
					break;
				case 'readable':
					$fileinfo['readable'] = is_readable($file);
					break;
				case 'writable':
					$fileinfo['writable'] = is_really_writable($file);
					break;
				case 'executable':
					$fileinfo['executable'] = is_executable($file);
					break;
				case 'fileperms':
					$fileinfo['fileperms'] = fileperms($file);
					break;
				case 'pathinfo':
					$fileinfo['pathinfo'] = pathinfo($file);
					break;
			}
		}
		return $fileinfo;
	}

	/**
	 * 创建目录
	 * @param $path
	 * @param int $mode
	 * @return bool
	 */
	public static function mkDir($dir, $mode = 0755)
	{
		if (empty($dir)) return false;
		if (!file_exists($dir)) {
			return (bool)@mkdir($dir, $mode, true);
		} else {
			return is_writable($dir);
		}
	}

	/**
	 * 递归复制文件或者目录，不存在的目录会自动创建
	 * @param $source
	 * @param $destination
	 * @return bool
	 */
	public static function copy($source, $destination)
	{
		if (!file_exists($source)) {
			return false;
		}
		if (is_dir($source)) {
			$flag = false;
			if ($fp = opendir($source)) {
				while (false !== ($file = readdir($fp))) {
					if ($file != '.' && $file != '..') {
						$flag = self::copy($source .DIRECTORY_SEPARATOR. $file, $destination.DIRECTORY_SEPARATOR.$file);
					}
				}
				closedir($fp);
			}
			return $flag;
		} else {
			$dir = dirname($destination);
			if (!self::mkDir($dir)) {
				return false;
			}
			return copy($source, $destination);
		}
	}

	/**
	 * 检查文件是否跟上一次有变化
	 * @param $file 支持文件或者文件夹
	 * @param string $cacheDir 结果存放的缓存路径
	 * @return bool
	 */
	public static function isModify($file, $cacheDir = DATA_DIR)
	{
		if (!is_writable($cacheDir)) {
			return true;
		}
		$cacheDir = rtrim($cacheDir, DIRECTORY_SEPARATOR);
		if (is_dir($file)) {
			$type = 'dir';
			$files = self::getFileNames($file, 1);
		} elseif (file_exists($file)) {
			$type = 'file';
			$files = array($file);
		} else {
			return true;
		}
		$cacheFile = $cacheDir . DIRECTORY_SEPARATOR . 'modify_' . $type . '_' . md5($file);
		$cacheData = file_exists($cacheFile) ? unserialize(self::read($cacheFile)) : array();
		$cache = array();
		$flag = false;
		foreach ($files as $f) {
			$cache[$f] = md5_file($f);
			if ($flag) {
				continue;
			}
			if (!isset($cacheData[$f]) || $cacheData[$f] !== $cache[$f]) {
				$flag = true;
			}
		}
		if ($flag) {
			self::write($cacheFile, serialize($cache));
		}
		return $flag;
	}

}
