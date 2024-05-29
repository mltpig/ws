<?php
namespace App\Api\Validate\Gm;
use EasySwoole\Component\CoroutineSingleTon;

class RechargeOrderGet
{
    use CoroutineSingleTon;

    private $rules = [
        'rid'        => 'required',
        'site'       => 'required|integer',
        'timestamp'  => 'required|notEmpty',
        'sign'       => 'required|notEmpty',
        'pagenum'    => 'required|notEmpty',
        'pagesize'   => 'required|notEmpty',
        'start_time' => 'required|notEmpty',
        'end_time'   => 'required|notEmpty',
        'cp_order'   => 'required',
        'chan_order' => 'required',
        'uid'        => 'required',

    ];

    public function getRules():array
    {
        return $this->rules;
    }
}
