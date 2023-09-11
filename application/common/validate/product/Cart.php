<?php

namespace app\common\validate\product;

use think\Validate;

class Cart extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'busid' => 'require', //必填
        'proid' => 'require', //必填
        'pronum' => 'require', //必填
        'price' => 'require', //必填
        'total' => 'require', //必填
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [
        'busid.require'  => '用户未知',
        'proid.require'  => '商品未知',
        'pronum.require'  => '请选择商品数量',
        'price.require'  => '请输入商品的单价',
        'total.require'  => '请输入商品总价',
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'edit' => ['pronum', 'total']
    ];
}
