<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('login_url'))
{
	function login_url($uri = '')
	{
		$CI =& get_instance();
		return $CI->config->site_url($uri,'login');
	}
}

/* End of file LI_url_helper.php */
/* Location: ./system/helpers/LI_url_helper.php */