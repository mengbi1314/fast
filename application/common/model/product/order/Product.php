<?php

namespace app\common\model\product\order;

use think\Model;

class Product extends Model
{
    protected $name = 'product_order';

    public function product()
    {
        return $this->belongsTo('app\common\model\Product\product', 'proid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function business()
    {
        return $this->belongsTo('app\common\model\Business\Business', 'busid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
