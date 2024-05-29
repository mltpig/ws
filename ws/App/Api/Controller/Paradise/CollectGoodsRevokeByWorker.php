<?php
namespace App\Api\Controller\Paradise;
use App\Api\Controller\BaseController;
use App\Api\Service\Actor\PlayerActorService;

//采集自己物资
class CollectGoodsRevokeByWorker extends BaseController
{

    public function index()
    {
        $uid   = $this->player->getData('openid');
        $site  = $this->player->getData('site');

        $actorId   = PlayerActorService::getInstance()->getAcrotId($uid,$site);

        $result = PlayerActorService::getInstance()->send($actorId,'recallWorker', $this->param );

        if(is_array($result) && array_key_exists('reward',$result) && $result['reward']) $this->player->goodsBridge($result['reward'],'福地');
        
        $this->sendMsg( $result );
    }

}