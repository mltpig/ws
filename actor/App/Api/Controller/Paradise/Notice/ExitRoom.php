<?php
namespace App\Api\Controller\Paradise\Notice;
use App\Api\Controller\BaseController;

//响应邻居发送获取场景信息指令
class ExitRoom extends BaseController
{

    public function index()
    {
        $this->player->setParadiseRoom( $this->param['playerKey'],'del' );
        
        return [];
    }

}