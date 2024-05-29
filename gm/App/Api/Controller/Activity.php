<?php
namespace App\Api\Controller;
use App\Api\Controller\BaseController;
use App\Api\Service\ActivityService;


class Activity extends BaseController
{

    public function push()
    {
        //检测当前是否有同类型活动
        $param = $this->param;

        $result = '当前有相同类型活动进行中';

        $data = ActivityService::getInstance()->get( $param['type'] );
        if(!$data || $data['stop_time'] && time() >  $data['stop_time'])
        {
            ActivityService::getInstance()->push( $param );
            $result = []; 
        }

        $this->rJson( $result );
    }

    public function stop()
    {
        $param = $this->param;

        $result = '无对应活动/活动已停止';
        if($data = ActivityService::getInstance()->get( $param['type'] ))
        {
            $nowTime  = strtotime(date('Y-m-d'));
            $stopTime = $data['stop_time'] ? 0 : strtotime('+'.($data['time_len']+1),$nowTime);

            $result = ActivityService::getInstance()->edit($param['type'],[ 'stop_time' =>  $stopTime ]  );
        }

        $this->rJson( $result );
    }

}