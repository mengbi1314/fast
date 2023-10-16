<?php

namespace app\rent\controller;

use think\Controller;

class Category extends Controller
{
    protected $CategoryModel = null;

    public function __construct()
    {
        parent::__construct();

        $this->CategoryModel = model('Category');
    }

    public function index()
    {
        $page = $this->request->param('page', 1, 'trim');
        $limit = $this->request->param('limit', 10, 'trim');

        $where = [
            'status' => 'normal'
        ];

        $count = $this->CategoryModel->where($where)->count();

        $list = $this->CategoryModel->where($where)->page($page, $limit)->select();

        $data = [
            'count' => $count,
            'list' => $list
        ];

        if ($list) {
            $this->success('查询成功', null, $data);
        } else {
            $this->error('暂无文章');
        }
    }

    public function recommend()
    {
        if ($this->request->isPost()) {
            // flag = recommend 
            $list = $this->CategoryModel->where(['flag' => ['LIKE', "%recommend%"]])->order('weigh DESC')->limit(8)->select();

            if ($list) {
                $this->success('查询成功', null, $list);
            }
        }
    }

    public function info()
    {
        $id = $this->request->param('id', 0, 'trim');
        $busid = $this->request->param('busid', 0, 'trim');

        $info = $this->CategoryModel->find($id);

        if (!$info) {
            $this->error('文章不存在');
        }

        $prev = $this->CategoryModel->where(['id' => ['<', $id]])->order('id DESC')->find();

        $next = $this->CategoryModel->where(['id' => ['>', $id]])->order('id ASC')->find();

        $business = model('business.Business')->find($busid);

        $info['collection_status'] = false;

        if ($business) {
            $collection = model('business.Collection')->where(['busid' => $busid, 'cateid' => $id])->find();

            $info['collection_status'] = $collection ? true : false;
        }

        $data = [
            'prev' => $prev,
            'info' => $info,
            'next' => $next
        ];

        $this->success('查询成功', null, $data);
    }
}
