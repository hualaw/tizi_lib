<?php

class TiziOauth {


	Const AUTORIZE_URL = "http://oauth_c.tizi.com/oauth/oauth/index";
	Const ACCESSTOKEN_URL = "http://oauth_c.tizi.com/oauth/oauth/access_token";
	Const USER_INFO_URL = "http://oauth_c.tizi.com/oauth/oauth_user/get_user_info";

	public $client_id;

	public $client_secret;

	public $access_token;

	public $redirect_uri;

	public $scope;

	private $_CI;

	public function __construct($config) {
		
		$this->client_id = $config['appid'];
		$this->client_secret = $config['appkey'];
		$this->redirect_uri = $config['callback'];
		$this->scope = $config['scope'];
			
		$this->_CI = &get_instance();
		$this->_CI->load->library('curl');
	}

	public function tizi_login() {
		
		$state = md5(uniqid(rand(), TRUE));
		$params = array();
		$params['client_id'] = $this->client_id;
		$params['redirect_uri'] = $this->redirect_uri;
		$params['response_type'] = 'code';
		$params['scope'] = $this->scope;
		$params['state'] = $state;

		$this->_CI->session->set_userdata('tizi_oauth_status', $state);

		$login_url = $this->combineURL(self::AUTORIZE_URL, $params);
		redirect($login_url);

	}
	
	public function getAccessToken() {
		
		$state = $this->_CI->session->userdata('tizi_oauth_status');
		if (empty($state) || $state != trim($_GET['state'])) {
		
			exit('state error');
		}

		$params = array();
		$params['grant_type'] = 'authorization_code';
		$params['client_id'] = $this->client_id;
		$params['client_secret'] = $this->client_secret;
		$params['redirect_uri'] = $this->redirect_uri;
		$params['code'] = $_GET['code'];

		$token_url = self::ACCESSTOKEN_URL;

		$result = $this->_post($token_url, $params);
		if (!isset($result['error']) && isset($result['access_token'])) {

			$this->access_token = $result['access_token'];
			return $this->access_token;

		}

		return false;

	}

	public function get_user_info(){
		
		$params = array();
		$params['client_id'] = $this->client_id;
		$params['access_token'] = $this->access_token;
		$user_info_url = $this->combineURL(self::USER_INFO_URL, $params);
		$result = json_decode($this->_get($user_info_url), true);
		print_r($result);
		exit;

	}

    private function combineURL($baseURL,$keysArr){
        $combined = $baseURL."?";
        $valueArr = array();

        foreach($keysArr as $key => $val){
            $valueArr[] = "$key=$val";
        }

        $keyStr = implode("&",$valueArr);
        $combined .= ($keyStr);
        
        return $combined;
    }
	
	
	private function _post($url, $params) {
	
		$curl = $this->_CI->curl;
		$curl->create($url);
		$curl->post($params);
		$result = json_decode($curl->execute(), true);
		return $result;
	
	}

	private function _get($url) {

		if (ini_get("allow_url_fopen") == "1") {
			$content = file_get_contents($url);
		}else{
			$curl = $this->_CI->curl;
			$curl->create($url);
			$content = json_decode($curl->execute(), true);
		}

		if(empty($content)){
			return false;
		}

		return $content;
		
	}

	


}
