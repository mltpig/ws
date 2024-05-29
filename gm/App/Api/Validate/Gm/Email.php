<?php
namespace App\Api\Validate\Gm;
use EasySwoole\Component\CoroutineSingleTon;

class Email
{
    use CoroutineSingleTon;

    private $rules = [
        'rid'       => 'required',
        'site'      => 'required|integer',
        'timestamp' => 'required|notEmpty',
        'sign'      => 'required|notEmpty',
        'title'      => 'required|notEmpty',
        'emailType'      => 'required|notEmpty',
        'content'      => 'required|notEmpty',
        'endTime'      => 'required|notEmpty',
        'idType'      => 'required|notEmpty',
        'userId'      => 'required|notEmpty',
        'isWorld'      => 'required|notEmpty',
        'goods'      => 'required|notEmpty',
        'ext'      => 'required',
        'lastLoginTime'      => 'required|notEmpty',
        
    ];
    
    public function getRules():array
    {
        return $this->rules;
    }
}
