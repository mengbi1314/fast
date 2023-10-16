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

    public function collection()
    {
        // 接收参数
        $busid = $this->request->param('busid', 0, 'trim');
        $cateid = $this->request->param('cateid', 0, 'trim');

        // 先查询该文章是否存在
        $cate = $this->CategoryModel->find($cateid);

        if (!$cate) {
            $this->error('文章不存在');
        }

        $business = model('business.Business')->find($busid);

        if (!$business) {
            $this->error('用户不存在');
        }

        // 先通过用户id和文章id查询用户收藏表是否有该记录，如果有的话，那么就是取消收藏，否则收藏

        // 实例化一个收藏模型
        $CollectionModel = model('business.Collection');

        $collection = $CollectionModel->where(['busid' => $busid, 'cateid' => $cateid])->find();

        $msg = '未知';

        if ($collection) {
            // 直接删除
            $result = $CollectionModel->destroy($collection['id']);

            $msg = '取消收藏';
        } else {
            // 收藏 => 往用户收藏表插入一条记录 => 前提需要插入的数据
            $data = [
                'busid' => $busid,
                'cateid' => $cateid,
            ];

            $result = $CollectionModel->validate('common/business/Collection.cate')->save($data);

            $msg = '收藏';
        }

        if ($result === false) {
            $this->error($CollectionModel->getError());
        } else {
            $this->success("{$msg}成功");
        }
    }
}
