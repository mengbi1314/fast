<?php

namespace app\stock\controller\manage;

use think\Controller;

class Product extends Controller
{
    /**
     * 当前控制器下的一个模型属性
     * 
     */
    // 初始化

    // 管理员模型
    protected $OrderModel = null;
    protected $ProductModel = null;

    public function _initialize()
    {
        parent::_initialize();
        // 引入的模型
        $this->OrderModel = model('product.order.Order');
        $this->ProductModel = model('product.order.Product');
    }

    // 商品订单列表
    public function index()
    {
        $result = $this->OrderModel->with(['product', 'business'])->select();
        $this->success('', '', $result);
    }

    // 删除
    public function del()
    {
        $id = $this->request->param('id', 0, 'trim');

        $row = $this->OrderModel->column('id');

        if (!in_array($id, $row)) {
            $this->error(__('没有找到该课程订单'));
            exit;
        }

        $result = $this->OrderModel->destroy($id);

        if ($result === false) {
            $this->error($this->OrderModel->getError());
            exit;
        }

        $this->success('课程订单删除成功！');
        exit;
    }
}
