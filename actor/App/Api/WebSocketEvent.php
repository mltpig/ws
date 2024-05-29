<?php 
namespace App\Api;

class WebSocketEvent
{
    public static function onRequest(\swoole_http_request $request, \swoole_http_response $response)
    {   
        $reMsg = '200';
        $response->write($reMsg);
    }

    public static function onOpen(\swoole_websocket_server $server, \swoole_http_request $request)
    {   


    }

    public static function onClose(\swoole_server $server, int $fd, int $reactorId)
    {

    }

    // public static function onPipeMessage(Swoole\Server $server, int $src_worker_id, mixed $message)
    // {
        
    // }

}