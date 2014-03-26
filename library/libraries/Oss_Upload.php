<?php
if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 
 * oss上传附件类
 * 
 */
require_once LIBPATH.'third_party/oss_sdk/sdk.class.php';
class Oss_Upload {
	
	private $_oss_access_id;
	
	private $_oss_access_key;
	
	private $_bulket;
	
	private $_oss_domain;
	
	private $_oss;
	
	public function __construct(){

	}
	
	public function set_config(array $configs){
		$ci =& get_instance();
		$ci->load->config('upload');
		$this->_oss_access_id	= isset($configs["oss_access_id"]) ? 
							$configs["oss_access_id"] : $ci->config->item("oss_access_id");
		$this->_oss_access_key	= isset($configs["oss_access_key"]) ? 
							$configs["oss_access_key"] : $ci->config->item("oss_access_key");
		$this->_bulket			= isset($configs["bulket"]) ? 
							$configs["bulket"] : $ci->config->item("bucket_aq");
		$this->_oss_domain		= isset($configs["oss_domain"]) ? 
							$configs["oss_domain"] : $ci->config->item("oss_domain");
		$this->_oss = new ALIOSS($this->_oss_access_id, $this->_oss_access_key, $this->_oss_domain);
		$this->_oss->set_debug_mode(false); //turn off debug info output to stderr
	}
	
	public function content_upload($content, $object){
		$options = array(
			"content" => $content,
			"length" => strlen($content)
		);
		return $this->_oss->upload_file_by_content($this->_bulket, $object, $options);
	}
	
}
