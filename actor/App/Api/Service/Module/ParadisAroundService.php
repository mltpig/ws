<?php
namespace App\Api\Service\Module;
use EasySwoole\Component\CoroutineSingleTon;
use EasySwoole\Redis\Redis;
use EasySwoole\Pool\Manager as PoolManager;
use App\Actor\PlayerActor;
use EasySwoole\Actor\ActorNode;
use App\Api\Service\Actor\PlayerActorService;
use App\Api\Utils\Keys;
use EasySwoole\EasySwoole\Config as GlobalConfig;

class ParadisAroundService
{
    use CoroutineSingleTon;

    public function getAroundListShowFmt(array $around,array $workers):array
    {
        $actorNode = new ActorNode( GlobalConfig::getInstance()->getConf("ACTOR") );
        $homes =  array_column(array_filter($workers),'uid','uid');

        $show = [];
        foreach ($around as $type => $playerids) 
        {
            foreach ($playerids as $playerid) 
            {   
                list($_prefix,$openid,$site) = explode(':',$playerid);

                $actorId = PlayerActorService::getInstance()->getAcrotId($openid,$site);

                $result  = PlayerActor::client($actorNode)->send($actorId,[ 'method' => 'NoticeGetAdminInfo','data' => [] ]);
                $show[ $type ][]   = [
                    'rid'       => $playerid,
                    'state'     => in_array($playerid,$homes) ? 1 : 0,
                    'goods'     => $result['goods'],
                    'head'     => $result['head'],
                    'nickname' => $result['nickname'],
                ];
            }
        }

        return $show;
    }

    public function getNewAroundList(int $node , int $num ,array $filter):array
    {
        $activeKey = Keys::getInstance()->getParadisActiveKey($node);
        return PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($activeKey,$num,$filter) {
            foreach ($filter as $key => $playerKey) 
            {
                $redis->sRem($activeKey,$playerKey);
            }

            $list = $redis->sRandMember($activeKey,$num);

            foreach ($filter as $key => $playerKey) 
            {
                $redis->sAdd($activeKey,$playerKey);
            }

            return $list ? $list : [];
        });
    }

    public function sendAroundMessage(array $nodeData,string $method ,array $data = []):array
    {
        $actorNode = new ActorNode( GlobalConfig::getInstance()->getConf("ACTOR") );
        $actorId   = PlayerActorService::getInstance()->getAcrotId($nodeData['uid'],$nodeData['site']);

        return PlayerActor::client($actorNode)->send($actorId,[ 'method' => $method ,'data' => $data ]);
    }
    
    public function existsPlayer(array $around,string $rid):array
    {
        $list = [];

        foreach ($around as $type => $playerids) 
        {
            foreach ($playerids as $playerid) 
            {   
                list($_prefix,$uid,$site) = explode(':',$playerid);
                // $list[md5($playerid)] = ['uid' => $uid,'site' => $site];
                $list[$playerid] = ['uid' => $uid,'site' => $site];
            }
        }
        
        return array_key_exists($rid,$list) ? $list[$rid] : [];
    }

}
