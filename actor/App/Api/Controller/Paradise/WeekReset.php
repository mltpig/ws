<?php
namespace App\Api\Controller\Paradise;
use App\Api\Controller\BaseController;
use App\Api\Service\Module\ParadisService;

//采集自己物资
class WeekReset extends BaseController
{

    public function index()
    {
        ParadisService::getInstance()->weekReset( $this->player );

        return [];
    }

}