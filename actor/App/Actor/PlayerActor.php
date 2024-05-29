<?php

namespace App\Actor;

use EasySwoole\Actor\AbstractActor;
use EasySwoole\Actor\ActorConfig;
use App\Api\Service\PlayerService;
use App\Api\Service\Module\ParadisService;
use App\Api\Validate\MapClass;

/**
 * 玩家Actor
 * Class PlayerActor
 * @package App\Player
 */
// 只处理 福地及订阅事件
class PlayerActor extends AbstractActor
{

    public $player   = null;

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
        //初始化
        $arg    = $this->getArg();
        $this->player = new PlayerService($arg['data']['openid'],$arg['data']['node']);
        //唤醒所有有人采集的actor

        ParadisService::getInstance()->resetTimer($this,$this->player);

        echo "Player Actor {$actorId} onStart\n";
    }

    /**
     * Actor收到消息时
     * @param $msg
     */
    protected function onMessage($param)
    {

        if(!is_array($param)) return '错误的数据格式';

        $this->player->before();

        $class = MapClass::getInstance()->getClassPath($param['method']);

        $result = $class::getInstance($this,$param['data'])->index();

        $this->player->saveData();

        return $result;
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
        var_dump($throwable->getMessage());
        echo "Player Actor {$actorId} onException\n";
    }

}