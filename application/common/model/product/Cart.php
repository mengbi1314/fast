<?php

namespace app\common\model\product;

use think\Model;

class Cart extends Model
{
    protected $name = 'order_cart';

    public function product()
    {
        return $this->belongsTo('app\common\model\product\Product', 'proid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
