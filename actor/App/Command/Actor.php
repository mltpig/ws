<?php

namespace App\Command;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\Color;
use EasySwoole\Command\CommandManager;
use EasySwoole\EasySwoole\Command\Utility;
use App\Actor\PlayerActor;
use App\Actor\CreateActor;
use EasySwoole\Actor\ActorNode;
use EasySwoole\EasySwoole\Config as GlobalConfig;
use Swoole\Coroutine\Scheduler;
use EasySwoole\EasySwoole\Core;
use EasySwoole\Component\TableManager;

class Actor implements CommandInterface
{
    public function commandName(): string
    {
        return 'actor';
    }

    public function desc(): string
    {
        return 'Actor manager';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {

        $commandHelp->addAction('status', 'view actor status');
        $commandHelp->addAction('exitAll', 'exit all actor');

        return $commandHelp;
        
    }

    public function exec(): ?string
    {
        \EasySwoole\Actor\Actor::getInstance()->register(PlayerActor::class);
        \EasySwoole\Actor\Actor::getInstance()->register(CreateActor::class);

        $action = CommandManager::getInstance()->getArg(0);
        Core::getInstance()->initialize();
        $run = new Scheduler();
        $run->add(function () use (&$result, $action) {
            if (method_exists($this, $action) && $action != 'help') {
                $result = $this->{$action}();
                return;
            }

            $result = CommandManager::getInstance()->displayCommandHelp($this->commandName());
        });

        $run->start();
        return $result;

    }

    protected function status()
    {
        $config = GlobalConfig::getInstance()->getConf("ACTOR");
        $node = new ActorNode(['ip' => $config['ip'],'listenPort' => $config['listenPort']]);
        $info = PlayerActor::client($node)->status();
        $info = CreateActor::client($node)->status();
    }

    protected function exitAll()
    {
        $config = GlobalConfig::getInstance()->getConf("ACTOR");
        $node = new ActorNode(['ip' => $config['ip'],'listenPort' => $config['listenPort']]);
        $info = PlayerActor::client($node)->exitAll();
        $info = CreateActor::client($node)->exitAll();
    }

}