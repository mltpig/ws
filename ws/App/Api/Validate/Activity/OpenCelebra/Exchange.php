<?php
namespace App\Api\Validate\Activity\OpenCelebra;
use EasySwoole\Component\CoroutineSingleTon;

class Exchange
{
    use CoroutineSingleTon;

    private $rules = [
        'method'     => 'required|notEmpty',
        'timestamp'  => 'required|notEmpty',
        'sign'       => 'required|notEmpty',
        'id'         =>'required|notEmpty',
        'num'        => 'required|notEmpty|integer|min:1',
    ];
    
    public function getRules():array
    {
        return $this->rules;
    }
}
