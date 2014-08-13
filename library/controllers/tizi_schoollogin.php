<?php  if(!defined("BASEPATH"))exit("No direct script access allowed");
require_once("tizi_controller.php");

class Tizi_Schoollogin extends Tizi_Controller {

    function __construct(){
        parent::__construct();
    }

    protected function callback(){
		$school_id = intval($this->input->post("school_id"));
		$username = trim($this->input->post("s_username"));
		$password = trim($this->input->post("s_password"));
		
		$school_id > 0 && $this->input->set_cookie(Constant::COOKIE_SCHOOL_LOGIN, $school_id, 86400 * 30);

		$this->load->model("class/classes_agents_model");
		$agents = $this->classes_agents_model->search($school_id, $username);
		if (NULL !== $agents){
			if ($agents["user_id"] > 0){
				$this->load->model("login/register_model");
				$verify_password = $this->register_model->verify_password($agents["user_id"], $password);
				if ($verify_password["errorcode"] === true){
					$remember = $this->input->post("remember", true);
					if($remember){
						$cookie_time = Constant::COOKIE_REMEMBER_EXPIRE_TIME;
					} else {
						$cookie_time = Constant::COOKIE_EXPIRE_TIME;
					}
					
					$this->load->model("login/session_model");
					$session = $this->session_model->generate_session($agents["user_id"]);
					$this->session_model->generate_cookie($agents["create_id"], $agents["user_id"], $cookie_time);
					$this->session_model->clear_mscookie();

					$sso_redirect = $this->session->userdata("sso_redirect");
					$redirect = $this->get_redirect($session["user_data"]["user_type"], $session["user_data"], "login", $sso_redirect);
					echo json_token(array("code" => 1, "redirect" => $redirect));exit;
				} else {
					echo json_token(array("code" => -3, "msg" => "姓名或密码错误"));exit;
				}
			} else if ($agents["create_id"] > 0){
				call_user_func("self::sso", $agents["create_id"], $password);
			} else {
				echo json_token(array("code" => -2, "msg" => "姓名或密码错误，请联系管理员"));exit;
			}
		} else {
			echo json_token(array("code" => -1, "msg" => "姓名或密码错误"));exit;
		}
    }
    
    protected function sso($create_id, $password){
		$this->load->model("class/classes_student_create");
		$data = $this->classes_student_create->id_create($create_id);
		if (md5("ti".$data["password"]."zi") === $password){
			$this->session->set_userdata("sso_t", Constant::LOGIN_SSO_TYPE_TADD);
			$this->session->set_userdata("sso_id", $create_id);
			$this->session->set_userdata("sso_ro", Constant::REG_ORIGIN_SCHOOL_LOGIN);
			echo json_token(array("code" => 1, "redirect" => login_url("sso/student")));exit;
		} else {
			echo json_token(array("code" => -3, "msg" => "姓名或密码错误"));exit;
		}
	}
    
}
/*Endoffile schoollogin.php*/
/*Location:./library/controllers/tizi_schoollogin.php*/