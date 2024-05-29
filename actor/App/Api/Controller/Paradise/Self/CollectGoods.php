<?php
namespace App\Api\Controller\Paradise\Self;
use App\Api\Controller\BaseController;
use App\Api\Table\ConfigParam;
use App\Api\Table\ConfigParadiseReward;
use App\Api\Service\Module\ParadisService;

//采集自己物资
class CollectGoods extends BaseController
{

    public function index()
    {
        $energy = $this->player->getParadiseEnergy();
        if( 0 >= $energy ) return '工人体力不足';

        $posid  = $this->param['id'];
        $useNum = $this->param['num'];
        
        $goods   = $this->player->getParadiseGoods($posid);
        $workers = $this->player->getParadiseWorker();
        
        if($goods['gid'] == -1) return '该物品已过期';
        
        $result = '采集人数超过上限';
        $limit  = ConfigParam::getInstance()->getFmtParam('HOMELAND_SINGLE_BOX_MOUSE_LIMIT_CONFIG');
        $reward = ConfigParadiseReward::getInstance()->getOne($goods['gid']);

        if($limit[ $reward['level']-1 ] >= $useNum)
        {
            $addNum     = ParadisService::getInstance()->getWorkerStatus($energy);
            $power      = ParadisService::getInstance()->getWorkerPower($energy);

            $needTime   = ParadisService::getInstance()->getGoodsNeedTime($goods['gid'],$addNum);            
            $freeWorker = ParadisService::getInstance()->getFreeWorker( $workers );

            $collect = $goods['player'];

            $self    = array_key_exists(_SELF,$collect) ? $collect[_SELF] : [];
            $visitor = array_key_exists(VISITOR,$collect) ? $collect[VISITOR] : [];
            //自己是否有在采集
            if($self)
            {
                $result = '正在采集中';
                //加 减
                $widCount = count($self['wid']);
                if($useNum != $widCount)
                {
                    //加
                    $diff = $useNum - $widCount;
                    if($diff > 0 )
                    {    
                        $result = '无空闲工人';
                        if(count($freeWorker) >=  $diff)
                        {
                            $i = 0;
                            foreach ($freeWorker as $key => $wid) 
                            {
                                if($i >= $diff) continue;
                                $i++;
                                $self['wid'][] = $wid;
                                $this->player->setParadiseWorker($wid,[ 'uid' => _SELF,'id' => $posid ],'set');
                            }
                        }
                    }else{
                        $index = abs($diff);
                        while ($index > 0) 
                        {
                            $wid = array_pop($self['wid']);
                            $this->player->setParadiseWorker($wid,[],'set');
                            $index--;
                        }
                    }

                    $self['step'] += ( time() - $self['time'] ) * $widCount;
                    $self['time'] = time();

                    //清除旧定时器
                    $this->playerActor->deleteTick($goods['timerid']);

                    //如果有访客，计算两者力量对比
                    list( $active , $len ) = ParadisService::getInstance()->getActiveStatus($self,$visitor);
                    
                    //添加计时器n秒后进行清算
                    $player = $this->player;
                    $newTimerid = $this->playerActor->after($len * 1000,function() use($player,$posid){
                        ParadisService::getInstance()->checkParadisGoods($player,$posid);
                    });

                    $collect['self'] = $self;

                    $this->player->setParadiseGoods($posid,'timerid',$newTimerid,'set');
                    $this->player->setParadiseGoods($posid,'active',$active,'set');
                    $this->player->setParadiseGoods($posid,'player',$collect,'set');

                    $result = ParadisService::getInstance()->getShowData( $this->player );
                }

            }else{
                
                if($useNum > count($freeWorker) ) return '无空闲工人';
                    
                $useWorker = [];
                $i = 0;
                foreach ($freeWorker as $key => $wid) 
                {
                    if($i >= $useNum) continue;
                    $i++;
                    $useWorker[] = $wid;
                    $this->player->setParadiseWorker($wid,[ 'uid' => _SELF,'id' => $posid ],'set');
                }

                //清除旧定时器
                if($goods['timerid'] != -1 ) $this->playerActor->deleteTick($goods['timerid']);

                //如果有访客，计算两者力量对比
                $tmp = [ 'need_time' => $needTime,'step' => 0,'wid' => $useWorker,'power' => $power ];
                list( $active , $len ) = ParadisService::getInstance()->getActiveStatus($tmp,$visitor);

                $collect[ _SELF ] = [
                    'wid'           => $useWorker,
                    'uid'           => _SELF,
                    'id'            => $posid,
                    'time'          => time(),
                    'len'           => 0,
                    'need_time'     => $needTime,
                    'step'          => 0,//已走过路程
                    'power'         => $power,
                    'head'          => $this->player->getHead(),
                    'nickname'      => $this->player->getNickname(),
                    'chara_belong'  => $this->player->getCharaBelong(),
                ];

                //添加计时器n秒后进行清算
                $player  = $this->player;
                $timerid = $this->playerActor->after($len * 1000,function() use($player,$posid){
                    ParadisService::getInstance()->checkParadisGoods($player,$posid);
                });

                $this->player->setParadiseGoods($posid,'active',$active,'set');
                $this->player->setParadiseGoods($posid,'timerid',$timerid,'set');
                $this->player->setParadiseGoods($posid,'player',$collect,'set');
                
                $result = ParadisService::getInstance()->getShowData( $this->player );
            }
        }

        return $result;;
    }

}