<?php
namespace App\Api\Service;

use App\Api\Utils\Keys;
use EasySwoole\Redis\Redis;
use EasySwoole\Pool\Manager as PoolManager;

class BaseService
{
    public $openid       = null; //String   ID
    public $site         = null; //Int      区服
    public $playerKey    = null; //String   玩家KEY
    public $paradise     = null; //Array    福地 
    public $arg          = null; //Array    参数控制
    public $comrade      = null; //Array    贤士坚持 
    public $user         = null; //Array    贤士坚持 
    public $last_time    = null; //Array    最后登录时间 
    public $status       = null; //Array    最后登录时间 
    public $resetFields  = ['paradise','arg','comrade','last_time','user','status']; //Array 重置字段 

    public function __construct(string $openid,int $site)
    {
        $this->site     = $site;
        $this->openid   = $openid;

        $this->playerKey = Keys::getInstance()->getPlayerKey($openid,$site);
        $this->getPlayerInfo();
        if(is_null($this->getData('last_time'))) var_dump($openid,$site);
    }

    //获取用户数据
    public function getPlayerInfo(): void
    {
        $this->setData('status',true);

        if ($userData = $this->findUserData()) $this->init($userData);
    }

    //查找用户
    public function findUserData(): array
    {
        
        return PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) {
            return $redis->hMGet($this->playerKey,$this->resetFields);
        });
    }

    //用户数据初始化
    private function init(array $userData): void
    {
        
        foreach ($userData as $name => $val) 
        {
            if (!property_exists($this, $name) || !is_null($this->{$name}) ) continue;
            $data = json_decode($val,true);
            $this->{$name} = is_array($data) ? $data : $val;
        }
        
    }
    
    public function afterCheck():void
    {
        foreach ($this->resetFields as $field) 
        {
            if($field === 'paradise') continue;
            $this->setData($field,null);
        }
    }

    //保存用户数据至Redis  默认全部保存
    public function saveData():void
    {
        if(!$this->getData('status')) return ;
        
        $this->afterCheck();

        PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) {
            $redis->hSet($this->playerKey,'paradise',json_encode( $this->paradise ) );
        });
    }

    //获取用户字段数据入口
    public function getData(string $property)
    {
        if (!property_exists($this, $property)) throw new \Exception($property . " 属性不存在");

        return $this->{$property};
    }

    //设置用户数据
    public function setData(string $property, $data): void
    {
        if (!property_exists($this, $property) || in_array($property,['playerKey'])) throw new \Exception($property . " 属性不存在");

        $this->{$property} = $data;

    }

}

