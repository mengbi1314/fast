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
        // 购物车的总数
        $CartCount = model('product.Cart')->where(['busid' => $busid])->count();

        $this->success('查询商品详情成功', null, ['CartCount' => $CartCount, 'product' => $product]);
    }

    public function collection()
    {
        $proid = $this->request->param('proid', '', 'trim');
        $busid = $this->request->param('busid', '', 'trim');

        $business = model('business.Business')->find($busid);

        if (!$business) {
            $this->error('用户不存在');
        }

        $product = $this->ProductModel->find($proid);

        if (!$product) {
            $this->error('商品不存在');
        }

        $collection = model('business.Collection')->where(['busid' => $busid, 'proid' => $proid])->find();

        $msg = '未知信息';

        if ($collection) {
            $result = $collection->delete();

            $msg = '取消收藏';
        } else {
            // 封装插入数据
            $data = [
                'busid' => $busid,
                'proid' => $proid,
            ];

            $result = model('business.Collection')->validate('common/business/Collection.product')->save($data);

            $msg = '收藏';
        }

        if ($result === false) {
            $this->error(model('business.Collection')->getError());
        } else {
            $this->success("{$msg}成功");
        }
    }
}
