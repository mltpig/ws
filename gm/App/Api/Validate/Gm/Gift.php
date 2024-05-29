<?php
namespace App\Api\Validate\Gm;
use EasySwoole\Component\CoroutineSingleTon;

class Gift
{
    use CoroutineSingleTon;

    private $rules = [
        'rid'       => 'required',
        'site'      => 'required|integer',
        'timestamp' => 'required|notEmpty',
        'sign'      => 'required|notEmpty',
        'giftId'    => 'required|notEmpty',
        'name'      => 'required|notEmpty',
        'goods'     => 'required|notEmpty',
        'ext'       => 'required',
        
    ];
    
    public function getRules():array
    {
        return $this->rules;
    }
}
