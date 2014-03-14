<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class LI_Config extends CI_Config {

	var $_config_paths = array(APPPATH, LIBPATH);

	function __construct()
	{
		if(isset($_SERVER['HTTPS']) && empty($_SERVER['HTTPS'])) $_SERVER['HTTPS'] = "off";
		parent::__construct();
	}

	function load($file = '', $use_sections = TRUE, $fail_gracefully = TRUE)
	{
		$file = ($file == '') ? 'config' : str_replace('.php', '', $file);
		$found = FALSE;
		$loaded = FALSE;

		$check_locations = defined('ENVIRONMENT')
			? array(ENVIRONMENT.'/'.$file, $file)
			: array($file);

		foreach ($this->_config_paths as $path)
		{
			foreach ($check_locations as $location)
			{
				$file_path = $path.'config/'.$location.'.php';

				if (in_array($file, $this->is_loaded, TRUE))//$file_path
				{
					$loaded = TRUE;
					continue 2;
				}

				if (file_exists($file_path))
				{
					$found = TRUE;
					break;
				}
			}

			if ($found === FALSE)
			{
				continue;
			}

			include($file_path);

			if ( ! isset($config) OR ! is_array($config))
			{
				if ($fail_gracefully === TRUE)
				{
					return FALSE;
				}
				show_error('Your '.$file_path.' file does not appear to contain a valid configuration array.');
			}

			if ($use_sections === TRUE)
			{
				if (isset($this->config[$file]))
				{
					$this->config[$file] = array_merge($this->config[$file], $config);
				}
				else
				{
					$this->config[$file] = $config;
				}
			}
			$this->config = array_merge($this->config, $config);

			$this->is_loaded[] = $file;//$file_path
			unset($config);

			$loaded = TRUE;
			log_message('debug', 'Config file loaded: '.$file_path);
			break;
		}

		if ($loaded === FALSE)
		{
			if ($fail_gracefully === TRUE)
			{
				return FALSE;
			}
			show_error('The configuration file '.$file.'.php does not exist.');
		}

		return TRUE;
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
