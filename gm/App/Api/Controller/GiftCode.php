<?php
namespace App\Api\Controller;

use App\Api\Model\ManageGift;
use App\Api\Model\ConfigGiftCode;
use EasySwoole\Mysqli\QueryBuilder;
use App\Api\Controller\BaseController;
use EasySwoole\EasySwoole\Logger;

class GiftCode extends BaseController
{
    public function index()
    {
        $param = $this->param;
        //礼包
        //礼包码
        $codeList  = json_decode($param['codeList'],true);
        if(is_array($codeList))
        {

            if(ManageGift::create()->get( [ 'gift_id' => $param['giftId'] ]) )
            {
                $newCode = [];
                foreach ($codeList as $key => $code) 
                {
                    $newCode[] = array(
                        'gift_id'     => $param['giftId'],
                        'type'        => $param['type'],
                        'code'        => $code,
                        'start_time'  => $param['startTime'],
                        'end_time'    => $param['endTime'],
                        'create_time' => date('Y-m-d H:i:s'),
                    );
                }

                try {

                    ConfigGiftCode::create()->func(function ( QueryBuilder $builder) use($newCode) {
                        $builder->insertAll('manage_gift_code',$newCode);
                    });;
                
                    $result = [];
                } catch(\Throwable  $e){

                    $result = $e->getMessage();
                    Logger::getInstance()->log($result);
                }
            }else{
                $result = '无效的礼包所属';
            }
        }else{
            $result = 'code列表 非json格式';
        }

        $this->rJson( $result );
    }

}