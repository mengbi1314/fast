<?php

namespace app\stock\controller;

use think\Controller;

class Index extends Controller
{
    protected $timeList = [];

    protected $ReceiveModel = null;
    protected $OrderModel = null;
    protected $SourceModel = null;
    protected $BusinessModel = null;

    public function __construct()
    {
        parent::__construct();

        $this->ReceiveModel = model('Business.Receive');

        $this->OrderModel = model('product.order.Order');

        $this->SourceModel = model('Business.Source');

        $this->BusinessModel = model('Business.Business');

        $year = date('Y');

        for ($i = 1; $i <= 12; $i++) {
            $start = date('Y-m-01', strtotime($year . '-' . $i));

            $end = date('Y-m-t', strtotime($year . '-' . $i));

            $this->timeList[] = [$start, $end];
        }
    }

    public function total()
    {
        $OrderCount = model('product.order.order')->count();

        $OrderMoney = model('product.order.order')->sum('amount');

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

    public function visit()
    {
        $VisitData = [];

        foreach ($this->timeList as $time) {
            $where = [
                'createtime' => ['between time', $time]
            ];

            $VisitData[] = model('business.Visit')->where($where)->count();
        }

        $this->success('查询回访记录成功', null, $VisitData);
    }

    public function receive()
    {
        foreach ($this->timeList as $item) {

            $where = [
                'status' => 'apply',
                'applytime' => ['between', $item]
            ];

            $apply[] = $this->ReceiveModel->where($where)->count();

            $where1 = [
                'status' => 'allot',
                'applytime' => ['between', $item]
            ];

            $allot[] = $this->ReceiveModel->where($where1)->count();

            $where2 = [
                'status' => 'recovery',
                'applytime' => ['between', $item]
            ];

            $recovery[] = $this->ReceiveModel->where($where2)->count();
        }

        $data = [
            'apply' => $apply,
            'allot' => $allot,
            'recovery' => $recovery
        ];

        $this->success('返回领取统计', '', $data);
    }

    public function order()
    {
        $data = [];

        $paid  = $this->OrderModel->where('status', 1)->count();

        $data[] = [
            'name' => '已支付',
            'value' => $paid
        ];

        $shipped = $this->OrderModel->where('status', 2)->count();
        $data[] = [
            'name' => '已发货',
            'value' => $shipped
        ];

        $received = $this->OrderModel->where('status', 3)->count();
        $data[] = [
            'name' => '已收货',
            'value' => $received
        ];

        $completed = $this->OrderModel->where('status', 4)->count();
        $data[] = [
            'name' => '已完成',
            'value' => $completed
        ];

        $this->success('订单统计', '', $data);
    }
    public function source()
    {
        $Sourcelist = $this->SourceModel->select();

        foreach ($Sourcelist as $item) {

            $count = $this->BusinessModel->where('sourceid', $item['id'])->count();

            $data[] = [
                'name' => $item['name'],
                'value' => $count
            ];
        }

        $this->success('来源统计', '', $data);
    }
}
