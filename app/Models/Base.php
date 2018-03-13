<?php
/**
 * Base.php for deploy.
 * @author SamWu
 * @date 2017/8/2 10:52
 * @copyright istimer.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Base extends Model
{
	public function scopeMultiWhere($query, $arr)
	{
		if (!is_array($arr)) {
			return $query;
		}

		foreach ($arr as $key => $value) {
			$query = $query->where($key, $value);
		}
		return $query;
	}

}