<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 第三方登录
 *
 */
class Oauth{

    private $platforms = array(
        'qq'=>'qqConnect.qqConnect',   
        'weibo'=>'weiboConnect.weiboConnect',
        'weixin'=>'wxConnect.wx_auth',
    );

    private $connect;

    public function __construct(){

    }

    public function init($platform = null){

        if($platform !== null){

            if(isset($this->platforms[$platform])){

                list($dir,$module) = explode(".",$this->platforms[$platform]);
                require(__DIR__.DIRECTORY_SEPARATOR.'Oauth'.DIRECTORY_SEPARATOR.$dir.DIRECTORY_SEPARATOR.$module.'.php');
				$this->connect = new $module;
            }else{

                throw new OauthException('Param Error:1');       

            }

        }else{
            throw new OauthException('Param Error:2');       
        }

    }

	public function wx_user_detail(){
	
		$auth_data = $this->connect->auth_data();
		$data = $this->connect->user_detail($auth_data);//获取详细资料

		return $data;
	
	}

    public function login(){

        return $this->connect->login();

    }

    public function callback(){
               
        return $this->connect->callback();

    }
    
}

class OauthException extends Exception{

}
