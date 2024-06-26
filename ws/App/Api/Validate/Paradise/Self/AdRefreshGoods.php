<?php
namespace App\Api\Validate\Paradise\Self;
use EasySwoole\Component\CoroutineSingleTon;

class AdRefreshGoods
{
    use CoroutineSingleTon;

    private $rules = [
        'method'       => 'required|notEmpty',
        'timestamp'    => 'required|notEmpty',
        'sign'         => 'required|notEmpty',
    ];
    
    public function getRules():array
    {
        return $this->rules;
    }
}
