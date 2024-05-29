<?php
namespace App\Api\Utils;
use EasySwoole\Component\CoroutineSingleTon;

class Keys 
{
    use CoroutineSingleTon;

    public function getPlayerKey(string $uid,int $site):string
    {   
        return 'player:'.$uid.':'.$site;
    }

    public function getEmailKey(string $uid,int $site,int $type):string
    {   
        return 'email:'.$uid.':'.$site.':'.$type;
    }

    public function getNoticeKey():string
    {   
        return 'notice';
    }

    public function getActivityName(string $name):string
    {
        return 'config:activity:'.$name;
    }
}
