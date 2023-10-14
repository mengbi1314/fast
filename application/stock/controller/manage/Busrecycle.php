<?php

namespace app\stock\controller\manage;

use think\Controller;

class Busrecycle extends Controller
{
    protected $AdminModel = null;

    protected $BusinessModel = null;

    public function __construct()
    {
        parent::__construct();

        $this->AdminModel = model('admin.Admin');

        $this->BusinessModel = model('business.Business');
    }


    /**
     * 显示资源列表
     */
    public function index()
    {
        $adminid = $this->request->param('adminid', 0, 'trim');

        $admin = $this->AdminModel->find($adminid);

        if (!$admin) {
            $this->error('管理员不存在');
        }

        if ($admin['status'] !== 'normal') {
            $this->error('当前管理员账号已被禁用');
        }

        $result = $this->BusinessModel->onlyTrashed()->where('adminid', $adminid)->select();

        if ($result === false) {
            $this->error('暂无客户回收站数据');
        } else {
            $this->success('客户回收站查询成功', null, $result);
        }
    }

    //还原回收站
    public function restore()
    {
        $adminid = $this->request->param('adminid', 0, 'trim');
        $busid = $this->request->param('busid', 0, 'trim');

        $admin = $this->AdminModel->find($adminid);

        if (!$admin) {
            $this->error('管理员不存在');
        }

        if ($admin['status'] !== 'normal') {
            $this->error('当前管理员账号已被禁用');
        }

        $business = $this->BusinessModel->withTrashed()->find($busid);

        if (!$business) {
            $this->error('用户不存在');
        }

        $data = [
            'id'         => $busid,
            'deletetime' => null,
        ];

        $result = $this->BusinessModel->isUpdate(true)->save($data);

        if (!$result) {
            $this->error('还原失败');
        } else {
            $this->success('还原成功');
        }
    }

    //真实删除
    public function del()
    {
        $adminid = $this->request->param('adminid', 0, 'trim');
        $busid = $this->request->param('busid', 0, 'trim');

        $admin = $this->AdminModel->find($adminid);

        if (!$admin) {
            $this->error('管理员不存在');
        }

        if ($admin['status'] !== 'normal') {
            $this->error('当前管理员账号已被禁用');
        }

        $business = $this->BusinessModel->withTrashed()->find($busid);

        if (!$business) {
            $this->error('用户不存在');
        }

        $result = $business->delete(true);

        if (!$result) {
            $this->error('删除失败');
        } else {
            $this->success('删除成功');
        }
    }
}
