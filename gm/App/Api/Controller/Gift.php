<?php
namespace App\Api\Controller;
use App\Api\Model\ManageGift;
use App\Api\Controller\BaseController;

class Gift extends BaseController
{

    public function index()
    {
        $param = $this->param;
        //礼包
        //礼包码
        if(is_array(json_decode($param['goods'],true)))
        {
            if(!ManageGift::create()->get( [ 'gift_id' => $param['giftId'] ]) )
            {
                if(!ManageGift::create()->get(['name' => $param['name'] ]))
                {
                    $goods  = json_decode($param['goods'],true);
                    foreach ($goods as $key => $value) 
                    {
                        $reward[] = ['type' => GOODS_TYPE_1,'gid' => $key,'num' => $value];
                    }
                    try {
                        // 执行更新 $model 的更新操作
                        ManageGift::create([
                            'gift_id' => $param['giftId'],
                            'name'    => $param['name'],
                            'reward'  => json_encode($reward,JSON_UNESCAPED_UNICODE),
                            'create_time'  => date('Y-m-d H:i:s'),
                        ])->save();
                 
                        $result = [];
                    } catch(\Throwable  $e){

                        $result = $e->getMessage();
                    }

                }else{
                    $result = '该礼包名重复';
                }
            }else{
                $result = '该礼包ID重复';
            }
        }else{
            $result = 'goods 非json格式';
        }

        $this->rJson( $result );
    }
}