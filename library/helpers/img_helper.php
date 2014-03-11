<?php 
if(!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('path2img')) {
    function path2img($path, $tag = true) {
		$src = "http://tizi-zujuan-thumb.oss.aliyuncs.com/";
		if($tag && $path) 
		{
			$path = '<img class="pre_img" src="'.$src.$path.'"/>';
		}	
		return $path;
    }  
}

if (!function_exists('path2video')) {
	function path2video($path, $tag = true) {
    	$src="http://tizi-kouyu-video.oss.aliyuncs.com/";
		if($tag && $path) 
		{
			$path = $src.$path;
		}	
		return $path;
    }
}

if (!function_exists('path2avatar')) {
	function path2avatar($user_id,$type=1) {
		$ci =& get_instance();
		$ci->load->config("upload");
		$domain_avatar = $ci->config->item("domain_avatar");
		$folder_avatar = $ci->config->item("folder_avatar");
        $prefix_avatar = $ci->config->item("prefix_avatar");
        $num_pf_avatar = $ci->config->item("num_pf_avatar");
		$path = $domain_avatar.$folder_avatar.alpha_id_num(intval($user_id/$num_pf_avatar)).'/'.md5($prefix_avatar.$user_id.'__avatar'.$type).'.jpg';
		return $path;
	}
}