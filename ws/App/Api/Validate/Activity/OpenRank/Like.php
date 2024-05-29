<?php
namespace App\Api\Validate\Activity\OpenRank;
use EasySwoole\Component\CoroutineSingleTon;

class Like
{
    use CoroutineSingleTon;

    private $rules = [
        'method'        => 'required|notEmpty',
        'timestamp'     => 'required|notEmpty',
        'sign'          => 'required|notEmpty',
        'index'         => 'required|notEmpty',
    ];
    
    public function getRules():array
    {
        return $this->rules;
    }
}
