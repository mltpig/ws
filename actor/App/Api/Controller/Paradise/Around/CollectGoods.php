<?php
namespace App\Api\Controller\Paradise\Around;
use App\Api\Controller\BaseController;
use App\Api\Table\ConfigParam;
use App\Api\Table\ConfigParadiseReward;
use App\Api\Service\Module\ParadisService;
use App\Api\Service\Module\ParadisAroundService;
use App\Task\Collected;
use EasySwoole\EasySwoole\Task\TaskManager;

//采集他人物资
class CollectGoods extends BaseController
{

    public function index()
    {
        $rid     = $this->param['rid'];
        // $around  = $this->player->getParadiseAround();

        list($_prefix,$uid,$site) = explode(':',$this->param['rid']);
        $playerData = ['uid' => $uid,'site' => $site];
        // $playerData =  ParadisAroundService::getInstance()->existsPlayer( $around,$rid );
        // if(!$playerData) return '无该邻居数据';

        //是否多次采集
        
        $posId  = $this->param['id'];
        $useNum = $this->param['num'];

        $energy    = $this->player->getParadiseEnergy();
        $workers   = $this->player->getParadiseWorker();
        $playerKey = $this->player->getData('playerKey');
        if(0 >= $energy)  return '工人体力不足';


        $goods = ParadisAroundService::getInstance()->sendAroundMessage( $playerData,'NoticeGetAdminGoodsDetail',['id' => $posId]);

        if($goods['gid'] == -1) return '该物品已过期';
        if($goods['type'] == 2) return '该物品不可采集';
        
        $visitor  = array_key_exists(VISITOR,$goods['player']) ? $goods['player'][VISITOR] : [];

        if($visitor && $visitor['uid'] != $playerKey) return '该物品已有他人采集';


        $limit  = ConfigParam::getInstance()->getFmtParam('HOMELAND_SINGLE_BOX_MOUSE_LIMIT_CONFIG');
        $reward = ConfigParadiseReward::getInstance()->getOne($goods['gid']);

        if($limit[ $reward['level']-1 ] <  $useNum) return '采集人数超过上限1';

        $widCount = $visitor ? count($visitor['wid']) : 0 ;
        // if($useNum <  $widCount) return '采集人数超过上限2';

        $diff = $useNum - $widCount;
        $freeWorker = ParadisService::getInstance()->getFreeWorker( $workers );
        if($visitor && $diff > 0  && count($freeWorker) < $diff ) return '无空闲工人';
        if(!$visitor && count($freeWorker) < $diff ) return '无空闲工人';
        
        $workerTask =  ParadisService::getInstance()->getWorkerTask( $workers );
        if(!$visitor && array_key_exists($rid,$workerTask)) return '只能采集一个';
        
        $addNum     = ParadisService::getInstance()->getWorkerStatus($energy);
        $power      = ParadisService::getInstance()->getWorkerPower($energy);
        $needTime   = ParadisService::getInstance()->getGoodsNeedTime($goods['gid'],$addNum);            

        if($visitor)
        {
            //加
            $diff = $useNum - $widCount;
            if($diff > 0 )
            {    
                $i = 0;
                foreach ($freeWorker as $key => $wid) 
                {
                    if($i >= $diff) continue;
                    $i++;
                    $visitor['wid'][] = $wid;
                    $this->player->setParadiseWorker($wid,[ 'uid' => $rid ,'id' => $posId ],'set');
                }
            }else{
                $index = abs($diff);
                while ($index > 0) 
                {
                    $wid = array_pop($visitor['wid']);
                    $this->player->setParadiseWorker($wid,[],'set');
                    $index--;
                }
            }

            $recode = ['id' => $posId,'wid' => $visitor['wid'],'uid' => $playerKey ];
            $result = ParadisAroundService::getInstance()->sendAroundMessage( $playerData,'NoticeModifyCollect',$recode);
            if(!is_array($result))
            {
                $this->player->setData('status',false);
                return $result;
            } 

        }else{
             
            $useWorker = [];
            $i = 0;
            foreach ($freeWorker as $key => $wid) 
            {
                if($i >= $useNum) continue;
                $i++;
                $useWorker[] = $wid;
                $this->player->setParadiseWorker($wid,[ 'uid' => $rid,'id' => $posId ],'set');
            }

            $recode = [
                'wid'       => $useWorker,
                'uid'       => $playerKey,
                'id'        => $posId,
                'time'      => time(),
                'len'       => 0,
                'need_time' => $needTime,
                'power'     => $power,
                'step'      => 0,//已走过路程
                'head'      => $this->player->getHead(),
                'nickname'  => $this->player->getNickname(),
                'chara_belong'  => $this->player->getCharaBelong(),
            ];

            $result = ParadisAroundService::getInstance()->sendAroundMessage( $playerData,'NoticeStartCollect',$recode);

            if(!is_array($result))
            {
                $this->player->setData('status',false);
                return $result;
            } 
        
        }

        $showData = ParadisService::getInstance()->getShowData( $this->player );
        $showData['list'] = $result;
        
        //通知被采集的人
        TaskManager::getInstance()->async(new Collected( $playerData ));
        return $showData;;
    }

}