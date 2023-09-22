<?php

namespace app\stock\controller;

use think\Controller;

class Index extends Controller
{
    protected $timeList = [];

    public function __construct()
    {
        parent::__construct();

        $year = date('Y');

        for ($i = 1; $i <= 12; $i++) {
            $start = date('Y-m-01', strtotime($year . '-' . $i));

            $end = date('Y-m-t', strtotime($year . '-' . $i));

            $this->timeList[] = [$start, $end];
        }
    }

    public function total()
    {
        $OrderCount = model('product.order.Order')->count();

        $OrderMoney = model('product.order.Order')->sum('amount');

        $BusinessCount = model('business.Business')->count();

        $data = [
            'OrderCount' => $OrderCount,
            'OrderMoney' => $OrderMoney,
            'BusinessCount' => $BusinessCount
        ];

        $this->success('查询成功', null, $data);
    }

    public function business()
    {
        $noCertifiedData = [];
        $CertifiedData = [];

        foreach ($this->timeList as $time) {
            $noCertified = [
                'auth' => 0,
                'createtime' => ['between time', $time]
            ];

            $noCertifiedData[] = model('business.Business')->where($noCertified)->count();

            $Certified = [
                'auth' => 1,
                'createtime' => ['between time', $time]
            ];

            $CertifiedData[] = model('business.Business')->where($Certified)->count();
        }

        $this->success('查询成功', null, ['noCertified' => $noCertifiedData, 'Certified' => $CertifiedData]);
    }
}
