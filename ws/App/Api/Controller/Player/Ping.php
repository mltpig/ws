<?php
namespace App\Api\Controller\Player;
use App\Api\Service\TreeService;
use App\Api\Service\RedPointService;
use App\Api\Service\TaskService;
use App\Api\Service\ActivityService;
use App\Api\Controller\BaseController;
use App\Api\Table\ConfigParam;
use App\Api\Utils\Consts;
use App\Api\Service\Module\XianYuanService;
use App\Api\Service\Module\ShangGuService;

class Ping extends BaseController
{

    public function index()
    {
        $this->sendMsg( [
            'tree'         => TreeService::getInstance()->getShowTree($this->player),
            'goods'        => $this->player->getGoodsInfo(),
            'arg'          => $this->player->getArgInfo(),
            'task'  	   => TaskService::getInstance()->getShowTask(  $this->player->getData('task') ),
            'daily_reward' => ActivityService::getInstance()->getDailyRewardFmt($this->player),
            'redPoint'     => RedPointService::getInstance()->getRedPoints($this->player),
			'activity' 	   => [
				'dailyReward'   => ConfigParam::getInstance()->getFmtParam('AD_REWARD'),
				'firstRecharge' => ActivityService::getInstance()->getFirstRechargeConfig($this->player),
				'signIn' 		=> ActivityService::getInstance()->getSignInState($this->player),
				'newYear' => [
					'begin' => strtotime(Consts::ACTIVITY_NEW_YEAR_BEGIN),
					'end' 	=> strtotime(Consts::ACTIVITY_NEW_YEAR_END),
				],
				'shanggu' => ShangGuService::getInstance()->getShowStatus(),
				'zhengji' => XianYuanService::getInstance()->getShowStatus(),
			],
        ] );
    }

}