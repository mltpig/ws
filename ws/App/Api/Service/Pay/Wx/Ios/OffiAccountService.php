<?php
namespace App\Api\Service\Pay\Wx\Ios;

use App\Api\Table\ConfigPaid;
use App\Api\Model\PayOrder;
use App\Api\Utils\Request;
use EasySwoole\Component\CoroutineSingleTon;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\Log\LoggerInterface;
use EasySwoole\Utility\SnowFlake;

class OffiAccountService
{
    use CoroutineSingleTon;

    public $appid      = 'wx1fdb6070c5ff0440';
    public $secret     = '43158880fbcd27d19651fb0b75732f11';
    public $mchid      = '1527320711';
    public $mchSecret  = 'tianjinyuren91120222MA06B1Y1XW99';

    public $notify_url = 'https://dev.shenzhenyuren.com/wydzg_yzy_iap/pay_success_jsapi';


    public function getAppid():string
    {
        return $this->appid;
    }

    public function getMchSecret():string
    {
        return $this->mchSecret;
    }

    public function run(array $param):string
    {
        if(!array_key_exists('code',$param) || !$param['code']) return $this->alter('invalid code');
        if(!array_key_exists('state',$param) || !$param['state']) return $this->alter('invalid code');

        if(!$info = PayOrder::create()->get(['order_id' => $param['state']])) return $this->alter('invalid state');
        if(!$recharge = ConfigPaid::getInstance()->getOne($info['recharge_id']) ) return $this->alter('invalid rechargeid');
        
        try {
            
            $openid = $this->getOffiAccountOpenid($param['code']);

            $prepayId = $this->unifiedorder($info->toArray(),$recharge,$openid);
            
            return $this->getPayHtml($prepayId);

        } catch (\Throwable $th) {
            return $this->alter('订单号：'.$param['state'].'，服务异常，请联系客服');
        }
    }

    public function getOffiAccountOpenid(string $code):string
    {

        $url   = 'https://api.weixin.qq.com/sns/oauth2/access_token';
        $param = [
            'appid'      => $this->appid,
            'secret'     => $this->secret,
            'code'       => $code,
            'grant_type' => 'authorization_code',
        ]; 

        list($result,$reBody) = Request::getInstance()->http($url,'get',$param);

        if(array_key_exists('errcode',$result))
        {
            Logger::getInstance()->log('access_token error:'.$reBody.' url : '.$url.'; param: '.json_encode($param),LoggerInterface::LOG_LEVEL_ERROR,'offi_account');

            throw new \Exception($result['errmsg']);
        } 

        return $result['openid'];
    }

    public function unifiedorder(array $info,array $recharge,string $openid)
    {
        $url   = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $param = [
            'appid'             => $this->appid,
            'mch_id'            => $this->mchid,
            'nonce_str'         => strval(SnowFlake::make(rand(0,31),rand(0,127))),
            'body'              => $recharge['repeat_reward']['num'].'商券' ,
            'out_trade_no'      => $info['order_id'],
            'total_fee'         => $recharge['price'],
            'notify_url'        => $this->notify_url,
            'trade_type'        => 'JSAPI',
            'spbill_create_ip'  => '127.0.0.1',
            'openid'            => $openid,
        ]; 

        $param['sign'] = $this->createSign($param,$this->mchSecret);
        $xml = $this->createXmlFmtData($param);
        
        list($result,$reBody) = Request::getInstance()->xmlHttp($url,$xml);

        if($result['return_code'] !== 'SUCCESS' || $result['result_code'] !== 'SUCCESS')
        {
            Logger::getInstance()->log('unifiedorder error:'.json_encode($result).' query : '.$url.'; post: '.json_encode($param),LoggerInterface::LOG_LEVEL_ERROR,'offi_account');

            throw new \Exception($result['return_msg']);
        } 

        return $result['prepay_id'];
    }

    public function createXmlFmtData(array $param):string
    {

        $xml = '<xml>';
        foreach ($param as $key => $value) 
        {
            is_numeric($key) && $key = "item id=\"$key\"";
            $xml    .=  "<$key>";
            $xml    .=  ( is_array($value) || is_object($value)) ? $this->createXmlFmtData($value) : $value;
            list($key, ) = explode(' ', $key);
            $xml    .=  "</$key>";
        }

        return $xml.'</xml>';
    }
    
    public function xml2Array(string $xml):array
    {
        //禁止引用外部xml实体 8 会抛弃 libxml_disable_internal_entity_loader
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

        return json_decode(json_encode($xmlstring), true);
       
    }

    public function createSign(array $param,string $secretKey):string
    {
        ksort($param);
        $string = '';
        foreach ($param as $key => $value) 
        {
            if($key === 'sign') continue;
            $string .= $key.'='.$value.'&';
        }

        return strtoupper(md5($string.'key='. $secretKey));
    }

    public function getPayJson(string $prepayId):string
    {
        $param = [
            "appId"     =>  $this->appid,  
            "timeStamp" => strval(time()),     
            "nonceStr"  => strval(SnowFlake::make(rand(0,31),rand(0,127))),     
            "package"   => "prepay_id=".$prepayId,     
            "signType"  => "MD5",
        ];

        $param['paySign'] = $this->createSign($param,$this->mchSecret);
        return json_encode($param);
    }

    public function getPayHtml(string $prepayId):string
    {
        return '<script type="text/javascript"> function onBridgeReady(){
            WeixinJSBridge.invoke(
               "getBrandWCPayRequest", '.$this->getPayJson($prepayId).',
               function(res){
               if(res.err_msg == "get_brand_wcpay_request:ok" ){
                    // 使用以上方式判断前端返回,微信团队郑重提示：
                    WeixinJSBridge.call("closeWindow");
               } 
            }); 
         }
         if (typeof WeixinJSBridge == "undefined"){
            if( document.addEventListener ){
                document.addEventListener("WeixinJSBridgeReady", onBridgeReady, false);
            }else if (document.attachEvent){
                document.attachEvent("WeixinJSBridgeReady", onBridgeReady); 
                document.attachEvent("onWeixinJSBridgeReady", onBridgeReady);
            }
         }else{
            onBridgeReady();
         }</script>';
    }

    public function alter(string $msg):string
    {
        return "<script type='text/javascript'> alert('".$msg."') </script>";
    }

}