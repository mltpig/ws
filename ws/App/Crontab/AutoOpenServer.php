<?php

namespace App\Crontab;

use App\Api\Service\AutoOpenServer\AutoOpenServerService;
use EasySwoole\Crontab\JobInterface;
use EasySwoole\EasySwoole\Task\TaskManager;

/**
 * 自动开服脚本
 */
class AutoOpenServer implements JobInterface
{
    public function crontabRule(): string
    {
        // 定义执行规则 根据Crontab来定义 每分钟
        return '*/1 * * * *';
    }

    public function jobName(): string
    {
        // 定时任务的名称
        return '判断是否自动开服';
    }

    public function run()
    {
        //开发者可投递给task异步处理
        TaskManager::getInstance()->async(function () {
            AutoOpenServerService::getInstance()->run();
        });

    }

    public function onException(\Throwable $throwable)
    {
        // 捕获run方法内所抛出的异常
    }

}