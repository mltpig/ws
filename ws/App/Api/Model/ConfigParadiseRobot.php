<?php
namespace App\Api\Model;
use EasySwoole\ORM\AbstractModel;

class ConfigParadiseRobot extends AbstractModel
{
    protected $tableName = 'config_paradise_robot';


    public function getConfig(int $limit):array
    {
        $all = $this->all();
        $list = [];
        foreach ($all as $key => $value) 
        {
            $list[ $value['openid'] ] = $value['openid'];
        }

        return array_rand( $list , $limit > 1 ? $limit : 2 );
    }
}
