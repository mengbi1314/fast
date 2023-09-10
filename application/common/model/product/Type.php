<?php

namespace app\common\model\product;

use think\Model;
use think\Env;

class Type extends Model
{
    protected $name = 'product_type';

    //追加不存在的数据字段
    protected $append = [
        'thumb_cdn', //单图
    ];

    public function getThumbCdnAttr($value, $data)
    {
        $thumb = ltrim($data['thumb'], '/');

        if (!$thumb || !is_file($thumb)) {
            $thumb = '/assets/img/logo.png';
        }

        $cdn = Env::get('site.url', config('site.url'));

        return $cdn . ltrim($thumb, '/');
    }
}
