<?php
namespace App\Api\Controller\Paradise\Notice;
use App\Api\Controller\BaseController;
use App\Api\Service\Module\ParadisService;

//响应成功采集
class CollectSuccess extends BaseController
{

    public function index()
    {
        $data = $this->param;
        // $data = [
        //     'method'   => 3,
        // 'reward' => $wid,
        // 'wid'    => $wid,
        // ];
        foreach ($data['wid'] as $wid) 
        {
            $this->player->setParadiseWorker($wid,[],'set');
        }

        if($data['reward'])
        {
            $newEnergy = $this->player->getParadiseEnergy() - count($data['wid']);
            $this->player->setParadiseEnergy( $newEnergy > 0 ? $newEnergy : 0  );
            ParadisService::getInstance()->receiveReward($this->player,$data['reward']);

            $record = [ 
                'a' , 
                $data['admin'],
                $this->player->getHead(),
                $this->player->getNickname(),
                $data['desc'],
                time(),
                $this->player->getCharaBelong(),
            ];

            ParadisService::getInstance()->saveRecord( $this->player->getData('openid'),$this->player->getData('site'),$record);
        } 
        
        return [];
    }

}