<?php

namespace app\common\model\business;

use think\Env;
use think\Model;

class Business extends Model
{
    // 指定的数据表
    protected $name = 'business';

    // 开启自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;

    // 追加数据表的不存在字段
    protected $append = [
        // 文本 => text 资源 => cdn 总数 => count
        'mobile_text',
        'avatar_cdn',
        'region_text',
        'deal_text',
        'gender_text'
    ];

    // 定义一个获取器
    public function getMobileTextAttr($value, $data)
    {
        $mobile = $data['mobile'] ?? '';

        if (empty($mobile)) {
            return false;
        }

        return substr_replace($mobile, '****', 3, 4);
    }

    // 头像获取器
    public function getAvatarCdnAttr($value, $data)
    {
        $avatar = $data['avatar'] ?? '';

        if (empty($avatar)) {
            $avatar = 'assets/home/images/avatar.jpg';
        }

        // 获取网站域名
        $cdn = Env::get('site.url', config('site.url'));

        return $cdn . $avatar;
    }

    public function getRegionTextAttr($value, $data)
    {
        $region_text = '';

        $province = model('Region')->where(['code' => $data['province']])->value('name');
        $city = model('Region')->where(['code' => $data['city']])->value('name');
        $district = model('Region')->where(['code' => $data['district']])->value('name');

        if ($province) {
            $region_text = $province;
        }

        if ($city) {
            $region_text .= '-' . $city;
        }

        if ($district) {
            $region_text .=  '-' . $district;
        }

        return $region_text;
    }

    public function source()
    {
        return $this->belongsTo('app\common\model\business\Source', 'sourceid', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function getDealTextAttr($value, $data)
    {
        $list = [0 => '未成交', 1 => '已成交'];

        return $list[$data['deal']];
    }

    public function getGenderTextAttr($value, $data)
    {
        $genderList = [0 => '保密', 1 => '男', 2 => '女'];

        return $genderList[$data['gender']];
    }
}
