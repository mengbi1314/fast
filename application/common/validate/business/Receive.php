<?php

namespace app\common\validate\business;

// 引入tp的验证器
use think\Validate;

// 客户验证器
class Receive extends Validate
{
    // 验证规则
    protected $rule = [
        'applyid' => ['require'],
        'busid' => ['require'],
        'status' => 'in:apply,allot,recovery,reject', // apply 申请 allot 分配 recovery 回收 reject  拒绝
    ];

    // 错误提示信息
    protected $message = [
        'applyid.require' => '申请人未知',
        'busid.require' => '领取用户ID未知',
        'invitecode.require' => '邀请码未知'
    ];

    // 验证场景
    protected $scene = [];
}
