<?php
namespace App\Api\Table;

use EasySwoole\Component\CoroutineSingleTon;

class Table
{
    use CoroutineSingleTon;

    public function create():void
    {
        ConfigParadiseLevel::getInstance()->create();
        ConfigParadiseReward::getInstance()->create();
        ConfigSkill::getInstance()->create();
        ConfigComrade::getInstance()->create();
        ConfigParam::getInstance()->create();
        ConfigGoods::getInstance()->create();
    }
    
    public function reset():void
    {
        echo "缓存初始化开始".date('Y-m-d H:i:s').PHP_EOL ;

        ConfigParadiseLevel::getInstance()->initTable();
        ConfigParadiseReward::getInstance()->initTable();
        ConfigSkill::getInstance()->initTable();
        ConfigComrade::getInstance()->initTable();
        ConfigParam::getInstance()->initTable();
        ConfigGoods::getInstance()->initTable();

        echo "缓存初始化结束".date('Y-m-d H:i:s').PHP_EOL ;

    }

}
