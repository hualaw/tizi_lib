<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class LI_Input extends CI_Input { 

	public function __construct()
	{
		parent::__construct();
	}

	function get($index = NULL, $xss_clean = FALSE)
	{
		// Check if a field has been provided
		if ($index === NULL AND ! empty($_GET))
		{
			$get = array();

			// loop through the full _GET array
			foreach (array_keys($_GET) as $key)
			{
				$get[$key] = trim($this->_fetch_from_array($_GET, $key, $xss_clean));
			}
			return trim($get);
		}

		return $this->_fetch_from_array($_GET, $index, $xss_clean);
	}

	function post($index = NULL, $xss_clean = FALSE)
	{
		// Check if a field has been provided
		if ($index === NULL AND ! empty($_POST))
		{
			$post = array();
			// Loop through the full _POST array and return it
			foreach (array_keys($_POST) as $key)
			{
				if(is_array($_POST[$key]))
				{
					$post[$key] = $this->_fetch_from_array($_POST, $index, $xss_clean);
				}
				else
				{
					$post[$key] = trim($this->_fetch_from_array($_POST, $key, $xss_clean));
				}
				
			}
			return $post;
		}
		else
		{
			$post = NULL;
			if(isset($_POST[$index]) && is_array($_POST[$index]))
			{
				$post = $this->_fetch_from_array($_POST, $index, $xss_clean);
			}
			else
			{
				$post = trim($this->_fetch_from_array($_POST, $index, $xss_clean));
			}
			return $post;
		}
	}

	//修复jsonp不能调用的字符串验证问题
	function _clean_input_keys($str)
	{
		$config = &get_config('config');
		if ( ! preg_match("/^[".$config['permitted_uri_chars']."]+$/i", rawurlencode($str)))
		{
			exit('Disallowed Key Characters.');
		}

		// Clean UTF-8 if supported
		if (UTF8_ENABLED === TRUE)
		{
			$str = $this->uni->clean_string($str);
		}

		return $str;
	}

}

/* End of file MY_Input.php */
/* Location: ./application/core/MY_Input.php */
