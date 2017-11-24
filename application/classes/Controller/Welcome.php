<?php defined('SYSPATH') or die('No direct script access.');
use Overtrue\Pinyin\Pinyin;
use EasyWeChat\Foundation\Application;
use Overtrue\EasySms\EasySms;

class Controller_Welcome extends Controller {

	public function action_index()
	{

        $options = [
            'debug'     => true,
            'app_id'    => 'wx3cf0f39249eb0e60',
            'secret'    => 'f1c242f4f28f735d4687abb469072a29',
            'token'     => 'easywechat',
            'log' => [
                'level' => 'debug',
                'file'  => '/tmp/easywechat.log',
            ],
            // ...
        ];

        $app = new Application($options);
        $pinyin = new Pinyin(); // 默认
        $data = $pinyin->convert('带着希望去旅行，比到达终点更美好');
        $data = DB::select()->from('user')->execute()->current();
        $log = new Monolog\Logger('name');
        Template::factory('Welcome/Index', array(
                'pinyin' => $log
                )
        )->response();
	}

    public function valid()
    {
        $echoStr = $_GET["echostr"];
        echo $echoStr;die;

        //valid signature , option
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }

    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        //extract post data
        if (!empty($postStr)){

                $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $fromUsername = $postObj->FromUserName;
                $toUsername = $postObj->ToUserName;
                $keyword = trim($postObj->Content);
                $time = time();
                $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            <FuncFlag>0</FuncFlag>
                            </xml>";
                if(!empty( $keyword ))
                {
                    $msgType = "text";
                    $contentStr = "Welcome to wechat world!";
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                }else{
                    echo "Input something...";
                }

        }else {
            echo "";
            exit;
        }
    }

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        //$token = TOKEN;
        $token = "jiabin2017";
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

} // End Welcome
