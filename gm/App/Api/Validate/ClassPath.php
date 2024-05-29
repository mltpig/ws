<?php
namespace App\Api\Validate;
use EasySwoole\Component\CoroutineSingleTon;

class ClassPath
{
    use CoroutineSingleTon;

    private $classList = array(
        "getRewardGoods"   => "\\App\\Api\\Validate\\Gm\\RewardGoods",
        "email"            => "\\App\\Api\\Validate\\Gm\\Email",
        "gift"             => "\\App\\Api\\Validate\\Gm\\Gift",
        "giftCode"         => "\\App\\Api\\Validate\\Gm\\GiftCode",
        "notice"           => "\\App\\Api\\Validate\\Gm\\Notice",
        "recallNotice"     => "\\App\\Api\\Validate\\Gm\\RecallNotice",

        "pushActivity"     => "\\App\\Api\\Validate\\Gm\\ActivityPush",
        "stopActivity"     => "\\App\\Api\\Validate\\Gm\\ActivityStop",
        "loopActivity"     => "\\App\\Api\\Validate\\Gm\\ActivityLoop",

        "pushBlacklist"    => "\\App\\Api\\Validate\\Gm\\BlacklistPush",
        "popBlacklist"     => "\\App\\Api\\Validate\\Gm\\BlacklistPop",
        
        "getPlayerInfo"    => "\\App\\Api\\Validate\\Gm\\PlayerInfo",

        "getRechargeOrder" => "\\App\\Api\\Validate\\Gm\\RechargeOrderGet",
        "rechargeCompensateReward" => "\\App\\Api\\Validate\\Gm\\RechargeCompensateReward",
    );

    public function getPath(string $event):string
    {
        return array_key_exists($event,$this->classList)? $this->classList[$event] :'';
    }
}
