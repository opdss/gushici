<?php
/**
 * Users.php for deploy.
 * @author SamWu
 * @date 2017/7/4 17:12
 * @copyright istimer.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Users extends Base
{
	use SoftDeletes;
	/**
	 * 表名
	 * @var string
	 */
	protected $table = "users";

	/**
	 * 主键
	 * @var string
	 */
	protected $primaryKey = 'uid';


	/**
	 * The attributes that should be mutated to dates.
	 * @var array
	 */
	protected $dates = ['deleted_at'];

	/**
	 * toArray时在数组中想要隐藏的属性。
	 *
	 * @var array
	 */
	protected $hidden = ['password'];

	/**
	 * toArray时在数组中可见的属性。
	 *
	 * @var array
	 */
	//protected $visible = ['username', 'nickname'];

	public static function login($username, $password, $isEmail = false)
	{
		$builder = $isEmail ? self::where('email', $username) : self::where('username', $username);
		$user = $builder->first();
		if (empty($user) || !self::passwordVerify($password, $user->password)) {
			return false;
		}
		$user->token = $user->genToken();
		$user->save();
		return $user->toArray();
	}

	public static function passwordHash($password)
	{
		return password_hash($password, PASSWORD_DEFAULT);
	}

	public static function passwordVerify($password, $hash)
	{
		return password_verify($password, $hash);
	}

	public function genToken()
	{
		return md5($this->username.$this->password.time());
	}
}