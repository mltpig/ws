<?php
namespace App\Api\Table;
use Swoole\Table;
use App\Api\Utils\Keys;
use EasySwoole\Component\TableManager;
use EasySwoole\Component\CoroutineSingleTon;
use EasySwoole\Redis\Redis;
use EasySwoole\Pool\Manager as PoolManager;

class Backlist
{
    use CoroutineSingleTon;

    protected $tableName = 'status_backlist';

    public function create():void
    {
        $columns = [
            'startTime'  => [ 'type'=> Table::TYPE_INT ,'size'=> 8 ],
            'endTime'    => [ 'type'=> Table::TYPE_INT ,'size'=> 8 ],
        ];

        TableManager::getInstance()->add( $this->tableName , $columns , 100000 );
    }

    public function initTable():void
    {
        
        $backlistKey = Keys::getInstance()->getBacklistKey();
        $list = PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($backlistKey) {
            return $redis->hGetAll($backlistKey);
        });
        
        if(!$list) return ;
        
        $table = TableManager::getInstance()->get($this->tableName);
        foreach ($list as $openid => $value) 
        {
            $data = json_decode($value,true);
            $table->set($openid,[
                'startTime' => $data['startTime'],
                'endTime'   => $data['endTime'],
            ]);
        }
    }

    public function add(string $openid,array $param ):void
    {
        $backlistKey = Keys::getInstance()->getBacklistKey();

        PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($backlistKey,$openid,$param) {
            return $redis->hSet($backlistKey,$openid,json_encode($param));
        });
        
        $table = TableManager::getInstance()->get($this->tableName);

        $table->set($openid,[
                'startTime' => $param['startTime'],
                'endTime'   => $param['endTime'],
            ]);
    }

    public function rem(string $openid ):void
    {
        $backlistKey = Keys::getInstance()->getBacklistKey();

        PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($backlistKey,$openid) {
            $redis->hDel($backlistKey,$openid);
        });

        TableManager::getInstance()->get($this->tableName)->del($openid);

    }

    public function get(string $openid ):array
    {

        $data = TableManager::getInstance()->get($this->tableName)->get($openid);
        
        return $data ? $data : [];

    }

}
