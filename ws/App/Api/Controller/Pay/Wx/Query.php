<?php
namespace App\Api\Controller\Pay\Wx;
use App\Api\Controller\BaseController;
use App\Api\Model\PayOrder;
use App\Api\Service\Module\TicketService;
use App\Api\Table\ConfigPaid;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\Log\LoggerInterface;

//升级
class Query extends BaseController
{

    public function index()
    {
        $outTradeNo  = $this->param['outTradeNo'];

        try {

            TicketService::getInstance($this->player)->queryOrder($outTradeNo);

            $orderObj = PayOrder::create()->get(['order_id' => $outTradeNo ]);

            if( !$orderObj )
            {
                Logger::getInstance()->log('pay query error: 无效的订单号'.$outTradeNo,LoggerInterface::LOG_LEVEL_ERROR,'pay_query');
                return json_encode(["ErrCode" => 99999,"ErrMsg" => "无效的订单号"],273);
            }
            $orderInfo = $orderObj->toArray();
            $config = ConfigPaid::getInstance()->getOne($orderInfo['recharge_id']);
            $result = [ 
                'ticket' => TicketService::getInstance()->getBalance(),
                'rechargeId' => $orderInfo['recharge_id'],
                'buyQuantity' => $config['price'],
                'currencyType' => 'CNY',
                'outTradeNo'=> $orderInfo['order_id'],
            ];
            
        } catch (\Throwable $th) {

            $result = $th->getMessage();
        }

        $this->sendMsg( $result );
    }

}