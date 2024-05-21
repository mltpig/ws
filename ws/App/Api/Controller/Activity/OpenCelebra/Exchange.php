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
        $param = $this->param;
        $config = ConfigShop::getInstance()->getOne($param['id']);

        $result   = '兑换上限已上限';
        if($config['buy_limit'] > $this->player->getArg($param['id']) )
        {
            $reward  = $goodsList = $config['reward'];
            $reward[] = ['gid' => $config['price']['gid'], 'num' => -$config['price']['num'], 'type' => GOODS_TYPE_1 ];
            
            $this->player->goodsBridge($reward,'兑换开服庆典');

            $this->player->setArg($param['id'],1,'add');

            $result = [
                'open_celebra' => OpenCelebraService::getInstance()->getOpenCelebraFmtData($this->player),
                'reward'   => $goodsList,
                'remain'   => $this->player->getGoods($config['price']['gid']),
            ];
        }
        $this->sendMsg( $result );
    }

}