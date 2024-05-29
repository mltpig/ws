<?php
namespace App\Api\Service;

use App\Actor\PlayerActor;
use EasySwoole\Actor\ActorNode;
use EasySwoole\EasySwoole\Config as GlobalConfig;
use EasySwoole\Component\CoroutineSingleTon;


class FudiService
{
    use CoroutineSingleTon;

    public function dailyReset(PlayerService $playerSer):void
    {
        // $actorNode = new ActorNode( GlobalConfig::getInstance()->getConf("ACTOR") );

    	// PlayerActor::client($actorNode)->send($actorId,$param);
    }

}
