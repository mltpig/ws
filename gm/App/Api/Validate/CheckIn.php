<?php
namespace App\Api\Validate;
use EasySwoole\Validate\Validate;
use EasySwoole\Component\CoroutineSingleTon;

class CheckIn
{
    use CoroutineSingleTon;
    public $secret = 'sdQ1K2xNV7f8YRMPWiSy8JOTtxZY3Dor';

    public function getValidateData(string $event,array $param):array
    {
        
        $class = ClassPath::getInstance()->getPath($event);

        if(!$class) return ['code' => 1 , 'msg' => $event." 验证规则设置错误"];
        if(!class_exists($class)) return ['code' => 1 , 'msg' => $event." 验证类未添加"];

        $rules = $class::getInstance()->getRules();

        if(!$rules) return ['code' => 1 , 'msg' => $event." 验证规则未设置"];

        $validate = Validate::make($rules);
        if(!$validate->validate($param)) return ['code' => 2 , 'msg' => $validate->getError()->__toString() ] ;
        
        $data = $validate->getVerifiedData();
        
        if( ($data['timestamp'] - time()) > 10 )  return ['code' => 2 , 'msg' => '请更新时间戳' ] ;

        if($this->createSign($data) !== $param['sign'] ) return  ['code' => 3 , 'msg' => "sign 验证错误"];

        return  $data;
    }

    public function createSign(array $param):string
    {
        ksort($param);
        $str = '';
        foreach ($param as $key => $val) 
        {
            if($key === 'sign') continue;
            $str .= $key.'='.$val.'&';
        }

        return strtolower(md5($str.$this->secret)) ;
    }

}
