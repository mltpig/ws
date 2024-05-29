<?php
namespace App\Api\Validate\Gm;
use EasySwoole\Component\CoroutineSingleTon;

class RechargeCompensateReward
{
    use CoroutineSingleTon;

    private $rules = [
        'rid'        => 'required',
        'site'       => 'required|integer',
        'timestamp'  => 'required|notEmpty',
        'sign'       => 'required|notEmpty',
        'cp_order'   => 'required',


    ];

    public function getRules():array
    {
        return $this->rules;
    }
}
