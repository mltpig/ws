<?php
namespace App\Api\Controller\Paradise\Notice;
use App\Api\Controller\BaseController;
use App\Api\Service\Module\ParadisService;

//响应邻居开始采集指令
class StartCollect extends BaseController
{

    public function index()
    {
        $param = $this->param;
        $posId = $this->param['id'];
        // power
        // $param = [
        // 'wid'       => $useWorker,
        // 'uid'       => $playerKey,
        // 'id'        => $posId,
        // 'time'      => time(),
        // 'len'       => 0,
        // 'need_time' => $needTime,
        // 'step'      => 0,//已走过路程
        // 'head'      => $this->player->getHead(),
        // 'nickname'  => $this->player->getNickname(),
        // ];

        $goods  = $this->player->getParadiseGoods($posId);

        if($goods['gid'] == -1) return '该物品已过期';
        
        $collect  = $goods['player'];
        
        $visitor  = array_key_exists(VISITOR,$collect) ? $collect[VISITOR] : [];
        if($visitor) return '该物品已有人采集中';
        
        $self  = array_key_exists(_SELF,$collect) ? $collect[_SELF] : [];

        $collect[ VISITOR ] = $param;

        if($self)
        {
            $visitor = $visitor ? $visitor : [ 'need_time' => $param['need_time'],'step' => 0,'wid' => $param['wid'],'power' => $param['power'] ];;
            list( $active , $len ) = ParadisService::getInstance()->getActiveStatus($self,$visitor);
            //游客比主人搞
            if($active === VISITOR)
            {
                $this->playerActor->deleteTick($goods['timerid']);
                $player  = $this->player;
                $timerid = $this->playerActor->after($len * 1000,function() use($player,$posId){
                    ParadisService::getInstance()->checkParadisGoods($player,$posId);
                });

                $this->player->setParadiseGoods($posId,'active',VISITOR,'set');
                $this->player->setParadiseGoods($posId,'timerid',$timerid,'set');
                
                //暂停时间
                $self['step'] += ( time() - $self['time'] ) * count($self['wid']);
                $self['time'] = time();
                $collect[ _SELF ] = $self;

            }
            
        }else{

            $len = div( $param['need_time'] , count($param['wid']));

            $player  = $this->player;
            $timerid = $this->playerActor->after($len * 1000,function() use($player,$posId){
                ParadisService::getInstance()->checkParadisGoods($player,$posId);
            });

            $this->player->setParadiseGoods($posId,'active',VISITOR,'set');
            $this->player->setParadiseGoods($posId,'timerid',$timerid,'set');
        }
        
        $this->player->setParadiseGoods($posId,'player',$collect,'set');

        $goods     = $this->player->getParadiseGoods();
        $energy    = $this->player->getParadiseEnergy();
        $playerKey = $this->player->getData('playerKey');
        $list      = ParadisService::getInstance()->getGoodsFmtShow($goods,$energy,$playerKey);
        foreach ($list as $posId => $value) 
        {
            $list[$posId]['rid'] = $this->player->getData('playerKey');
        }

        //
        ParadisService::getInstance()->dingRoom($this->player->getParadiseRoom());

        return $list;
    }

}