<?php

namespace app\rent\controller\product;

use think\Controller;

class Order extends Controller
{
    protected $LeaseModel = null;

    public function __construct()
    {
        parent::__construct();

        $this->LeaseModel = model('product.Lease');
    }

    public function index()
    {
        $busid = $this->request->param('busid', 0, 'trim');
        $page = $this->request->param('page', 1, 'trim');
        $limit = $this->request->param('limit', 10, 'trim');
        $status = $this->request->param('status', 0, 'trim');

        $where = [
            'busid' => $busid
        ];

        if ($status) {
            $where['lease.status'] = $status;
        }

        $count = $this->LeaseModel->with(['product'])->where($where)->count();

        $list = $this->LeaseModel->with(['product'])->where($where)->page($page, $limit)->select();

        $data = [
            'count' => $count,
            'list' => $list,
        ];

        if ($list) {
            $this->success('查询成功', null, $data);
        } else {
            $this->error('暂无订单');
        }
    }
}
