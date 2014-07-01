<?php  if(!defined("BASEPATH"))exit("No direct script access allowed");
require_once("tizi_controller.php");

class Tizi_Ssologin extends Tizi_Controller {

    function __construct(){
        parent::__construct();
    }

    protected function callback(){
		$open_id = $this->input->get("open_id");
		$token = $this->input->get("token");
		$this->load->model("sso/sso_model");
		$sso = $this->sso_model->openid_token($open_id, $token);
		if (!empty($sso)){
			$sso_redirect = $this->session->userdata("sso_redirect");
			if ($sso["user_id"] > 0){
				$this->load->model("login/session_model");
				$session = $this->session_model->generate_session($sso["user_id"]);
				$this->session_model->generate_cookie($sso["open_id"], $sso["user_id"]);
				$this->session_model->clear_mscookie();
				$sso_redirect = $this->get_redirect($session["user_data"]["user_type"], 
					$session["user_data"], "login", $sso_redirect);
				redirect($sso_redirect);
			} else {
				$this->session->set_userdata("sso_t", Constant::LOGIN_SSO_TYPE_SSO);
				$this->session->set_userdata("sso_id", $sso["id"]);
				$sso_redirect = login_url("sso/role?platform=sso");
				redirect($sso_redirect);
			}
		}
    }
    
}
/*Endoffile ssologin.php*/
/*Location:./library/controllers/tizi_ssologin.php*/