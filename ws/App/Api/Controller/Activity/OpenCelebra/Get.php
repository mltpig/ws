<?php
namespace App\Api\Controller\Activity\OpenCelebra;
use App\Api\Utils\Consts;
use App\Api\Service\Module\OpenCelebraService;
use App\Api\Controller\BaseController;

class Get extends BaseController
{

    public function index()
    { 
        $result = [
            'open_celebra' => OpenCelebraService::getInstance()->getOpenCelebraFmtData($this->player),
        ];

        $this->sendMsg( $result );
    }

}