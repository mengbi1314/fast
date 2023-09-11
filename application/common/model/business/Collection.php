<?php

namespace app\common\model\business;

use think\Model;

class Collection extends Model
{
    protected $name = 'business_collection';

    // 开启自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
}
