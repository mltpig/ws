<?php
namespace EasySwoole\EasySwoole;

use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\Socket\Dispatcher;

use EasySwoole\EasySwoole\Config as GlobalConfig;

use EasySwoole\Pool\Config as PoolConfig;
use EasySwoole\Pool\Manager as PoolManager;
use EasySwoole\Redis\Config\RedisConfig;

use EasySwoole\ORM\DbManager;
use EasySwoole\ORM\Db\Config as DbConfig;
use EasySwoole\ORM\Db\Connection;

use App\Api\WebSocketParser;
use App\Api\WebSocketEvent;

use App\Api\Table\Table;

use EasySwoole\Actor\Actor;
use App\Actor\CreateActor;
use App\Actor\PlayerActor;
use App\Api\Service\Actor\ActorService;

class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');

        $config      = new PoolConfig();
        $redisConfig = new RedisConfig(GlobalConfig::getInstance()->getConf('REDIS'));
        PoolManager::getInstance()->register(new \App\Pool\RedisPool($config,$redisConfig),'redis');

        //mysql
        $config = new DbConfig(GlobalConfig::getInstance()->getConf("MYSQL"));
        DbManager::getInstance()->addConnection(new Connection($config));
    }

    public static function mainServerCreate(EventRegister $register)
    {
        Table::getInstance()->create();

        // 创建一个 Dispatcher 配置
        $conf = new \EasySwoole\Socket\Config();
        // 设置 Dispatcher 为 WebSocket 模式
        $conf->setType(\EasySwoole\Socket\Config::WEB_SOCKET);
        // 设置解析器对象
        $conf->setParser(new WebSocketParser());
        // 创建 Dispatcher 对象 并注入 config 对象
        $dispatch = new Dispatcher($conf);
        // 给server 注册相关事件 在 WebSocket 模式下  on message 事件必须注册 并且交给 Dispatcher 对象处理
        $register->set(EventRegister::onMessage, function (\swoole_websocket_server $server, \swoole_websocket_frame $frame) use ($dispatch) {
            $dispatch->dispatch($server, $frame->data, $frame);
        });        
        //自定义事件
        $register->set(EventRegister::onOpen, [WebSocketEvent::class,'onOpen']);         
        $register->set(EventRegister::onRequest, [WebSocketEvent::class,'onRequest']);         
        $register->set(EventRegister::onClose, [WebSocketEvent::class,'onClose']);
        // $register->set(EventRegister::onPipeMessage, [WebSocketEvent::class,'onPipeMessage']);
        
        // 注册Actor管理器
        $actorConfig = GlobalConfig::getInstance()->getConf("ACTOR");
        $server = \EasySwoole\EasySwoole\ServerManager::getInstance()->getSwooleServer();
        Actor::getInstance()->register(CreateActor::class);
        Actor::getInstance()->register(PlayerActor::class);
        Actor::getInstance()
            ->setTempDir(EASYSWOOLE_TEMP_DIR)
            ->setProxyNum(3)
            ->setListenAddress($actorConfig['ip'])
            ->setListenPort($actorConfig['listenPort'])
            ->attachServer($server);

        $register->add($register::onWorkerStart, function (\Swoole\Server $server,int $workerId){
            if($workerId !== 0) return;
            \EasySwoole\Component\Timer::getInstance()->after(1 * 1000, function () {
                ActorService::getInstance()->init();
            });
        });
    }

    public static function onRequest(Request $request, Response $response): bool
    {
        $serverInfo = $request->getServerParams();
        if ($serverInfo['path_info'] == '/favicon.ico' || $serverInfo['request_uri'] == '/favicon.ico') 
        {
            $response->withStatus(404);
            return false;
        }
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        // TODO: Implement afterAction() method.
    }
}