<?php
namespace App\Api\Service\Gm;

use App\Api\Model\Player;
use App\Api\Table\Backlist;
use EasySwoole\Component\CoroutineSingleTon;

class BacklistService
{
    use CoroutineSingleTon;

    public function add(array $param):void
    {
        $userId = json_decode($param['userId'],true);   
        foreach ($userId as $key => $tag) 
        {
            $openid = '';
            if($param['type'] == 1)
            {
                $userInfo = Player::create()->get(['roleid' => $tag]);
                if($userInfo) $openid = $userInfo->openid;
            }elseif($param['type'] == 2){
                $openid = $tag;
            }
            if(!$openid) continue;
            Backlist::getInstance()->add($openid,['reason'  => $param['reason'],'startTime' => $param['startTime'],'endTime' => $param['endTime'] ]);
        }

    }
    
    public function rem(array $param):void
    {
        $userId = json_decode($param['userId'],true);
        foreach ($userId as $key => $tag) 
        {
            $openid = '';
            if($param['type'] == 1)
            {
                $userInfo = Player::create()->get(['roleid' => $tag]);
                if($userInfo) $openid = $userInfo->openid;
            }elseif($param['type'] == 2){
                $openid = $tag;
            }
            if(!$openid) continue;
            Backlist::getInstance()->rem($openid);
        }
    }

    public function get(string $openid):bool
    {

        if(!$data = Backlist::getInstance()->get($openid)) return false;
        
        return time() < $data['endTime'];
    }

}
