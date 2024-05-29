<?php
namespace App\Api\Controller\Paradise\Around;
use App\Api\Utils\Consts;
use App\Api\Service\Module\ParadisAroundService;
use App\Api\Controller\BaseController;

//获取周围福地
class Get extends BaseController
{

    public function index()
    {

        $around  = $this->player->getParadiseAround();
        $workers = $this->player->getParadiseWorker();

        $time = $this->player->getParadiseArg(Consts::HOMELAND_TARGET_REFRESH_TIME);

        return  [
            'list'       => ParadisAroundService::getInstance()->getAroundListShowFmt($around,$workers),
            'remianTime' => $time ? $time - time() : 0
        ];
    }

}