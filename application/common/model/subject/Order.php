<?php

namespace app\common\model\Subject;

use think\Model;

// 软删除的模型
use traits\model\SoftDelete;

class Order extends Model
{
    //继承软删除
    use SoftDelete;

    //模型对应的是哪张表
    protected $name = "subject_order";

    //指定一个自动设置的时间字段
    //开启自动写入
    protected $autoWriteTimestamp = true;

    //设置字段的名字
    protected $createTime = "createtime"; //插入的时候设置的字段名

    //禁止 写入的时间字段
    protected $updateTime = false;

    // 软删除的字段
    protected $deleteTime = 'deletetime';

    // 追加字段
    protected $append = [
        'comment_status'
    ];

    public function subject()
    {
        return $this->belongsTo('app\common\model\Subject\Subject', 'subid', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function business()
    {
        // belongsTo('关联模型名','外键名','关联表主键名',['模型别名定义'],'join类型');
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
