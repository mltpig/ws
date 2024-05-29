<?php
namespace App\Api\Validate\Ext;
use EasySwoole\Component\CoroutineSingleTon;

class OrderPush
{
    use CoroutineSingleTon;

    private $rules = [
        'method'       => 'required|notEmpty',
        'timestamp'    => 'required|notEmpty',
        'sign'         => 'required|notEmpty',
        'outTradeNo'   => 'required|notEmpty',
    ];
    
    public function getRules():array
    {
        return $this->rules;
    }
}
