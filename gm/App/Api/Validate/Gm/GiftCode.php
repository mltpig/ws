<?php
namespace App\Api\Validate\Gm;
use EasySwoole\Component\CoroutineSingleTon;

class GiftCode
{
    use CoroutineSingleTon;

    private $rules = [
        'site'      => 'required|notEmpty',
        'giftId'    => 'required|notEmpty',
        'type'      => 'required|notEmpty',
        'startTime' => 'required|integer',
        'endTime'   => 'required|notEmpty',
        'codeList'  => 'required|notEmpty',
        'ext'       => 'required',
        'rid'       => 'required|notEmpty',
        'timestamp' => 'required|notEmpty',
        'sign'      => 'required|notEmpty',
        
    ];

    public function getRules():array
    {
        return $this->rules;
    }
}
