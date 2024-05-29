<?php
namespace App\Api\Controller\Paradise;
use App\Api\Controller\BaseController;
use App\Api\Service\Module\ParadisService;
use App\Api\Service\Module\ParadisAroundService;
use App\Task\Collected;
use EasySwoole\EasySwoole\Task\TaskManager;

//管理界面根据工人ID召回
class RecallWorker extends BaseController
{

    public function index()
    {
        $id      = $this->param['id'];
        $rid     = $this->param['rid'];
        $workers = $this->player->getParadiseWorker();
        
        if(!array_key_exists($id,$workers) || !$workers[$id]) return '该工人未雇佣/采集';
        
        $collect = $workers[$id];
        //撤销自己采集人员
        if($collect['uid'] === _SELF)
        {
            $goods  = $this->player->getParadiseGoods($collect['id']);

            if($goods['gid'] == -1)  return '该物品已过期';
            
            if(!array_key_exists(_SELF,$goods['player'])) return '当前未采集';
            $posid   = $collect['id']; 
            $self    = $goods['player'][_SELF];
            $visitor = array_key_exists(VISITOR,$goods['player']) ? $goods['player'][VISITOR] : [];

            foreach ($self['wid'] as $wid) 
            {
                $this->player->setParadiseWorker($wid,[],'set');
            }
            
            unset($goods['player'][_SELF]);
            
            $this->player->setParadiseGoods($collect['id'],'player',$goods['player'],'set');

            if($goods['active'] === _SELF)
            {
                //清除旧定时器
                $this->playerActor->deleteTick($goods['timerid']);
                //增加新定时器
                //更改主动之人
                $timerid = -1;
                if($visitor)
                {
                    $len = div( time() - $visitor['time']  , count($visitor['wid']) );
                    $player  = $this->player;
                    $timerid = $this->playerActor->after($len * 1000,function() use($player,$posid){
                        ParadisService::getInstance()->checkParadisGoods($player,$posid);
                    });

                    $this->player->setParadiseGoods($posid,'active',VISITOR,'set');
                }

                $this->player->setParadiseGoods($posid,'timerid',$timerid,'set');
            }
            
        }else{

            list($_p,$uid,$site) = explode(':',$collect['uid']);
            $playerData = ['uid' => $uid,'site'=> $site];
            ParadisAroundService::getInstance()->sendAroundMessage(['uid' => $uid,'site'=> $site],'NoticeRecallWorker',['id' => $collect['id'] ]);
            //不看结果。直接释放对应工人
            foreach ($workers as $wid => $value) 
            {
                if(!$value || $value['uid'] != $collect['uid']) continue;
                $this->player->setParadiseWorker($wid,[],'set');
            }

            TaskManager::getInstance()->async(new Collected( $playerData ));

        }


        $result = ParadisService::getInstance()->getShowData( $this->player );
        if($rid && $this->player->getParadiseTmp('rid') === $this->param['rid'] )
        {
            list($_p,$uid,$site) = explode(':',$rid);
            $list = ParadisAroundService::getInstance()->sendAroundMessage(['uid' => $uid,'site'=> $site],'NoticeGetAdminDetail',[]);
            foreach ($list as $key => $value) 
            {
                $list[$key]['rid'] = $this->param['rid'];
            }
            $result['list'] = $list;
        }
        $result['rid'] = $this->param['rid'];
        $this->player->setParadiseReward([]);
        return $result;
    }

}