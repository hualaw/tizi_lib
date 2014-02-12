<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Page_Token {
    private $_ci;
    private $token; // Page Token 

    public function __construct(){
        $this->_ci = & get_instance();
        $this->set_token();
    }

	public function check_csrf_token($page_name,$input_token)	
	{
		if(!$page_name) return false;
		if(empty($input_token)) return false;
		$csrf_token = $this->_ci->session->userdata('csrf_token');
		if($csrf_token) $csrf_token=json_decode($csrf_token,true);		
		else return false;
		if(isset($csrf_token[$page_name])&&$input_token==$csrf_token[$page_name]) return true;
		else return false;	
	}

	public function generate_csrf_token($page_name)
	{
		if(!$page_name) return false;
		$csrf_token = $this->_ci->session->userdata('csrf_token');
		if($csrf_token) $csrf_token=json_decode($csrf_token,true);
		else $csrf_token=array($page_name=>'');
		$csrf_token[$page_name]=$this->token;
        $this->_ci->session->set_userdata('csrf_token', json_encode($csrf_token));
		return $this->token;
    }	
	
	public function get_csrf_token($page_name)
	{
		if(!$page_name) return false;
        $csrf_token = $this->_ci->session->userdata('csrf_token');
        if(empty($csrf_token)) return false;
		else $csrf_token = json_decode($csrf_token,true);		
		if(isset($csrf_token[$page_name])) return $csrf_token[$page_name];
		else return false;
	}

	public function token_json_encode($param)
	{
        $page_name=$this->_ci->input->post('page_name');
		$param['token']=$this->get_csrf_token($page_name);
		return json_encode($param);
	}

    protected function set_token(){
        $this->token = md5(uniqid(rand(), TRUE));
    }
}


