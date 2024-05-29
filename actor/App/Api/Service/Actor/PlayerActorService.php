<?php
namespace App\Api\Service\Actor;

use App\Api\Utils\Keys;
use EasySwoole\Component\CoroutineSingleTon;
use EasySwoole\Redis\Redis;
use EasySwoole\Pool\Manager as PoolManager;

class PlayerActorService
{
    use CoroutineSingleTon;

    public function init():void
    {
        $allNodeKey = Keys::getInstance()->getNodeListKey();
        PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($allNodeKey) {
            $allNode = $redis->hGetAll($allNodeKey);
            foreach ($allNode as $node => $createTime) 
            {
                $actorHashKey = Keys::getInstance()->getPlayerActorHashKey($node);
                $redis->del($actorHashKey);
            }
        });

    }

    public function getAcrotId(string $openid,int $node):string
    {
        $actorId = $this->getOne($openid,$node);

        if($actorId) return $actorId;
        
        return CreateActorService::getInstance()->createPlayerActor($openid,$node);
    }

    public function getOne(string $openid,int $node):string
    {
        $actorHashKey = Keys::getInstance()->getPlayerActorHashKey($node);
        $actorId = PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($actorHashKey,$openid) {
            return $redis->hGet($actorHashKey,$openid);
        });

        return $actorId ? $actorId : '';
    }

    public function setCacheAcrotId(string $openid,string $node,string $actorId):void
    {
        $actorHashKey = Keys::getInstance()->getPlayerActorHashKey($node);
        PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($actorHashKey,$openid,$actorId) {
            return $redis->hSet($actorHashKey,$openid,$actorId);
        });
    }

}
