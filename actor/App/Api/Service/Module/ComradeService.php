<?php
namespace App\Api\Service\Module;

use App\Api\Table\ConfigSkill;
use App\Api\Table\ConfigComrade;
use App\Api\Service\PlayerService;
use EasySwoole\Component\CoroutineSingleTon;

class ComradeService
{
    use CoroutineSingleTon;


    public function getLvStage(int $lv,array $lvRange):int
    {
        foreach ($lvRange as $stage => $value) 
        {
            if($value[0] == $lv || $value[1] == $lv || $lv > $value[0] && $lv < $value[1] ) return $stage;
        }
    }

    public function getLvStageByTalent(array $comrades,int $talent)
    {
        $sum = 0;

        foreach ($comrades as $id => $detail) 
        {
            if($detail['state'] != 1) continue;
            
            $config = ConfigComrade::getInstance()->getOne($id);
            if($config['talent'] != $talent) continue;

            $lv = $this->getLvStage($detail['lv'],$config['talent_level_up']);
            if(!$lv) continue;
            
            $skillConfig  = ConfigSkill::getInstance()->getOne($talent);
            foreach ($skillConfig['type'] as $type)
            {
                $sum += $skillConfig['params'][0][0]  +  ( $skillConfig['upgradeParams'][0][0] * ( $lv - 1)) ;
            }
            
        }
        
        return $sum;
    }

}
