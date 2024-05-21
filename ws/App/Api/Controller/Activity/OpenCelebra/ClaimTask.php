<?php
namespace App\Api\Controller\Activity\OpenCelebra;
use App\Api\Utils\Consts;
use App\Api\Table\ConfigTask;
use App\Api\Service\Module\OpenCelebraService;
use App\Api\Controller\BaseController;

class ClaimTask extends BaseController
{

    public function index()
    {
        $task       = $this->player->getData('task');
        $taskInfo   = OpenCelebraService::getInstance()->getOpenCelebra104Task($task);
        $rewards    = [];
        foreach($taskInfo as $k => $v)
        {
            if($v['state'] != 1) continue;
            $this->player->setTask($v['id'],1,2,'set');

            $taskConfig = ConfigTask::getInstance()->getOne($v['id']);
            $this->player->goodsBridge($taskConfig['rewards'],'开服庆典任务奖励');

            $this->player->setArg($taskConfig['rewards'][0]['gid'], $taskConfig['rewards'][0]['num'], 'add');

            foreach($taskConfig['rewards'] as $reward)
            {
                $rewards[] = $reward;
            }
        }

        $result = [
            'open_celebra' => OpenCelebraService::getInstance()->getOpenCelebraFmtData($this->player),
            'reward'       => OpenCelebraService::getInstance()->aggregateAwards($rewards)
        ];

        $this->sendMsg( $result );
    }

}