<?php
namespace App\Api\Controller\Activity\OpenCelebra;
use App\Api\Utils\Consts;
use App\Api\Table\ConfigParam;
use App\Api\Table\ConfigShop;
use App\Api\Service\Module\OpenCelebraService;
use App\Api\Controller\BaseController;

class Exchange extends BaseController
{

    public function index()
    {   
        $param  = $this->param;
        $number = $param['num'];
        $config = ConfigShop::getInstance()->getOne($param['id']);

        $costGid    = $config['price']['gid'];
        $costTotal  = $config['price']['num'] * $number;

        $result = '积分不足';
        if($this->player->getGoods($costGid) >= $costTotal)
        {
            if($config['buy_limit'] == -1 )
            {
                $reward   = [];
                foreach ($config['reward'] as $detail) 
                {
                    $detail['num'] *= $number;
                    $reward[] = $detail;
                }

                $costList = [ [ 'type' => GOODS_TYPE_1,'gid' => $costGid,'num' => -$costTotal ] ];
                $this->player->goodsBridge(array_merge($reward,$costList),'兑换开服庆典');
    
                $this->player->setArg($param['id'],1,'add');
                $result = [
                    'open_celebra'  => OpenCelebraService::getInstance()->getOpenCelebraFmtData($this->player),
                    'reward'        => $reward,
                    'remain'        => $this->player->getGoods($costGid),
                ];
            }else{
                $result = '选择数量超过限制';
                if($config['buy_limit'] >= $number )
                {
                    $result   = '兑换已达上限';
                    $nowNum   = $this->player->getArg($param['id']);
                    if($config['buy_limit'] > $nowNum )
                    {
                        $result   = '总计数量超过限制,请重新选择';
                        $totalNum = $nowNum + $number;
                        if($config['buy_limit'] >= $totalNum)
                        {
                            $this->player->setArg($param['id'],$number,'add');
    
                            $reward   = [];
                            foreach ($config['reward'] as $detail) 
                            {
                                $detail['num'] *= $number;
                                $reward[] = $detail;
                            }
    
                            $costList = [ [ 'type' => GOODS_TYPE_1,'gid' => $costGid,'num' => -$costTotal ] ];
                            $this->player->goodsBridge(array_merge($reward,$costList),'兑换开服庆典');
    
                            $result = [
                                'open_celebra'  => OpenCelebraService::getInstance()->getOpenCelebraFmtData($this->player),
                                'reward'        => $reward,
                                'remain'        => $this->player->getGoods($costGid),
                            ];
                        }
                    }
                }
            }
        }
        $this->sendMsg( $result );
    }

}