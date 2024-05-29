<?php
namespace App\Api\Service\Pay\Wx\Ios;

use App\Api\Table\ConfigPaid;
use App\Api\Model\PayOrder;
use App\Api\Service\Pay\Wx\VoucherService;
use App\Api\Service\PlayerService;
use EasySwoole\Component\CoroutineSingleTon;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\Log\LoggerInterface;
use EasySwoole\Component\TableManager;
use EasySwoole\EasySwoole\ServerManager;

class PayCallBackService
{
    use CoroutineSingleTon;

    public function run(string $xml):string
    {
        $param = OffiAccountService::getInstance()->xml2Array($xml) ;

        if(!$param) return json_encode(["return_code" => 'FAIL',"return_msg" => "参数为空"],273);
        
        $secretKey = OffiAccountService::getInstance()->getMchSecret();

        $sign = OffiAccountService::getInstance()->createSign($param,$secretKey);

        $wxSign = array_key_exists('sign',$param) ? $param['sign'] : '';

        if($sign !== $wxSign ) return json_encode(["return_code" => 'FAIL',"return_msg" => "sign err"],273);

        try {

            $orderObj = PayOrder::create()->get(['order_id' => $param['out_trade_no'] ]);

            if( !$orderObj )
            {
                Logger::getInstance()->log('pay calback error: 无效的订单号'.$param,LoggerInterface::LOG_LEVEL_ERROR,'pay_callback_ios');
                return json_encode(["return_code" => 'FAIL',"return_msg" => "无效的订单号"],273);
            } 
            
            if( !$config = ConfigPaid::getInstance()->getOne($orderObj['recharge_id']))
            {
                Logger::getInstance()->log('无效的充值档次'.$param['out_trade_no'],LoggerInterface::LOG_LEVEL_ERROR,'pay_callback_ios');
                return json_encode(["return_code" => 'FAIL',"return_msg" => "无效的充值档次"],273);
            }

            if( $orderObj->state ) return json_encode(["return_code" => 0,"return_msg" => "OK"],273);

            $orderObj->update([
                'channe_order' => $param['transaction_id'],
                'state'        => 1,
                'update_time'  => date('Y-m-d H:i:s'),
            ]);

            $rewardNum = $config['repeat_reward']['num'];

            $balance = VoucherService::getInstance($orderObj['openid'],$orderObj['site'])->present($rewardNum);

            $orderObj->update(['state' => 2 ]);

            $this->ding($orderObj['openid'],$orderObj['site'],$rewardNum,$balance,$orderObj->toArray());

            return json_encode(["return_code" => 0,"return_msg" => "OK"],273);

        } catch (\Throwable $th) {

            return json_encode(["return_code" => 'FAIL',"return_msg" => $th->getMessage() ],273);

        }

    }

    public function ding(string $openid,string $site,int $rewardNum,int $balance,$orderInfo):void
    {
        $fdInfo = TableManager::getInstance()->get(TABLE_UID_FD)->get($openid);
        if(!$fdInfo) return;

        $server = ServerManager::getInstance()->getSwooleServer();
        if(!$server->isEstablished($fdInfo['fd'])) return;

        $player = new PlayerService($openid,$site,$fdInfo['fd']);

        if(!$player->getData('create_time'))
        {
            Logger::getInstance()->log('ding error: find openid error'.$openid.'  '.$site ,LoggerInterface::LOG_LEVEL_ERROR,'pay_callback_ios');
            return ;
        } 

        try {
            $config = ConfigPaid::getInstance()->getOne($orderInfo['recharge_id']);
            $data = [
                'code'=> SUCCESS,
                'method'=>'pay_success',
                'data'=> [
                   // 'rewardNum' => $rewardNum,
                    'ticket' => $balance,
                    'rechargeId' => $orderInfo['recharge_id'],
                    'num' => $config['repeat_reward']['num'],
                    'buyQuantity' => $config['price'],
                    'currencyType' => 'CNY',
                    'outTradeNo'=> $orderInfo['order_id'],
                ]
            ];
    
            $server->push($fdInfo['fd'],json_encode($data,JSON_UNESCAPED_UNICODE|JSON_FORCE_OBJECT));

        } catch (\Throwable $th) {
            Logger::getInstance()->log('ding error:'.$th->getMessage(),LoggerInterface::LOG_LEVEL_ERROR,'pay_callback_ios');
        }

    }


}