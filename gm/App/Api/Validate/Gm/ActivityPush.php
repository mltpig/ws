<?php
namespace App\Api\Validate\Gm;
use EasySwoole\Component\CoroutineSingleTon;

class ActivityPush
{
    use CoroutineSingleTon;

    private $rules = [
        'uniqueId'   => 'required|integer',
        'site'       => 'required|notEmpty',
        'ext'        => 'required',
        'type'       => 'required|integer|between:1,2',
        'time_type'  => 'required|between:1,2',
        'start_time' => 'required',
        'start_day'  => 'required',
        'loop'       => 'required|between:0,1',
        'time_len'   => 'required|integer',
        'rid'        => 'required|notEmpty',
        'timestamp'  => 'required|notEmpty',
        'sign'       => 'required|notEmpty',
    ];
    
    public function getRules():array
    {
        return $this->rules;
    }
}
