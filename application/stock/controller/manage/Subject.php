<?php

namespace app\stock\controller\manage;

use think\Controller;

class Subject extends Controller
{
    protected $AdminModel = null;

    protected $BusinessModel = null;

    protected $SubjectModel = null;

    protected $SubjectOrderModel = null;

    public function __construct()
    {
        parent::__construct();

        $this->AdminModel = model('admin.Admin');

        $this->BusinessModel = model('business.Business');

        $this->SubjectModel = model('subject.Subject');

        $this->SubjectOrderModel = model('subject.Order');
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

        $result = $this->SubjectOrderModel->with(['subject', 'business'])->order('createtime DESC')->select();

        if (!$result) {
            $this->error('暂无课程订单');
        } else {
            $this->success('查询课程订单数据成功', null, $result);
        }
    }

    //删除订单
    public function del()
    {
        $adminid = $this->request->param('adminid', 0, 'trim');
        $orderid = $this->request->param('orderid', 0, 'trim');

        $admin = $this->AdminModel->find($adminid);

        if (!$admin) {
            $this->error('管理员不存在');
        }

        if ($admin['status'] !== 'normal') {
            $this->error('当前管理员账号已被禁用');
        }

        $order = $this->SubjectOrderModel->find($orderid);

        if (!$order) {
            $this->error('订单不存在');
        }

        $result = $this->SubjectOrderModel->destroy($orderid);

        if (!$result) {
            $this->error('删除失败');
        } else {
            $this->success('删除成功');
        }
    }
}
