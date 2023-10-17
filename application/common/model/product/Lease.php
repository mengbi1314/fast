<?php

namespace app\common\model\product;

use think\Model;
use traits\model\SoftDelete;

class Lease extends Model
{
    use SoftDelete;

    protected $name = 'lease';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;

    // 软删除字段
    protected $deleteTime = 'deletetime';

    // 追加不存在的字段
    protected $append = [
        'endtime_text',
        'status_text'
    ];

    public function getEndtimeTextAttr($value, $data)
    {
        return date("Y-m-d H:i", $data['endtime']);
    }

    public function product()
    {
        return $this->belongsTo('app\common\model\Product\Product', 'proid', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function business()
    {
        return $this->belongsTo('app\common\model\Business\Business', 'busid', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function getStatusTextAttr($val, $data)
    {
        switch ($data['status']) {
            case '1':
                return '已下单';
                break;

            case '2':
                return '已发货';
                break;

            case '3':
                return '已收货';
                break;

            case '4':
                return '已归还';
                break;

            case '5':
                return '已退还押金';
                break;

            case '6':
                return '已完成';
                break;
        }
    }
}
