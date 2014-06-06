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

if ( ! function_exists('jia_url'))
{
	function jia_url($uri = '')
	{
		return site_url($uri,'jia');
	}
}

if ( ! function_exists('xue_url'))
{
	function xue_url($uri = '')
	{
		return site_url($uri,'xue');
	}
}

if ( ! function_exists('survey_url'))
{
	function survey_url($uri = '')
	{
		return site_url($uri,'survey');
	}
}

if ( ! function_exists('bbs_url'))
{
	function bbs_url($uri = '')
	{
		return site_url($uri,'bbs');
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
		return Constant::redirect_url($user_type, $redirect_type, $redirect_url);
	}
}
/* End of file LI_url_helper.php */
/* Location: ./system/helpers/LI_url_helper.php */