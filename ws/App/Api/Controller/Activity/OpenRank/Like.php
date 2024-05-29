<?php
namespace App\Api\Controller\Activity\OpenRank;
use App\Api\Utils\Keys;
use App\Api\Utils\Consts;
use App\Api\Table\ConfigParam;
use App\Api\Service\Module\OpenRankService;
use App\Api\Controller\BaseController;

class Like extends BaseController
{

    public function index()
    {
        $index  = $this->param['index'];
        $site   = $this->param['site'];
        $likeArr = OpenRankService::getInstance()->getLikeState($this->player);

        $result = '今日已点赞';
        if(empty($likeArr[$index]))
        {
            $key    = Keys::getInstance()->getShowSiteRank($site);
            $key_d  = Keys::getInstance()->getLikeSiteRank($site);
    
            $palyer = OpenRankService::getInstance()->selectShowSiteRank($key, $index);  // 获取哈希结构排名index玩家
            OpenRankService::getInstance()->updateLikeSiteRankByIncr($key_d, 1, $palyer); // 点赞+1
    
            $keys = [
                1 => Consts::LIKE_RNAK_STATE_1,
                2 => Consts::LIKE_RNAK_STATE_2,
                3 => Consts::LIKE_RNAK_STATE_3,
            ];
            $this->player->setArg($keys[$index],time(),'reset');

            $cost = ConfigParam::getInstance()->getFmtParam("OPENSERVICE_SPRINT_RANK_REWARD");
            $reward[] = [ 'type' => GOODS_TYPE_1, 'gid' => $cost['gid'], 'num' => $cost['num'] ];

            $result = [
                'world'      => OpenRankService::getInstance()->getLikeRankFmtData($this->player),
                'reward'     => $reward,
            ];
        }

        $this->sendMsg($result);
    }

}