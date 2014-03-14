<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('site_url'))
{
	function site_url($uri = '', $url_prefix = 'base')
	{
		$CI =& get_instance();
		return $CI->config->site_url($uri, $url_prefix);
	}
}

if ( ! function_exists('login_url'))
{
	function login_url($uri = '')
	{
		return site_url($uri,'login');
	}
}

if ( ! function_exists('tizi_url'))
{
	function tizi_url($uri = '')
	{
		return site_url($uri,'tizi');
	}
}

if ( ! function_exists('edu_url'))
{
	function edu_url($uri = '')
	{
		return site_url($uri,'edu');
	}
}

if ( ! function_exists('jxt_url'))
{
	function jxt_url($uri = '')
	{
		return site_url($uri,'jxt');
	}
}

if ( ! function_exists('zl_url'))
{
	function zl_url($uri = '')
	{
		return site_url($uri,'zl');
	}
}

if ( ! function_exists('static_url'))
{
	function static_url($site = '')
	{
		if($site) $site = $site.'_';
		$CI =& get_instance();
		$static_url = $CI->config->item($site.'static_url');
		if(stripos($static_url,'http') === false) $static_url = site_url($static_url).'/';
		return $static_url;
	}
}

if ( ! function_exists('redirect_url'))
{
	function redirect_url($user_type, $redirect_type = 'login', $redirect_url = '')
	{
		$CI =& get_instance();
		if($CI->session->userdata("user_type") == Constant::USER_TYPE_RESEARCHER)
		{
			$CI->load->model("user_data/researcher_data_model");
			$researcher_data = $CI->researcher_data_model->get_researcher_data($CI->session->userdata("user_id"));
			$redirect_url = isset($researcher_data->domain_name)?$researcher_data->domain_name:'';
		}
		return Constant::redirect_url($user_type, $redirect_type, $redirect_url);
	}
}
/* End of file LI_url_helper.php */
/* Location: ./system/helpers/LI_url_helper.php */