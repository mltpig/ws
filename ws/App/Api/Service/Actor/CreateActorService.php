<?php
namespace App\Api\Service\Actor;
use App\Api\Utils\Keys;
use EasySwoole\Component\CoroutineSingleTon;
use App\Actor\CreateActor;
use EasySwoole\Actor\ActorNode;
use EasySwoole\EasySwoole\Config as GlobalConfig;
use EasySwoole\Redis\Redis;
use EasySwoole\Pool\Manager as PoolManager;

class CreateActorService
{
    use CoroutineSingleTon;

    public $actorNum  = 5;
    
    public function initTable():void
    {

        $actorHashKey = Keys::getInstance()->getCreateActorHashKey();

        $actorNode = new ActorNode(GlobalConfig::getInstance()->getConf("ACTOR"));

        for ($id = 0; $id < $this->actorNum ; $id++) 
        { 
            $actorId = CreateActor::client($actorNode)->create();
            PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($actorHashKey,$id,$actorId) {
                return $redis->hSet($actorHashKey,$id,$actorId);
            });
        }

    }


    public function getOne(int $id):string
    {
        $actorHashKey = Keys::getInstance()->getCreateActorHashKey();

        return PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($actorHashKey,$id) {
            return $redis->hGet($actorHashKey,$id);
        });

    }

    public function createPlayerActor(string $openid,int $node):string
    {
        $id = (getSumAscii($openid) % $this->actorNum);
        $createActorId =  $this->getOne( $id);
        $actorNode   = new ActorNode( GlobalConfig::getInstance()->getConf("ACTOR") );
        return CreateActor::client($actorNode)->send($createActorId,['method' => 'create','data' => [ 'openid' => $openid,'node' => $node ] ]);
    }

}
