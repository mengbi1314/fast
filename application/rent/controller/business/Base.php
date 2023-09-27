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
        ];

        $result = $this->BusinessModel->validate('common/business/Business.register')->save($data);

        if ($result === false) {
            $this->error($this->BusinessModel->getError());
        } else {
            $this->success('注册成功');
        }
    }
}
