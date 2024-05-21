<?php
namespace App\Api\Validate\Activity\SignIn;
use EasySwoole\Component\CoroutineSingleTon;

class Receive
{
    use CoroutineSingleTon;

    private $rules = [
        'method'     => 'required|notEmpty',
        'timestamp'  => 'required|notEmpty',
        'sign'       => 'required|notEmpty',
        'isAd'      => 'required|notEmpty|between:0,1',
    ];
    
    public function getRules():array
    {
        return $this->rules;
    }
}
