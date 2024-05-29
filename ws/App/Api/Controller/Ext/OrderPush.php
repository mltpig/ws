<?php

namespace App\Api\Controller\Ext;

use App\Api\Controller\BaseController;
use App\Api\Model\PayOrder;
use App\Api\Service\Pay\Wx\PayCallBack;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\Log\LoggerInterface;

class OrderPush extends BaseController
{

    //手动触发推动订单
    public function index()
    {
        $outTradeNo = $this->param['outTradeNo'];

        $orderObj = PayOrder::create()->get(['order_id' => $outTradeNo]);

        if (!$orderObj) {
            Logger::getInstance()->log('pay test push: 无效的订单号' . $outTradeNo, LoggerInterface::LOG_LEVEL_ERROR, 'pay_test_push');
            return json_encode(["ErrCode" => 99999, "ErrMsg" => "无效的订单号"], 273);
        }
        \Swoole\Coroutine::sleep(4);
        PayCallBack::getInstance()->ding($orderObj->toArray());
    }

}