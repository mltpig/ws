<?php
namespace App\Api\Service;
use App\Api\Service\Module\ParadisService;
use EasySwoole\Component\CoroutineSingleTon;
use App\Api\Utils\Consts;

class PlayerService extends BaseService
{
    use CoroutineSingleTon;

    public function before():void
    {
        $this->getPlayerInfo();
        
        $time     = time();
        $lastTime = strtotime($this->getData('last_time'));

        ParadisService::getInstance()->check($this,$time,$lastTime);
    }


    public function getArg(int $id):int
    {
		return array_key_exists($id,$this->arg) ? $this->arg[$id] : 0;
    }

    public function getParadiseGoods(int $posid=null):array
    {
		return is_null($posid) ? $this->paradise['list'] : $this->paradise['list'][$posid];
    }

    public function setParadiseGoods(int $posid,string $field,$value,string $action):void
    {
		switch ($action)
		{
			case 'set':
				$this->paradise['list'][$posid][$field] = $value;
			break;
			case 'flushall':
				$this->paradise['list'][$posid] = $value;
			break;
		}
    }

    public function getParadiseEnergy():int
    {
		return $this->paradise['worker']['energy'];
    }

    public function setParadiseEnergy(int $number):void
    {

		$this->paradise['worker']['energy'] = $number;

    }

    public function getParadiseWorker():array
    {
		return $this->paradise['worker']['list'];
    }

    public function setParadiseWorker(int $workerid,array $value):void
    {

        $this->paradise['worker']['list'][$workerid] = $value;

    }

    public function getParadiseAround(string $module = null):array
    {
		return is_null($module) ? $this->paradise['around'] : $this->paradise['around'][$module] ;
    }

    public function setParadiseAround(string $module, int $index, string $value):void
    {
		//0 - 2 regular
		//3 - 9 refresh
		$this->paradise['around'][$module][$index] = $value;
    }

    public function getParadiseReward():array
    {
		return $this->paradise['reward'];
    }

    public function setParadiseReward(array $value):void
    {
		$this->paradise['reward'] = $value;
    }

    public function getParadiseArg(int $id ):int
    {

        if(!array_key_exists('arg',$this->paradise))  $this->paradise['arg'] = [];

		return array_key_exists($id,$this->paradise['arg']) ? $this->paradise['arg'][$id] : 0 ;
    }


    public function setParadiseArg(int $id ,int $value ):void
    {

        if(!array_key_exists('arg',$this->paradise))  $this->paradise['arg'] = [];

		$this->paradise['arg'][$id] = $value ;
    }

    public function getParadiseTmp(string $field ):string
    {

        if(!array_key_exists('tmp',$this->paradise))  $this->paradise['tmp'] = [];

		return array_key_exists($field,$this->paradise['tmp']) ? $this->paradise['tmp'][$field] : '' ;
    }

    public function setParadiseTmp(string $field , string $value):void
    {

        if(!array_key_exists('tmp',$this->paradise))  $this->paradise['tmp'] = [];

		$this->paradise['tmp'][$field] = $value ;
    }

    public function getParadiseRoom():array
    {

        if(!array_key_exists('room',$this->paradise))  $this->paradise['room'] = [];
        
        return $this->paradise['room'];

    }

    public function setParadiseRoom(string $playerKey,string $action ):void
    {

        if(!array_key_exists('room',$this->paradise))  $this->paradise['room'] = [];
        
        switch ($action)
        {
            case 'set':
                $this->paradise['room'][$playerKey] = 1;
            break;
            case 'del':
                unset($this->paradise['room'][$playerKey]);
            break;
            case 'flushall':
                $this->paradise['room'] = [];
            break;
        }
    }

    public function getHead():array
    {
		return $this->user['head'];
    }

    public function getNickname():string
    {
		return $this->user['nickname'];
    }

    /**
     * 获取模型属性
     */
    public function getCharaBelong():int
    {
        return $this->getArg(Consts::CHARA_BELONG) ? $this->getArg(Consts::CHARA_BELONG) : -1;
    }
    
}
