<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('oss_image_upload')) {
	function oss_image_upload($name, $object = null, $bucket = 'aq', $config = array()){
		$ci =& get_instance();
		$ci->load->config("upload");
		if (!isset($_FILES[$name]["error"]) || $_FILES[$name]["error"] != 0){
			return -1;	//上传失败
		}
		
		$pathinfo = pathinfo($_FILES[$name]["name"]);
		$ext = isset($pathinfo["extension"]) ? $pathinfo["extension"] : "";
		$exts = $ci->config->item("exts_".$bucket);
		$exts = explode(",", $exts);
		if (!in_array($ext, $exts)){
			return -2;	//文件格式不正确
		}
		
		$maxwidth = $ci->config->item("max_width_".$bucket);
		$maxheight = $ci->config->item("max_height_".$bucket);
		$size_limit = $ci->config->item("size_".$bucket);
		
		$content = resize_image($_FILES[$name]["tmp_name"], $maxwidth, $maxheight);
		$filesize = strlen($content);
		if ($filesize > $size_limit){
			return -3;	//文件过大
		}
		
		$md5 = md5(uniqid());
		$filename = alpha_id(mt_rand(1000000, 9999999)) . "." . $ext;
		if(!$object) $object = date("Ym") . "/" . substr($md5, 3, 2) . "/" . substr($md5, 7,26).$filename;
		$ci->load->library("Oss_Upload");
		$configs = array(
			"oss_access_id"		=> $ci->config->item("oss_access_id"),
			"oss_access_key"	=> $ci->config->item("oss_access_key"),
			"bulket"			=> $ci->config->item("bucket_".$bucket),
			"oss_domain"		=> $ci->config->item("oss_domain")
		);
		//$configs = array_merge($configs,$config);
		$ci->oss_upload->set_config($configs);
		$oss_response = $ci->oss_upload->content_upload($content, $object);
		
		if ($oss_response->status == 200){
			return "http://".$configs["bulket"].".oss.aliyuncs.com/".$object;
		} else {
			return -1;	//上传失败
		}
	}
}

if (!function_exists('resize_image')) {
	function resize_image($filepath, $maxwidth, $maxheight){
		require_once LIBPATH."third_party/phpthumb/ThumbLib.inc.php";
		$thumb = PhpThumbFactory::create($filepath);
		$thumb->resize($maxwidth, $maxheight);
		$content = $thumb->getImageAsString();
		return $content;
	}
}
