<?php
namespace App\Api\Controller;
use App\Api\Validate\CheckIn;
use EasySwoole\Http\AbstractInterface\Controller;

class BaseController extends Controller
{
    public $param;
    public $uri;

    protected function onRequest(?string $action): ?bool
    {

        $tag   = CHANNEL_TAGE.'/';
        $this->uri = ltrim($this->request()->getServerParams()['request_uri'],'/');
        strpos($this->uri , $tag) !== 0 ? : $this->uri = substr($this->uri,strlen($tag));

        $content = $this->request()->getBody()->__toString();
        $request = json_decode($content, true);

        $result  = CheckIn::getInstance()->getValidateData($this->uri,$request);

        if(array_key_exists('code',$result)) return $this->rJson($result['msg'],true);
        
        $this->param = $result;
        
        return true;
    }

    protected function rJson($result,bool $force = false):bool
    {

        if (!$this->response()->isEndResponse()) 
        {
            $data =  !is_array($result) ? [ "code"=> ERROR , "msg" => $result ]: [ "code"=> SUCCESS , "data" => $result ];
            
            $this->response()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->response()->withHeader('Content-type', 'application/json;charset=utf-8');
            $this->response()->withStatus(200);

            return $force === false ? true : false;
        } else {
            return false;
        }
    }
}