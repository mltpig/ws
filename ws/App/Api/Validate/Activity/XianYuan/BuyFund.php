<?php
namespace App\Api\Validate\Activity\XianYuan;
use EasySwoole\Component\CoroutineSingleTon;

class BuyFund
{
    use CoroutineSingleTon;

    private $rules = [
        'method'     => 'required|notEmpty',
        'timestamp'  => 'required|notEmpty',
        'sign'       => 'required|notEmpty',
    ];
    
    public function getRules():array
    {
        return $this->rules;
    }
}
