<?php

namespace app\common\model\business;

use app\common\model\Region;
use think\Model;
use traits\model\SoftDelete;

class Business extends Model
{
    // 指定数据表
    protected $name = 'business';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;

    use SoftDelete;
    protected $deleteTime = 'deletetime';

    // 追加数据表不存在的字段
    protected $append = [
        'mobile_text',
        'avatar_cdn',
        'region_text',
        'deal_text',
        'createtime_text',
        'deletetime_text',
        'gender_text',
        'sourceid_text',
    ];

    // 获取器
    public function getMobileTextAttr($value, $data)
    {
        return substr_replace($data['mobile'], '****', 3, 4);
    }

    public function getSourceidTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['sourceid']) ? $data['sourceid'] : 0);
        $list = $this->getSourceidList();
        return isset($list[$value]) ? $list[$value] : '未知';
    }

    public function getSourceidList()
    {
        $res = model('business.Source')->column('id,name');
        return $res;
    }

    public function getGenderList()
    {
        return ['0' => __('保密'), '1' => __('男'), '2' => __('女')];
    }

    public function getGenderTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['gender']) ? $data['gender'] : 0);
        $list = $this->getGenderList();
        return isset($list[$value]) ? $list[$value] : '未知';
    }

    // 定义日期格式
    public function getCreatetimeTextAttr($value, $data)
    {
        $createtime = isset($data['createtime']) ? $data['createtime'] : 0;

        return date('Y-m-d H:i', $createtime);
    }

    // 定义日期格式
    public function getDeletetimeTextAttr($value, $data)
    {
        $deletetime = isset($data['deletetime']) ? $data['deletetime'] : 0;

        return date('Y-m-d H:i', $deletetime);
    }

    public function getAvatarCdnAttr($values, $data)
    {
        $cdn = config('site.url');

        $avatar = $data['avatar'] ?? '';

        if (empty($avatar)) {
            $avatar = '/assets/img/avatar.png';
        }

        return $cdn . ltrim($avatar, '/');
    }

    public function getRegionTextAttr($value, $data)
    {
        $province = model('Region')->where('code', $data['province'])->value('name');
        $city = model('Region')->where('code', $data['city'])->value('name');
        $district = model('Region')->where('code', $data['district'])->value('name');
        if ($province) {
            $value = $province;
        }
        if ($city) {
            $value .= $city;
        }
        if ($district) {
            $value .= $district;
        }

        return $value;
    }

    public function getDealList()
    {
        return ['0' => __('未成交'), '1' => __('已成交')];
    }

    public function getDealTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['deal']) ? $data['deal'] : '');
        $list = $this->getDealList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function region()
    {
        return $this->belongsTo('app\common\model\Region', 'district', 'code', [], 'LEFT')->setEagerlyType(0);
    }

    public function source()
    {
        return $this->belongsTo('app\common\model\business\Source', 'sourceid', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function admin()
    {
        return $this->belongsTo('app\admin\model\Admin', 'adminid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
