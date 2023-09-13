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

        // 结算了购物车后的余额
        $UpdateMoney = bcsub($business['money'], $total, 2);

        if ($UpdateMoney < 0) {
            $this->error('余额不足，请及时充值');
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
}
