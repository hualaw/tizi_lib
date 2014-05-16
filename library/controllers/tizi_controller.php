<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tizi_Controller extends MY_Controller {
	
    function __construct()
    {
        parent::__construct();
    }

    protected function get_redirect($user_type,$user_data,$redirect_type,$redirect_url=false)
   	{
   		if($redirect_url) $redirect=$redirect_url;
        else $redirect=redirect_url($user_type,$redirect_type);
   		switch ($user_type) 
		{
			case Constant::USER_TYPE_STUDENT:
				if(empty($user_data['email']) || !$user_data['register_grade'])
				{
					$redirect=redirect_url(Constant::USER_TYPE_STUDENT,'perfect');
					$redirect.='?redirect='.urlencode($redirect_url);
				}
				break;
            case Constant::USER_TYPE_TEACHER:
            	if(!$user_data['register_subject'])
				{
					$redirect=redirect_url(Constant::USER_TYPE_TEACHER,'perfect');
					$redirect.='?redirect='.urlencode($redirect_url);
				}
				break;
            case Constant::USER_TYPE_PARENT:	
            case Constant::USER_TYPE_RESEARCHER:
            default:
            	break;
		}
		return $redirect;
   	}

}	
/* End of file login.php */
/* Location: ./application/controllers/login/login.php */
