<?php
namespace App\Api\Controller\Paradise\Notice;
use App\Api\Controller\BaseController;

//获取福地信息
class GetAdmintInfo extends BaseController
{

    public function index()
    {

        $show   = [];
        $goods  = $this->player->getParadiseGoods();
        foreach ($goods as $pos =>  $detail) 
        {
            $show[$pos] =  ['id' => $pos,'gid' => $detail['gid'] ];
        }

        return [
            'head'     => $this->player->getHead(),
            'nickname' => $this->player->getNickname(),
            'goods'    => $show,
        ];
    }

}