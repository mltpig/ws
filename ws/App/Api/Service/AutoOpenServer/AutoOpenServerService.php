<?php

namespace App\Api\Service\AutoOpenServer;

use App\Api\Service\Node\NodeService;
use App\Api\Utils\Request;
use EasySwoole\Component\CoroutineSingleTon;
use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\Log\LoggerInterface;

class AutoOpenServerService
{
    use CoroutineSingleTon;

    public function run()
    {
        //dev环境下不触发
        if (Core::getInstance()->runMode() === 'dev') {
            return 0;
        }

        $userNum = 3000;
        $payUserNum = 300;
        $appId = 'yrXQ9uSc4YRTIfFAenbG';
        $host = 'https://ddht-api.shenzhenyuren.com';
        //$host = 'http://www.yysf.com';
        $yrsfUrl = $host . '/node_user_data';
        $username = 'chenjicong';
        $password = 'chenjicong';


        //1,模拟登录获取token
        $token = $this->getToken($username, $password, $host);

        //获取区服数据
        $nodeInfo = NodeService::getInstance()->getServerLastNodeInfo();
        if (!$nodeInfo) {
            Logger::getInstance()->log('get node data error:' . json_encode($nodeInfo), LoggerInterface::LOG_LEVEL_ERROR, 'auto_open_node_error');
            return '获取区服数据失败';
        }

        //2,获取用户数据 有效用户，付费用户
        //区服id  nodeId
        //游戏id appId yrR85uRMs4mursvYSsNW
        //startTime: 2024-05-17 00:00:00 开服时间
        //endTime: 2024-05-23  00:00:00 当前时间
        // 游戏id，区服id，开服时间，当前时间
        $headers = ['Authorization' => $token];
        $param = ['appId' => $appId, 'nodeId' => $nodeInfo['id'], 'startTime' => date('Y-m-d H:i:s', $nodeInfo['time']), 'endTime' => date('Y-m-d H:i:s')];
        list($result, $body) = Request::getInstance()->http($yrsfUrl, 'get', $param, $headers);
        if (!isset($result['code']) || $result['code'] != 200) {
            Logger::getInstance()->log('get yrsf data error:' . $body . ';url:' . $yrsfUrl, LoggerInterface::LOG_LEVEL_ERROR, 'auto_open_node_error');
        } else {
            //3,判断是否需要开启新服
            if ($result['data']['pay_num'] >= $payUserNum || $result['data']['effective_num'] >= $userNum) {
                $param = ['site' => $nodeInfo['id'] + 1];
                $param['account'] = '5xR4KC8mNE7iNY7zKHB6fjGkjn2tCy';
                $param['pwd'] = 'sBT2fmf6FsT4PYnmYBD75KcfcFkCyGfMncYWBAF4';
                $reMsg = NodeService::getInstance()->openNewServer($param);
                if($reMsg != 200){
                    Logger::getInstance()->log('open new server  error:'.$reMsg,LoggerInterface::LOG_LEVEL_ERROR,'auto_open_node_error');
                }
            }
        }

        //4,开启新服
        return json_encode([$param, $headers, $result]);
    }

    public function encrypt(string $data, string $key, string $iv)
    {
        return base64_encode(openssl_encrypt($data, 'AES-128-CBC', $key, 1, $iv));
    }

    //
    public function getToken($username, $password, $host)
    {
        //$url = 'https://ddht-api.shenzhenyuren.com/login';
        $url = $host . '/login';

        $password = $this->encrypt($password, 'JMDpWovn2UVynQkC', 'Yrmhlmz8rLhw25PZ');
        $param = ['a' => $username, 'b' => $password];
        list($result, $body) = Request::getInstance()->http($url, 'post', $param);

        if ($result['code'] == 200) {
            return $result['data']['token'];
        } else {
            Logger::getInstance()->log('get token error:' . $body, LoggerInterface::LOG_LEVEL_ERROR, 'auto_open_node_error');
            return false;
        }

    }

}