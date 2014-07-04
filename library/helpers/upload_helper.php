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

if (!function_exists('oss_space_image_upload')) {
	function oss_space_image_upload($name, $object = null, $bucket = 'aq', $config = array()){
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

/*user doc_file upload*/
function user_doc_file_upload($name){
	$ci =& get_instance();
	$ci->load->config("upload");
	$pathinfo = pathinfo($_FILES[$name]["name"]);
	$ext = isset($pathinfo["extension"]) ? $pathinfo["extension"] : "";
	$exts_files = $ci->config->item("exts_files");
	$exts_files = explode(",", $exts_files);
	if (!in_array($ext, $exts_files)){
		return -2;	//文件格式不正确
	}
	$size_limit = $ci->config->item("size_files");
	$filesize = $_FILES[$name]["size"];
	if ($filesize > $size_limit){
		return -3;	//文件过大
	}
	$md5 = md5(uniqid());
	$filename = alpha_id(mt_rand(1000000, 9999999)) . "." . $ext;
	$object = "teacher_doc/".date("Ym") . "/" . substr($md5, 3, 2) . "/" . substr($md5, 7,26).$filename;
	$ci->load->library("Oss_Upload");
	$configs = array(
			"oss_access_id"		=> $ci->config->item("oss_access_id"),
			"oss_access_key"	=> $ci->config->item("oss_access_key"),
			"bulket"			=> $ci->config->item("bucket_document"),
			"oss_domain"		=> $ci->config->item("oss_domain")
		);
	$ci->oss_upload->set_config($configs);
	$content = @file_get_contents($_FILES[$name]["tmp_name"]);
	$oss_response = $ci->oss_upload->content_upload($content, $object);
	if ($oss_response->status == 200){
		return $object;
	} else {
		return -1;	//上传失败
	}
}

/*网盘 文档上传 cloud doc_file upload*/
function cloud_doc_file_upload($name){
	$ci =& get_instance();
	$ci->load->config("upload");
	$pathinfo = pathinfo($_FILES[$name]["name"]);
	$ext = isset($pathinfo["extension"]) ? $pathinfo["extension"] : "";
	$exts_files = $ci->config->item("exts_files");
	$exts_files = explode(",", $exts_files);
	if (!in_array($ext, $exts_files)){
		return -2;	//文件格式不正确
	}
	$size_limit = $ci->config->item('cloud_one_file_size');
	$filesize = $_FILES[$name]["size"];
	if ($filesize > $size_limit){
		return -3;	//文件过大
	}
	$md5 = md5(uniqid());
	$filename = alpha_id(mt_rand(1000000, 9999999)) . "." . $ext;
	$object = "teacher_doc/".date("Ym") . "/" . substr($md5, 3, 2) . "/" . substr($md5, 7,26).$filename;
	$ci->load->library("Oss_Upload");
	$configs = array(
			"oss_access_id"		=> $ci->config->item("oss_access_id"),
			"oss_access_key"	=> $ci->config->item("oss_access_key"),
			"bulket"			=> $ci->config->item("bucket_document"),//需要重新设个bucket
			"oss_domain"		=> $ci->config->item("oss_domain")
		);
	$ci->oss_upload->set_config($configs);
	$content = @file_get_contents($_FILES[$name]["tmp_name"]);
	$oss_response = $ci->oss_upload->content_upload($content, $object);
	if ($oss_response->status == 200){
		return $object;
	} else {
		log_message('error_tizi', 'oss_upload_error:'.$_FILES[$name]["name"].'stauts'.$oss_response->status);
		return -1;	//上传失败
	}
}

//检查上传的文件的类型，不属于其中一种就报错；
function cloud_upload_file_type_check($ext){
	$ext = strtolower($ext);
	$ci =& get_instance();
	$ci->load->config("upload");
	$exts_files = $ci->config->item("exts_files");
	$exts_files = explode(",", $exts_files);
	if (in_array($ext, $exts_files)){
		return Constant::CLOUD_FILETYPE_DOC;	//文件格式不正确
	}elseif(in_array($ext,explode(",",Constant::CLOUD_PIC_TYPES))){
		return Constant::CLOUD_FILETYPE_PIC;
	}elseif(in_array($ext,explode(",",Constant::CLOUD_AUDIO_TYPES))){
		return Constant::CLOUD_FILETYPE_AUDIO;
	}elseif(in_array($ext,explode(",",Constant::CLOUD_VIDEO_TYPES))){
		return Constant::CLOUD_FILETYPE_VIDEO;
	}else{
		return Constant::CLOUD_FILETYPE_OTHER;
	}
}


function word_img_upload($name){
	$ci =& get_instance();
	$ci->load->config("upload");
	$pathinfo = pathinfo($_FILES[$name]["name"]);
	$ext = isset($pathinfo["extension"]) ? $pathinfo["extension"] : "";
	/*$exts_files = $ci->config->item("exts_files");
	$exts_files = explode(",", $exts_files);
	if (!in_array($ext, $exts_files)){
		return -2;	//文件格式不正确
	}*/
	$size_limit = $ci->config->item("size_files");
	$filesize = $_FILES[$name]["size"];
	if ($filesize > $size_limit){
		return -3;	//文件过大
	}
	$md5 = md5(uniqid());
	$filename = alpha_id(mt_rand(1000000, 9999999)) . "." . $ext;
	$object = "word_img/".date("Ym") . "/" . substr($md5, 3, 2) . "/" . substr($md5, 7,26).$filename;
	$ci->load->library("Oss_Upload");
	$configs = array(
			"oss_access_id"		=> $ci->config->item("oss_access_id"),
			"oss_access_key"	=> $ci->config->item("oss_access_key"),
			"bulket"			=> $ci->config->item("bucket_document"),
			"oss_domain"		=> $ci->config->item("oss_domain")
		);
	$ci->oss_upload->set_config($configs);
	$content = @file_get_contents($_FILES[$name]["tmp_name"]);
	$oss_response = $ci->oss_upload->content_upload($content, $object);
	if ($oss_response->status == 200){
		return "http://".$configs["bulket"].".oss.aliyuncs.com/".$object;
	} else {
		return -1;	//上传失败
	}
}
