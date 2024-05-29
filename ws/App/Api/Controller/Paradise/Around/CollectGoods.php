<?php
namespace App\Api\Controller\Paradise\Around;
use App\Api\Controller\BaseController;
use App\Api\Service\Actor\PlayerActorService;
use App\Api\Service\TaskService;

//采集自己物资
class CollectGoods extends BaseController
{

    public function index()
    {
        $uid   = $this->player->getData('openid');
        $site  = $this->player->getData('site');

        $actorId = PlayerActorService::getInstance()->getAcrotId($uid,$site);

        $result  = PlayerActorService::getInstance()->send($actorId,'aroundCollectGoods', $this->param );

        if(is_array($result)) TaskService::getInstance()->setVal($this->player,63,1,'add');

        $this->sendMsg( $result );
    }

}