<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class LI_Config extends CI_Config {

	var $_config_paths = array(APPPATH, LIBPATH);

	function __construct()
	{
		if(isset($_SERVER['HTTPS']) && empty($_SERVER['HTTPS'])) $_SERVER['HTTPS'] = "off";
		parent::__construct();
	}

	function site_url($uri = '', $url_prefix = 'base')
	{
		if (!$this->slash_item($url_prefix.'_url')&&!$this->config[$url_prefix.'_url'])
		{
			$this->set_item($url_prefix.'_url', $this->slash_item('base_url'));
		}

		if ($uri == '')
		{
			return $this->slash_item($url_prefix.'_url').$this->item('index_page');
		}

		if ($this->item('enable_query_strings') == FALSE)
		{
			$suffix = ($this->item('url_suffix') == FALSE) ? '' : $this->item('url_suffix');
			return $this->slash_item($url_prefix.'_url').$this->slash_item('index_page').$this->_uri_string($uri).$suffix;
		}
		else
		{
			return $this->slash_item($url_prefix.'_url').$this->item('index_page').'?'.$this->_uri_string($uri);
		}
	}

}

// END LI_Config class

/* End of file LI_Config.php */
/* Location: ./library/core/LI_Config.php */
