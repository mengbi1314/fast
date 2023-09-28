<?php

namespace app\common\model\product\order;

use think\Model;
use traits\model\SoftDelete;

class Order extends Model
{
    use SoftDelete;

    protected $autoWriteTimestamp = true;

    protected $createTime = 'createtime';
    protected $updateTime = false;

    protected $deleteTime = 'deletetime';

    protected $name = 'order';

    protected  $append = [
        'status_text',
        'createtime_text'
    ];

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
            '-5' => __('退货审核失败')
        ];
    }

    public function getStatusTextAttr($value, $data)
    {
        $StatusList = $this->getStatusList();

        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function orderproduct()
    {
        return $this->belongsTo('app\common\model\Product\order\Product', 'busid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function getCreatetimeTextAttr($value, $data)
    {

        return $this->belongsTo('app\common\model\Product\order\Product', 'busid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function product()
    {
        return $this->belongsTo('app\common\model\Product\Product', 'busid', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function business()
    {
        return $this->belongsTo('app\common\model\Business\Business', 'busid', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function getCommentStatusAttr($value, $data)
    {
        $busid = $data['busid'] ?? '';
        $subid = $data['subid'] ?? '';

        $comment = model('Subject.Comment')->where(['busid' => $busid, 'subid' => $subid])->find();

        if ($comment) {
            return true;
        } else {
            return false;
        }
    }
}
