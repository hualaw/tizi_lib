<?php

include_once( __DIR__.DIRECTORY_SEPARATOR.'SaeTOAuthV2.php' );
include_once( dirname(__DIR__).DIRECTORY_SEPARATOR.'Connect.php' );

class weiboConnect extends Connect{

    private $connect;
    private $config;

    public function __construct(){

        self::$module = 'weibo';
        $this->config = $this->get_config();
        $this->connect = new SaeTOAuthV2( $this->config['appid'] , $this->config['appkey'] );

    }

    public function login(){

        $code_url = $this->connect->getAuthorizeURL( WB_CALLBACK_URL );
        header("Location:$code_url");

    }

    public function callback(){

        if (isset($_REQUEST['code'])) {
            $keys = array();
            $keys['code'] = $_REQUEST['code'];
            $keys['redirect_uri'] = $this->config['callback'];
            try {
                $token = $this->connect->getAccessToken( 'code', $keys ) ;
            } catch (OAuthException $e) {

            }
        }
        $open_id = $this->connect->get_uid();

        return  array(
            'access_token'=>$access_token,
            'open_id'=>$open_id
        );
    }

}
