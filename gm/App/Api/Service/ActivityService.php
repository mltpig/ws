<?php
namespace App\Api\Service;

use App\Api\Model\Player;
use App\Api\Utils\Keys;
use EasySwoole\ORM\DbManager;
use EasySwoole\Redis\Redis;
use EasySwoole\Pool\Manager as PoolManager;
use EasySwoole\Component\CoroutineSingleTon;

class ActivityService
{

    use CoroutineSingleTon;
    //查找用户
    public function get(int $type):array
    {

        switch ($type) 
        {
            case 1:
                $activityName = Keys::getInstance()->getActivityName('zhengji');
             break;
            case 2:
                $activityName = Keys::getInstance()->getActivityName('shanggu');
             break;
        }


        return PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($activityName) {
            return $redis->hGetAll($activityName);
        });
    }

    public function push(array $param):void
    {
        switch ($param['type']) 
        {
            case 1:
                $activityName = Keys::getInstance()->getActivityName('zhengji');
             break;
            case 2:
                $activityName = Keys::getInstance()->getActivityName('shanggu');
             break;
        }
        
        $data = [
            'id'            => $param['uniqueId'],//用于判断不同活动ID
            'site'          => $param['site'],
            'time_type'     => $param['time_type'],
            'start_time'    => $param['start_time'],
            'start_day'     => $param['start_day'],
            'loop'          => $param['loop'],
            'is_stop'       => 0,
            'stop_time'     => 0,
            'time_len'      => $param['time_len'],
        ];

        PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($activityName,$data) {
            return $redis->hMSet($activityName,$data);
        });

    }

    public function edit(int $type , array $data):array
    {
        switch ($type) 
        {
            case 1:
                $activityName = Keys::getInstance()->getActivityName('zhengji');
             break;
            case 2:
                $activityName = Keys::getInstance()->getActivityName('shanggu');
             break;
        }
        return PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($activityName,$data) {
            return $redis->hMSet($activityName,$data);
        });

    }




}

