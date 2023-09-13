<?php

namespace app\common\model\product\order;

use think\Model;
use traits\model\SoftDelete;

class Order extends Model
{
    use SoftDelete;

    //自动写入时间戳
    protected $autoWriteTimestamp = true;

    //定义时间戳
    protected $createTime = 'createtime';
    protected $updateTime = false;

    //软删除字段
    protected $deleteTime = 'deletetime';

    protected $name = 'order';

    //追加字段
    protected $append = [
        'status_text',
        'createtime_text'
    ];

    //订单状态数据
    public function getStatusList()
    {
        return [
            '0' => __('未支付'),
            '1' => __('已支付'),
            '2' => __('已发货'),
            '3' => __('已收货'),
            '4' => __('已完成'),
            '-1' => __('仅退款'),
            '-2' => __('退款退货'),
            '-3' => __('售后中'),
            '-4' => __('退货审核成功'),
            '-5' => __('退货审核失败'),
        ];
    }
    public function getStatusTextAttr($value, $data)
    {
        $StatusList = $this->getStatusList();

        return $StatusList[$data['status']];
    }

    public function OrderProduct()
    {
        return $this->hasMany('app\common\model\product\order\Product', 'orderid', 'id');
    }

    public function getCreatetimeTextAttr($value, $data)
    {
        return date('Y-m-d H:i:s', $data['createtime']);
    }
}
