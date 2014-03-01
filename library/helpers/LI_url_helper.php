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
/* End of file LI_url_helper.php */
/* Location: ./system/helpers/LI_url_helper.php */