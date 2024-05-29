<?php
namespace App\Api\Service\Node;

use App\Api\Model\ConfigParadiseRobot;
use App\Api\Service\DoufaService;
use App\Api\Service\Module\ParadisRobotService;
use App\Api\Utils\Keys;
use EasySwoole\Redis\Redis;
use EasySwoole\Pool\Manager as PoolManager;
use EasySwoole\Component\CoroutineSingleTon;

class NodeService
{
    use CoroutineSingleTon;

    public function getServerNodeList():array
    {
        $serverNodeKey = Keys::getInstance()->getNodeListKey();

        return  PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($serverNodeKey) {
            return $redis->hGetAll($serverNodeKey);
        });
        
    }
    
    public function existsNode(int $site):bool
    {
        $serverNodeKey = Keys::getInstance()->getNodeListKey();

        return  PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($serverNodeKey,$site) {
            return $redis->hExists($serverNodeKey,$site);
        });
        
    }
    
    public function isLogin(string $openid):bool
    {
        $cacheKey = Keys::getInstance()->getLoginSetKey();
        return PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($cacheKey,$openid) {
            return $redis->hExists($cacheKey,$openid);
        });
    }

    public function openNewServer(array $param):int
    {
        if(!array_key_exists('site',$param) || !$param['site'] ) return 401;
        if(!array_key_exists('account',$param) || !$param['account'] ) return 402;
        if(!array_key_exists('pwd',$param) || !$param['pwd'] ) return 403;
        $site = $param['site'];

        if(filter_var($site, FILTER_VALIDATE_INT) === false) return 405;
        if($param['account'] !== '5xR4KC8mNE7iNY7zKHB6fjGkjn2tCy' || $param['pwd'] !== 'sBT2fmf6FsT4PYnmYBD75KcfcFkCyGfMncYWBAF4') return 406;
        //开新服，当前只有斗法机器人
        
        $openTime = strtotime(date('Y-m-d'));

        $serverNodeKey = Keys::getInstance()->getNodeListKey();
        
        $hExists = PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($serverNodeKey,$site,$openTime) {
            if($redis->hExists($serverNodeKey,$site)) return true;  
            DoufaService::getInstance()->createRobot($site);
            $redis->hSet($serverNodeKey,$site,$openTime);
            return false;
        });

        if($hExists) return 200;
        //初始化福地机器人
        $robot = (new ConfigParadiseRobot)->getConfig(20);
        foreach ($robot as $key => $openid) 
        {
            new ParadisRobotService($openid,$site);
        }

        foreach ($robot as $key => $openid) 
        {
            $player = new ParadisRobotService($openid,$site);
            $player->initFudiData();
            $player->saveData();
        }

        return 200;

    }


    public function setLastLoginNode(string $openid,int $node):void
    {
        $cacheKey = Keys::getInstance()->getLastLoginNodeKey();
        PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($cacheKey,$openid,$node) {
            $redis->hset($cacheKey,$openid,$node);
        });
    }

    public function getLastLoginNode(string $openid):int
    {
        $cacheKey = Keys::getInstance()->getLastLoginNodeKey();
        $node = PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($cacheKey,$openid) {
            return $redis->hget($cacheKey,$openid);
        });

        return $node ? $node : 1;
    }

    public function getOpenTime(int $node):int
    {
        $cacheKey = Keys::getInstance()->getNodeListKey();
        return PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($cacheKey,$node) {
            return $redis->hget($cacheKey,$node);
        });
    }

    /**
     * 获取最后一个服id
     * @return array
     * @throws \EasySwoole\Pool\Exception\PoolEmpty
     * @throws \Throwable
     */
    public function getServerLastNodeInfo()
    {
        $serverNodeKey = Keys::getInstance()->getNodeListKey();

        $nodeList   = PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($serverNodeKey) {
            return $redis->hGetAll($serverNodeKey);
        });
        $res = array();
        foreach ($nodeList as $nodeId => $time){
            $res = ['id' => $nodeId ,'time'=> $time];
        }
        return $res;

    }


}