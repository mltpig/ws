<?php

namespace App\Actor;

use EasySwoole\Actor\AbstractActor;
use EasySwoole\Actor\ActorConfig;

/**
 * 玩家Actor
 * Class PlayerActor
 * @package App\Player
 */
// 只处理 福地及订阅事件
class PlayerActor extends AbstractActor
{

    /**
     * 配置当前的Actor
     * @param ActorConfig $actorConfig
     */
    public static function configure(ActorConfig $actorConfig)
    {
        $actorConfig->setActorName('PlayerActor');
        $actorConfig->setWorkerNum(1);
    }

    /**
     * Actor首次启动时
     */
    protected function onStart()
    {
        $actorId = $this->actorId();
        echo "Player Actor {$actorId} onStart\n";
    }

    /**
     * Actor收到消息时
     * @param $msg
     */
    protected function onMessage($param)
    {
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