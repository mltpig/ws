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

    public function getRankName(string $rankName,int $site):string
    {   
        return "rank:".$rankName.':'.$site;
    } 

    public function getDoufaRecordKey(string $playerid,int $site):string
    {   
        return "doufa:record:".$playerid.':'.$site;
    } 

    public function getAccessTokenKey(string $channel):string
    {   
        return "token:".$channel;
    } 

    public function getFudiRecordKey(string $playerid,int $site):string
    {   
        return "fudi:record:".$playerid.':'.$site;
    } 

    public function getNodeKey(string $playerid):string
    {   
        return "node:".$playerid;
    } 

    public function getDoufaRobotKey(int $site):string
    {   
        return "config_robot_".$site;
    } 

    public function getNodeListKey():string
    {   
        return "server:node";
    } 
    
    public function getLoginSetKey():string
    {   
        return 'login:hash';
    }

    public function getLogGoodsKey(string $gamename):string
    {   
        return 'log_prop_'.$gamename;
    }
    
    public function getFiveStarKey(string $openid):string
    {
        return "five_star:".$openid;
    }

    public function getLastLoginNodeKey():string
    {
        return "last:login";
    }
    public function getBacklistKey():string
    {
        return "status:backlist";
    }
    public function getActivityName(string $name):string
    {
        return 'config:activity:'.$name;
    }
    public function getOpenRankName():string
    {   
        return "open_celebration";
    }

    public function getCreateActorHashKey():string
    {
        return "actor:create";
    }

    public function getPlayerActorHashKey(int $node):string
    {
        return "actor:player:".$node;
    }

    public function getParadisActiveKey(int $node):string
    {
        return "paradis:active:".$node;
    }

    public function getShowSiteRank(int $node):string
    {
        return "show:rank:".$node;
    }

    public function getLikeSiteRank(int $node):string
    {
        return "like:rank:".$node;
    }
}
