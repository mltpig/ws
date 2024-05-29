<?php
namespace App\Api\Service;

use App\Api\Model\Player;
use App\Api\Utils\Keys;

use EasySwoole\ORM\DbManager;
use EasySwoole\Redis\Redis;
use EasySwoole\Pool\Manager as PoolManager;

class PlayerService
{
    protected $openid       = null; //String   ID
    protected $roleid       = null; //String   ID
    protected $site         = null; //Int      区服
    protected $last_time    = null; //String   最后一次存档时间
    protected $create_time  = null; //String   账号创建时间
    public    $playerKey    = null; //String   玩家KEY
    public    $fd           = null; //String   玩家KEY
    protected $role         = null; //Array  角色信息
    protected $goods        = null; //Array  背包物品
    protected $arg          = null; //Array  int   控制参数
    protected $tmp          = null; //Array  字符串 临时保存、控制参数;
    protected $task         = null; //Array  任务
    protected $tree         = null; //Array[lv,state,timestamp]  仙树
    protected $equip        = null; //Array [ 1:[等级，品质，攻击。生命，防御，敏捷]...] 装备
    protected $equip_tmp    = null; //Array 抽卡装备放置区
    protected $chapter      = null; //int 关卡冒险;
    protected $ext          = null; //客户端自保存数据;
    protected $cloud        = null; //座驾
    protected $head         = null; //头像[1:境界头像，2:渠道头像]
    protected $chara        = null; //模型[1: 境界,2: 活动]]
    protected $user         = null; //模型[]
    protected $doufa        = null; //斗法
    protected $challenge    = null; //int 挑战妖王;
    protected $paradise     = null; //Array 福地 交由actor处理，此处只读，不保存
    protected $comrade      = null; //Array [ list id : ['lv' => 1,'step' => 0,'battle' => 0] ] 贤士
    protected $pet          = null; //Array [ list id : ['lv' => 1,'step' => 0,'battle' => 0] ] 武将
    protected $spirit       = null; //Array [ list id : ['lv' => 1,'step' => 0,'battle' => 0] ] 红颜
    protected $tactical     = null; //Array  阵法
    protected $tower        = null; //Array [ id : 1, buffnum : 0, 'bufftemp' => [], 'buff' => [] ] 镇妖塔
    protected $equipment    = null;
    protected $demon_trail  = null;
    protected $secret_tower = null;
    protected $magic        = null;
    protected $fund         = null;
    protected $xianyuan     = null;
    protected $shanggu      = null;


    public function __construct(string $openid,int $site)
    {
        $this->site   = $site;
        $this->openid = $openid;
        $this->playerKey = Keys::getInstance()->getPlayerKey($openid,$site);
        $this->getPlayerInfo();
    }

    //获取用户数据
    public function getPlayerInfo(): void
    {
        if ($userData = $this->findUserData()) $this->init($userData);
    }

    //查找用户
    public function findUserData(): array
    {

        $userCache = PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) {
            return $redis->hGetAll($this->playerKey);
        });

        if (!empty($userCache)) return $userCache;

        $userObj = DbManager::getInstance()->invoke(function ($client) {
            return Player::invoke($client)->get(['openid' => $this->openid,'site' => $this->site]);
        });

        if (is_null($userObj)) return array();

        return $this->mysql2Cache($userObj->toArray());
    }

    //用户数据初始化
    private function init(array $userData): void
    {
        
        foreach ($userData as $name => $val) 
        {
            if (!property_exists($this, $name) || in_array($name,['playerKey'])) continue;
            $data = $name != 'ext' ? json_decode($val,true) : $val;
            $this->{$name} = is_array($data) ? $data : $val;
        }

    }

    //mysql数据格式转化为缓存数据格式
    private function mysql2Cache(array $userInfo): array
    {
        $playerData = array();
        foreach ($userInfo as $name => $val) 
        {
            if (!property_exists($this, $name)) continue;
            $playerData[$name] = $val;
        }

        return $playerData;
    }

    //获取用户字段数据入口
    public function getData(string $property,string $field = null )
    {
        if (!property_exists($this, $property)) throw new \Exception($property . " 属性不存在");

        if(is_null($field)) return $this->{$property};

        if(!array_key_exists($field,$this->{$property}) ) throw new \Exception($property . " 属性不存在 ".$field.' 键');
        
        return $this->{$property}[$field];
    }


    public static  function sendEmail(string $openid,int $site,int $type = 1,string $emailId,string $email ):void
    {
        $emailKey = Keys::getInstance()->getEmailKey($openid,$site,$type);
        PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($emailKey,$emailId,$email) {
            return $redis->hset($emailKey,$emailId,$email);
        });
    }

    public function getGoods(int $id):int
    {
		return array_key_exists($id,$this->goods) ? $this->goods[$id] : 0;
    }
}

