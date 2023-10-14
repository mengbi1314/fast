<?php

namespace app\stock\controller\manage;

use think\Controller;
use think\Request;

class Privatesea extends Controller
{
    protected $businessModel = null;
    protected $adminModel    = null;
    public function __construct()
    {
        parent::__construct();
        $this->businessModel = model('business.Business');
        $this->adminModel    = model('admin.Admin');
    }
    /**
     * 私海列表
     */
    public function index()
    {
        $adminid = $this->request->param('adminid', '', 'trim');
        $admin   = $this->adminModel->find($adminid);
        if (!$admin) {
            $this->error('管理员不存在');
        }
        if ($admin['status'] !== 'normal') {
            $this->error('该管理员账号已禁用');
        }
        $business = $this->businessModel->with('source')->where('adminid', $adminid)->select();
        if (!$business) {
            $this->error('客户不存在');
        }
        $this->success('查询成功', null, $business);
    }
    /**
     * 私海详情
     */
    public function info()
    {
        $busid = $this->request->param('busid', 0, 'trim');
        $adminid = $this->request->param('adminid', 0, 'trim');
        $admin = $this->adminModel->find($adminid);
        if (!$admin) {
            $this->error('管理员不存在');
        }
        if ($admin['status'] !== 'normal') {
            $this->error('该管理员账号已禁用');
        }
        $business = $this->businessModel->with('source')->where(['business.id' => $busid, 'adminid' => $adminid])->find();
        if (!$business) {
            $this->error('该客户不存在');
        } else {
            $this->success('查询成功', null, $business);
        }
    }
    /**
     * 查询客户来源
     */
    public function source()
    {
        $sourceList = model('business.Source')->select();
        $this->success('查询成功', null, $sourceList);
    }
    /**
     * 编辑私海客户
     */
    public function edit()
    {
        $params = $this->request->param();
        $business = $this->businessModel->find($params['id']);
        if (!$business) {
            $this->error('该客户不存在');
        }
        $data = [
            'id'        => $params['id'],
            'nickname'  => $params['nickname'],
            'mobile'    => $params['mobile'],
            'gender'    => $params['gender'],
            'sourceid'  => $params['sourceid'],
            'auth'      => $params['auth']
        ];
        if (isset($params['password'])) {
            $password = md5(md5($params['password']) . $business['salt']);
            if ($password === $business['password']) {
                $this->error('新密码不能跟原密码一致');
            }
            $salt = build_ranstr();
            $password = md5(md5($params['password']) . $salt);
            $data['password'] = $password;
            $data['salt'] = $salt;
        }
        $region = $params['region'][2];
        if ($region) {
            $parentPath = model('Region')->where('code', $region)->value('parentpath');
            if (!$parentPath) {
                $this->error('地区不存在');
            }
            $region = explode(',', $parentPath);
            $data['province'] = $region[0] ?? '';
            $data['city'] = $region[1] ?? '';
            $data['district'] = $region[2] ?? '';
        }
        $result = $this->businessModel->isUpdate(true)->save($data);
        if ($result === false) {
            $this->error('编辑失败');
        } else {
            $this->success('编辑成功');
        }
    }
    /**
     * 回收私海客户
     */
    public function recovery()
    {
        $adminid = $this->request->param('adminid', '', 'trim');
        $busid = $this->request->param('busid', '', 'trim');
        $admin = $this->adminModel->find($adminid);
        if (!$admin) {
            $this->error('管理员不存在');
        }
        if ($admin['status'] !== 'normal') {
            $this->error('该管理员账号已禁用');
        }
        $business = $this->businessModel->find($busid);
        if (!$business) {
            $this->error('该客户不存在');
        }
        $data = [
            'id' => $busid,
            'adminid' => null
        ];
        $result = $this->businessModel->isUpdate(true)->save($data);
        if ($result === false) {
            $this->error('回收失败');
        } else {
            $this->success('回收成功');
        }
    }
    /**
     * 删除私海
     */
    public function del()
    {
        $adminid = $this->request->param('adminid', '', 'trim');
        $busid = $this->request->param('busid', '', 'trim');
        $admin = $this->adminModel->find($adminid);
        if (!$admin) {
            $this->error('管理员不存在');
        }
        if ($admin['status'] !== 'normal') {
            $this->error('该管理员账号已禁用');
        }
        $business = $this->businessModel->find($busid);
        if (!$business) {
            $this->error('该客户不存在');
        }
        $result = $this->businessModel->destroy($busid);
        if ($result === false) {
            $this->error('删除失败');
        } else {
            $this->success('删除成功');
        }
    }
}
