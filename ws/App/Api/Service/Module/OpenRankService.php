<?php
namespace App\Api\Service\Module;

use App\Api\Utils\Keys;
use App\Api\Utils\Consts;
use App\Api\Table\ConfigParam;
use App\Api\Table\ConfigNickname;
use App\Api\Service\EmailService;
use App\Api\Service\RankService;
use App\Api\Service\PlayerService;
use App\Api\Service\Node\NodeService;
use App\Api\Service\module\PetService;
use EasySwoole\Utility\SnowFlake;
use EasySwoole\Component\CoroutineSingleTon;
use EasySwoole\Redis\Redis;
use EasySwoole\Pool\Manager as PoolManager;
use EasySwoole\EasySwoole\Task\TaskManager;

class OpenRankService
{
    use CoroutineSingleTon;

    public function dailyReset(PlayerService $playerSer):void
    {
        $playerSer->setArg(Consts::LIKE_RNAK_STATE_1,1,'unset');
        $playerSer->setArg(Consts::LIKE_RNAK_STATE_2,1,'unset');
        $playerSer->setArg(Consts::LIKE_RNAK_STATE_3,1,'unset');
    }

    public function getLikeState(PlayerService $playerSer):array
    {
        return [
            1 => $playerSer->getArg(Consts::LIKE_RNAK_STATE_1),
            2 => $playerSer->getArg(Consts::LIKE_RNAK_STATE_2),
            3 => $playerSer->getArg(Consts::LIKE_RNAK_STATE_3),
        ];
    }

    function checkTime($startTimestamp, $resetInterval) {
        $startTimestamp   = strtotime(date('Y-m-d',$startTimestamp));
        $endTimestamp     = $startTimestamp + $resetInterval;
        return $endTimestamp  - time();
    }

    public function settlementRewards():void
    { 
        \EasySwoole\EasySwoole\Logger::getInstance()->waring('开服冲榜排行榜奖励 || 结算点赞排行榜');

        $sites = NodeService::getInstance()->getServerNodeList();

        foreach ($sites as $siteid => $openTime) 
        {
            //结算点赞排行榜
            $openServiceTime        = ConfigParam::getInstance()->getFmtParam('OPENSERVICE_SPRINT_TIME_LIMIT');
            $openServiceRankTime    = ConfigParam::getInstance()->getFmtParam('OPENSERVICE_SPRINT_RANK_TIME_LIMIT');

            $count_time     = $openServiceTime + $openServiceRankTime;
            $like_second    = $this->checkTime($openTime,$count_time);
            if($like_second < 1){
                TaskManager::getInstance()->async(function () use($siteid){
                    $this->delLikeRank($siteid); //每天0点执行异步任务,时间差小于1,删除点赞榜缓存
                });
            }

            //开服冲榜排行榜奖励
            $second = $this->checkTime($openTime, $openServiceTime);
            if($second > 1) continue; //每天0点执行异步任务,时间差小于1,则表示活动以结束
            
            TaskManager::getInstance()->async(function () use($siteid){
                $this->beginDistributeReward($siteid);
            });
        }
    }

    public function delLikeRank(int $siteid):void
    {
        PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($siteid) {
            $redis->del(Keys::getInstance()->getShowSiteRank($siteid));
            $redis->del(Keys::getInstance()->getLikeSiteRank($siteid));
        });
    }

    public function beginDistributeReward(int $siteid):void
    {
        $name       = Keys::getInstance()->getOpenRankName();
        $rankList   = RankService::getInstance()->getRankPlayeridByIndex($name,$siteid,99);
        RankService::getInstance()->delRank($name,$siteid); //删除排行榜

        $dayReward  = $this->getSettlementRewardsFmt('OPENSERVICE_SPRINT_REWARD');

        $index = 1;
        foreach ($rankList as $player => $score) 
        {
            if($index <= 3){
                $show_site = Keys::getInstance()->getShowSiteRank($siteid);
                $like_site = Keys::getInstance()->getLikeSiteRank($siteid);
                $this->updateShowSiteRank($show_site, $index, $player);
                $this->updateLikeSiteRank($like_site, 0, $player);
            }

            $this->sendRewardEmail($player,$siteid,$index,$this->getMatchReward($index,$dayReward));
            $index++;
        }

    }

    public function getMatchReward(int $index,array $config):array
    {

        foreach ($config as $value) 
        {
            if($value['begin'] == $index || $value['end'] == $index || $index > $value['begin'] && $index < $value['end'] ) return $value['rewards'];
        }

        return [];
    }

    public function getSettlementRewardsFmt(string $keyName):array
    {
        $config  = [];
        $string  = ConfigParam::getInstance()->getOne($keyName);
        $list    = explode(';',$string);
        foreach ($list as $value) 
        {
            $rewards  = [];
            list($bego,$end,$rewardStr) = explode(',',$value);
            $rewardList = explode('|',$rewardStr);
            foreach ($rewardList as $key => $detail)
            {
                $item = getFmtGoods(explode('=',$detail));
                $item['type'] = GOODS_TYPE_1;
                $rewards[] = $item;
            }

            $config[] = [
                'begin'   => $bego,
                'end'     => $end,
                'rewards' => $rewards,
            ];
        }
        return $config;
    }

    public function sendRewardEmail(string $playerid,int $site,int $index,array $reward):void
    {
        $email  = [
            'title'      => '开服冲榜奖励',
            'content'    => '大人在开服冲榜活动中出类拔萃，位列第'.$index.'位，特奉上冲榜奖励一份~<br/>',
            'start_time' => time(),
            'end_time'   => time()+2592000,
            'reward'     => $reward,
            'from'       => '貂蝉',
            'state'      => 0,
        ];
        
        $emailId = strval(SnowFlake::make(rand(0,31),rand(0,127)));
        EmailService::getInstance()->set($playerid,$site,1,$emailId,$email);
    }

    public function getLikeRankFmtData(PlayerService $playerSer)
    {
        $rankData = [];

        $site      = $playerSer->getData('site');
        $show_site = Keys::getInstance()->getShowSiteRank($site);
		$rank_list = PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($show_site) {
            return $redis->hGetAll($show_site);
		});

        foreach($rank_list as $index => $id)
        {
            $player     = new PlayerService($id,$site);
            $user       = $player->getUserInfo();
            $like_key   = Keys::getInstance()->getLikeSiteRank($site);

            $rankData[] = [
                'index'    => $index,
                'score'    => $this->getLikeSiteRankScore($like_key, $id),
                'playerid' => $id,
                'rolelv'   => $player->getData('role','lv'),
                'head'     => $user['head'],
                'nickname' => $user['nickname'],
                'chara'    => $user['chara'],
                'chara_belong'  => $user['chara_belong'],
                'cloudid'       => $player->getData('cloud','apply'),
                'pet'           => PetService::getInstance()->getPetGoIds($player->getData('pet')),
            ];
        }

        return $rankData;
    }

    //Hash结构保存排名key[index]value[player] || zadd有序集合统计点赞数
    public function updateShowSiteRank(string $name, int $index, string $member):void
	{
		PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($name,$index,$member) {
            $redis->hSet($name,$index,$member);
		});
	}

    public function selectShowSiteRank(string $name, int $index)
	{
		return PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($name,$index) {
            return $redis->hget($name,$index);
		});
	}

    public function updateLikeSiteRank(string $name, int $index, string $member):void
	{
		PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($name,$index,$member) {
            $redis->zAdd($name,$index,$member);
		});
	}

    public function updateLikeSiteRankByIncr(string $name, int $index=1, string $member):int
	{
		return PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($name,$index,$member) {
            return $redis->zInCrBy($name,$index,$member);
		});
	}

    public function getLikeSiteRankScore(string $name, string $member):int
	{
		return PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($name,$member) {
            return $redis->zScore($name,$member);
		});
	}

}
