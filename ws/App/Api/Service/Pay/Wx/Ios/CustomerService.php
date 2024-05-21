<?php
namespace App\Api\Service\Pay\Wx\Ios;

use App\Api\Table\ConfigPaid;
use App\Api\Model\PayOrder;
use App\Api\Service\Channel\WeixinService;
use App\Api\Service\PlayerService;
use App\Api\Utils\Request;
use EasySwoole\Component\CoroutineSingleTon;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\Log\LoggerInterface;

class CustomerService
{
    use CoroutineSingleTon;
    
    public $redirect_uri = 'https://dev.shenzhenyuren.com/wydzg_yzy_iap/jsapi';

    public function run(array $param):string
    {

        if(!$order = PayOrder::create()->get(['order_id' => $param['PagePath']])) return 'success';
        
        $this->send($order->toArray(),$param['FromUserName'],$param['PagePath']);

        return 'success';
    }

    public function enter(array $param):string
    {

        if(!$order = PayOrder::create()->order('id','desc')->get(['openid' => $param['FromUserName'] ,'state' => 0])) return 'success';

        $this->send($order->toArray(),$param['FromUserName'],$order['order_id']);

        return 'success';
    }


    public function send(array $info , string $touser,string $order):void
    {

        $user = PlayerService::getInstance($info['openid'],$info['site'])->getData('user');
        if(is_null($user)) return ;
        
        if(!$recharge = ConfigPaid::getInstance()->getOne($info['recharge_id']) ) return ;
        

        $desc = '金额：'.( $recharge['price']/100 ).'元
角色：桃园'.$info['site'].'区 '.$user['nickname'];

        $query  = [
            'appid' => OffiAccountService::getInstance()->getAppid() ,
            'redirect_uri' => $this->redirect_uri,
            'response_type' => 'code',
            'scope' => 'snsapi_base',
            'state' => $order 
        ];
        $targetUtl = 'https://open.weixin.qq.com/connect/oauth2/authorize?'.http_build_query($query).'#wechat_redirect';

        $this->push($touser,$desc,$targetUtl);
    }

    public function push(string $touser,string $desc,string $targetUtl):void
    {
        $token = WeixinService::getInstance()->getAccessToken();
        $url   = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$token;

        $request = [
            'access_token' => $token,
            'touser'       => $touser,
            'msgtype'      => 'link',
            'link' => [
                'title' => '点我充值',
                'description' => $desc,
                'url'         => $targetUtl,
                'thumb_url'   => 'https://ysjdftz-cdn.jinkezhexin.top/wx/static/click_recharge.jpg',
            ],
        ]; 

        list($result,$reBody) = Request::getInstance()->http($url,'post',$request);
        if($result['errcode'])
        {
            Logger::getInstance()->log('getbalance error:'.$reBody.' query : '.$url.'; post: '.json_encode($request),LoggerInterface::LOG_LEVEL_ERROR,'customer_service');
        } 


    }


}