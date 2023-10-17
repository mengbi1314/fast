<?php

namespace app\rent\controller\product;

use think\Controller;

class Lease extends Controller
{
    // 租赁模型
    protected $LeaseModel = null;

    public function __construct()
    {
        parent::__construct();

        $this->LeaseModel = model('product.Lease');
    }

    public function add()
    {
        $params = $this->request->param();

        /* 
            创建一个租赁订单
            扣除该商品的库存
            扣除该用户的余额
            创建消费记录
        */

        // 加载模型
        $BusinessModel = model('business.Business');
        $ProductModel = model('product.Product');
        $RecordModel = model('business.Record');
        $RegionModel = model('Region');

        // 查询商品是否存在
        $product = $ProductModel->find($params['proid']);

        if (!$product) {
            $this->error('商品不存在');
        }

        // 库存
        if ($product['stock'] <= 0) {
            $this->error('商品库存不足');
        }

        $business = $BusinessModel->find($params['busid']);

        if (!$business) {
            $this->error('用户不存在');
        }

        // 用户的余额是否充足
        $UpdateMoney = bcsub($business['money'], $params['price'], 2);

        if ($UpdateMoney < 0) {
            $this->error('余额不足，请及时充值');
        }

        // 开启事务
        $this->LeaseModel->startTrans();
        $BusinessModel->startTrans();
        $ProductModel->startTrans();
        $RecordModel->startTrans();

        // 封装租赁订单数据
        $LeaseData = [
            'busid' => $params['busid'],
            'proid' => $params['proid'],
            'rent'  => $params['rent'],
            'price' => $params['price'],
            'createtime' => $params['createtime'],
            'endtime' => $params['endtime'],
            'address' => $params['address'],
            'status' => 1,
            'mobile' => $params['mobile'],
            'nickname' => $params['nickname'],
        ];

        $path = $RegionModel->where(['code' => $params['code']])->value('parentpath');

        if (!$path) {
            $this->error('所选地区不存在');
        }

        $pathArr = explode(',', $path);

        $LeaseData['province'] = $pathArr[0] ?? null;
        $LeaseData['city'] = $pathArr[1] ?? null;
        $LeaseData['district'] = $pathArr[2] ?? null;

        $res = build_upload('card');

        if ($res['code'] === 0) {
            $this->error($res['msg']);
        }

        $LeaseData['card'] = $res['data'];

        $LeaseStatus = $this->LeaseModel->validate('common/product/Lease')->save($LeaseData);

        if ($LeaseStatus === false) {
            @is_file(ltrim($LeaseData['card'], '/')) && @unlink(ltrim($LeaseData['card'], '/'));
            $this->error($this->LeaseModel->getError());
        }

        // 更新用户余额
        $BusinessData = [
            'id' => $params['busid'],
            'money' => $UpdateMoney
        ];

        $BusinessStatus = $BusinessModel->isUpdate(true)->save($BusinessData);

        if ($BusinessStatus === false) {
            @is_file(ltrim($LeaseData['card'], '/')) && @unlink(ltrim($LeaseData['card'], '/'));
            $this->LeaseModel->rollback();
            $this->error('更新用户余额失败');
        }

        // 更新商品库存
        $ProductData = [
            'id' => $params['proid'],
            'stock' => bcsub($product['stock'], 1)
        ];

        $ProductStatus = $ProductModel->isUpdate(true)->save($ProductData);

        if ($ProductStatus === false) {
            @is_file(ltrim($LeaseData['card'], '/')) && @unlink(ltrim($LeaseData['card'], '/'));
            $this->LeaseModel->rollback();
            $BusinessModel->rollback();
            $this->error('更新商品库存失败');
        }

        // 新增一条消费记录
        $RecordData = [
            'busid' => $params['busid'],
            'total' => $params['price'],
            'content' => "您租赁的商品【{$product['name']}】消费了 {$params['price']}元（包含押金）"
        ];

        $RecordStatus = $RecordModel->validate('common/business/Record')->save($RecordData);

        if ($RecordStatus === false) {
            @is_file(ltrim($LeaseData['card'], '/')) && @unlink(ltrim($LeaseData['card'], '/'));
            $this->LeaseModel->rollback();
            $BusinessModel->rollback();
            $ProductModel->rollback();
            $this->error($RecordModel->getError());
        }

        if ($LeaseStatus === false || $BusinessStatus === false || $ProductStatus === false || $RecordStatus === false) {
            @is_file(ltrim($LeaseData['card'], '/')) && @unlink(ltrim($LeaseData['card'], '/'));
            $this->LeaseModel->rollback();
            $BusinessModel->rollback();
            $ProductModel->rollback();
            $RecordModel->rollback();
            $this->error('租赁失败');
        } else {
            $this->LeaseModel->commit();
            $BusinessModel->commit();
            $ProductModel->commit();
            $RecordModel->commit();
            $this->success('租赁成功');
        }
    }
}
