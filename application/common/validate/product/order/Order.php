<?php

namespace app\common\validate\product\order;

use think\Validate;

class Order extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'busid' => 'require', //必填
        'businessaddrid' => 'require', //必填
        'code' => 'require|unique:order',
        'status' => 'number|in:0,1,2,3,4',  //给字段设置范围
        'amount' => 'require'
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [
        'busid.require'  => '用户信息未知',
        'businessaddrid.require'  => '收货地址未知',
        'code.require' => '订单号必填',
        'code.unique' => '订单号已存在，请重新输入',
        'amount.unique' => '订单号金额未知',
    ];
}
