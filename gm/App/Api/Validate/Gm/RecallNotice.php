<?php
namespace App\Api\Validate\Gm;
use EasySwoole\Component\CoroutineSingleTon;

class RecallNotice
{
    use CoroutineSingleTon;

    private $rules = [
        'site'       => 'required|integer',
        'noticeId'   => 'required|notEmpty',
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
