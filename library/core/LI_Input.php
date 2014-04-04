<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class LI_Input extends CI_Input { 

	public function __construct()
	{
		parent::__construct();
	}

	function get($index = NULL, $xss_clean = FALSE, $tags_clean = FALSE, $default = FALSE)
	{
		// Check if a field has been provided
		if ($index === NULL AND ! empty($_GET))
		{
			$get = array();

			// loop through the full _GET array
			foreach (array_keys($_GET) as $key)
			{
				$get[$key] = trim($this->_fetch_from_array($_GET, $key, $xss_clean));
				if($tags_clean) $get[$key] = htmlspecialchars(strip_tags($get[$key]));
			}
			return $get;
		}
		else
		{
			$get = $this->_fetch_from_array($_GET, $index, $xss_clean);
			if($tags_clean) $get = htmlspecialchars(strip_tags($get));
			if(!$get && $default !== false) $get = $default;
			return $get;
		}
	}

	function post($index = NULL, $xss_clean = FALSE, $tags_clean = FALSE, $default = FALSE)
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
					if($tags_clean) $post[$key] = htmlspecialchars(strip_tags($post[$key]));
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
				if($tags_clean) $post = htmlspecialchars(strip_tags($post));
				if(!$post && $default !== false) $post = $default;
			}
			return $post;
		}
	}

	function get_post($index = '', $xss_clean = FALSE, $tags_clean = FALSE, $default = FALSE)
	{
		if ( ! isset($_POST[$index]) )
		{
			return $this->get($index, $xss_clean, $tags_clean, $default);
		}
		else
		{
			return $this->post($index, $xss_clean, $tags_clean, $default);
		}
	}

	function post_get($index = '', $xss_clean = FALSE, $tags_clean = FALSE, $default = FALSE)
	{
		return $this->get_post($index, $xss_clean, $tags_clean, $default);
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
