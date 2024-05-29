<?php

namespace App\Api\Utils;
use EasySwoole\Component\TableManager;

class Lock
{

    static function exists(string $openid):bool
    {
        //openid 本身就是唯一
        $table = TableManager::getInstance()->get(TABLE_UID_LOCK);

        if( $table->exist($openid) ) return true;
        
        $table->set($openid,['is' => 1]);

        return false;
    }
    
    static function rem(string $openid):void
    {
        TableManager::getInstance()->get(TABLE_UID_LOCK)->del($openid);
    }

}
