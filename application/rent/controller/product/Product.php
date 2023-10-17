<?php

namespace app\rent\controller\product;

use think\Controller;

class Product extends Controller
{
    protected $ProductModel = null;

    public function __construct()
    {
        parent::__construct();

        $this->ProductModel = model('product.Product');
    }

    public function index()
    {
        $page = $this->request->param('page', 1, 'trim');
        $limit = $this->request->param('limit', 10, 'trim');

        $where = [
            'status' => 1,
            'rentstatus' => ['<>', 1]
        ];

        $count = $this->ProductModel->where($where)->count();

        $list = $this->ProductModel->where($where)->order('createtime DESC')->page($page, $limit)->select();

        if ($list) {
            $this->success('查询成功', null, ['count' => $count, 'list' => $list]);
        } else {
            $this->error('暂无商品');
        }
    }
}
