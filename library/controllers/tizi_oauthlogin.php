<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tizi_Oauthlogin extends MY_Controller {
	
	function __construct()
    {
        parent::__construct();

		$this->load->model("login/login_model");
		$this->load->model("login/session_model");
    }

    public function index()
    {

    }

    public function callback()
	{
		
	}

}	
/* End of file login.php */
/* Location: ./application/controllers/login/login.php */
