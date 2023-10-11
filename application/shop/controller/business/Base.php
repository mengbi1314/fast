<?php

namespace app\shop\controller\business;

use think\Db;
use think\Controller;
use app\common\library\Email;

class Base extends Controller
{
    // 用户模型
    protected $BusinessModel = null;

    public function __construct()
    {
        parent::__construct();

        // 加载模型
        $this->BusinessModel = model('business.Business');
    }

    public function register()
    {
        $mobile = $this->request->param('mobile', '', 'trim');
        $password = $this->request->param('password', '', 'trim');

        // 判断密码是否为空
        if (!$password) {
            $this->error('请输入密码');
        }

        // 生成密码盐
        $salt = build_ranstr();

        $password = md5($password . $salt);

        // 封装数据
        $data = [
            'mobile' => $mobile,
            'password' => $password,
            'salt' => $salt,
            'money' => 0,
            'auth' => 0,
            'deal' => 0,
        ];

        $source = model('business.Source')->where(['name' => ['LIKE', '%商城%']])->find();

        if ($source) {
            $data['sourceid'] = $source['id'];
        }

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
            $this->error('手机号未注册');
        }

        $password = md5($password . $business['salt']);

        if ($password != $business['password']) {
            $this->error('密码错误');
        }

        // 封装返回用户信息
        $data = [
            'id' => $business['id'],
            'mobile' => $business['mobile'],
            'mobile_text' => $business['mobile_text'],
            'avatar' => $business['avatar'],
            'avatar_cdn' => $business['avatar_cdn'],
            'nickname' => $business['nickname'],
            'email' => $business['email'],
            'gender' => $business['gender'],
            'province' => $business['province'],
            'city' => $business['city'],
            'district' => $business['district'],
            'region_text' => $business['region_text'],
            'auth' => $business['auth'],
        ];



        $this->success('登录成功', null, $data);
    }

    public function check()
    {
        $id = $this->request->param('id', 0, 'trim');
        $mobile = $this->request->param('mobile', '', 'trim');


        // halt($id, $mobile);

        $business = $this->BusinessModel->where(['id' => $id, 'mobile' => $mobile])->find();



        if (!$business) {
            $this->error('非法登录111111');
        }

        // 封装返回用户信息
        $data = [
            'id' => $business['id'],
            'mobile' => $business['mobile'],
            'mobile_text' => $business['mobile_text'],
            'avatar' => $business['avatar'],
            'avatar_cdn' => $business['avatar_cdn'],
            'nickname' => $business['nickname'],
            'email' => $business['email'],
            'gender' => $business['gender'],
            'province' => $business['province'],
            'city' => $business['city'],
            'district' => $business['district'],
            'region_text' => $business['region_text'],
            'auth' => $business['auth'],
        ];

        $this->success('验证成功', null, $data);
    }

    public function profile()
    {
        $params = $this->request->param();

        $business = $this->BusinessModel->find($params['id']);

        if (!$business) {
            $this->error('用户不存在');
        }
        // 封装更新数据
        $data = [
            'id' => $params['id'],
            'nickname' => $params['nickname'],
            'email' => $params['email'],
            'gender' => $params['gender'],
        ];

        // 如果邮箱有更新的话就要重新验证字段重置为0
        if ($params['email'] != $business['email']) {
            $data['auth'] = 0;
        }

        // 修改密码
        $password = $params['password'] ?? '';

        if ($password) {
            $repass = md5($password . $business['salt']);

            if ($repass == $business['password']) {
                $this->error('新密码不能与原密码一致');
            }

            $salt = build_ranstr();

            $password = md5($password . $salt);

            $data['password'] = $password;

            $data['salt'] = $salt;
        }

        // 地区
        if (!empty($params['code'])) {
            // 通过地区码去获取ID路径
            $path = model('Region')->where(['code' => $params['code']])->value('parentpath');

            // 如果路径为空就提示
            if (empty($path)) {
                $this->error('所选地区不存在');
            }

            // 转成数组
            $pathArr = explode(',', $path);

            // 赋值
            $data['province'] = $pathArr[0] ?? null;
            $data['city'] = $pathArr[1] ?? null;
            $data['district'] = $pathArr[2] ?? null;
        }

        // 头像上传
        if (isset($_FILES['avatar']) && $_FILES['avatar']['size'] > 0) {
            // 调用上传图片函数
            $res = build_upload('avatar');

            // 上传失败
            if ($res['code'] === 0) {
                $this->error($res['msg']);
            }

            // 赋值
            $data['avatar'] = $res['data'];
        }

        $result = $this->BusinessModel->validate('common/business/Business.profile')->isUpdate(true)->save($data);

        if ($result === false) {
            if (isset($data['avatar']) && $_FILES['avatar']['size']) {
                @is_file(ltrim($data['avatar'], '/')) && @unlink($data['avatar'], '/');
            }

            $this->error($this->BusinessModel->getError());
        } else {
            if (isset($data['avatar']) && $_FILES['avatar']['size']) {
                @is_file(ltrim($business['avatar'], '/')) && @unlink($business['avatar'], '/');
            }

            $this->success('更新资料成功');
        }
    }


    //发送邮箱验证码
    public function sendCode()
    {
        $id = $this->request->param('id', '', 'trim');
        $email = $this->request->param('email', '', 'trim');

        $where = [
            'id' => $id,
            'email' => $email,
        ];

        $business = $this->BusinessModel->where($where)->find();

        if (!$business) {
            $this->error('用户不存在');
        }

        Db::startTrans();

        //生成验证码
        $code = build_ranstr(4);

        $Ems = model('Ems')->where('email', $email)->find();

        if ($Ems) {
            //更新
            $data = [
                'id'    =>  $Ems['id'],
                'code'  =>  $code,
                'times' =>  $Ems['times'] + 1,
            ];

            $result = model('Ems')->isUpdate(true)->save($data);
        } else {

            //添加
            $data = [
                'event' =>  '邮箱认证',
                'email' =>  $email,
                'code'  =>  $code,
                'times' =>  1,
                'ip'    =>  $this->request->ip(),
            ];

            $result = model('Ems')->validate('common/Ems')->save($data);
        }

        if (!$result || $result === false) {
            $this->error(model('Ems')->getError());
        }

        /* 
             网易
                 smtp.163.com
 
             qq
                 smtp.qq.com
 
             端口
                 465
                 注意：服务器的安全组或者防火墙设置465端口开放
         */

        $content = " <h1>欢迎认证Vue商城</h1>
         您的验证码为：<b>$code</b>";

        // 实例化发送邮件类
        $Email = new Email();
        // 获取发件人的邮箱 config() 获取配置项或者设置配置项
        $fromEmail = config('site.mail_from');
        $fromUser = config('site.mail_smtp_user');

        // 设置邮件并且发送
        //自定义 ->from($fromEmail, $fromUser)
        $emailStatus = $Email->from($fromEmail, $fromUser)->subject('Vue商城邮箱认证')->message($content)->to($email)->send();

        if ($result === false || $emailStatus === false) {
            Db::rollback();
            $this->error($Email->getError());
        } else {
            Db::commit();
            $this->success('发送成功');
        }
    }

    //邮箱认证
    public function email()
    {
        if ($this->request->isPost()) {
            $id = $this->request->post('id');
            $code = strtoupper($this->request->post('code', '', 'trim'));
            $email = $this->request->post('email', '', 'trim');

            $res = model('Ems')->where('email', $email)->find();

            if (!$res) {
                $this->error('该邮箱不存在');
            }

            if (strtoupper($res['code']) != $code) {
                $this->error('验证码错误');
            }

            $endTime = $res['createtime'] + 300; // 300秒

            $endStatus = time() > $endTime; // 验证码过期

            if ($endStatus) {
                model('Ems')->destroy($res['id']);
                $this->error('验证码已过期');
            }

            //开启business和ems的事务
            $this->BusinessModel->startTrans();
            model('Ems')->startTrans();

            //更新用户认证状态
            $busres = $this->BusinessModel->isUpdate(true)->save(['auth' => 1], ['id' => $id]);

            if ($busres === false) {
                $this->error($this->BusinessModel->getError());
            }

            //认证成功后删除验证码
            $EmsStatus = model('Ems')->destroy($res['id']);

            if ($EmsStatus === false) {
                $this->BusinessModel->rollback(); // 多表操作->事务回滚
                $this->error(model('Ems')->getError());
            }

            //大判断
            if ($busres === false || $EmsStatus === false) {
                $this->BusinessModel->rollback(); // 多表操作->事务回滚
                model('Ems')->rollback();
                $this->error('认证失败');
            } else {
                $this->BusinessModel->commit();
                model('Ems')->commit();
                $this->success('认证成功');
            }
        }
    }

    // 我的收藏
    public function collection()
    {
        $busid = $this->request->param('busid', '', 'trim');

        $res = $this->BusinessModel->find($busid);

        if (!$res) {
            $this->error(__('用户不存在'));
        }

        $collection = model('business.Collection')->with('product')->where('busid', $busid)->select();

        if (!$collection) {
            $this->error(__('暂无收藏'));
        }

        $count = model('product.Product')->count();

        $this->success('查询收藏数据成功', null, ['count' => $count, 'list' => $collection]);
    }

    // 消费记录
    public function record()
    {
        $id = $this->request->param('busid', 0, 'trim');

        $business = model('business.Business')->find($id);

        if (!$business) {
            $this->error('用户不存在');
        }
        $record = model('business.Record')->where('busid', $id)->order('id desc')->select();

        if (!empty($record)) {
            $this->success('查询用户消费纪律成功', null, $record);
        } else {
            $this->error('暂无用户消费记录');
        }
    }
}
