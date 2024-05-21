<?php
namespace App\Api\Service\Pay\Wx;
use App\Api\Service\Pay\Wx\Ios\CustomerService;
use EasySwoole\Component\CoroutineSingleTon;

class MessageService
{
    use CoroutineSingleTon;
    
    private $token            = 'R3WJkdLCiF0gSG74qBHYEoWWhZe5YcIe';
    private $EncodingAESKey   = 'rtEyzsGpHPxAjHnTeDyD1AM7MXi4bvB8NLlZP0xdfmO';

    public function firstCheck(array $param):string
    {

        $echostr   =  array_key_exists('echostr',$param) ? $param["echostr"] : '';
        $signature =  array_key_exists('signature',$param) ? $param["signature"] : '';

        return $signature === $this->createSign($param,$this->token) ? $echostr : 'signature error';
    }

    public function createSign(array $param):string
    {
        $nonce     =  array_key_exists('nonce',$param) ? $param["nonce"] : '';
        $timestamp =  array_key_exists('timestamp',$param) ? $param["timestamp"] : '';
    
        $tmpArr = [ $this->token , $timestamp, $nonce ];
        
        sort($tmpArr, SORT_STRING);

        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
    
        return $tmpStr;
    }

    public function run(\swoole_http_request $request):string
    {
        $get  = $request->get ? $request->get : [];

        if( $this->firstCheck( $get ) ) return json_encode(["ErrCode" => 99999,"ErrMsg" => "signature"],273);

        $param = json_decode($request->getContent(),true);

        if(!is_array($param)) return json_encode(["ErrCode" => 99999,"ErrMsg" => "body 非json格式"],273);
        
        return $this->distribute($param);
    }

    public function distribute(array $param):string
    {

        if(array_key_exists('MsgType',$param) && $param['MsgType'] === 'event')
        {
            switch ($param['Event']) 
            {
                case 'minigame_deliver_goods':
                    return GameGiftService::getInstance()->send($param['MiniGame']);
                break;
                case 'user_enter_tempsession':
                    return CustomerService::getInstance()->enter($param);
                break;
            }
        } 
        
        if(array_key_exists('MsgType',$param) && $param['MsgType'] === 'miniprogrampage')   return CustomerService::getInstance()->run($param);
        
        return 'success';
    }

}