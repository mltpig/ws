<?php
namespace App\Api\Controller\Paradise\Self;
use App\Api\Controller\BaseController;
use App\Api\Table\ConfigParam;
use App\Api\Service\Actor\PlayerActorService;

//刷新自己物资
class AdRefreshGoods extends BaseController
{

    public function index()
    {

        $limit = ConfigParam::getInstance()->getFmtParam('HOMELAND_FREE_REFRESH_TIME');

        $result = '数量不足';
        if($limit > $this->player->getArg(PARADISE_AD_REFRES_GOODS))
        {

            $this->player->setArg(PARADISE_AD_REFRES_GOODS,1,'add');

            $uid   = $this->player->getData('openid');
            $site  = $this->player->getData('site');
    
            $actorId = PlayerActorService::getInstance()->getAcrotId($uid,$site);
            $result  = PlayerActorService::getInstance()->send($actorId,'adRefreshGoods', $this->param );
    
        }

        $this->sendMsg( $result );
    }

}