<?php

namespace app\shop\controller\product;

use think\Controller;

class Type extends Controller
{
    protected $TypeModel = null;

    public function __construct()
    {
        parent::__construct();

        $this->TypeModel = model('product.Type');
    }
    public function index()
    {
        $TypeList = $this->TypeModel->order('weight DESC')->select();

        array_unshift($TypeList, ['name' => '全部', 'id' => 0]);

        $data = [
            'TypeList' => $TypeList
        ];
        $this->success('查询数据成功', null, $data);
    }
    public function product()
    {
    }
}
