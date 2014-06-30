<?php 
if(!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('path2img')) {
    function path2img($path, $tag = true) {
		$src = "http://tizi-zujuan-thumb.oss.aliyuncs.com/";
		if($tag && $path) 
		{
			$path = '<img class="pre_img" src="'.$src.ltrim($path,'/').'"/>';
		}	
		return $path;
    }  
}

if (!function_exists('path2video')) {
	function path2video($path, $tag = true) {
    	$src="http://tizi-kouyu-video.oss.aliyuncs.com/";
		if($tag && $path) 
		{
			$path = $src.ltrim($path,'/');
		}	
		return $path;
    }
}

if (!function_exists('path2champion')) {
	function path2champion($path, $tag = true) {
    	$src="http://tizi.oss.aliyuncs.com/";
		if($tag && $path)
		{
			$path = $src.ltrim($path,'/');
		}
		return $path;
    }
}

if (!function_exists('path2avatar')) {
	function path2avatar($user_id,$type=1) {
		$ci =& get_instance();
		$ci->load->config("upload",true,true);
		$avatar = $ci->config->item("upload");
		$domain_avatar = isset($avatar["domain_avatar"])?$avatar["domain_avatar"]:'';
		$folder_avatar = isset($avatar["folder_avatar"])?$avatar["folder_avatar"]:'';
        $prefix_avatar = isset($avatar["prefix_avatar"])?$avatar["prefix_avatar"]:'';
        $num_pf_avatar = isset($avatar["num_pf_avatar"])?$avatar["num_pf_avatar"]:'';
        if(isset($avatar["num_pf_avatar"]))
        {
        	$num_pf_avatar = alpha_id_num(intval($user_id/$avatar["num_pf_avatar"]));
        }
		$path = $domain_avatar.$folder_avatar.$num_pf_avatar.'/'.md5($prefix_avatar.$user_id.'__avatar'.$type).'.jpg';
		return $path;
	}
}

if (!function_exists('path2spaceAvatar')) {
	function path2spaceAvatar($user_id,$type=1) {
		$ci =& get_instance();
		$ci->load->config("upload",true,true);
		$avatar = $ci->config->item("upload");
		$domain_avatar = isset($avatar["domain_avatar"])?$avatar["domain_avatar"]:'';
		$folder_avatar = isset($avatar["space_folder_avatar"])?$avatar["space_folder_avatar"]:'';
        $prefix_avatar = isset($avatar["space_prefix_avatar"])?$avatar["space_prefix_avatar"]:'';
        $num_pf_avatar = isset($avatar["space_num_pf_avatar"])?$avatar["space_num_pf_avatar"]:'';
        if(isset($avatar["space_num_pf_avatar"]))
        {
        	$num_pf_avatar = alpha_id_num(intval($user_id/$avatar["space_num_pf_avatar"]));
        }
		$path = $domain_avatar.$folder_avatar.$num_pf_avatar.'/'.md5($prefix_avatar.$user_id.'__avatar'.$type).'.jpg';
		return $path;
	}
}
