<?php
namespace App\Api\Service\Pay\Wx\Ios;

use App\Api\Model\PayOrder;
use App\Api\Service\Pay\Wx\VoucherService;
use EasySwoole\Component\CoroutineSingleTon;

class CompensateReward
{
    use CoroutineSingleTon;

    public function run(array $orderInfo,array $rewardConfig):string
    {

        try {
            
            $rewardNum = $rewardConfig['repeat_reward']['num'];
            
            $balance = VoucherService::getInstance($orderInfo['openid'],$orderInfo['site'])->present($rewardNum);

            PayOrder::create()->update(['state' => 2 ],['order_id' => $orderInfo['order_id']]);

            PayCallBackService::getInstance()->ding($orderInfo['openid'],$orderInfo['site'],$rewardNum,$balance,$orderInfo);

            return json_encode(["code" => 0,"msg" => "success"],273);

        } catch (\Throwable $th) {

            return json_encode(["code" => 1,"msg" => $th->getMessage() ],273);

        }

    }

}