<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('login_url'))
{
	function login_url($uri = '')
	{
		$CI =& get_instance();
		return $CI->config->site_url($uri,'login');
	}
}

if ( ! function_exists('tizi_url'))
{
	function tizi_url($uri = '')
	{
		$CI =& get_instance();
		return $CI->config->site_url($uri,'tizi');
	}
}

if ( ! function_exists('jxt_url'))
{
	function jxt_url($uri = '')
	{
		$CI =& get_instance();
		return $CI->config->site_url($uri,'jxt');
	}
}

if ( ! function_exists('static_url'))
{
	function static_url($site = '')
	{
		if($site) $site = $site.'-';
		$CI =& get_instance();
		return $CI->config->item($site.'static_url');
	}
}
/* End of file LI_url_helper.php */
/* Location: ./system/helpers/LI_url_helper.php */