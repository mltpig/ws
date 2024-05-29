<?php
namespace App\Api\Controller\Paradise\Self;
use App\Api\Controller\BaseController;
use App\Api\Service\Module\ParadisService;

//工人雇佣
class WorkerAdd extends BaseController
{

    public function index()
    {
      
        $workerLen = count($this->player->getParadiseWorker());

        $this->player->setParadiseWorker($workerLen+1,[]);
        
        return ParadisService::getInstance()->getShowData( $this->player );
    }

}