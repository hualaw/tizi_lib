<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * JSON的相关处理
 * 2013-08-31
 * @author jiangwuzhang
 */
if (!function_exists('json_out')){

	function json_out($data = array()){
		echo json_token(array('data' => $data));
		exit;
	}

}

if (!function_exists('json_get')){

	function json_get($data = array()){
		header('Content-type:text/html;charset=utf-8');
		echo json_encode($data, JSON_HEX_TAG);
		exit;
	}

}

// Wangnan Dafen
if( ! function_exists('json_output') )
{
    /**
     * JSON 输出
     *
     * @param  array  $data 数组数据
     * @return [type]       [description]
     * @author wangnan <wangnan@tizi.com>
     */
    function json_output($data = array())
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        die;
    }
}