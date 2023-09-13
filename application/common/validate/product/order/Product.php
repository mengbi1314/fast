<?php

namespace app\common\validate\product\order;


use think\Validate;

class Product extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'orderid' => 'require', //必填
        'proid' => 'require', //必填
        'pronum' => 'require|gt:0', //必填
        'price' => 'require|egt:0', //必填
        'total' => 'require|egt:0', //必填
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [
        'orderid.require' => '订单ID未知',
        'proid.require' => '商品ID未知',
        'pronum.require' => '请填写商品数量',
        'price.require' => '请填写商品的单价',
        'total.require' => '请填写商品的总价',
    ];
}
