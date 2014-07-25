<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * area的一些替换
 * @author jiangwuzhang
 */
if (!function_exists('province2city')){
	
	function province2city($province_id){
		$rel = array(
			2 => 52,
			25 => 321,
			27 => 343,
			32 => 394,
			33 => 395
		);
		if (array_key_exists($province_id, $rel)){
			return $rel[$province_id];
		}
		return $province_id;
	}
	
}


if (!function_exists('ismunicipality')){
	
	function ismunicipality($province_id){
		$rel = array(
			2 => 52,
			25 => 321,
			27 => 343,
			32 => 394,
			33 => 395
		);
		return array_key_exists($province_id, $rel) or in_array($province_id, $rel) ? true : false;
	}
	
}