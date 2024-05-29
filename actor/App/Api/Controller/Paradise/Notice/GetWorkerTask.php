<?php
namespace App\Api\Controller\Paradise\Notice;
use App\Api\Controller\BaseController;

//响应获取对应功能状态
class GetWorkerTask extends BaseController
{

    public function index()
    {
        $collect    = $this->player->getParadiseGoods( $this->param['posId'] );
        $playerInfo = [];
        foreach ($collect['player'] as $who => $detail) 
        {
            $wCount       = count($detail['wid']);
            $remianTime   = $detail['need_time'] - ( ( time() - $detail['time']  ) * $wCount + $detail['step']);
            $playerInfo[] = [
                'active'    =>  $collect['active'] === $who ? 1 : 0,//取反
                'who'       =>  $who === _SELF ? 1 : 2,
                'status'    =>  $who === _SELF ? 'a' : 'g',//取反
                'time'      =>  div($remianTime,$wCount)+0,
                'wCount'    =>  $wCount,
                'head'      =>  $detail['head'],
                'nickname'  =>  $detail['nickname'],
            ];
        }

        return ['playerInfo' => $playerInfo ,'gid' => $collect['gid'] ];
    }

}