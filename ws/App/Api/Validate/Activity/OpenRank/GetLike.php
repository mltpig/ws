<?php
namespace App\Api\Validate\Activity\OpenRank;
use EasySwoole\Component\CoroutineSingleTon;

class GetLike
{
    use CoroutineSingleTon;

    private $rules = [
        'method'        => 'required|notEmpty',
        'timestamp'     => 'required|notEmpty',
        'sign'          => 'required|notEmpty',
    ];
    
    public function getRules():array
    {
        return $this->rules;
    }
}
