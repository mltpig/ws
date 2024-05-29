<?php
namespace App\Api\Controller\Paradise\Notice;
use App\Api\Controller\BaseController;
use App\Api\Service\Module\ParadisService;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\Log\LoggerInterface;

//响应邻居撤回工人
class RecallWorker extends BaseController
{

    public function index()
    {

        $posId = $this->param['id'];
        // $param = [ 'id'  => $posId ];

        $goods  = $this->player->getParadiseGoods($posId);

        if($goods['gid'] == -1)
        {
            Logger::getInstance()->log('响应访客召回工人:  该物品已过期 === param: '.json_encode($this->param,JSON_UNESCAPED_UNICODE) );
            return [];
        } 
        
        $collect  = $goods['player'];
        
        $visitor  = array_key_exists(VISITOR,$collect) ? $collect[VISITOR] : [];
        if(!$visitor)
        {
            Logger::getInstance()->log('响应访客召回工人:  未有采集=== param: '.json_encode($this->param,JSON_UNESCAPED_UNICODE) );
            return [];
        } 
        
        $self  = array_key_exists(_SELF,$collect) ? $collect[_SELF] : [];

        unset($collect[ VISITOR ]);
        $this->playerActor->deleteTick($goods['timerid']);

        if($self)
        {
            list( $active , $len ) = ParadisService::getInstance()->getActiveStatus($self,[]);
            
            //暂停时间
            $self['time']     = time();
            $collect[ _SELF ] = $self;
            
            $player  = $this->player;
            $timerid = $this->playerActor->after($len * 1000,function() use($player,$posId){
                ParadisService::getInstance()->checkParadisGoods($player,$posId);
            });

            $this->player->setParadiseGoods($posId,'active',$active,'set');
            $this->player->setParadiseGoods($posId,'timerid',$timerid,'set');
            
        }else{
            $this->player->setParadiseGoods($posId,'active','','set');
        }
        
        $this->player->setParadiseGoods($posId,'player',$collect,'set');

        return [];
    }

}