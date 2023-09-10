<?php

namespace app\common\model\product;

use think\Env;
use think\Model;

class Product extends Model
{
    protected $name = 'product';

    // 追加不存在的数据表字段
    protected $append = [
        'thumb_cdn', // 单图
        'thumbs_cdn', // 多图
    ];

    public function getThumbCdnAttr($value, $data)
    {
        $thumbsArr = explode(',', $data['thumbs']);

        $thumbsArr = array_filter($thumbsArr);

        $thumb = $thumbsArr[0] ?? '';

        if (!$thumb || !is_file(ltrim($thumb, '/'))) {
            $thumb =  '/uploads/20230501/20230501xOv3JSp9UMWCHfP2.jpg';
        }

        $cdn = Env::get('site.url', config('site.url'));

        return $cdn . ltrim($thumb, '/');
    }

    public function getThumbsCdnAttr($value, $data)
    {
        $cdn = Env::get('site.url', config('site.url'));

        if (!$data['thumbs']) {
            return $cdn . '/uploads/20230501/20230501xOv3JSp9UMWCHfP2.jpg';
        }

        $thumbStr = str_replace('/uploads/', $cdn . 'uploads/', $data['thumbs']);

        $thumbsArr = explode(',', $thumbStr);

        $thumbsArr = array_filter($thumbsArr);

        return $thumbsArr;
    }
}
