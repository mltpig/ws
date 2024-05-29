<?php
namespace App\Api\Controller;
use App\Api\Model\Player;
use App\Api\Service\PlayerService;
use App\Api\Controller\BaseController;

class PlayerInfo extends BaseController
{

    public function index()
    {
        $param  = $this->param;
        $site   = $param['site'];
        $openid = '';
        if($param['idType'] == 1)
        {
            $userInfo = Player::create()->get(['roleid' => $param['userId']]);
            if($userInfo) 
            {
                $openid = $userInfo->openid;
                $site = $userInfo->site;
            }
        }elseif($param['idType'] == 2){
            $openid = $param['userId'];
        }

        $result = '无效ID';
        if($openid)
        {
            
            $result = '未查找到';
            $player =  new PlayerService($openid,$site);

            if($player->getData('last_time'))
            {
                $result = [

                    ['key' => 'Openid','value' => $player->getData('openid')],
                    ['key' => '角色ID','value' => $player->getData('roleid')],
                    ['key' => '昵称','value' => $player->getData('user','nickname')],
                    ['key' => '元宝','value' => $player->getGoods(100000)],
                    ['key' => '银币','value' => $player->getGoods(100003)],
                    ['key' => '包子','value' => $player->getGoods(100004)],
                    ['key' => '角色等级','value' => $player->getData('role','lv')],
                    ['key' => '军旗等级','value' => $player->getData('tree','lv')],
                    ['key' => '当前关卡','value' => $player->getData('chapter')],
                    ['key' => '当前挑战','value' => $player->getData('challenge')],
                    ['key' => '最后在线','value' => $player->getData('last_time')],
                    ['key' => '创号时间','value' => $player->getData('create_time')],
                
                ];
            }
        }

        $this->rJson($result);
    }

}