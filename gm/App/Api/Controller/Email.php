<?php
namespace App\Api\Controller;
use App\Api\Service\PlayerService;
use App\Api\Service\EamilService;
use EasySwoole\Utility\SnowFlake;
use EasySwoole\EasySwoole\Task\TaskManager;
use App\Api\Controller\BaseController;

class Email extends BaseController
{

    public function index()
    {
        $param = $this->param;
        $result = '异常';
        if(!$param['isWorld'])
        {
            $roleids = json_decode($this->param['userId']);
            $result = '';
            $openids = [];
            foreach ($roleids as $key => $uid) 
            {
                if($result) continue;
    
                $player = new PlayerService($uid,$param['site']);
    
                if(is_null($player->getData('last_time'))) $result = $uid.'不是合法的UID';
                $openids[] = $player->getData('openid');
            }
    
            if(!$result)
            {
                $goods  = json_decode($param['goods'],true);
                $reward = [];
                foreach ($goods as $key => $value) {
                    $reward[] = ['type' => GOODS_TYPE_1,'gid' => $key,'num' => $value];
                }
                $email  = json_encode([
                    'title'      => $param['title'],
                    'content'    => $param['content'],
                    'start_time' => time(),
                    'end_time'   => $param['endTime'],
                    'reward'     => $reward,
                    'from'       => '水镜先生',
                    'state'      => 0,
                ]);
    
                foreach ($openids as $openid) 
                {
                    $index = strval(SnowFlake::make(rand(0,31),rand(0,127)));
                    PlayerService::sendEmail($openid,$param['site'],1,$index,$email);
                }
                $result = [];
            }
        }else{
            $result = [];
            $goods  = json_decode($param['goods'],true);
            $reward = [];
            foreach ($goods as $key => $value) {
                $reward[] = ['type' => GOODS_TYPE_1,'gid' => $key,'num' => $value];
            }
            $email  = json_encode([
                'title'      => $param['title'],
                'content'    => $param['content'],
                'start_time' => time(),
                'end_time'   => $param['endTime'],
                'reward'     => $reward,
                'from'       => '水镜先生',
                'state'      => 0,
            ]);
            //开发者可投递给task异步处理
            TaskManager::getInstance()->async(function () use($param,$email){
                EamilService::getInstance()->sendWorldEmail($param['site'],$email);
            });
        }

        $this->rJson( $result );
    }
}