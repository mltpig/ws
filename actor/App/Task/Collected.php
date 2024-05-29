<?php 
namespace App\Task;

use EasySwoole\Task\AbstractInterface\TaskInterface;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\Log\LoggerInterface;
use App\Api\Utils\Request;

class Collected implements TaskInterface
{
    protected $data;

    public function __construct($data)
    {
        // 保存投递过来的数据
        $this->data = $data;
    }

    public function run(int $taskId, int $workerIndex)
    {
        $url   = NOTIFY_URL.'/fudi';
        $param = [
            'uid'   => $this->data['uid'],
            'site'  => $this->data['site'],
            'event' => 'collected',
        ];
        Request::getInstance()->http($url,'get',$param);
    }

    public function onException(\Throwable $throwable, int $taskId, int $workerIndex)
    {
        Logger::getInstance()->log($throwable->getMessage(),LoggerInterface::LOG_LEVEL_ERROR,'Collected');

    }
}