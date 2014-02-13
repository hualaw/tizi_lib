<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class LI_Controller extends CI_Controller{

	protected $tizi_uid=0;
	protected $tizi_utype=0;
	protected $tizi_uname='';
	protected $tizi_urname='';

	public function __construct()
	{
		parent::__construct();
	}

	protected function init()
	{
		$this->tizi_uid=$this->session->userdata("user_id");
        $this->tizi_utype=$this->session->userdata("user_type");
        $this->tizi_uname=$this->session->userdata("uname");
		$this->tizi_urname=$this->session->userdata('urname');
	}

	protected function auto_login()
	{
        $this->_username=$this->input->cookie(Constant::COOKIE_TZUSERNAME);

		if(!$this->tizi_uid&&$this->_username)	
		{
			$this->load->model("login/session_model");

			$expire_time=Constant::COOKIE_EXPIRE_TIME;
			$user_id=0;
			$this->load->model("redis/redis_model");
			if($this->redis_model->connect('auto_login'))
			{
				$login_value=json_decode($this->cache->get($this->_username));
				if(!empty($login_value))
				{
					$user_id=$login_value->user_id;
					$expire_time=$login_value->expire_time;
				}
				$this->session_model->clear_cookie();
				if($user_id)
				{	
           			$login_status=$this->session_model->generate_session($user_id);
					if($login_status)
					{
						$username=$this->encrypt->decode($this->_username);
						$username=substr($username,0,strlen($username)-10);
            			$this->session_model->generate_cookie($username,$user_id,$expire_time);
            			$this->session_model->clear_mscookie();
            		}
				}
			}
			else
			{
				$this->session_model->clear_cookie();
			}
		}
	}

}

/* End of file LI_Controller.php */
/* Location: ./library/core/LI_Controller.php */
