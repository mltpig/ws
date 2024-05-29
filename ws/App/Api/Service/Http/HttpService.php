<?php
namespace App\Api\Service\Http;

use App\Api\Service\AutoOpenServer\AutoOpenServerService;
use App\Crontab\AutoOpenServer;
use EasySwoole\Component\CoroutineSingleTon;
use App\Api\Service\Node\ServerService;
use App\Api\Service\Module\ParadisService;
use App\Api\Service\Pay\PaySuccessCallBackService;
use App\Api\Service\Pay\Wx\Ios\OffiAccountService;
use App\Api\Service\Pay\Wx\Ios\PayCallBackService;
use App\Api\Service\Pay\Wx\MessageService as WxMessageService;
use App\Api\Table\Table;
use App\Api\Service\Node\NodeService;
use App\Api\Service\Gm\GmService;

class HttpService
{
    use CoroutineSingleTon;

    public function run(\swoole_http_request $request):string
    {
        $get      = $request->get ? $request->get : [];
        $pathInfo = $request->server['path_info'];

        switch ($pathInfo) 
        {
            case '/'.CHANNEL.'/clearTable':
                Table::getInstance()->reset();
                break;
            case '/'.CHANNEL.'/openServer':
                $reMsg = NodeService::getInstance()->openNewServer($get);
                break;
            case '/'.CHANNEL.'/exitGame':
                $reMsg = ServerService::getInstance()->exitGame($get);
                break;
            case '/'.CHANNEL.'/payNotify':
                $method = $request->server['request_method'];
                if($method === 'GET')
                {
                    $reMsg = PaySuccessCallBackService::getInstance()->check($get);
                }elseif($method === 'POST'){
                    $reMsg = PaySuccessCallBackService::getInstance()->payCallBack( $request );
                }
                break;
            case '/'.CHANNEL.'/message':
                $method = $request->server['request_method'];
                if($method === 'GET')
                {
                    $reMsg = WxMessageService::getInstance()->firstCheck($get);
                }elseif($method === 'POST'){
                    $reMsg = WxMessageService::getInstance()->run( $request );
                }
                break;
            case '/'.CHANNEL.'/jsapi':
                $reMsg = OffiAccountService::getInstance()->run($get);
                break;
            case '/'.CHANNEL.'/pay_success_jsapi':
                $reMsg = PayCallBackService::getInstance()->run( $request->getContent() );
                break;
            case '/'.CHANNEL.'/addBacklist':
            case '/'.CHANNEL.'/remBacklist':
            case '/'.CHANNEL.'/rechargeCompensateReward':
                $reMsg = GmService::getInstance()->run($pathInfo, $request->getContent() );
                break;
            case '/'.CHANNEL.'/fudi':
                $reMsg = ParadisService::getInstance()->push($get);
                break;
            case '/'.CHANNEL.'/testAutoOpenServer':
                $reMsg =  AutoOpenServerService::getInstance()->run();
                break;
            default:
                $reMsg = 200;
                break;
        }

        return $reMsg;
    }

}
