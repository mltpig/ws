<?php
namespace App\Api\Controller\Paradise\Self;
use App\Api\Controller\BaseController;
use App\Api\Service\Actor\PlayerActorService;

//获取当前福地物品状态
class Get extends BaseController
{

    public function index()
    {
        
        $uid   = $this->player->getData('openid');
        $site  = $this->player->getData('site');

        $actorId = PlayerActorService::getInstance()->getAcrotId($uid,$site);

        $result  = PlayerActorService::getInstance()->send($actorId,'getSelfParadiseInfo', [] );
        $result['isopen'] = $this->param['isopen'];

        if(is_array($result) && array_key_exists('reward',$result) && $result['reward']) $this->player->goodsBridge($result['reward'],'福地');
        
        $this->sendMsg( $result );
    }

}