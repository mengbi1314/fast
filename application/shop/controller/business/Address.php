<?php

namespace app\shop\controller\business;

use think\Controller;

class Address extends Controller
{
    // 收货地址模型
    protected $AddressModel = null;

    public function __construct()
    {
        parent::__construct();

        $this->AddressModel = model('business.Address');
    }

    public function index()
    {
        $busid = $this->request->param('busid', '', 'trim');

        $business = model('business.Business')->find($busid);

        if (!$business) {
            $this->error('用户不存在');
        }

        $list = $this->AddressModel->where(['busid' => $busid])->select();

        if ($list) {
            $this->success('查询收货地址数据成功', null, $list);
        } else {
            $this->error('暂无收货地址');
        }
    }

    public function add()
    {
        $params = $this->request->param();

        $business = model('business.Business')->find($params['busid']);

        if (!$business) {
            $this->error('用户不存在');
        }

        // 封装插入数据
        $data = [
            'busid' => $params['busid'],
            'consignee' => $params['consignee'],
            'mobile' => $params['mobile'],
            'address' => $params['address'],
            'status' => $params['status'],
        ];

        $this->AddressModel->startTrans();

        if ($params['status'] == 1) {
            $this->AddressModel->where(['busid' => $params['busid']])->update(['status' => 0]);
        }

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

        // 插入数据表
        $result = $this->AddressModel->validate('common/business/Address')->save($data);

        if ($result === false) {
            $this->AddressModel->rollback();
            $this->error($this->AddressModel->getError());
        } else {
            $this->AddressModel->commit();
            $this->success('添加成功');
        }
    }

    public function selected()
    {
        $id = $this->request->param('id', '', 'trim');
        $busid = $this->request->param('busid', '', 'trim');

        $business = model('business.Business')->find($busid);

        if (!$business) {
            $this->error('用户不存在');
        }

        $address = $this->AddressModel->find($id);

        if (!$address) {
            $this->error('该收货地址不存在');
        }

        $auth = $this->AddressModel->where(['id' => $id, 'busid' => $busid])->find();

        if (!$auth) {
            $this->error('您没有权限修改他人的收货地址');
        }

        $this->AddressModel->startTrans();

        $this->AddressModel->where(['busid' => $busid])->update(['status' => 0]);

        $data = [
            'id' => $id,
            'status' => 1
        ];

        $result = $this->AddressModel->isUpdate(true)->save($data);

        if ($result === false) {
            $this->AddressModel->rollback();
            $this->error('设置默认收货地址失败');
        } else {
            $this->AddressModel->commit();
            $this->success('设置默认收货地址成功');
        }
    }
}
