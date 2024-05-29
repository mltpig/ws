<?php

namespace App\Actor;

use EasySwoole\Actor\AbstractActor;
use EasySwoole\Actor\ActorConfig;
use App\Api\Service\PlayerService;
use EasySwoole\EasySwoole\ServerManager;
use App\Actor\PlayerActor;
use App\Api\Service\Actor\PlayerActorService;
use EasySwoole\Actor\ActorNode;
use EasySwoole\EasySwoole\Config as GlobalConfig;

/**
 * 玩家Actor
 * Class CreateActor
 * @package App\Player
 */
class CreateActor extends AbstractActor
{

    /**
     * 配置当前的Actor
     * @param ActorConfig $actorConfig
     */
    public static function configure(ActorConfig $actorConfig)
    {
        $actorConfig->setActorName('CreateActor');
        $actorConfig->setWorkerNum(1);
    }

    /**
     * Actor首次启动时
     */
    protected function onStart()
    {
        $actorId = $this->actorId();
        echo "Create Actor {$actorId} onStart\n";
    }

    /**
     * Actor收到消息时
     * @param $msg
     */
    protected function onMessage($param)
    {
        if(!is_array($param)) return '错误的数据格式';

        $node   = $param['data']['node'];
        $openid = $param['data']['openid'];

        if( $actorId = PlayerActorService::getInstance()->getOne($openid,$node) ) return $actorId;

        $actorNode = new ActorNode(GlobalConfig::getInstance()->getConf("ACTOR"));

        $actorId   = PlayerActor::client($actorNode)->create( ['method' => 'create','data' => $param['data'] ] );

        PlayerActorService::getInstance()->setCacheAcrotId($openid,$node,$actorId);

        return $actorId;
    }

    /**
     * Actor即将退出前
     * @param $arg
     */
    protected function onExit($arg)
    {
        $actorId = $this->actorId();
        echo "Player Actor {$actorId} onExit\n";
    }

    /**
     * Actor发生异常时
     * @param \Throwable $throwable
     */
    protected function onException(\Throwable $throwable)
    {
        $actorId = $this->actorId();
        echo "Player Actor {$actorId} onException\n";
    }

}