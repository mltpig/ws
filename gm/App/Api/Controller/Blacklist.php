<?php
namespace App\Api\Controller;
use App\Api\Controller\BaseController;
use App\Api\Utils\Consts;
use App\Api\Utils\Request;

class Blacklist extends BaseController
{

    public function add()
    {
        $param = $this->param;

        $api = NOTIFY_URL.'/addBacklist';
        $param = [ 
            'tag'       => encrypt('dividendr3WSzC7ZxJ',Consts::AES_KEY,Consts::AES_IV) ,
            'code'      => encrypt('bA6FjyenSbPBsfAaT5x5',Consts::AES_KEY,Consts::AES_IV),
            'timestamp' => time(),
            'type'      => $param['idType'],
            'reason'    => $param['reason'],
            'userId'    => $param['userId'],
            'startTime' => $param['startTime'],
            'endTime'   => $param['endTime'],
        ];

        $param['sign'] = $this->createSign($param,'vfxloCPx2oGssv7qqXekl1D7U3cKj2TN');


        list($result,$body) = Request::getInstance()->http($api,'post',$param);

        $this->rJson(  !is_array($result) || !isset($result['code'])  ? $body : []);
    }

    public function rem()
    {
        $param = $this->param;

        $api = NOTIFY_URL.'/remBacklist';
        $param = [ 
            'tag'       => encrypt('dividendr3WSzC7ZxJ',Consts::AES_KEY,Consts::AES_IV) ,
            'code'      => encrypt('bA6FjyenSbPBsfAaT5x5',Consts::AES_KEY,Consts::AES_IV),
            'timestamp' => time(),
            'type'      => $param['idType'],
            'reason'    => $param['reason'],
            'userId'    => $param['userId'],
        ];

        $param['sign'] = $this->createSign($param,'vfxloCPx2oGssv7qqXekl1D7U3cKj2TN');

        list($result,$body) = Request::getInstance()->http($api,'post',$param);
        
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