<?php
namespace App\Api\Controller\Paradise\Notice;
use App\Api\Table\ConfigParam;
use App\Api\Table\ConfigParadiseReward;
use App\Api\Service\Module\ParadisService;
use App\Api\Controller\BaseController;

//响应邻居发送获取场景信息指令
class GetAdmintDetail extends BaseController
{

    public function index()
    {

        $data = $this->param;
        if(array_key_exists('playerKey',$data)) $this->player->setParadiseRoom( $data['playerKey'],'set' );

        $goods  = $this->player->getParadiseGoods();
        $show   = [];
        $limitConfig =  ConfigParam::getInstance()->getFmtParam('HOMELAND_SINGLE_BOX_MOUSE_LIMIT_CONFIG');
        $energy = $this->player->getParadiseEnergy();
        $addNum = ParadisService::getInstance()->getWorkerStatus( $energy );

        $playerKey = $this->player->getData('playerKey');

        foreach ($goods as $posId => $collect) 
        {
            $playerInfo = [];
            foreach ($collect['player'] as $who => $detail) 
            {
                if(!$detail) continue;
                $workerCount  = count($detail['wid']);

                $len    = div( $detail['need_time'] - ( ( time() - $detail['time'] ) + $detail['step']) , $workerCount ) + 0;
                $playerInfo[] = [
                    'who'      => $who === _SELF ? 2 : 1,
                    'rid'      => $who === _SELF ? $playerKey : $detail['uid'],
                    'status'   => $who === _SELF ? 'a' : 'g',
                    'active'   => $collect['active'] === $who  ? 1 : 0,
                    'nickname' => $detail['nickname'],
                    'head'     => $detail['head'],
                    'wCount'   => $workerCount,
                    'time'     => $len > 0 ? $len : 1,
                    'worker'   => $detail['wid'][0],
                ];
            }

            $goodsConfig =  ConfigParadiseReward::getInstance()->getOne($collect['gid']);
            $needTime = $collect['gid'] != -1 ? ParadisService::getInstance()->getGoodsNeedTime($collect['gid'],$addNum) : 0;

            $show[ $posId ] = [
                'id'           => $posId,
                'gid'          => $collect['gid'],
                'exp'          => $collect['exp'],
                'type'         => $collect['type'],
                'player'       => $playerInfo,
                'time'         => $collect['time'],
                'drift'        => $collect['drift'],
                'need_time'    => $needTime,
                'worker_limit' => $collect['gid'] != -1 ? $limitConfig[$goodsConfig['level']- 1] : 0,
            ];
        }

        return $show;
    }

}