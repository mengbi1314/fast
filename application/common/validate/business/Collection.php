<?php

namespace app\common\validate\business;

use think\Validate;

// 用户消费记录的验证器
class Collection extends Validate
{
    // require 必填，unique表示该字段在pre_business属于唯一字段
    protected $rule =   [
        'proid'  => 'require',
        'busid'   => 'require',
        'cateid' => 'require',
    ];

    protected $message  =   [
        'proid.require' => '商品未知',
        'busid.require'     => '用户未知',
        'cateid.require'   => '文章未知',
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'product' => ['proid', 'busid'],
        'cate' => ['cateid', 'busid']
    ];
}
