<?php
namespace App\Api\Service\Module;

use App\Api\Service\PlayerService;
use App\Api\Table\ConfigParadiseLevel;
use App\Api\Table\ConfigParadiseReward;
use App\Api\Table\ConfigGoods;
use App\Api\Table\ConfigParam;
use EasySwoole\Component\CoroutineSingleTon;
use EasySwoole\Redis\Redis;
use EasySwoole\Pool\Manager as PoolManager;
use EasySwoole\Utility\SnowFlake;
use App\Api\Utils\Keys;
use App\Api\Utils\Consts;
use App\Api\Service\Module\ParadisAroundService;
use \App\Actor\PlayerActor;
use App\Task\RouseActor;
use App\Task\Collected;
use EasySwoole\EasySwoole\Task\TaskManager;

class ParadisService
{
    use CoroutineSingleTon;

    public function dailyReset(PlayerService $playerSer):void
    {
        $maxEnergy = $this->getMaxEnergy($playerSer->getData('comrade'));

        $playerSer->setParadiseEnergy($maxEnergy);

    }

    public function weekReset(PlayerService $playerSer):void
    {
        //刷新下发七个人

        $refresh   = $playerSer->getParadiseAround('refresh');
        $refresh[] = $playerSer->getData('playerKey');
        $node      = $playerSer->getData('site');
        $newList = ParadisAroundService::getInstance()->getNewAroundList($node,3,$refresh);
        foreach ($newList as $posid => $newPlayerId) 
        {
            $playerSer->setParadiseAround('regular',$posid,$newPlayerId,'set');
        }
    }

    
    public function getRandGoods():array
    {
        $level = ConfigParadiseLevel::getInstance()->getRewardLevel();
        $gid   = ConfigParadiseReward::getInstance()->getReward($level);
        return  [ 'gid' => $gid,'player' => [],'time' => 0 ,'timerid' => 0,'type' => 1,'exp' => 0,'drift' => 0 ];
    }

    public function check(PlayerService $player,int $time,int $lastTime):void
    {
        $oldtime = $player->getParadiseArg(Consts::HOMELAND_TARGET_REFRESH_TIME);

        if($oldtime && $time >= $oldtime) $player->setParadiseArg(Consts::HOMELAND_TARGET_REFRESH_TIME,0);

        $goods  = $player->getParadiseGoods();
        $expTime = time() + ConfigParam::getInstance()->getFmtParam('HOMELAND_AUTO_REFRESH_TIME_PER');

        foreach ($goods as $posId => $goodsDetail)
        {
            //失效物品刷新
            if($goodsDetail['gid'] == -1)
            {
                if($time <  ($goodsDetail['exp']) ) continue;

                $player->setParadiseGoods($posId,'',$this->getRandGoods(),'flushall');

            }else{
                //广告刷新物品，无人采集 ， 超时删除
                if($goodsDetail['type'] == 2 && $time >= $goodsDetail['time'] && !$goodsDetail['player'])
                {
                    $player->setParadiseGoods($posId,'player',[],'set');
                    $player->setParadiseGoods($posId,'gid',-1,'set');
                    $player->setParadiseGoods($posId,'exp',$expTime,'set');
                }
            }

        }

        $this->autoRefreshGoods($player,$time,$lastTime);
    }

    public function checkParadisGoods(PlayerService $player,int $posId):void
    {
        var_dump(date('Y-m-d H:i:s'));
        $goods     = $player->getParadiseGoods();
        $energy    = $player->getParadiseEnergy();

        $expTime = time() + ConfigParam::getInstance()->getFmtParam('HOMELAND_AUTO_REFRESH_TIME_PER');

        if(!array_key_exists($posId,$goods ) ) return ;
        $collect = $goods[$posId];
        //定时器之间存在并发，导致一个物品多个定时器
        if($collect['gid'] == -1 ) return ;

        $player->setParadiseGoods($posId,'player',[],'set');
        $player->setParadiseGoods($posId,'gid',-1,'set');
        $player->setParadiseGoods($posId,'exp',$expTime,'set');
        $player->setParadiseGoods($posId,'timerid',-1,'set');
        
        $rewardConf =  ConfigParadiseReward::getInstance()->getOne($collect['gid']);

        $receiveGid = $rewardConf['reward']['gid'];
        $receiveNum = $rewardConf['reward']['num'];

        $newRewards  = [];
        foreach ($collect['player'] as $who => $detail) 
        {
            //采集成功者
            if($collect['active'] === $who )
            {
                $goodsInfo = ConfigGoods::getInstance()->getOne($rewardConf['reward']['gid']);

                if($who === _SELF)
                {
                    //释放工人
                    foreach ($detail['wid'] as  $wid) 
                    {
                        $player->setParadiseWorker($wid,[],'set');
                    }

                    $energy -= count($detail['wid']);
                    
                    $player->setParadiseEnergy( $energy );

                    array_key_exists($receiveGid,$newRewards) ? $newRewards[ $receiveGid ] += $receiveNum : $newRewards[ $receiveGid ] = $receiveNum;

                    $openid   = '';
                    $head     = $detail['head'];
                    $nickname = $detail['nickname'];
                }else{

                    // //他人采集自己物品成功
                    list($_p,$uid,$site) = explode(':',$detail['uid']);
                    $nodeData = ['uid' => $uid,'site' => $site ]; 
                    ParadisAroundService::getInstance()->sendAroundMessage($nodeData,'NoticeCollectSuccess',[
                        'admin' => $player->getData('openid'),
                        'desc' => '成功采集了'.$rewardConf['level'].'级'.$goodsInfo['name'],
                        'wid' => $detail['wid'],
                        'reward' => [ $receiveGid => $receiveNum ]
                    ]);  
                   
                    $openid   = $detail['uid'];
                    $head     = $detail['head'];
                    $nickname = $detail['nickname'];
                }
    
                $record = [ 
                    $who === _SELF ? 'a' : 'g' , 
                    $openid,
                    $head,
                    $nickname,
                    '成功采集了'.$rewardConf['level'].'级'.$goodsInfo['name'],
                    time(),
                    $detail['chara_belong'],
                ];
                
                $this->saveRecord( $player->getData('openid'),$player->getData('site'),$record);
            }else{
                if($who === _SELF)
                {
                    //释放工人
                    foreach ($detail['wid'] as  $wid) 
                    {
                        $player->setParadiseWorker($wid,[],'set');
                    }
                }else{
                    //通知他人采集结束
                    list($_p,$uid,$site) = explode(':',$detail['uid']);
                    ParadisAroundService::getInstance()->sendAroundMessage(['uid' => $uid,'site' => $site ],'NoticeCollectSuccess',[ 'wid' => $detail['wid'],'reward' => [] ]);
                }
            }


        }

        if($newRewards) $this->receiveReward($player,$newRewards);
        
        $player->setData('status',true);
        $player->saveData();
    }

    public function receiveReward(PlayerService $player,array $newReward):void
    {
        $existsReward = $player->getParadiseReward();

        foreach ($newReward as $gid => $num) 
        {
            array_key_exists($gid,$existsReward) ? $existsReward[$gid] += $num : $existsReward[$gid] = $num;
        }

        $player->setParadiseReward($existsReward);
        
    }

    public function autoRefreshGoods(PlayerService $player,int $time,int $lastTime):void
    {
        $mapList = [
            '00' => 1 , '01'  => 1, '02' => 1, '03' => 1, '04' => 1, '05' => 1, '06' => 1, '07' => 1 ,'08' => 1, '09' => 1,
            '10' => 2,'11' => 2,'12' => 2,'13' => 2,'14' => 2,'15' => 2,'16' => 2,'17' => 2,
            '18' => 3,'19' => 3,'20' => 3,'21' => 3,'22' => 3,'22' => 3,'23' => 3
        ];

        $nowHour = $mapList[date('H',$time)];


        $tag  = $player->getParadiseArg(PARADISE_AUTO_REFRESH_TIME);

        if($tag != $nowHour)
        {
            $this->refreshGoods($player);
            $player->setParadiseArg(PARADISE_AUTO_REFRESH_TIME,$nowHour);
        } 
        
        if(date('Y-m-d',$time) !== date('Y-m-d',$lastTime ) && $tag != $nowHour)
        {
            $this->refreshGoods($player);
            $player->setParadiseArg(PARADISE_AUTO_REFRESH_TIME,$nowHour);
        } 
        
    }

    public function refreshGoods(PlayerService $player):void
    {

        $goods = $player->getParadiseGoods();

        foreach ($goods as $posId => $goodsDetail)
        {
            //视频刷新物品及有人拉取的物品 不刷新
            if($goodsDetail['type'] == 2 || $goodsDetail['player']) continue;
            $player->setParadiseGoods($posId,'',$this->getRandGoods(),'flushall');
        }

    }


    public function getShowData(PlayerService $player):array
    {

        $goods     = $player->getParadiseGoods();
        $worker    = $player->getParadiseWorker();
        $reward    = $player->getParadiseReward();
        $energy    = $player->getParadiseEnergy();
        $comrade   = $player->getData('comrade');
        $adCount   = $player->getArg(PARADISE_AD_REFRES_GOODS);
        $workerNum = count($worker);
        $playerKey = $player->getData('playerKey');
        
        $workerShow = $this->getWorkerFmtShow($worker,$goods);
        return [
            'list'   => $this->getGoodsFmtShow($goods,$energy,$playerKey),
            'worker' => [
                'total' => $workerNum,
                'free'  => count($this->getFreeWorker($worker)),
                'list'  => array_values($workerShow),
            ],
            'reward' => $this->getRewardFmtShow($reward),
            'config' => [
                'refresh_cost'     => $this->getRefreshCost(),
                'refresh_ad_limit' => ConfigParam::getInstance()->getFmtParam('HOMELAND_FREE_REFRESH_TIME'),
                'refresh_ad_use'   => $adCount,
                'max_energy'       => $this->getMaxEnergy($comrade),
                'worker_status'    => ConfigParam::getInstance()->getFmtParam('HOMELAND_ENERGY_DIVIDE'),
                'worker_cost'      => $this->getWorkerBuyCost($workerNum)
            ],
            'energy'  => $energy,
        ];
    }

    public function getGoodsFmtShow(array $goods,int $energy,string $playerKey):array
    {
        $show = [];

        $limitConfig =  ConfigParam::getInstance()->getFmtParam('HOMELAND_SINGLE_BOX_MOUSE_LIMIT_CONFIG');
        $addNum = $this->getWorkerStatus( $energy );

        foreach ($goods as $posId => $collect)
        {
            $worker = [];
            $drift  = 0;
            foreach ($collect['player'] as $who => $detail) 
            {
                $worker[$who] = $detail;
                if(!$detail) continue;

                $wCount = count($detail['wid']);

                $active = $collect['active'] === $who ? 1 : 0;

                $collectRemianTime = $detail['need_time'] - ( ( time() - $detail['time']  ) * $wCount + $detail['step']);

                $worker[$who] = [
                    'who'      => $who == _SELF ? 2 : 1,
                    'rid'      => $who == _SELF ? $playerKey : $detail['uid'],
                    'active'   => $active,
                    'status'   => $who === _SELF ? 'a' : 'g',
                    'head'     => $detail['head'],
                    'nickname' => $detail['nickname'],
                    'wCount'   => $wCount,
                    'worker'   => $detail['wid'][0],
                    'time'     => $collectRemianTime > 0 ? div($collectRemianTime,$wCount) + 0 : 1,
                ];

                if($collect['active'] === _SELF)  $drift = $collectRemianTime - $detail['need_time'];
                if($collect['active'] === VISITOR) $drift = $detail['need_time'] - $collectRemianTime;
            }

            $goodsConfig =  ConfigParadiseReward::getInstance()->getOne($collect['gid']);

            $needTime = $collect['gid'] != -1 ? $this->getGoodsNeedTime($collect['gid'],$addNum) : 0;
            $show[] = [
                'id'           => $posId,
                'gid'          => $collect['gid'],
                'exp'          => $collect['gid'] > 0 ? 0 : $collect['exp'] - time(),
                'type'         => $collect['type'],
                'player'       => array_values($worker)  ,
                'time'         => $collect['time'] > 0 ? $collect['time'] - time() : 0,
                'rid'          => '',
                'drift'        => $drift,
                'need_time'    => $needTime,
                'worker_limit' => $collect['gid'] != -1 ? $limitConfig[$goodsConfig['level']- 1] : 0,
            ];

        }
        
        return $show;

    }


    public function getWorkerGoods(array $workers):array
    {
        //好友家一次只能一次
        $list = [];
        foreach ($workers as $wid => $value) 
        {
            if(!$value) continue;
            $list[ $value['uid'].$value['id'] ] = ['wid' => $wid ,'uid' => $value['uid'],'posId' => $value['id'] ];
        }

        return $list;
    }

    public function getWorkerFmtShow(array $workers,array $goods):array
    {
        $show = [];
        $workerTasks  = $this->getWorkerGoods($workers);
        foreach ($workerTasks as $value)
        {
            $wid   = $value['wid'];
            $uid   = $value['uid'];
            $posId = $value['posId'];
            //采集自己家
            if($uid == _SELF)
            {
                $collect = $goods[$posId];
                $gid     = $goods[$posId]['gid'];
                $playerInfo = [];
                foreach ($collect['player'] as $who => $detail) 
                {
                    $active       = $collect['active'] === $who ? 1 : 0;
                    $wCount       = count($detail['wid']);
                    $remianTime   = $detail['need_time'] - ( ( time() - $detail['time']  ) * $wCount + $detail['step']);
                    $playerInfo[] = [
                        'active'    =>  $active,
                        'who'       =>  $who === _SELF ? 2 : 1,
                        'status'    =>  $who === _SELF ? 'a' : 'g',
                        'time'      =>  div($remianTime,$wCount)+0,
                        'wCount'    =>  $wCount,
                        'head'      =>  $detail['head'],
                        'nickname'  =>  $detail['nickname'],
                    ];
                }
                
            }else{
                //采集其他人
                list($_p,$uid,$site) = explode(':',$uid);
                //自己的数据及可能存在的福地主人数据
                $result = ParadisAroundService::getInstance()->sendAroundMessage(['uid' => $uid,'site' => $site],'NoticeGetWorkerTask',[ 'posId' => $posId]);
                $gid        = $result['gid'];
                $playerInfo = $result['playerInfo'];
            }

            $show[] = [
                'id'     => $wid,
                'gid'    => $gid,
                'player' => $playerInfo,
            ];

        }

        return $show;

    }

    public function getRefreshCost():array
    {
        $refreshCost = ConfigParam::getInstance()->getFmtParam('HOMELAND_PAY_REFRESH_COST');
        $refreshCost['type'] = GOODS_TYPE_1;

        return $refreshCost;
    }

    public function getWorkerBuyCost(int $workerNum):array
    {
        $costConfig = ConfigParam::getInstance()->getFmtParam('HOMELAND_WORKER_COST');

        $buyCost    = $workerNum - 1 >= count($costConfig) ? [] : $costConfig[$workerNum - 1];
        
        if($buyCost) $buyCost['type'] =  GOODS_TYPE_1;

        return $buyCost;
    }

    public function getRewardFmtShow(array $reward):array
    {
        $rewardFmt = [];
        foreach ($reward as $gid => $num) 
        {
            $goodsInfo = ConfigGoods::getInstance()->getOne($gid);
            
            $rewardFmt[] = [ 'type' => $goodsInfo['type'], 'gid'  => $gid, 'num' => $num ];
        }

        return $rewardFmt;
    }
    
    public function getWorkerTask(array $worker):array
    {
        //好友家一次只能一次
        $list = [];
        foreach ($worker as $wid => $value) 
        {
            if(!$value) continue;
            $list[ $value['uid'] ] = ['wid' => $wid ,'posId' => $value['id'] ];
        }

        return $list;
    }

    public function getFreeWorker(array $worker):array
    {
        $workerId = [];
        foreach ($worker as $wid => $value) 
        {
            if($value) continue;
            $workerId[] = $wid;
        }
        return $workerId;
    }

    public function getGoodsNeedTime(int $gid,float $addNum):int
    {
        $config = ConfigParadiseReward::getInstance()->getOne($gid);
        return mul($config['time_param'],$addNum);
    }

    public function getWorkerStatus(int $energy):float
    {
        // HOMELAND_ENERGY_DIVIDE	  100|50|25|15
        // HOMELAND_ENERGY_SPEED	  20|40|80|4000
        // HOMELAND_ENERGY_COPE_SPEED 500|300|100|10
        $target = ConfigParam::getInstance()->getFmtParam('HOMELAND_ENERGY_SPEED');

        $power = $this->getStatusConfig($energy,$target );

        return $power ? $power : 8000;
    }

    public function getWorkerPower(int $energy):int
    {
        $target = ConfigParam::getInstance()->getFmtParam('HOMELAND_ENERGY_COPE_SPEED');

        $power = $this->getStatusConfig($energy,$target );
        
        return $power ? $power : 10;
    }

    public function getStatusConfig(int $energy, array $target )
    {
        $config = ConfigParam::getInstance()->getFmtParam('HOMELAND_ENERGY_DIVIDE');
        $count  = count($config) -1 ;
        $list   = [];

        foreach ($config as $key => $value) 
        {
            //正常设置上限 20000
            if(!$key)  $list[] = ['begin' => 20000,'end' => $config[ $key+1 ]+1 ];
            if($key > 0 && $key < $count)  $list[] = ['begin' => $value,'end' => $config[ $key+1 ]+1 ];
            if($key == $count) $list[] = ['begin' => $value,'end' => 0 ];
        }
        $num = 0;
        foreach ($list as $index => $detail)
        {
            if($energy < $detail['begin'] && $energy > $detail['end'] || $energy == $detail['begin'] || $energy == $detail['end'] ) $num = $target[$index];
        }

        return $num; 
    }

    public function getMaxEnergy(array $comrades):int
    {
        $paradiseNum = ConfigParam::getInstance()->getFmtParam('HOMELAND_ENERGY_DIVIDE')[0];
        $comradeAdd  = ComradeService::getInstance()->getLvStageByTalent($comrades,60003);
        
        return $paradiseNum + $comradeAdd;
    }


    public function getActiveStatus(array $self,array $visitor):array
    {

        if($self && !$visitor)
        {
            $selfWorker = count($self['wid']);
            $active     = _SELF;
            $len        = div( $self['need_time'] - $self['step'] , $selfWorker );
        }elseif(!$self && $visitor){
            $visitorWorker = count($visitor['wid']);
            $active        = VISITOR;
            $len           = div( $visitor['need_time'] - $visitor['step'] , $visitorWorker);
        }elseif($self && $visitor){            
            $selfWorker    = count($self['wid']);
            $selfPower     = $selfWorker * $self['power']; 
            $visitorWorker = count($visitor['wid']);
            $visitorPower  = $visitorWorker * $visitor['power']; 
            if($selfPower >= $visitorPower)
            {
                $active = _SELF;
                $len    = div( $self['need_time'] - $self['step'] , $selfWorker);
            }else{
                $active = VISITOR;
                $len    = div( $visitor['need_time'] - $visitor['step'] , $visitorWorker );
            }
        }

        return [ $active , $len ];
    }

    public function saveRecord(string $playerid,int $site,array $content):void
    {
        $rid      = strval( SnowFlake::make(rand(0,31),rand(0,127)) );
        $cacheKey = Keys::getInstance()->getFudiRecordKey($playerid,$site);
        PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($cacheKey,$rid,$content){
            return $redis->hSet($cacheKey,$rid,json_encode($content));
        });
    }

    public function resetTimer(PlayerActor $PlayerActor,PlayerService $PlayerService ):void
    {
        $PlayerService->before();

        $goods  = $PlayerService->getParadiseGoods();

        foreach ($goods as $posId => $collect)
        {

            if(!$collect['player']) continue;

            $detail = $collect['player'][$collect['active']];

            $len = div( $detail['need_time'] -  ( ( time() -  $detail['time'] ) + $detail['step']) , count($detail['wid']) );
            //采集完及时长小于5秒 都统一为 5秒
            $len = $len > 0 ? $len : 1;
            $timerid = $PlayerActor->after($len * 1000,function() use($PlayerService,$posId){
                ParadisService::getInstance()->checkParadisGoods($PlayerService,$posId);
            });

            $PlayerService->setParadiseGoods($posId,'timerid',$timerid,'set');

            if($collect['active'] === _SELF) continue;
            TaskManager::getInstance()->async(new RouseActor($collect['player'][VISITOR]['uid']));
        }

        $PlayerService->saveData();


        $worker  = $this->getWorkerTask( $PlayerService->getParadiseWorker() );
        foreach ($worker as $uid => $collect) 
        {
            if($uid === _SELF) continue;
            TaskManager::getInstance()->async(new RouseActor($uid));
        }

    }

    public function dingRoom(array $room):void
    {
        //当前所有在房间的UID都通知
        if(!$room) return ;

        foreach ($room as $key => $value) 
        {
            // //他人采集自己物品成功
            list($_p,$uid,$site) = explode(':',$key);
            
            TaskManager::getInstance()->async(new Collected( ['uid' => $uid,'site' => $site ] ));
        }
    }
}
