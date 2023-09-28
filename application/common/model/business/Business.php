<?php

namespace app\common\model\business;

use think\Model;

// 软删除的模型
use traits\model\SoftDelete;


/**
 * 用户模型
 */
class Business extends Model
{
    //继承软删除
    use SoftDelete;

    //模型对应的是哪张表
    protected $name = "business";

    //指定一个自动设置的时间字段
    //开启自动写入
    protected $autoWriteTimestamp = true;

    //设置字段的名字
    protected $createTime = "createtime"; //插入的时候设置的字段名

    //禁止 写入的时间字段
    protected $updateTime = false;

    // 软删除的字段
    protected $deleteTime = 'deletetime';

    protected $append = [
        'sex_text', //性别
        'region_text', //地区字符串,
        'deal_text', //成交状态
        'avatar_cdn', // 头像资源
        'mobile_text', // 手机字符串
    ];

    //新建一个获取器
    // value 是 sex这个字段的值
    // public function getSexAttr($value) 覆盖原字段
    //sex_text

    //增加新字段 sex_text
    public function getSexTextAttr($value, $data)
    {
        $sexlist = [0 => '保密', 1 => '男', 2 => '女'];

        $gender = isset($data['gender']) ? $data['gender'] : '';

        if ($gender >= '0') {
            return $sexlist[$gender];
        }
        return;
    }

    //
    public function getDealTextAttr($value, $data)
    {
        $sexlist = [0 => '未成交', 1 => '已成交'];

        $deal = isset($data['deal']) ? $data['deal'] : '';

        if ($deal >= '0') {
            return $sexlist[$deal];
        }
        return;
    }

    public function getRegionTextAttr($value, $data)
    {
        $province = model('Region')->where(['code' => $data['province']])->find();

        $city = model('Region')->where(['code' => $data['city']])->find();

        $district = model('Region')->where(['code' => $data['district']])->find();

        $output = [];

        if ($province) {
            $output[] = $province['name'];
        }

        if ($city) {
            $output[] = $city['name'];
        }

        if ($district) {
            $output[] = $district['name'];
        }

        //广东省-广州市-海珠区
        return implode('-', $output);
    }

    //给模型定义一个关联查询
    public function provinces()
    {
        // belongsTo('关联模型名','外键名','关联表主键名',['模型别名定义'],'join类型');
        //参数1：关联的模型
        //参数2：用户表的外键的字段
        //参数3：关联表的主键
        //参数4：模型别名
        //参数5：链接方式 left
        // setEagerlyType(1) IN查询
        // setEagerlyType(0) JOIN查询
        return $this->belongsTo('app\common\model\Region', 'province', 'code', [], 'LEFT')->setEagerlyType(0);
    }

    //查询城市
    public function citys()
    {
        return $this->belongsTo('app\common\model\Region', 'city', 'code', [], 'LEFT')->setEagerlyType(0);
    }

    //查询地区
    public function districts()
    {
        return $this->belongsTo('app\common\model\Region', 'district', 'code', [], 'LEFT')->setEagerlyType(0);
    }

    // 客户来源
    public function source()
    {
        return $this->belongsTo('app\common\model\Business\Source', 'sourceid', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function admin()
    {
        return $this->belongsTo('app\admin\model\Admin', 'adminid', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function getAvatarCdnAttr($value, $data)
    {
        $avatar = isset($data['avatar']) ? $data['avatar'] : '';

        $cdn = config('site.url');

        if (!is_file('.' . $avatar)) {
            // 返回默认头像
            $avatar = '/assets/home/images/avatar.jpg';
        }

        return $cdn . $avatar;
    }

    public function getMobileTextAttr($value, $data)
    {
        $mobile = $data['mobile'] ?? '';

        if (empty($mobile)) {
            return false;
        }

        return substr_replace($mobile, '****', 3, 4);
    }
}
