<?php

class TiziOauth {


	Const AUTORIZE_URL = "http://oauth_c.tizi.com/oauth/show";
	Const ACCESSTOKEN_URL = "http://oauth_c.tizi.com/oauth/access_token";
	Const USER_INFO_URL = "http://oauth_c.tizi.com/oauth/user/get_user_info";
	Const GET_OPENID_URL = "http://oauth_c.tizi.com/oauth/me";

	public $client_id;

	public $client_secret;

	public $access_token;

	public $openid;

	public $redirect_uri;

	public $scope;

	private $_CI;

	public function __construct($config) {
		
		isset($config['appid']) && $this->client_id = $config['appid'];
		isset($config['appkey']) && $this->client_secret = $config['appkey'];
		isset($config['callback']) && $this->redirect_uri = $config['callback'];
		isset($config['scope']) && $this->scope = $config['scope'];
			
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

		$this->_CI->session->set_userdata('tizi_oauth_state', $state);

		$login_url = $this->combineURL(self::AUTORIZE_URL, $params);
		redirect($login_url);

	}
	
	public function getAccessToken() {
		
		$state = $this->_CI->session->userdata('tizi_oauth_state');
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

		$result = json_decode($this->post($token_url, $params), true);
		
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

		$response = $this->get(self::GET_OPENID_URL, $params);

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
		$result = json_decode($this->get(self::USER_INFO_URL, $params), true);
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
	
    /**
     * get
     */
    public function get($url, $keysArr){
        $combined = $this->combineURL($url, $keysArr);
        return $this->get_contents($combined);
    }

    /**
     * post
     */
    public function post($url, $keysArr, $flag = 0){

        $ch = curl_init();
        if(! $flag) curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
        curl_setopt($ch, CURLOPT_POST, TRUE); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $keysArr); 
        curl_setopt($ch, CURLOPT_URL, $url);
        $ret = curl_exec($ch);

        curl_close($ch);
        return $ret;
    }

    public function get_contents($url){

        if (ini_get("allow_url_fopen") == "1") {
            $response = file_get_contents($url);
        }else{
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_URL, $url);
            $response =  curl_exec($ch);
            curl_close($ch);
        }

        if(empty($response)){
            $this->error->showError("50001");
        }

        return $response;
    }

	


}
