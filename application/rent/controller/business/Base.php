<?php

namespace app\rent\controller\business;

use think\Controller;

class Base extends Controller
{
    protected $BusinessModel = null;

    public function __construct()
    {
        parent::__construct();

        $this->BusinessModel = model('business.Business');
    }

    public function register()
    {
        $mobile = $this->request->param('mobile', '', 'trim');
        $password = $this->request->param('password', '', 'trim');

        // 手动判断一下密码是否为空
        if (empty($password)) {
            $this->error('请输入密码');
        }

        // 生成密码盐
        $salt = build_ranstr();

        $password = md5($password . $salt);

        $data = [
            'mobile' => $mobile,
            'password' => $password,
            'auth' => 0,
            'money' => 0,
            'deal' => 0,
            'salt' => $salt
        ];

        $result = $this->BusinessModel->validate('common/business/Business.register')->save($data);

        if ($result === false) {
            $this->error($this->BusinessModel->getError());
        } else {
            $this->success('注册成功');
        }
    }

    public function login()
    {
        $mobile = $this->request->param('mobile', '', 'trim');
        $password = $this->request->param('password', '', 'trim');

        $business = $this->BusinessModel->where(['mobile' => $mobile])->find();

        if (!$business) {
            $this->error('用户不存在');
        }

        $password = md5($password . $business['salt']);

        if ($password != $business['password']) {
            $this->error('密码错误');
        }

        $data = [
            'id' => $business['id'],
            'mobile' => $business['mobile'],
            'mobile_text' => $business['mobile_text'],
            'nickname' => $business['nickname'],
            'email' => $business['email'],
            'avatar_cdn' => $business['avatar_cdn'],
            'gender' => $business['gender'],
            'gender' => $business['gender'],
            'region_text' => $business['region_text'],
            'province' => $business['province'],
            'city' => $business['city'],
            'district' => $business['district'],
            'auth' => $business['auth']
        ];

        $this->success('登录成功', null, $data);
    }
}
