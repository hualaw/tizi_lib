<?php  if(!defined("BASEPATH"))exit("No direct script access allowed");
require_once("tizi_controller.php");

class Tizi_Schoollogin extends Tizi_Controller {

    function __construct(){
        parent::__construct();
    }

    protected function callback(){
		$school_id = intval($this->input->get("school_id"));
		$username = trim($this->input->get("username"));
		$password = trim($this->input->get("password"));
		$this->load->model("class/classes_agents_model");
		$agents = $this->classes_agents_model->search($school_id, $username);
		if (NULL !== $agents){
			if ($agents["user_id"] > 0){
				$this->load->model("login/session_model");
				$session = $this->session_model->generate_session($agents["user_id"]);
				$this->session_model->generate_cookie(md5($agents["user_id"].time()), $agents["user_id"]);
				$this->session_model->clear_mscookie();
				$redirect = $this->get_redirect($session["user_data"]["user_type"], $session["user_data"], "login");
				echo json_token(array("code" => 1, "redirect" => $redirect));
			} else if ($agents["create_id"] > 0){
				call_user_func("self::sso", $agents["create_id"], $password);
			} else {
				echo json_token(array("code" => -2, "msg" => "姓名或密码错误，请联系管理员"));
			}
		} else {
			echo json_token(array("code" => -1, "msg" => "姓名或密码错误"));
		}
    }
    
    protected function sso($create_id, $password){
		$this->load->model("class/classes_student_create");
		$data = $this->classes_student_create->id_create($create_id);
		if (md5("ti".$data["password"]."zi") === $password){
			$this->session->set_userdata("sso_t", Constant::LOGIN_SSO_TYPE_TADD);
			$this->session->set_userdata("sso_id", $create_id);
			echo json_token(array("code" => 1, "redirect" => login_url("sso/student")));
		} else {
			echo json_token(array("code" => -3, "msg" => "姓名或密码错误"));
		}
	}
    
}
/*Endoffile schoollogin.php*/
/*Location:./library/controllers/tizi_schoollogin.php*/