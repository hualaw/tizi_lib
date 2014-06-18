<?php

require_once( dirname(__DIR__).DIRECTORY_SEPARATOR.'Connect.php' );
require_once( __DIR__.DIRECTORY_SEPARATOR.'TiziOauth.php' );

class tiziConnect extends Connect{

    private $config;
    private $connect;

    public function __construct(){

        parent::__construct();
        self::$module = 'tizi';
        $this->config = $this->get_config();
        $this->connect = new TiziOauth($this->config);

    }

    public function login(){

        $this->connect->tizi_login();

    }

    public function callback(){
		
		$data = array();
		$access_token = $this->connect->getAccessToken();
		if ($access_token) {
			
			$openId = $this->connect->getOpenId();
			if($openId){
			
				$result = $this->connect->get_user_info();
				print_r($result);
				exit;
				$data['nickname'] = $result['result']['nick'];
				
			}
			
		}else{

			echo "faild";

		}
		
    }

}
