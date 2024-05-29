<?php
namespace App\Api\Controller\Paradise;
use App\Api\Service\Module\ParadisService;
use App\Api\Controller\BaseController;
use App\Api\Service\Actor\PlayerActorService;
use App\Api\Utils\Keys;

//装备上阵
class GotoAround extends BaseController
{

    public function index()
    {

        $result = '无效玩家';

        $uid  = $this->param['uid'];
        $site = $this->param['site'];
        $record = ParadisService::getInstance()->getOneRecord($uid,$site,$this->param['rid']);
        if($record)
        {
            list($status,$arounduid) = json_decode($record,true);

            $result = '不可为自己';
            if($arounduid)
            {

                $this->param['rid'] = $arounduid;
                $this->param['isopen'] = '';
                $this->param['scene']  = 'goto';
    
                $actorId = PlayerActorService::getInstance()->getAcrotId($uid,$site);
        
                $result  = PlayerActorService::getInstance()->send($actorId,'getAroundParadiseInfo', $this->param );
        
                if(is_array($result) && array_key_exists('reward',$result) && $result['reward']) $this->player->goodsBridge($result['reward'],'福地');

            }

        }
        
        $this->sendMsg($result );
    }

}