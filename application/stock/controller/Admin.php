<?php

namespace app\stock\controller;

use think\Controller;

class Admin extends Controller
{
    // 管理员模型
    protected $AdminModel = null;

    public function __construct()
    {
        parent::__construct();

        $this->AdminModel = model('admin.Admin');
    }

    public function wxlogin()
    {
        // 接收临时登录凭证
        $code = $this->request->param('code', '', 'trim');

        // 判断是否为空
        if (empty($code)) {
            $this->error('获取临时登录凭证失败');
        }

        // 发起请求对接微信对接服务
        $res = $this->code2Session($code);

        if ($res['code'] === 0) {
            $this->error($res['msg']);
        }

        $openid = $res['data']['openid'] ?? '';

        if (empty($openid)) {
            $this->error('获取登录凭证失败');
        }

        $admin = $this->AdminModel->where(['openid' => $openid])->find();

        if (!$admin) {
            $this->error('请先绑定账号', null, ['action' => 'bind', 'openid' => $openid]);
        }

        if ($admin['status'] !== 'normal') {
            $this->error('当前账号已被禁用');
        }

        $data = [
            'id' => $admin['id'],
            'username' => $admin['username'],
            'nickname' => $admin['nickname'],
            'avatar_cdn' => $admin['avatar_cdn'],
            'avatar' => $admin['avatar'],
            'email' => $admin['email'],
            'mobile' => $admin['mobile'],
            'group_text' => $admin['group_text'],
            'createtime' => $admin['createtime']
        ];

        $this->success('登录成功', null, $data);
    }

    public function unbinding()
    {
        $id = $this->request->param('id', '', 'trim');

        $admin = $this->AdminModel->find($id);

        if (!$admin) {
            $this->error('账号不存在');
        }

        if ($admin['status'] !== 'normal') {
            $this->error('当前账号已被禁用');
        }

        // 封装更新管理员的数据
        $data = [
            'id' => $id,
            'openid' => null
        ];

        $result = $this->AdminModel->isUpdate(true)->save($data);

        if ($result === false) {
            $this->error('解绑失败');
        } else {
            $this->success('解绑成功');
        }
    }

    public function bind()
    {
        $username = $this->request->param('username', '', 'trim');
        $password = $this->request->param('password', '', 'trim');
        $openid = $this->request->param('openid', '', 'trim');

        $admin = $this->AdminModel->where(['username' => $username])->find();

        if (!$admin) {
            $this->error('账号不存在');
        }

        $password = md5(md5($password) . $admin['salt']);

        if ($password !== $admin['password']) {
            $this->error('密码错误');
        }

        if ($admin['status'] !== 'normal') {
            $this->error('当前账号已被禁用');
        }

        $data = [
            'id' => $admin['id'],
            'openid' => $openid
        ];

        $result = $this->AdminModel->isUpdate(true)->save($data);

        if ($result === false) {
            $this->error('绑定账号失败');
        } else {
            $this->success('绑定账号成功');
        }
    }

    public function login()
    {
        $username = $this->request->param('username', '', 'trim');
        $password = $this->request->param('password', '', 'trim');

        $admin = $this->AdminModel->where(['username' => $username])->find();

        if (!$admin) {
            $this->error('账号不存在');
        }

        $password = md5(md5($password) . $admin['salt']);

        if ($password != $admin['password']) {
            $this->error('密码错误');
        }

        if ($admin['status'] !== 'normal') {
            $this->error('当前账号已被禁用');
        }

        $data = [
            'id' => $admin['id'],
            'username' => $admin['username'],
            'nickname' => $admin['nickname'],
            'avatar_cdn' => $admin['avatar_cdn'],
            'avatar' => $admin['avatar'],
            'email' => $admin['email'],
            'mobile' => $admin['mobile'],
            'group_text' => $admin['group_text'],
            'createtime' => $admin['createtime']
        ];

        $this->success('登录成功', null, $data);
    }

    public function upload()
    {
        $adminid = $this->request->param('adminid', '', 'trim');

        $admin = $this->AdminModel->find($adminid);

        if (!$admin) {
            $this->error('当前管理员账号不存在');
        }

        if ($admin['status'] !== 'normal') {
            $this->error('当前管理员账号已被禁用');
        }

        $res = build_upload('avatar');

        if ($res['code'] === 0) {
            $this->error($res['msg']);
        }

        $data = [
            'id' => $adminid,
            'avatar' => $res['data']
        ];

        $result = $this->AdminModel->isUpdate(true)->save($data);

        if ($result === false) {
            @is_file(ltrim($data['avatar'], '/')) && @unlink(ltrim($data['avatar'], '/'));
            $this->error('更新头像失败');
        } else {
            @is_file(ltrim($admin['avatar'], '/')) && @unlink(ltrim($admin['avatar'], '/'));

            $admin = $this->AdminModel->find($adminid);

            $data = [
                'id' => $admin['id'],
                'username' => $admin['username'],
                'nickname' => $admin['nickname'],
                'avatar_cdn' => $admin['avatar_cdn'],
                'avatar' => $admin['avatar'],
                'email' => $admin['email'],
                'mobile' => $admin['mobile'],
                'group_text' => $admin['group_text'],
                'createtime' => $admin['createtime']
            ];

            $this->success('更新头像成功', null, $data);
        }
    }

    // 获取登录用户的openid的方法
    private function code2Session($code)
    {
        // 定义返回结果数组
        $result = [
            'code' => 0,
            'msg' => '登录凭证未知',
            'data' => null,
        ];

        // 如果code是存在就开始请求
        if ($code) {
            // 小程序appid(改成自己的)
            $appid = 'wxa675c4a689bd8ffb';

            // 小程序密钥(改成自己的)
            $AppSecret = 'f40786541c1c3c192140640f38196ee1';

            // 请求地址
            $apiUrl = "https://api.weixin.qq.com/sns/jscode2session?appid=$appid&secret=$AppSecret&js_code=$code&grant_type=authorization_code";

            $res = httpRequest($apiUrl);

            // 由于接口返回的数据是json数据包，转成数组
            $res = json_decode($res, true);

            $result = [
                'code' => 1,
                'msg' => '获取登录凭证成功',
                'data' => $res
            ];

            return $result;
        }

        return $result;
    }
}
