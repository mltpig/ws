<?php
//全局bootstrap事件
date_default_timezone_set('Asia/Shanghai');
EasySwoole\Component\Di::getInstance()->set(EasySwoole\EasySwoole\SysConst::HTTP_CONTROLLER_MAX_DEPTH,5);
EasySwoole\Component\Di::getInstance()->set(EasySwoole\EasySwoole\SysConst::HTTP_CONTROLLER_NAMESPACE,'App\\Api\\Controller\\');


defined('SUCCESS') or define('SUCCESS',200);
defined('ERROR') or define('ERROR',400);

defined('CHANNEL_TAGE') or define('CHANNEL_TAGE','wx_gm');
defined('NOTIFY_URL') or define('NOTIFY_URL','https://ysjdftz.shenzhenyuren.com/wx');

//排行标识符
defined('RANK_TAG') or define('RANK_TAG','_yr_');
//
defined('USER_SET') or define('USER_SET','user_set');

defined('GOODS_TYPE_1') or define('GOODS_TYPE_1',101);
defined('GOODS_TYPE_2') or define('GOODS_TYPE_2',102);
//保持位数即可
function numToStr($num)
{
    if (stripos($num,'e') === false) return $num;
    $num = trim(preg_replace('/[=\'"+]/','',$num,1),'"');
    list($string,$len) = explode('e',$num);
    return bcmul($string,bcpow('10',$len));
}

function encrypt(string $data,string $key,string $iv):string
{
    return base64_encode(openssl_encrypt($data, 'AES-128-CBC', $key, 1, $iv));
}

function decrypt(string $data,string $key,string $iv):string
{
    return openssl_decrypt(base64_decode($data), 'AES-128-CBC', $key, 1, $iv);
}