<?php 
if(!defined("BASEPATH")) exit("No direct script access allowed");

if (!function_exists("cloud_mparse")) {
    function cloud_mparse($msize){
		if ($msize >= 1024 && $msize < 1048576){
			return ceil($msize / 1024)."G";
		} else if ($msize >= 1048576){
			return ceil($msize / 1048576)."T";
		} else {
			return $msize."M";
		}
	}
}