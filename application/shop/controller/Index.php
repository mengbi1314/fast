<?php

namespace app\shop\controller;

use think\Controller;

class Index extends Controller
{
    public function index()
    {

        //推荐商品数据
        $recommendList  = model('product.Product')->where(['flag' => 3])->order('createtime DESC')->limit(6)->select();

        //商品分类的数据
        $TypeList = model('product.Type')->order('weight DESC')->limit(8)->select();

        // 新品商品的数据

        // 热销商品的数据

        // 组装数据
        $data = [
            'recommendList' => $recommendList,
            'TypeList' => $TypeList
        ];

        $this->success('查询数据成功', null, $data);
    }
}
