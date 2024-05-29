<?php
namespace App\Api\Service\Actor;
use EasySwoole\Component\CoroutineSingleTon;
use App\Api\Table\Table;

class ActorService
{
    use CoroutineSingleTon;

    public $actorNum  = 5;
    
    public function init():void
    {   
        //配置初始化
        Table::getInstance()->reset();
        //删除actorId 存储ID => actorId 是规律的，防止项目重启后出现 A 链接 B Actor
        PlayerActorService::getInstance()->init();
        //直接覆盖
        CreateActorService::getInstance()->init();
    }


}
