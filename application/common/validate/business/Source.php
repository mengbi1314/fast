<?php

namespace app\common\validate\business;

use think\Validate;

class Source extends Validate
{
    protected $rule =   [
        'name'  => 'require'
    ];

    protected $message  =   [
        'name.require' => '客户来源名称必填'
    ];
}
