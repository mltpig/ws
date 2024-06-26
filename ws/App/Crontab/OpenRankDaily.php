<?php
namespace App\Crontab;
use EasySwoole\EasySwoole\Task\TaskManager;
use \EasySwoole\Crontab\JobInterface;
use App\Api\Service\Module\OpenRankService;
use App\Api\Utils\Request;
use EasySwoole\EasySwoole\Config as GlobalConfig;

class OpenRankDaily implements JobInterface
{
    public function crontabRule(): string
    {
        // 定义执行规则 根据Crontab来定义
        return '0 0 * * *';
    }

    public function jobName(): string
    {
        // 定时任务的名称
        return '开服冲榜活动结算';
    }

    public function run()
    {
        //开发者可投递给task异步处理
        TaskManager::getInstance()->async(function (){
            OpenRankService::getInstance()->settlementRewards();
        });
        
    }

    public function onException(\Throwable $throwable)
    {
        // 捕获run方法内所抛出的异常
    }
}