<?php
namespace App\Api\Controller\Paradise\Notice;
use App\Api\Controller\BaseController;
use App\Api\Service\Module\ParadisService;

//响应采集工人数
class ModifyCollect extends BaseController
{

    public function index()
    {

        $uid   = $this->param['uid'];
        $wid   = $this->param['wid'];
        $posId = $this->param['id'];

        $goods  = $this->player->getParadiseGoods($posId);

        if($goods['gid'] == -1) return '该物品已过期';
        
        $collect = $goods['player'];

        $visitor  = array_key_exists(VISITOR,$collect) ? $collect[VISITOR] : [];
        
        if(!$visitor) return '物品无人采集';
        if($visitor && $visitor['uid'] != $uid) return '不是本人采集';

        $self    = array_key_exists(_SELF,$collect) ? $collect[_SELF] : [];

        $visitorWorkerCount = count($visitor['wid']);

        $visitor['wid'] = $wid;
        if($self)
        {
            list( $active , $len ) = ParadisService::getInstance()->getActiveStatus($self,$visitor);

            $this->playerActor->deleteTick($goods['timerid']);
            $player  = $this->player;
            $timerid = $this->playerActor->after($len * 1000,function() use($player,$posId){
                ParadisService::getInstance()->checkParadisGoods($player,$posId);
            });

            $this->player->setParadiseGoods($posId,'active',$active,'set');
            $this->player->setParadiseGoods($posId,'timerid',$timerid,'set');

            //游客比主人高
            if($active === VISITOR)
            {
                $visitor['step'] += ( time() - $visitor['time'] ) * count($visitor['wid']);
                $visitor['time'] = time();
            }else{
                $self['step'] += ( time() - $self['time'] ) * count($self['wid']);
                $self['time'] = time();
                $collect[ _SELF ] = $self;
            }

        }else{
            
            $this->playerActor->deleteTick($goods['timerid']);

            $visitor['step'] += ( time() - $visitor['time'] ) * $visitorWorkerCount;
            $visitor['time'] = time();

            $len    = div( $visitor['need_time'] - $visitor['step'] , count($wid));
            $player  = $this->player;
            $timerid = $this->playerActor->after($len * 1000,function() use($player,$posId){
                ParadisService::getInstance()->checkParadisGoods($player,$posId);
            });

            $this->player->setParadiseGoods($posId,'timerid',$timerid,'set');
        }
        
        $collect[VISITOR] = $visitor;
        $this->player->setParadiseGoods($posId,'player',$collect,'set');

        $goods  = $this->player->getParadiseGoods();
        $energy = $this->player->getParadiseEnergy();
        $playerKey = $this->player->getData('playerKey');
        $list   = ParadisService::getInstance()->getGoodsFmtShow($goods,$energy,$playerKey);
        foreach ($list as $posId => $value) 
        {
            $list[$posId]['rid'] = $this->player->getData('playerKey');
        }
        
        ParadisService::getInstance()->dingRoom($this->player->getParadiseRoom());

        return $list;
    }

}