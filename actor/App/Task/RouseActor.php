<?php 
namespace App\Task;

use App\Api\Service\Actor\CreateActorService;
use EasySwoole\Task\AbstractInterface\TaskInterface;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\Log\LoggerInterface;
use App\Api\Utils\Request;

class RouseActor implements TaskInterface
{
    protected $uid;

    public function __construct(string $uid)
    {
        // 保存投递过来的数据
        $this->uid  = $uid;

    }

    public function run(int $taskId, int $workerIndex)
    {
        list($_pre,$openid,$site) = explode(':',$this->uid);
        CreateActorService::getInstance()->createPlayerActor($openid,$site);
    }

    public function onException(\Throwable $throwable, int $taskId, int $workerIndex)
    {
        Logger::getInstance()->log($throwable->getMessage(),LoggerInterface::LOG_LEVEL_ERROR,'Collected');

    }
}