<?php

namespace app\stock\controller\manage;

use think\Controller;

class Source extends Controller
{
    //管理员模型
    protected $AdminModel = null;

    //客户来源模型
    protected $SourceModel = null;

    public function __construct()
    {
        parent::__construct();

        $this->AdminModel = model('admin.Admin');
        $this->SourceModel = model('business.Source');
    }

    public function index()
    {
        $admin = $this->AdminModel->find();

        if (!$admin) {
            $this->error('当前管理员账号不存在');
        }

        if ($admin['status'] !== 'normal') {
            $this->error('当前管理员账号已被禁用');
        }
        $list = $this->SourceModel->order('id DESC')->select();

        if ($list) {
            $this->success('查询成功', null, $list);
        } else {
            $this->error('暂无客户来源');
        }
    }
}
