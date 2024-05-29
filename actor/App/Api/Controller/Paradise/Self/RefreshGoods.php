<?php
namespace App\Api\Controller\Paradise\Self;
use App\Api\Controller\BaseController;
use App\Api\Service\Module\ParadisService;

//刷新自己物资
class RefreshGoods extends BaseController
{

    public function index()
    {

        $goods = $this->player->getParadiseGoods();

        foreach ($goods as $posid => $detail) 
        {
            if($detail['player']) continue;

            $newGoods = ParadisService::getInstance()->getRandGoods();

            $this->player->setParadiseGoods($posid,'',$newGoods,'flushall');
        }
        
        return ParadisService::getInstance()->getShowData( $this->player );

    }

}