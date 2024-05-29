<?php
namespace App\Api\Controller\Paradise\Notice;
use App\Api\Controller\BaseController;

//响应邻居发送获取场景信息指令
class GetAdminGoodsDetail extends BaseController
{

    public function index()
    {

        return $this->player->getParadiseGoods($this->param['id']);
    }

}