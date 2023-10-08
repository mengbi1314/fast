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
}
