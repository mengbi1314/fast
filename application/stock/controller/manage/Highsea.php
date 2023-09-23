<?php

namespace app\stock\controller\manage;

use think\Controller;
use think\Db;

class Highsea extends Controller
{
    // 客户模型
    protected $BusinessModel = null;

    // 管理员模型
    protected $AdminModel = null;

    public function __construct()
    {
        parent::__construct();

        $this->BusinessModel = model('business.Business');
        $this->AdminModel = model('admin.Admin');

        $adminid = $this->request->param('adminid', '', 'trim');

        $admin = $this->AdminModel->find($adminid);

        if (!$admin) {
            $this->error('当前管理员账号不存在');
        }

        if ($admin['status'] !== 'normal') {
            $this->error('当前管理员账号已被禁用');
        }
    }

    public function index()
    {
        // SELECT * FROM `pre_business` WHERE `adminid` IS NULL ORDER BY `createtime` DESC
        $list = $this->BusinessModel->where(['adminid' => ['exp', Db::raw('IS NULL')]])->order('createtime DESC')->select();

        if ($list) {
            $this->success('查询成功', null, $list);
        } else {
            $this->error('公海暂无客户');
        }
    }

    public function info()
    {
        $busid = $this->request->param('businessid', 0, 'trim');

        $business = $this->BusinessModel->with(['source'])->find($busid);

        if (!$business) {
            $this->error('该客户不存在');
        }

        $this->success('查询成功', null, $business);
    }
}
