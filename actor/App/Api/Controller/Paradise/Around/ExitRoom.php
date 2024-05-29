<?php
namespace App\Api\Controller\Paradise\Around;
use App\Api\Service\Module\ParadisAroundService;
use App\Api\Controller\BaseController;


//退出房间
class ExitRoom extends BaseController
{

    public function index()
    {

        $rid = $this->player->getParadiseTmp('rid');

        list($_p,$uid,$site) = explode(':',$rid);

        ParadisAroundService::getInstance()->sendAroundMessage([ 'uid' => $uid,'site' => $site ],'NoticeExitRoom',[ 'playerKey' => $this->player->getData('playerKey') ]);

        return [];

    }

}