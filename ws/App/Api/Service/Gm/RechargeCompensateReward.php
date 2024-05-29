<?php
namespace App\Api\Service\Gm;
use App\Api\Model\PayOrder;
use App\Api\Table\ConfigPaid;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\Log\LoggerInterface;
use EasySwoole\Component\CoroutineSingleTon;
use App\Api\Service\Pay\Wx\Ios\CompensateReward;

class RechargeCompensateReward
{
    use CoroutineSingleTon;
    
    private $token            = 'R3WJkdLCiF0gSG74qBHYEoWWhZe5YcIe';
    private $EncodingAESKey   = 'rtEyzsGpHPxAjHnTeDyD1AM7MXi4bvB8NLlZP0xdfmO';

    public function run(array $param):string
    {
        //查询订单
        $orderObj = PayOrder::create()->get(['order_id' => $param['cp_order'] ]);
        if( !$orderObj )
        {
            Logger::getInstance()->log('pay calback error: 无效的订单号'.json_encode($param),LoggerInterface::LOG_LEVEL_ERROR,'RechargeCompensateReward');
            return json_encode(["return_code" => 'FAIL',"return_msg" => "无效的订单号"],273);
        } 

        if( $orderObj->state != 1 )
        {
            Logger::getInstance()->log('pay calback error: 无效的订单状态'.json_encode($param),LoggerInterface::LOG_LEVEL_ERROR,'RechargeCompensateReward');
            return json_encode(["return_code" => 'FAIL',"return_msg" => "无效的订单状态"],273);
        } 
        
        if( !$config = ConfigPaid::getInstance()->getOne($orderObj['recharge_id']))
        {
            Logger::getInstance()->log('无效的充值档次'.json_encode($param),LoggerInterface::LOG_LEVEL_ERROR,'RechargeCompensateReward');
            return json_encode(["return_code" => 'FAIL',"return_msg" => "无效的充值档次"],273);
        }
        //判断订单是否未下发奖励
        //下发奖励
        //推送奖励

        return CompensateReward::getInstance()->run($orderObj->toArray(),$config);

    }


}