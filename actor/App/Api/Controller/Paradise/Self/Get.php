<?php
namespace App\Api\Controller\Paradise\Self;
use App\Api\Controller\BaseController;
use App\Api\Service\Module\ParadisService;

//获取当前福地物品状态
class Get extends BaseController
{

    public function index()
    {

        $result = ParadisService::getInstance()->getShowData( $this->player );

        $this->player->setParadiseReward([]);
        $result['playerKey'] = $this->player->getData('playerKey');
        return $result;
    }

}