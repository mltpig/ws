<?php
namespace App\Api\Controller\Paradise\Self;
use App\Api\Controller\BaseController;
use App\Api\Table\ConfigParadiseReward;
use App\Api\Service\Module\ParadisService;

//广告刷新自己物资
class AdRefreshGoods extends BaseController
{

    public function index()
    {

        $goods = $this->player->getParadiseGoods();

        $isProtect = true;

        foreach ($goods as $posid => $detail) 
        {

            if($detail['player']) continue;

            //保护，五颗星
            if($isProtect)
            {
                $gid   = ConfigParadiseReward::getInstance()->getReward(5);
                $newGoods =   [ 'gid' => $gid,'player' => [],'time' => time() + 120,'type' => 2 ,'timerid' => 0,'exp' => 0,'drift' => 0];
                $isProtect = false;
            }else{
                $newGoods = ParadisService::getInstance()->getRandGoods();
            }

            $this->player->setParadiseGoods($posid,'',$newGoods,'flushall');
        }

        return ParadisService::getInstance()->getShowData( $this->player );
    }

}