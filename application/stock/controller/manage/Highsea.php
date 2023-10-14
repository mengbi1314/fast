<?php

namespace app\stock\controller\manage;

use think\Controller;

class Highsea extends Controller
{

    protected $AdminModel = null;

    protected $BusinessModel = null;

    protected $ReceiveModel = null;

    public function __construct()
    {
        parent::__construct();

        $this->AdminModel = model('admin.Admin');

        $this->BusinessModel = model('business.Business');

        $this->ReceiveModel = model('business.Receive');
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

        $result = $this->BusinessModel->with(['source'])->whereNull('adminid')->select();

        if ($result === false) {
            $this->error('暂无公海数据');
        } else {
            $this->success('公海资源查询成功', null, $result);
        }
    }

    //公海详情
    public function info()
    {
        $busid = $this->request->param('busid', 0, 'trim');

        $business = $this->BusinessModel->find($busid);

        if (!$business) {
            $this->error('用户不存在');
        }

        $result = $this->BusinessModel->with(['source', 'region'])->find($busid);

        if (!$result) {
            $this->error('查询用户信息失败');
        } else {
            $this->success('查询用户信息成功', null, $result);
        }
    }

    //查询管理员
    public function admin()
    {
        $list = $this->AdminModel->select();

        if (!$list) {
            $this->error('暂无管理员');
        } else {
            $this->success('查询管理员成功', null, $list);
        }
    }

    //客户公海分配
    public function allot()
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

        $business = $this->BusinessModel->find($busid);

        if (!$business) {
            $this->error('用户不存在');
        }

        // 开启事务
        $this->BusinessModel->startTrans();
        $this->ReceiveModel->startTrans();

        $BusinessData = [
            'id'        =>  $busid,
            'adminid'   =>  $adminid
        ];

        $BusinessStatus = $this->BusinessModel->isUpdate(true)->save($BusinessData);

        if ($BusinessStatus === false) {
            $this->error('更新用户数据失败');
        }

        $ReceiveData = [
            'applyid' => $adminid,
            'status' => 'allot',
            'busid' => $busid
        ];

        $ReceiveStatus = $this->ReceiveModel->validate('common/business/Receive')->save($ReceiveData);

        if ($ReceiveStatus === false) {
            $this->BusinessModel->rollback();
            $this->error($this->ReceiveModel->getError());
        }

        if ($BusinessStatus === false || $ReceiveStatus === false) {
            $this->BusinessModel->rollback();
            $this->ReceiveModel->rollback();
            $this->error('申请失败');
        } else {
            $this->BusinessModel->commit();
            $this->ReceiveModel->commit();
            $this->success('申请成功');
        }
    }

    //公海申请领取
    public function apply()
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

        $business = $this->BusinessModel->find($busid);

        if (!$business) {
            $this->error('用户不存在');
        }

        // 开启事务
        $this->BusinessModel->startTrans();
        $this->ReceiveModel->startTrans();

        $BusinessData = [
            'id'        =>  $busid,
            'adminid'   =>  $adminid
        ];

        $BusinessStatus = $this->BusinessModel->isUpdate(true)->save($BusinessData);

        if ($BusinessStatus === false) {
            $this->error('更新用户数据失败');
        }

        $ReceiveData = [
            'applyid' => $adminid,
            'status' => 'apply',
            'busid' => $busid
        ];

        $ReceiveStatus = $this->ReceiveModel->validate('common/business/Receive')->save($ReceiveData);

        if ($ReceiveStatus === false) {
            $this->BusinessModel->rollback();
            $this->error($this->ReceiveModel->getError());
        }

        if ($BusinessStatus === false || $ReceiveStatus === false) {
            $this->BusinessModel->rollback();
            $this->ReceiveModel->rollback();
            $this->error('申请失败');
        } else {
            $this->BusinessModel->commit();
            $this->ReceiveModel->commit();
            $this->success('申请成功');
        }
    }
}
