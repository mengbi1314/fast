<?php

namespace app\common\model\product\order;

use think\Model;

class Product extends Model
{
    protected $name = 'order_product';

    public function products()
    {
        return $this->belongsTo('app\common\model\product\Product', 'proid', 'id')->setEagerlyType(0);
    }
}
