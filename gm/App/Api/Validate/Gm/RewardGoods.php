<?php
namespace App\Api\Validate\Gm;
use EasySwoole\Component\CoroutineSingleTon;

class RewardGoods
{
    use CoroutineSingleTon;

    private $rules = [
        'rid'       => 'required',
        'site'      => 'required|integer',
        'timestamp' => 'required|notEmpty',
        'sign'      => 'required|notEmpty',

    ];
    
    public function getRules():array
    {
        return $this->rules;
    }
}
