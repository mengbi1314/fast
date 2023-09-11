<?php

namespace app\shop\controller\product;

use think\Controller;

class Cart extends Controller
{
    // 购物车模型
    protected $CartModel = null;

    protected $BusinessModel = null;
    protected $ProductModel = null;

    public function __construct()
    {
        parent::__construct();

        $this->CartModel = model('product.Cart');
        $this->BusinessModel = model('business.Business');
        $this->ProductModel = model('product.Product');
    }

    public function index()
    {
        $busid = $this->request->param('busid', 0, 'trim');

        $business = $this->BusinessModel->find($busid);

        if (!$business) {
            $this->error('用户不存在');
        }

        $list =  $this->CartModel->with(['product'])->where(['busid' => $busid])->order('cart.id DESC')->select();

        if ($list) {
            $this->success('查询购物车数据成功', null, $list);
        } else {
            $this->error('空空如也');
        }
    }

    public function add()
    {
        $proid = $this->request->param('proid', 0, 'trim');
        $busid = $this->request->param('busid', 0, 'trim');

        $business = $this->BusinessModel->find($busid);

        if (!$business) {
            $this->error('用户不存在');
        }

        $product = $this->ProductModel->find($proid);

        if (!$product) {
            $this->error('商品不存在');
        }

        $cart = $this->CartModel->where(['busid' => $busid, 'proid' => $proid])->find();

        $num = isset($cart['pronum']) ? $cart['pronum'] + 1 : 1;

        if ($num > $product['stock']) {
            $this->error('商品库存不足');
        }

        if ($cart) {
            $data = [
                'id' => $cart['id'],
                'pronum' => $num,
                'total' => bcmul($num, $cart['price'])
            ];

            $result = $this->CartModel->validate('common/product/Cart.edit')->isUpdate(true)->save($data);
        } else {
            // 封装数据
            $data = [
                'busid' => $busid,
                'proid' => $proid,
                'pronum' => $num,
                'price' => $product['price'],
                'total' => bcmul($num, $product['price'])
            ];

            $result = $this->CartModel->validate('common/product/Cart')->save($data);
        }

        if ($result === false) {
            $this->error($this->CartModel->getError());
        } else {
            $this->success('加入购物车成功');
        }
    }

    public function update()
    {
        $busid = $this->request->param('busid', 0, 'trim');
        $cartid = $this->request->param('cartid', 0, 'trim');
        $pronum = $this->request->param('pronum', 0, 'trim');

        $business = $this->BusinessModel->find($busid);

        if (!$business) {
            $this->error('用户不存在');
        }

        $cart = $this->CartModel->find($cartid);

        if (!$cart) {
            $this->error('用户不存在');
        }

        $data = [
            'id' => $cartid,
            'pronum' => $pronum,
            'total' => bcmul($pronum, $cart['price'], 2)
        ];

        $result = $this->CartModel->isUpdate(true)->save($data);

        if ($result === false) {
            $this->error('更新该购物车商品数量失败');
        } else {
            $this->success('更新该购物车商品数量成功');
        }
    }
}
