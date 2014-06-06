<?php
/**
 * 微信oAuth认证
 */
require_once(__DIR__.DIRECTORY_SEPARATOR."wx_base.php");

class Wx_Auth extends Wx_Base{

    private $we_obj;

	public function __construct(){

        parent::__construct();
        $this->we_obj = new Wechat($this->config);

	}
	
	public function auth_data(){

		$scope = $this->config['scope'];
		$code = isset($_GET['code'])?$_GET['code']:'';
        if ($code) {
            $json = $this->we_obj->getOauthAccessToken();
            if (!$json) {
                die('获取用户授权失败，请重新确认');
            }
            return $json;
        }else{
            $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $oauth_url = $this->we_obj->getOauthRedirect($url,"wxbase",$scope);
            header('Location: ' . $oauth_url);
        }
	}

    public function user_detail($auth_data){
        
        $open_id = $auth_data['openid'];
        $access_token = $auth_data['access_token'];

        $userinfo = $this->we_obj->getUserInfo($open_id);
        $wxuser = array();

        if ($userinfo && !empty($userinfo['nickname'])) {
            $wxuser = array(
                'open_id'=>$open_id,
                'nickname'=>$userinfo['nickname'],
                'sex'=>intval($userinfo['sex']),
                'location'=>$userinfo['province'].'-'.$userinfo['city'],
                'avatar'=>$userinfo['headimgurl'],
                'access_token'=>$access_token
            );
        } elseif (strstr($json['scope'],'snsapi_userinfo')!==false) {
            $userinfo = $this->we_obj->getOauthUserinfo($access_token, $open_id);
            if ($userinfo && !empty($userinfo['nickname'])) {
                $wxuser = array(
                    'open_id'=>$open_id,
                    'nickname'=>$userinfo['nickname'],
                    'sex'=>intval($userinfo['sex']),
                    'location'=>$userinfo['province'].'-'.$userinfo['city'],
                    'avatar'=>$userinfo['headimgurl'],
                    'access_token'=>$access_token
                );
            } else {
                $wxuser = array(
                    'open_id'=>$open_id
                );
            }
        }

        return $wxuser;       

    }



}
