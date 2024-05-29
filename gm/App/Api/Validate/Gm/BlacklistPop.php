<?php
namespace App\Api\Validate\Gm;
use EasySwoole\Component\CoroutineSingleTon;

class BlacklistPop
{
    use CoroutineSingleTon;

    private $rules = [
        'idType'    => 'required',
        'reason'    => 'required|notEmpty',
        'userId'    => 'required|notEmpty',
        'ext'       => 'required',
        'rid'       => 'required|notEmpty',
        'site'      => 'required',
        'timestamp' => 'required|notEmpty',
        'sign'      => 'required|notEmpty',
        
    ];
    

    public function getRules():array
    {
        return $this->rules;
    }
}
