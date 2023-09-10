<?php

namespace app\shop\controller\product;

use think\Controller;

class Product extends Controller
{
    // 商品模型
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
        $OrderBy = $this->request->param('OrderBy', 'createtime', 'trim');
        $flag = $this->request->param('flag', '', 'trim');
        $search = $this->request->param('search', '', 'trim');
        $typeid = $this->request->param('typeid', '', 'trim');

        $where = [];

        if ($typeid) {
            $where['typeid'] = $typeid;
        }

        if ($flag) {
            $where['flag'] = $flag;
        }

        if ($search) {
            $where['name'] = ['LIKE', "%$search%"];
        }

        $count = $this->ProductModel->where($where)->count();

        $list = $this->ProductModel->where($where)->order("$OrderBy DESC")->page($page, $limit)->select();

        if ($list) {
            $this->success('查询商品数据成功', null, ['count' => $count, 'list' => $list]);
        } else {
            $this->error('暂无商品');
        }
    }

    public function info()
    {
        $id = $this->request->param('id', 0, 'trim');
        $busid = $this->request->param('busid', 0, 'trim');

        $product = $this->ProductModel->find($id);

        if (!$product) {
            $this->error('商品不存在');
        }

        $business = model('business.Business')->find($busid);

        $product['collection_status'] = false;

        if ($business) {
            // 查询用户收藏表是否有这条收藏记录
            $collection = model('business.Collection')->where(['proid' => $id, 'busid' => $busid])->find();

            if ($collection) {
                $product['collection_status'] = true;
            }
        }

        $this->success('查询商品详情成功', null, $product);
    }
}
