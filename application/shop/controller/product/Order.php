<?php

namespace app\shop\controller\product;

use think\Controller;

class Order extends Controller
{
    // 订单模型
    protected $OrderModel = null;

    // 订单商品模型
    protected $OrderProductModel = null;

    // 用户模型
    protected $BusinessModel = null;

    public function __construct()
    {
        parent::__construct();

        $this->OrderModel = model('product.order.Order');
        $this->OrderProductModel = model('product.order.Product');
        $this->BusinessModel = model('business.Business');
    }

    public function index()
    {
        $busid = $this->request->param('busid', 0, 'trim');
        $page = $this->request->param('page', 1, 'trim');
        $limit = $this->request->param('limit', 10, 'trim');
        $status = $this->request->param('status', '', 'trim');

        $where = [
            'busid' => $busid,
        ];

        if ($status == '-1') {
            $where['status'] = ['IN', ['-1', '-2', '-3', '-4', '-5']];
        } elseif ($status || $status == '0') {
            $where['status'] = $status;
        }

        $count = $this->OrderModel->where($where)->count();

        $list = $this->OrderModel->with(['order_product' => ['products']])->where($where)->page($page, $limit)->order('createtime DESC')->select();

        if ($list) {
            $this->success('查询订单数据成功', null, ['count' => $count, 'list' => $list]);
        } else {
            $this->error('暂无订单');
        }
    }

    public function confirm()
    {
        $cartids = $this->request->param('cartids', '', 'trim');
        $busid = $this->request->param('busid', '', 'trim');
        $content = $this->request->param('content', '', 'trim');
        $addrid = $this->request->param('addrid', '', 'trim');

        $business = $this->BusinessModel->find($busid);

        if (!$business) {
            $this->error('用户不存在');
        }

        $address = model('business.Address')->find($addrid);

        if (!$address) {
            $this->error('选择收货地址不存在');
        }

        $CartModel = model('product.Cart');
        $ProductModel = model('product.Product');

        $CartList = $CartModel->with(['product'])->where(['cart.id' => ['IN', $cartids]])->select();

        if (!$CartList) {
            $this->error('下单的购物车不存在');
        }

        // 该订单总价
        $total = 0;

        foreach ($CartList as $item) {
            if ($item['pronum'] > $item['product']['stock']) {
                $this->error("{$item['product']['name']}库存不足");
            }

            $total += $item['total'];
        }

        /* 
            新增订单以及订单商品
            更新商品库存
            删除购物车记录
        */

        $this->OrderModel->startTrans();
        $this->OrderProductModel->startTrans();
        $CartModel->startTrans();
        $ProductModel->startTrans();

        // 封装订单数据
        $OrderData = [
            'code' => build_code('BU'),
            'busid' => $busid,
            'businessaddrid' => $addrid,
            'amount' => $total,
            'remark' => $content,
            'status' => 0,
        ];

        $OrderStatus = $this->OrderModel->validate('common/product/order/Order')->save($OrderData);

        if ($OrderStatus === false) {
            $this->error($this->OrderModel->getError());
        }

        // 定义订单商品数组以及更新商品库存
        $OrderProductData = [];
        $ProductData = [];

        foreach ($CartList as $item) {
            $OrderProductData[] = [
                'orderid' => $this->OrderModel->id,
                'proid' => $item['proid'],
                'pronum' => $item['pronum'],
                'price' => $item['price'],
                'total' => $item['total'],
            ];

            $ProductData[] = [
                'id' => $item['proid'],
                'stock' => bcsub($item['product']['stock'], $item['pronum'])
            ];
        }

        // 批量插入数据表
        $OrderProductStatus = $this->OrderProductModel->validate('common/product/order/Product')->saveAll($OrderProductData);

        if ($OrderProductStatus === false) {
            $this->OrderModel->rollback();
            $this->error($this->OrderProductModel->getError());
        }

        $ProductStatus = $ProductModel->isUpdate(true)->saveAll($ProductData);

        if ($ProductStatus === false) {
            $this->OrderModel->rollback();
            $this->OrderProductModel->rollback();
            $this->error('更新商品库存失败');
        }

        $CartStatus = $CartModel->destroy($cartids);

        if ($CartStatus === false) {
            $this->OrderModel->rollback();
            $this->OrderProductModel->rollback();
            $ProductModel->rollback();
            $this->error('删除购物车失败');
        }

        if ($OrderStatus === false ||  $OrderProductStatus === false || $ProductStatus === false || $CartStatus === false) {
            $this->OrderModel->rollback();
            $this->OrderProductModel->rollback();
            $ProductModel->rollback();
            $CartModel->rollback();
            $this->error('下单失败');
        } else {
            $this->OrderModel->commit();
            $this->OrderProductModel->commit();
            $ProductModel->commit();
            $CartModel->commit();
            $this->success('下单成功');
        }
    }

    public function pay()
    {
        $busid = $this->request->param('busid', 0, 'trim');
        $orderid = $this->request->param('orderid', 0, 'trim');

        $business = $this->BusinessModel->find($busid);

        if (!$business) {
            $this->error('用户不存在');
        }

        $order = $this->OrderModel->find($orderid);

        if (!$order) {
            $this->error('订单不存在');
        }

        // 结算了购物车后的余额
        $UpdateMoney = bcsub($business['money'], $order['amount'], 2);

        if ($UpdateMoney < 0) {
            $this->error('余额不足，请及时充值');
        }

        /* 
            更新订单状态
            更新用户余额
            新增消费记录
        */

        // 实例化模型
        $RecordModel = model('business.Record');

        // 开启事务
        $this->OrderModel->startTrans();
        $this->BusinessModel->startTrans();
        $RecordModel->startTrans();

        // 封装订单数据
        $OrderData = [
            'id' => $orderid,
            'status' => 1,
        ];

        $OrderStatus = $this->OrderModel->isUpdate(true)->save($OrderData);

        if ($OrderStatus === false) {
            $this->error('更新订单状态失败');
        }

        // 更新用户余额
        $BusinessData = [
            'id' => $busid,
            'money' => $UpdateMoney
        ];

        $BusinessStatus = $this->BusinessModel->isUpdate(true)->save($BusinessData);

        if ($BusinessStatus === false) {
            $this->OrderModel->rollback();
            $this->error('更新用户余额失败');
        }

        // 新增消费记录
        $RecordData = [
            'busid' => $busid,
            'total' => $order['amount'],
            'content' => "您的订单{$order['code']}共消费了{$order['amount']}"
        ];

        $RecordStatus = $RecordModel->validate('common/business/Record')->save($RecordData);

        if ($RecordStatus === false) {
            $this->OrderModel->rollback();
            $this->BusinessModel->rollback();
            $this->error($RecordModel->getError());
        }

        if ($OrderStatus === false || $BusinessStatus === false || $RecordStatus === false) {
            $this->OrderModel->rollback();
            $this->BusinessModel->rollback();
            $RecordModel->rollback();
            $this->error('支付失败');
        } else {
            $this->OrderModel->commit();
            $this->BusinessModel->commit();
            $RecordModel->commit();
            $this->success('支付成功');
        }
    }
}
