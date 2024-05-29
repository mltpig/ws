<?php
namespace App\Api\Service\Module;

use App\Api\Service\PlayerService;
use App\Api\Table\ConfigParadiseLevel;
use App\Api\Table\ConfigParadiseReward;
use App\Api\Table\ConfigParam;
use EasySwoole\Component\CoroutineSingleTon;
use EasySwoole\Redis\Redis;
use EasySwoole\Pool\Manager as PoolManager;
use App\Api\Utils\Keys;
use App\Api\Service\Actor\PlayerActorService;
use App\Api\Service\Node\NodeService;
use EasySwoole\Component\TableManager;
use EasySwoole\EasySwoole\ServerManager;

class ParadisService
{
    use CoroutineSingleTon;

    public function dailyReset(PlayerService $playerSer):void
    {
        //福地每日广告刷新次数
        $playerSer->setArg(PARADISE_AD_REFRES_GOODS,1,'unset'); 

        $uid   = $playerSer->getData('openid');
        $site  = $playerSer->getData('site');

        $actorId = PlayerActorService::getInstance()->getAcrotId($uid,$site);

        PlayerActorService::getInstance()->send($actorId,'dailyReset', [] );

    }

    public function weekReset(PlayerService $playerSer):void
    {
        $uid   = $playerSer->getData('openid');
        $site  = $playerSer->getData('site');
        $actorId = PlayerActorService::getInstance()->getAcrotId($uid,$site);
        PlayerActorService::getInstance()->send($actorId,'weekReset', [] );

    }

    public function initParadise(PlayerService $player):void
    {
        if($player->getData('paradise')) return;

        $goodsDetail = [];
        for ($i=1; $i < 7; $i++) 
        { 
            $goodsDetail[$i] = $this->getRandGoods();
        }

        $list = $this->getAroundList($player->getData('site'),10,$player->getData('playerKey'));
        $refresh = $regular = [];
        foreach ($list as $key => $value) 
        {
            $key < 3 ? $refresh[] = $value : $regular[] = $value;
        }
        
        $workerInt = ConfigParam::getInstance()->getFmtParam('HOMELAND_BASIC_WORKER_NUM')+1;
        $workerList = [];
        for ($i=1; $i < $workerInt; $i++) 
        { 
            $workerList[ $i ] = [];
        }

        $paradise = [
            'list'   => $goodsDetail,
            'worker' => [ 'energy' => 100, 'list'   => $workerList ],
            'around' => [ 'refresh' => $refresh, 'regular' => $regular ],
            'reward' => []
        ];

        $player->setData('paradise',null,$paradise);
    }

    public function getInitParadise(int $site,$playerKey):string
    {

        $goodsDetail = [];
        for ($i=1; $i < 7; $i++) 
        { 
            $goodsDetail[$i] = $this->getRandGoods();
        }

        $list = $this->getAroundList($site,10,$playerKey);
        $refresh = $regular = [];
        foreach ($list as $key => $value) 
        {
            $key < 3 ? $refresh[] = $value : $regular[] = $value;
        }
        
        $workerInt = ConfigParam::getInstance()->getFmtParam('HOMELAND_BASIC_WORKER_NUM')+1;
        $workerList = [];
        for ($i=1; $i < $workerInt; $i++) 
        { 
            $workerList[ $i ] = [];
        }

        return json_encode([
            'list'   => $goodsDetail,
            'worker' => [ 'energy' => 100, 'list'   => $workerList ],
            'around' => [ 'refresh' => $refresh, 'regular' => $regular ],
            'reward' => []
        ]);


    }

    public function getAroundList(int $node,int $num,string $playerKey ):array
    {
        //三个不活跃
        //七个活跃
        $paradisActiveKey = Keys::getInstance()->getParadisActiveKey($node);
        return PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($paradisActiveKey,$num,$playerKey) {
            $redis->sRem($paradisActiveKey,$playerKey);
            $list = $redis->sRandMember($paradisActiveKey,$num);
            $redis->sAdd($paradisActiveKey,$playerKey);
            return $list ? $list : [];
        });
    }

    public function getRandGoods():array
    {
        $level = ConfigParadiseLevel::getInstance()->getRewardLevel();
        $gid   = ConfigParadiseReward::getInstance()->getReward($level);
        return  [ 'gid' => $gid,'player' => [],'time' => 0 ,'type' => 1,'exp' => 0,'drift' => 0 ];
    }

    public function getRecord(string $playerid,int $site):array
    {
        $cacheKey = Keys::getInstance()->getFudiRecordKey($playerid,$site);
        $content  = PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($cacheKey){
            return $redis->hGetAll($cacheKey);
        });

        $list = [];
        $now  = time();
        foreach ($content as $rid => $string) 
        {
            list($status,$_uid,$head,$nickname,$desc,$time,$belong) = json_decode($string,true);
            $list[$time] = [
                'rid'           => strval($rid),
                'nickname'      => $nickname,
                'head'          => $head,
                'status'        => $status,
                'desc'          => $desc,
                'time'          => date('Ymd',$time) === date('Ymd',$now) ? date('H:i',$time) : ceil(($now+28800)/DAY_LENGHT) - ceil(($time+28800) /DAY_LENGHT).'天前',
                'chara_belong'  => $belong,
            ];
        }
        ksort($list);
        return array_reverse($list);
    }

    public function getOneRecord(string $playerid,int $site,string $rid):string
    {
        $cacheKey = Keys::getInstance()->getFudiRecordKey($playerid,$site);
        return PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($cacheKey,$rid){
            return $redis->hGet($cacheKey,$rid);
        });
    }

    public function push(array $param):string
    {

        if( !array_key_exists('uid',$param) || !$param['uid'] ) return 400;
        if( !array_key_exists('site',$param) || !$param['site'] ) return 400;
        if( !array_key_exists('event',$param) || !$param['event'] || !in_array($param['event'],['collected','collecteOver'])) return 400;
        if( !NodeService::getInstance()->existsNode($param['site']) ) return 400;

        if(!$fdInfo = TableManager::getInstance()->get(TABLE_UID_FD)->get($param['uid'])) return 400 ;

        $wsServer = ServerManager::getInstance()->getSwooleServer();
        if(!$wsServer->isEstablished($fdInfo['fd']) ) return 400;

        $data = [];
        switch ($param['event']) 
        {
            case 'collected':
                $data = ['code'=> SUCCESS,'method'=>'collected','data'=> [] ];
            break;
            case 'collecteOver':
                $data = ['code'=> SUCCESS,'method'=>'collecteOver','data'=> [] ];
            break;
        }

        $wsServer->push($fdInfo['fd'],json_encode( $data ,JSON_UNESCAPED_UNICODE|JSON_FORCE_OBJECT));
        
        return 200;
    }
    
}
