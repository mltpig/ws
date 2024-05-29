<?php
namespace App\Api\Controller\Paradise\Around;
use App\Api\Table\ConfigParam;
use App\Api\Utils\Consts;
use App\Api\Controller\BaseController;
use App\Api\Service\Module\ParadisAroundService;

//刷新 周边福地
class Refresh extends BaseController
{

    public function index()
    {

        if($this->player->getParadiseArg(Consts::HOMELAND_TARGET_REFRESH_TIME)) return '暂时无法探寻';
        
        $timeLen = ConfigParam::getInstance()->getFmtParam('HOMELAND_TARGET_REFRESH_TIME'); 

        $this->player->setParadiseArg(Consts::HOMELAND_TARGET_REFRESH_TIME, time() + $timeLen);

        $regular   = $this->player->getParadiseAround('regular');
        $refresh   = $this->player->getParadiseAround('refresh');
        $total     = array_merge($regular,$refresh);
        $total[]   = $this->player->getData('playerKey');
        $node      = $this->player->getData('site');

        $newList = ParadisAroundService::getInstance()->getNewAroundList($node,3,$total);
        foreach ($newList as $posid => $newPlayerId) 
        {
            $this->player->setParadiseAround('refresh',$posid,$newPlayerId,'set');
        }

        $around  = $this->player->getParadiseAround();
        $workers = $this->player->getParadiseWorker();

        $time = $this->player->getParadiseArg(Consts::HOMELAND_TARGET_REFRESH_TIME);
        return [ 
            'list' => ParadisAroundService::getInstance()->getAroundListShowFmt($around,$workers),
            'remianTime' => $time ? $time - time() : 0
        ];
    }

}