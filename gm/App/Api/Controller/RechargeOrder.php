<?php
namespace App\Api\Controller;

use App\Api\Model\RechargeOrder as RechargeOrderModel ;
use App\Api\Model\ConfigPaid;
use App\Api\Controller\BaseController;
use App\Api\Utils\Consts;
use App\Api\Utils\Request;

class RechargeOrder extends BaseController
{
    public function index()
    {

        $param = $this->param;
        //礼包
        //礼包码
        $where = [
            'create_time' => [ [ date('Y-m-d H:i:s',$param['start_time']) , date('Y-m-d H:i:s',$param['end_time'])]  ,'between'],
        ];

        if($param['site']) $where[ 'site' ] = [ $param['site']  ,'=' ];
        if($param['cp_order']) $where[ 'cp_order' ] = [ $param['cp_order']  ,'=' ];
        if($param['chan_order']) $where['chan_order' ] = [  $param['chan_order']  ,'=' ];
        if($param['uid']) $where[ 'openid' ] = [  $param['openid']  ,'=' ];

        $start = $param['pagenum'] > 0 ? ($param['pagenum']-1)*$param['pagesize'] : 0;

        $orders = (new RechargeOrderModel)->where($where)->limit($start,$param['pagesize'])->all();
        $list   = $this->getPaidConfig();
        $result = [];
        foreach ($orders as $key => $value) 
        {
            $result[] = [
                'id'            => $value['id'],
                'cp_order'      => $value['order_id'],
                'channe_order'  => $value['channe_order'],
                'openid'        => $value['openid'],
                'state'         => $value['state'],
                'price'         => $list[$value['recharge_id']]['price'],
                'reward'        => $list[$value['recharge_id']]['reward'],
                'update_time'   => $value['update_time'],
                'create_time'   => $value['create_time'],
            ];
        }

        $count = (new RechargeOrderModel)->where($where)->count();

        $this->rJson([
            'list'  => $result,
            'total' => $count,
        ] );
    }

    public function getPaidConfig():array
    {
        $list  = []; 
        $paids = (new ConfigPaid)->all();
        foreach ($paids as $key => $value)
        {
            
            list($gid,$num) = explode('=',$value['repeat_reward']);

            $list[ $value['id'] ] = [
                'price'  => $value['price']/100,
                'reward' => $num.'商券',
            ];
        }

        return $list;
    }

    public function compensate()
    {
        $param = $this->param;

        $api = NOTIFY_URL.'/rechargeCompensateReward';
        $param = [ 
            'tag'       => encrypt('dividendr3WSzC7ZxJ',Consts::AES_KEY,Consts::AES_IV) ,
            'code'      => encrypt('bA6FjyenSbPBsfAaT5x5',Consts::AES_KEY,Consts::AES_IV),
            'timestamp' => time(),
            'cp_order'  => $param['cp_order'],

        ];

        $param['sign'] = $this->createSign($param,'vfxloCPx2oGssv7qqXekl1D7U3cKj2TN');


        list($result,$body) = Request::getInstance()->http($api,'post',$param);
        var_dump($result);
        $this->rJson(  !is_array($result) || !isset($result['code'])  ? $body : []);
    }

    public function createSign(array $param,string $secret):string
    {
        ksort($param);
        $str = '';
        foreach ($param as $key => $val) 
        {
            if($key === 'sign' || is_array($val)) continue;
            $str .= $key.'='.$val.'&';
        }
        return strtolower(md5($str.$secret)) ;
    }
}
