<?php
namespace App\Api\Validate\Gm;
use EasySwoole\Component\CoroutineSingleTon;

class Notice
{
    use CoroutineSingleTon;

    private $rules = [
        'site'       => 'required|integer',
        'noticeId'   => 'required|notEmpty',
        'title'      => 'required|notEmpty',
        'type'       => 'required|integer',
        'content'    => 'required|notEmpty',
        'startTime'  => 'required|notEmpty',
        'endTime'    => 'required|notEmpty',
        'ext'        => 'required',
        'showTime'   => 'required',
        'rid'        => 'required|notEmpty',
        'timestamp'  => 'required|notEmpty',
        'sign'       => 'required|notEmpty',
    ];

    public function getRules():array
    {
        return $this->rules;
    }
}
