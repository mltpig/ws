<?php
namespace App\Api\Validate\Gm;
use EasySwoole\Component\CoroutineSingleTon;

class PlayerInfo
{
    use CoroutineSingleTon;

    private $rules = [
        'site'       => 'required|notEmpty',
        'idType'     => 'required',
        'userId'     => 'required',
        'ext'        => 'required',
        'rid'        => 'required|notEmpty',
        'timestamp'  => 'required|notEmpty',
        'sign'       => 'required|notEmpty',
    ];
    
    public function getRules():array
    {
        return $this->rules;
    }
}
