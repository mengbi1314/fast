<?php

namespace app\stock\controller\manage;

use think\Controller;

class Source extends Controller
{
    // 管理员模型
    protected $AdminModel = null;

    // 客户来源模型
    protected $SourceModel = null;

    public function __construct()
    {
        parent::__construct();

        $this->AdminModel = model('admin.Admin');
        $this->SourceModel = model('business.Source');
    }

    public function index()
    {
        $adminid = $this->request->param('adminid', 0, 'trim');

        $admin = $this->AdminModel->find($adminid);

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

    public function add()
    {
        $name = $this->request->param('name', '', 'trim');
        $adminid = $this->request->param('adminid', 0, 'trim');

        $admin = $this->AdminModel->find($adminid);

        if (!$admin) {
            $this->error('当前账号不存在');
        }

        if ($admin['status'] !== 'normal') {
            $this->error('当前账号已被禁用');
        }

        $data = [
            'name' => $name
        ];

        $result = $this->SourceModel->validate('common/business/Source')->save($data);

        if ($result === false) {
            $this->error($this->SourceModel->getError());
        } else {
            $this->success('添加成功');
        }
    }

    public function del()
    {
        $adminid = $this->request->param('adminid', 0, 'trim');
        $id = $this->request->param('id', 0, 'trim');

        $admin = $this->AdminModel->find($adminid);

        if (!$admin) {
            $this->error('当前账号不存在');
        }

        if ($admin['status'] !== 'normal') {
            $this->error('当前账号已被禁用');
        }

        $source = $this->SourceModel->find($id);

        if (!$source) {
            $this->error('该客户来源不存在');
        }

        $result = $this->SourceModel->destroy($id);

        if ($result === false) {
            $this->error('删除失败');
        } else {
            $this->success('删除成功');
        }
    }

    public function info()
    {
        $adminid = $this->request->param('adminid', 0, 'trim');
        $id = $this->request->param('id', 0, 'trim');

        $admin = $this->AdminModel->find($adminid);

        if (!$admin) {
            $this->error('当前账号不存在');
        }

        if ($admin['status'] !== 'normal') {
            $this->error('当前账号已被禁用');
        }

        $source = $this->SourceModel->find($id);

        if (!$source) {
            $this->error('该客户来源不存在');
        }

        $this->success('查询成功', null, $source);
    }

    public function edit()
    {
        $id = $this->request->param('id', '', 'trim');
        $name = $this->request->param('name', '', 'trim');
        $adminid = $this->request->param('adminid', 0, 'trim');

        $admin = $this->AdminModel->find($adminid);

        if (!$admin) {
            $this->error('当前账号不存在');
        }

        if ($admin['status'] !== 'normal') {
            $this->error('当前账号已被禁用');
        }


        $source = $this->SourceModel->find($id);

        if (!$source) {
            $this->error('该客户来源不存在');
        }

        $data = [
            'id' => $id,
            'name' => $name
        ];

        $result = $this->SourceModel->validate('common/business/Source')->isUpdate(true)->save($data);

        if ($result === false) {
            $this->error($this->SourceModel->getError());
        } else {
            $this->success('编辑成功');
        }
    }
}
