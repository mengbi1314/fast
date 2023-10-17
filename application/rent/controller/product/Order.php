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
    }
}
