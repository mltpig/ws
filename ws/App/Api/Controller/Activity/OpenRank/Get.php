<?php
namespace App\Api\Controller\Activity\OpenRank;
use App\Api\Utils\Keys;
use App\Api\Utils\Consts;
use App\Api\Table\ConfigParam;
use App\Api\Service\DoufaService;
use App\Api\Service\RankService;
use App\Api\Service\Node\NodeService;
use App\Api\Service\Module\OpenRankService;
use App\Api\Controller\BaseController;

class Get extends BaseController
{

    public function index()
    { 
        $site = $this->param['site'];
        list($myInfo,$worldInfo) = RankService::getInstance()->getRankInfo(Keys::getInstance()->getOpenRankName(),$this->player->getData('openid'),$site);
        $openTime = NodeService::getInstance()->getOpenTime($site);
        $this->sendMsg( [
            'countdown' => OpenRankService::getInstance()->checkTime($openTime, ConfigParam::getInstance()->getFmtParam('OPENSERVICE_SPRINT_TIME_LIMIT') + 0),
            'my'    => $myInfo,
            'world' => DoufaService::getInstance()->getRankPlayerInfo($worldInfo,$site),
        ] );
    }

}