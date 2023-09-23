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

    protected $Admin = [];

    public function __construct()
    {
        parent::__construct();

        $this->BusinessModel = model('business.Business');
        $this->AdminModel = model('admin.Admin');

        $adminid = $this->request->param('adminid', '', 'trim');

        $this->Admin = $this->AdminModel->find($adminid);

        if (!$this->Admin) {
            $this->error('当前管理员账号不存在');
        }

        if ($this->Admin['status'] !== 'normal') {
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

    public function apply()
    {
        $busid = $this->request->param('busid', 0, 'trim');

        $business = $this->BusinessModel->find($busid);

        if (!$business) {
            $this->error('客户不存在');
        }

        $ReceiveModel = model('business.Receive');

        // 开启事务
        $this->BusinessModel->startTrans();
        $ReceiveModel->startTrans();

        $BusinessData = [
            'id' => $busid,
            'adminid' => $this->Admin['id']
        ];

        $BusinessStatus = $this->BusinessModel->isUpdate(true)->save($BusinessData);

        if ($BusinessStatus === false) {
            $this->error('更新用户数据失败');
        }

        $ReceiveData = [
            'applyid' => $this->Admin['id'],
            'status' => 'apply',
            'busid' => $busid
        ];

        $ReceiveStatus = $ReceiveModel->validate('common/business/Receive')->save($ReceiveData);

        if ($ReceiveStatus === false) {
            $this->BusinessModel->rollback();
            $this->error($ReceiveModel->getError());
        }

        if ($BusinessStatus === false || $ReceiveStatus === false) {
            $this->BusinessModel->rollback();
            $ReceiveModel->rollback();
            $this->error('申请失败');
        } else {
            $this->BusinessModel->commit();
            $ReceiveModel->commit();
            $this->success('申请成功');
        }
    }
}
