<?php
namespace App\Api\Controller;
use App\Actor\PlayerActor;
use EasySwoole\Component\CoroutineSingleTon;

class BaseController
{
    use CoroutineSingleTon;

    public $param       = null;
    public $player      = null;
    public $playerActor = null;

    public function __construct(PlayerActor $playerActor,array $param)
    {
        $this->param       = $param;
        $this->player      = $playerActor->player;
        $this->playerActor = $playerActor;
    }

}