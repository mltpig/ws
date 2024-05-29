<?php
namespace App\Api\Controller;
use EasySwoole\Redis\Redis;
use EasySwoole\Pool\Manager as PoolManager;
use App\Api\Controller\BaseController;
use App\Api\Utils\Keys;

class Notice extends BaseController
{

    public function index()
    {
        $param = $this->param;
        $noticeKey = Keys::getInstance()->getNoticeKey();
        $noticeid  = $param['noticeId'];
        $content = json_encode([
            'title'      => $param['title'],
            'content'    => $param['content'],
            'start_time' => $param['startTime'],
            'end_time'   => $param['endTime'],
            'showTime'   => $param['showTime'],
        ],JSON_UNESCAPED_UNICODE);

        PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($noticeKey,$content){
            return $redis->set($noticeKey,$content);
        });
        $this->rJson([]);
    }

    public function recall()
    {
        $param = $this->param;
        $noticeKey = Keys::getInstance()->getNoticeKey();
        PoolManager::getInstance()->get('redis')->invoke(function (Redis $redis) use($noticeKey){
            return $redis->unlink($noticeKey);
        });
        $this->rJson([]);
    }
}