<?php

include_once( __DIR__.DIRECTORY_SEPARATOR.'SaeTOAuthV2.php' );
include_once( dirname(__DIR__).DIRECTORY_SEPARATOR.'Connect.php' );

class weiboConnect extends Connect{

    private $config;
    private $connect;

    public function __construct(){

        self::$module = 'weibo';
        $this->config = $this->get_config();
        $this->connect = new SaeTOAuthV2( $this->config['appid'] , $this->config['appkey'] );

    }

    public function login(){

        $code_url = $this->connect->getAuthorizeURL( $this->config['callback'] );
        header("Location:$code_url");

    }

    public function callback(){

        if (isset($_REQUEST['code'])) {
            $keys = array();
            $keys['code'] = $_REQUEST['code'];
            $keys['redirect_uri'] = $this->config['callback'];
            try {
                $token_data = $this->connect->getAccessToken( 'code', $keys ) ;
                $token_val = $token_data['access_token'];
                $client_connect = new SaeTClientV2( $this->config['appid'] ,  $this->config['appkey'] , $token_val );
            } catch (OAuthException $e) {
                echo $e->getMessage();
            }
            $user = $client_connect->get_uid();
            $open_id = 0;
            if(isset($user['uid'])){
                $open_id = $user['uid'];
            }
            return  array(
                'access_token'=>$token_val,
                'open_id'=>$open_id
            );
        }
    }

}
