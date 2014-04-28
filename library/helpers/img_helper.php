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

/*
    从七牛上获取图片   mode，long，short都是七牛接口的参数
*/
if (!function_exists('qiniu_img')) {
    function qiniu_img($key,$mode=0,$short=0,$long=600,$ttl=3600) {
        $ci =& get_instance();
        $ci->load->model('redis/redis_model');
        $redis_key = $key.'_'.$mode.'_'.$short.'_'.$long;
        if($ci->redis_model->connect('qiniu_img')){ //连得上redis，取的到值就直接返回值
            $path = $ci->cache->redis->get($redis_key);
            if($path !== false){ //取的到值就直接返回值
                return $path ;
            }
        }
        // var_dump($key);die;
        //连不上redis或者redis中没有相应的值,就去七牛上获取，然后存入redis
        $ci->load->library('qiniu');
        $path = $ci->qiniu->qiniu_get_image($key,$mode,$short,$long);
        if($path){
            $ci->cache->redis->save($redis_key,$path,$ttl);
            return $path;
        }
        return false;
    }
}