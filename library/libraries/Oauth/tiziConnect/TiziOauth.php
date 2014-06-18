<?php

class TiziOauth {


	Const AUTORIZE_URL = "http://oauth_c.tizi.com/oauth/show";
	Const ACCESSTOKEN_URL = "http://oauth_c.tizi.com/oauth/access_token";
	Const USER_INFO_URL = "http://oauth_c.tizi.com/oauth/user/get_user_info";
	Const GET_OPENID_URL = "http://oauth_c.tizi.com/oauth/get_openid";

	public $client_id;

	public $client_secret;

	public $access_token;

	public $openid;

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

	public function getOpenId(){
		
		$params = array(
			"access_token" => $this->access_token
		);

		$openid_url = $this->combineURL(self::GET_OPENID_URL, $params);
		$response = $this->_get($openid_url);

		$result = json_decode($response, true);
		if (isset($result['openid'])) {
		
			$this->openid = $result['openid'];
			return $result['openid'];

		}
		return false;
		
	}

	public function get_user_info(){
		
		$params = array();
		$params['openid'] = $this->openid;
		$params['access_token'] = $this->access_token;
		$user_info_url = $this->combineURL(self::USER_INFO_URL, $params);
		$result = json_decode($this->_get($user_info_url), true);
		return $result;

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
