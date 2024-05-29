<?php
namespace App\Api\Controller\Paradise\Around;
use App\Api\Controller\BaseController;
use App\Api\Service\Actor\PlayerActorService;

//刷新自己物资
class Refresh extends BaseController
{

    public function index()
    {

        $uid   = $this->player->getData('openid');
        $site  = $this->player->getData('site');

        $actorId = PlayerActorService::getInstance()->getAcrotId($uid,$site);

        $result  = PlayerActorService::getInstance()->send($actorId,'refreshParadiseAround', $this->param );


        $this->sendMsg($result );
    }

}