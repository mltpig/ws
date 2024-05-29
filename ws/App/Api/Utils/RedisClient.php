<?php

namespace App\Api\Utils;
use EasySwoole\Redis\Redis;
use EasySwoole\Pool\Manager as PoolManager;

class RedisClient
{

    static function invoke(string $name,callable $call, ?float $timeout = null)
    {
        return PoolManager::getInstance()->get($name)->invoke($call,$timeout);
    }


}
