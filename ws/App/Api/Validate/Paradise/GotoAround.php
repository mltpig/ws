<?php
namespace App\Api\Validate\Paradise;
use EasySwoole\Component\CoroutineSingleTon;

class GotoAround
{
    use CoroutineSingleTon;

    private $rules = [
        'method'      => 'required|notEmpty',
        'timestamp'   => 'required|notEmpty',
        'sign'        => 'required|notEmpty',
        'rid'         => 'required|notEmpty',
    ];
    
    public function getRules():array
    {
        return $this->rules;
    }
}
