<?php
namespace App\Api\Controller\Paradise\Around;
use App\Api\Service\Module\ParadisService;
use App\Api\Service\Module\ParadisAroundService;
use App\Api\Controller\BaseController;
use App\Api\Table\ConfigParam;

//获取他人福地物品状态
class Info extends BaseController
{

    public function index()
    {
        $goods     = $this->player->getParadiseGoods();
        $worker    = $this->player->getParadiseWorker();
        $energy    = $this->player->getParadiseEnergy();
        $comrade   = $this->player->getData('comrade');
        $adCount   = $this->player->getArg(PARADISE_AD_REFRES_GOODS);
        $workerNum = count($worker);
        

        $around  = $this->player->getParadiseAround();
        list($_prefix,$uid,$site) = explode(':',$this->param['rid']);
        $playerData = ['uid' => $uid,'site' => $site];
        // if($this->param['scene'] != 'goto')
        // {
        //     $playerData =  ParadisAroundService::getInstance()->existsPlayer( $around,$this->param['rid'] );
        //     if(!$playerData) return '无该邻居数据';
        // }else{
        //     list($_prefix,$uid,$site) = explode(':',$this->param['rid']);
        //     $playerData = ['uid' => $uid,'site' => $site];
        // }

        $goods     = $this->player->getParadiseGoods();
        $worker    = $this->player->getParadiseWorker();

        $list = ParadisAroundService::getInstance()->sendAroundMessage($playerData,'NoticeGetAdminDetail',[ 'playerKey' => $this->player->getData('playerKey') ]);

        foreach ($list as $key => $value) 
        {
            $list[$key]['rid'] = $this->param['rid'];
        }

        $this->player->setParadiseTmp('rid',$this->param['rid']);

        $reward = $this->player->getParadiseReward();
        $this->player->setParadiseReward([]);

        return [
            'list'   => $list,
            'isopen' => $this->param['isopen'],
            'worker' => [
                'total' => count($worker),
                'free'  => count(ParadisService::getInstance()->getFreeWorker($worker)),
                'list'  => array_values( ParadisService::getInstance()->getWorkerFmtShow($worker,$goods) ),
            ],
            'config' => [
                'refresh_cost'     => ParadisService::getInstance()->getRefreshCost(),
                'refresh_ad_limit' => ConfigParam::getInstance()->getFmtParam('HOMELAND_FREE_REFRESH_TIME'),
                'refresh_ad_use'   => $adCount,
                'max_energy'       => ParadisService::getInstance()->getMaxEnergy($comrade),
                'worker_status'    => ConfigParam::getInstance()->getFmtParam('HOMELAND_ENERGY_DIVIDE'),
                'worker_cost'      => ParadisService::getInstance()->getWorkerBuyCost($workerNum)
            ],
            'energy'  => $energy,
            'reward'  => ParadisService::getInstance()->getRewardFmtShow($reward),
        ];

    }

}