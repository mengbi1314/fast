<?php

namespace app\stock\controller\manage;

use think\Controller;

class Visit extends Controller
{

    protected $VisitModel    = null;
    protected $BusinessModel = null;
    protected $AdminModel    = null;

    public function __construct()
    {
        parent::__construct();

        $this->VisitModel    = model('business.Visit');
        $this->BusinessModel = model('business.Business');
        $this->AdminModel    = model('admin.Admin');
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

        $result = $this->VisitModel->alias('visit')->with('business')->where('visit.adminid', $adminid)->order('createtime DESC')->select();

        if (!$result) {
            $this->error('暂无用户回访记录');
        } else {
            $this->success('查询用户回访记录成功', null, $result);
        }
    }

    //查询用户
    public function business()
    {
        $adminid = $this->request->param('adminid', 0, 'trim');

        $admin = $this->AdminModel->find($adminid);

        if (!$admin) {
            $this->error('管理员不存在');
        }

        if ($admin['status'] !== 'normal') {
            $this->error('当前管理员账号已被禁用');
        }

        $business = $this->BusinessModel->where('adminid', $adminid)->select();

        if (!$business) {
            $this->error('暂无用户');
        } else {
            $this->success('查询用户成功', null, $business);
        }
    }

    //添加回访
    public function add()
    {
        $busid   = $this->request->param('busid', 0, 'trim');
        $adminid = $this->request->param('adminid', 0, 'trim');
        $content = $this->request->param('content', '', 'trim');

        $admin = $this->AdminModel->find($adminid);

        if (!$admin) {
            $this->error('管理员不存在');
        }

        if ($admin['status'] !== 'normal') {
            $this->error('当前管理员账号已被禁用');
        }

        $business = $this->BusinessModel->find($busid);

        if (!$business) {
            $this->error('暂无用户');
        }

        $data = [
            'busid'   => $busid,
            'adminid' => $adminid,
            'content' => $content,
        ];

        $result = $this->VisitModel->validate('common/business/Visit')->save($data);

        if (!$result) {
            $this->error('添加回访记录失败');
        } else {
            $this->success('添加回访记录成功');
        }
    }

    //查询回访详情
    public function info()
    {
        $adminid = $this->request->param('adminid', 0, 'trim');
        $id      = $this->request->param('id', 0, 'trim');

        $admin = $this->AdminModel->find($adminid);

        if (!$admin) {
            $this->error('管理员不存在');
        }

        if ($admin['status'] !== 'normal') {
            $this->error('当前管理员账号已被禁用');
        }

        $visit = $this->VisitModel->with('business')->find($id);

        if (!$visit) {
            $this->error('回访记录不存在');
        } else {
            $this->success('查询回访记录成功', '', $visit);
        }
    }

    //编辑回访
    public function edit()
    {
        $id      = $this->request->param('id', 0, 'trim');
        $busid   = $this->request->param('busid', 0, 'trim');
        $adminid = $this->request->param('adminid', 0, 'trim');
        $content = $this->request->param('content', '', 'trim');

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

        $visit = $this->VisitModel->find($id);

        if (!$visit) {
            $this->error('回访记录不存在');
        }

        if ($content == $visit['content']) {
            $this->error('回访内容未做修改');
        }

        $data = [
            'id'      => $id,
            'busid'   => $busid,
            'adminid' => $adminid,
            'content' => $content,
        ];

        $result = $this->VisitModel->validate('common/business/Visit')->isUpdate(true)->save($data);

        if (!$result) {
            $this->error('编辑回访记录失败');
        } else {
            $this->success('编辑回访记录成功');
        }
    }

    //删除回访
    public function del()
    {
        $id = $this->request->param('id', 0, 'trim');
        $adminid = $this->request->param('adminid', 0, 'trim');

        $admin = $this->AdminModel->find($adminid);

        if (!$admin) {
            $this->error('管理员不存在');
        }

        if ($admin['status'] !== 'normal') {
            $this->error('当前管理员账号已被禁用');
        }

        $visit = $this->VisitModel->find($id);

        if (!$visit) {
            $this->error('回访记录不存在');
        }

        $result = $this->VisitModel->destroy($id);

        if (!$result) {
            $this->error('删除回访记录失败');
        } else {
            $this->success('删除回访记录成功');
        }
    }
}
