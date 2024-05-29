<?php
namespace App\Api\Service\Module;

use App\Api\Utils\Keys;
use EasySwoole\Redis\Redis;
use EasySwoole\Pool\Manager as PoolManager;
use EasySwoole\Component\CoroutineSingleTon;

class ConfigService
{
    use CoroutineSingleTon;

    private $site  = null;
    private $serverOpenTime  = null;
    private $activityShanggu = null;
    private $activityZhengji = null;

    public function __construct()
    {
        $configs = PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) {
            $shanggu  = Keys::getInstance()->getActivityName('shanggu');
            $zhengji  = Keys::getInstance()->getActivityName('zhengji');
            $nodeList = Keys::getInstance()->getNodeListKey();
            return [
                'activityShanggu' => $redis->hGetAll($shanggu),
                'activityZhengji' => $redis->hGetAll($zhengji),
                'serverOpenTime'  => $redis->hGetAll($nodeList),
            ];
        });

        foreach ($configs as $key => $val)
        {
            if (!property_exists($this, $key) ) continue;
            $this->{$key} = $val;
        }

    }

    public function gc():void
    {
        foreach ($this as $key => $val)
        {
            $this->{$key} = null;
        }

    }

    public function setSite(int $site):void
    {
        $this->site = $site;
    }

    public function getActivityShanggu():array
    {
        $config = $this->activityShanggu;
        //开始时间  重置周期
        if($config['time_type'] == 1)
        {
            $begin = strtotime('+'.($config['start_day'] - 1).'day',$this->getOpenTime( $this->site ));
        }else{
            $begin = strtotime( $config['start_time'] );
        }

        return [$begin,$config['time_len'] * DAY_LENGHT,$config['stop_time'],$config['id']];
    }

    public function getActivityZhengji():array
    {
        $config = $this->activityZhengji;
        //开始时间  重置周期
        if($config['time_type'] == 1)
        {
            $begin = strtotime('+'.($config['start_day'] - 1).'day',$this->getOpenTime( $this->site ));
        }else{
            $begin = strtotime( $config['start_time'] );
        }
        return [$begin,$config['time_len'] * DAY_LENGHT,$config['stop_time'],$config['id']];
    }

    public function getOpenTime(int $site):int
    {
        return array_key_exists($site,$this->serverOpenTime) ? $this->serverOpenTime[$site] : 0;
    }

}
