<?php 
if(!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('generate_token')) {
    function generate_token($page_name,$force_generate=false) 
    {
		$ci = &get_instance();
		$csrf_token=$ci->page_token->get_csrf_token($page_name);
		if(!$csrf_token || $force_generate) $csrf_token=$ci->page_token->generate_csrf_token($page_name);
		return $csrf_token;
    }   
}

if (!function_exists('json_ntoken')) {
    function json_ntoken($param) 
    {
    	$ci = &get_instance();
    	$callback_name=$ci->input->get_post('callback_name',true);
    	$param['callback']=$callback_name?true:false;
    	
		header('Content-type:text/html;charset=utf-8');
		if($callback_name) return $callback_name.'('.json_encode($param, JSON_HEX_TAG).')';
		return json_encode($param, JSON_HEX_TAG);
    }
}

if (!function_exists('json_token')) {
    function json_token($param) 
    {
        $ci = &get_instance();
		$page_name=$ci->input->post('page_name',true);
		$callback_name=$ci->input->get_post('callback_name',true);
		$param['token']=$page_name?generate_token($page_name):'';
		$param['callback']=$callback_name?true:false;

		header('Content-type:text/html;charset=utf-8');
		if($callback_name) return $callback_name.'('.json_encode($param, JSON_HEX_TAG).')';
		return json_encode($param, JSON_HEX_TAG);
    }
}

if (!function_exists('page_uri')) {
    function page_uri() 
    {
        $ci = &get_instance();
		$page_uri = array();
		$segment=$ci->uri->segment_array();
		if(empty($segment)) $segment=$ci->uri->rsegment_array();
		if(!empty($segment))
		{
			foreach($segment as $k => $seg)
			{
				$page_uri[$k]= $seg;	
				if($k==3) break;
			}
			if(!empty($page_uri)) $page_uri = implode('_',$page_uri);
			else $page_uri = '';
			
			$page_uri = md5($page_uri);
	        return $page_uri;
	    }
	    else
	    {
	    	return '';
	    }
    }
}
