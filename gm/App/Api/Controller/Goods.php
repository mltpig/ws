<?php
namespace App\Api\Controller;

use App\Api\Controller\BaseController;

class Goods extends BaseController
{

    public function index()
    {
        $list = [
            100000 => "元宝",100003 => "银币",100004 => "包子",100005 => "通灵水",
            100006 => "混元石",100012 => "更名卡",100025 => "日晷",100026 => "切磋券",
            100029 => "馒头"
        ];

        $this->rJson( $list );
    }
}