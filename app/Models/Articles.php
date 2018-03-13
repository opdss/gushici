<?php
/**
 * Created by PhpStorm.
 * User: wuxin
 * Date: 2018/3/13
 * Time: 22:11
 */

namespace App\Models;


class Articles extends Base
{
    /**
     * 表名
     * @var string
     */
    protected $table = "articles";

    /**
     * 主键
     * @var string
     */
    protected $primaryKey = 'id';


    public function documents()
    {
        return $this->hasMany('\App\Models\Documents', 'map_id', 'id');
    }

}