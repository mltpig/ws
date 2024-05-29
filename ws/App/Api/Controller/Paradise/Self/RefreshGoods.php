<?php
namespace App\Api\Controller\Paradise\Self;
use App\Api\Controller\BaseController;
use App\Api\Table\ConfigParam;
use App\Api\Service\Actor\PlayerActorService;

//刷新自己物资
class RefreshGoods extends BaseController
{

    public function index()
    {

        $cost = ConfigParam::getInstance()->getFmtParam('HOMELAND_PAY_REFRESH_COST');
        
        $result = '数量不足';
        $has = $this->player->getGoods($cost['gid']);
        if( $has > $cost['num'] )
        {
    
            $costList = [ [ 'type' => GOODS_TYPE_1,'gid' => $cost['gid'],'num' => -$cost['num'] ] ];
            $this->player->goodsBridge($costList,'福地刷新物资',$has);

            $uid   = $this->player->getData('openid');
            $site  = $this->player->getData('site');
    
            $actorId = PlayerActorService::getInstance()->getAcrotId($uid,$site);
            $result  = PlayerActorService::getInstance()->send($actorId,'refreshGoods', $this->param );
    
            $result['remain'] = $this->player->getGoods($cost['gid']);
        }

        $this->sendMsg( $result );
    }

}