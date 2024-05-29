<?php
namespace App\Api\Controller\Paradise\Around;
use App\Api\Controller\BaseController;
use App\Api\Service\Actor\PlayerActorService;

//获取当前福地物品状态
class Get extends BaseController
{

    public function index()
    {

        $uid               = $this->player->getData('openid');
        $site              = $this->player->getData('site');
        $this->param['fd'] = $this->player->getData('fd');

        $actorId = PlayerActorService::getInstance()->getAcrotId($uid,$site);

        $result  = PlayerActorService::getInstance()->send($actorId,'getAroundParadiseList', $this->param );

        $this->sendMsg($result);
    }

}