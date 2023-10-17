<?php

namespace app\rent\controller\business;

use think\Controller;
use think\Db;

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
            'gender_text' => $business['gender_text'],
            'region_text' => $business['region_text'],
            'province' => $business['province'],
            'city' => $business['city'],
            'district' => $business['district'],
            'auth' => $business['auth']
        ];

        $this->success('登录成功', null, $data);
    }

    public function profile()
    {
        $params = $this->request->param();

        $business = $this->BusinessModel->find($params['id']);

        if (!$business) {
            $this->error('用户不存在');
        }

        $data = [
            'id' => $business['id'],
            'nickname' => $params['nickname'],
            'email' => $params['email'],
            'gender' => $params['gender']
        ];

        if ($data['email'] != $business['email']) {
            $data['auth'] = 0;
        }

        if (!empty($params['password'])) {
            $repass = md5($params['password'] . $business['salt']);

            if ($repass == $business['password']) {
                $this->error('新密码不能与原密码一致');
            }

            $salt = build_ranstr();

            $password = md5($params['password'] . $salt);

            $data['salt'] = $salt;
            $data['password'] = $password;
        }

        if (!empty($params['code'])) {
            $path = model('Region')->where(['code' => $params['code']])->value('parentpath');

            if (!$path) {
                $this->error('所选地区不存在');
            }

            [$province, $city, $district] = explode(',', $path);

            $data['province'] = $province ?? null;
            $data['city'] = $city ?? null;
            $data['district'] = $district ?? null;
        }

        if (isset($_FILES['avatar']) && $_FILES['avatar']['size'] > 0) {
            $res = build_upload('avatar');

            if ($res['code'] === 0) {
                $this->error($res['msg']);
            }

            $data['avatar'] = $res['data'];
        }

        // 更新数据表
        $result = $this->BusinessModel->validate('common/business/Business.profile')->isUpdate(true)->save($data);

        if ($result === false) {
            if (isset($data['avatar']) && $_FILES['avatar']['size'] > 0) {
                @is_file(ltrim($data['avatar'], '/')) && @unlink(ltrim($data['avatar'], '/'));
            }

            $this->error($this->BusinessModel->getError());
        } else {
            if (isset($data['avatar']) && $_FILES['avatar']['size'] > 0) {
                @is_file(ltrim($business['avatar'], '/')) && @unlink(ltrim($business['avatar'], '/'));
            }

            $business = $this->BusinessModel->find($params['id']);

            $data = [
                'id' => $business['id'],
                'mobile' => $business['mobile'],
                'mobile_text' => $business['mobile_text'],
                'nickname' => $business['nickname'],
                'email' => $business['email'],
                'avatar_cdn' => $business['avatar_cdn'],
                'gender' => $business['gender'],
                'gender_text' => $business['gender_text'],
                'region_text' => $business['region_text'],
                'province' => $business['province'],
                'city' => $business['city'],
                'district' => $business['district'],
                'auth' => $business['auth']
            ];

            $this->success('更新成功', null, $data);
        }
    }

    public function count()
    {
        $busid = $this->request->param('busid', 0, 'trim');

        $business = $this->BusinessModel->find($busid);

        if (!$business) {
            $this->error('用户不存在');
        }

        // 分别获取收藏文章，收藏商品，订单的数据总数
        $CateCount = model('business.Collection')->where(['busid' => $busid, 'cateid' => ['EXP', Db::raw('IS NOT NULL')]])->count();

        $ProductCount = model('business.Collection')->where(['busid' => $busid, 'proid' => ['EXP', Db::raw('IS NOT NULL')]])->count();

        $OrderCount = model('product.Lease')->where(['busid' => $busid])->count();

        $data = [
            'CateCount' => $CateCount,
            'ProductCount' => $ProductCount,
            'OrderCount' => $OrderCount
        ];

        $this->success('查询成功', null, $data);
    }
}
