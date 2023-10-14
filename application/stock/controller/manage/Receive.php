<?php

namespace app\stock\controller\manage;

use think\Controller;

class Receive extends Controller
{
    /**
     * 查询领取记录
     */
    public function index()
    {
        $adminid = $this->request->param('adminid', 0, 'trim');

        $admin = model('admin.Admin')->find($adminid);

        if (!$admin) {
            $this->error('管理员不存在');
        }

        if ($admin['status'] !== 'normal') {
            $this->error('当前管理员账号已被禁用');
        }

        $receive = model('business.Receive')->with('business')->where('applyid', $adminid)->order('applytime DESC')->select();

        if (!$receive) {
            $this->error('暂无领取记录');
        } else {
            $this->success('查询领取记录成功', null, $receive);
        }
    }
}
