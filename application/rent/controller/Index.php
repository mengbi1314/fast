<?php

namespace app\rent\controller;

use think\Controller;

class Index extends Controller
{
    public function index()
    {
        $ProductList = model('product.Product')->where(['rentstatus' => ['<>', 1], 'status' => 1, 'flag' => 3])->order('createtime DESC')->limit(6)->select();

        $CateList = model('Category')->where(['flag' => ['LIKE', "%index%"]])->order('weigh DESC')->limit(10)->select();

        $data = [
            'ProductList' => $ProductList,
            'CateList' => $CateList
        ];

        $this->success('查询成功', null, $data);
    }
}
