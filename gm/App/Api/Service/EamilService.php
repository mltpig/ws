<?php
namespace App\Api\Service;

use App\Api\Model\Player;
use App\Api\Utils\Keys;
use EasySwoole\Utility\SnowFlake;
use EasySwoole\ORM\DbManager;
use EasySwoole\Redis\Redis;
use EasySwoole\Pool\Manager as PoolManager;
use EasySwoole\Component\CoroutineSingleTon;

class EamilService
{

    use CoroutineSingleTon;
    //查找用户
    public function sendWorldEmail(int $site,string $emailContent): void
    {
        if(!$total = $this->getOpenidCount($site)) return ;
        $count = 0;
        $limit = 20000;
        $num = ceil($total/$limit);
        for ( $page = 1 ; $page <= $num; $page++) 
        { 
            $openids = $this->getOpenid($site,$page,$limit);
            foreach ($openids as $key => $value) 
            {
                $count++;
                $emailId  = strval(SnowFlake::make(rand(0,31),rand(0,127)));
                $emailKey = Keys::getInstance()->getEmailKey($value->openid,$site,1);
                PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($emailKey,$emailId,$emailContent) {
                    return $redis->hset($emailKey,$emailId,$emailContent);
                });
            }
        }
    }

    public function getOpenidCount(int $site):int
    {
        return DbManager::getInstance()->invoke(function ($client) use($site) {
            return Player::invoke($client)->where(['site' => $site])->count('id');
        });
    }

    public function getOpenid(int $site,$page,$limit)
    {
        return DbManager::getInstance()->invoke(function ($client) use($site,$page,$limit) {
            return Player::invoke($client)
                        ->field('id,openid')
                        ->limit( $limit * ($page - 1) , $limit)
                        ->all(['site' => $site]);
        });
    }


}

