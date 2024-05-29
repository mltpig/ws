<?php
namespace App\Api\Controller\Paradise\Around;
use App\Api\Controller\BaseController;
use App\Api\Service\Actor\PlayerActorService;

//获取当前福地物品状态
class Info extends BaseController
{

    public function index()
    {

        $uid   = $this->player->getData('openid');
        $site  = $this->player->getData('site');

        $actorId = PlayerActorService::getInstance()->getAcrotId($uid,$site);

        $this->param['scene']  = 'info';
        $result  = PlayerActorService::getInstance()->send($actorId,'getAroundParadiseInfo', $this->param );

        if(is_array($result) && array_key_exists('reward',$result) && $result['reward']) $this->player->goodsBridge($result['reward'],'福地');
        
        $this->sendMsg( $result );
    }

}