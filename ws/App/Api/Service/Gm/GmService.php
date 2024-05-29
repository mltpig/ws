<?php
namespace App\Api\Service\Gm;

use App\Api\Utils\Consts;
use EasySwoole\Component\CoroutineSingleTon;


class GmService
{
    use CoroutineSingleTon;

    public function run(string $path ,string $body):string
    {
        $param = json_decode($body,true);

        if(!is_array($param)) return json_encode(["code" => 1,"msg" => "body 非json格式"],273);
        
        if( !isset($param['sign']) || $param['sign'] !== $this->createSign($param,'vfxloCPx2oGssv7qqXekl1D7U3cKj2TN')) return json_encode(["code" => 1,"msg" => "sign err"],273);

        $tag  = decrypt($param['tag'],Consts::AES_KEY,Consts::AES_IV);
        $code = decrypt($param['code'],Consts::AES_KEY,Consts::AES_IV);

        if($tag !== 'dividendr3WSzC7ZxJ' && $code !== 'bA6FjyenSbPBsfAaT5x5')  return json_encode(["code" => 1,"msg" => "server err"],273);


        return $this->distribute($path,$param);
    }

    public function createSign(array $param,string $secret):string
    {
        ksort($param);
        $str = '';
        foreach ($param as $key => $val) 
        {
            if($key === 'sign' || is_array($val)) continue;
            $str .= $key.'='.$val.'&';
        }
        return strtolower(md5($str.$secret)) ;
    }


    public function distribute(string $path,array $param):string
    {
        $reMsg = json_encode(['code' => 0,'msg' => 'success']);
        switch ($path) 
        {
            case '/'.CHANNEL.'/addBacklist':
                BacklistService::getInstance()->add($param);
            break;
            case '/'.CHANNEL.'/remBacklist':
                BacklistService::getInstance()->rem($param);
            break;
            case '/'.CHANNEL.'/rechargeCompensateReward':
                RechargeCompensateReward::getInstance()->run($param);
            break;
        }
        
        return $reMsg;
    }

}
