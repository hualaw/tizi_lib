<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class LI_Exceptions extends CI_Exceptions {
	
	public function __construct()
	{
		parent::__construct();
	}

	function show_404($page = '', $log_error = TRUE, $data = array())
	{
		$heading = "404 Page Not Found";
		$message = "The page you requested was not found.";

		// By default we log this, but allow a dev to skip it
		if ($log_error)
		{
			log_message('error', '404 Page Not Found --> '.$page);
		}

		echo $this->show_error($heading, $message, 'error_404', 404, $data);
		exit;
	}

	function show_error($heading, $message, $template = 'error_general', $status_code = 500, $data = array())
	{
		if(!empty($data))
		{
			foreach($data as $k => $d)
			{
				$$k = $d;
			}
		}

		set_status_header($status_code);

		$message = '<p>'.implode('</p><p>', ( ! is_array($message)) ? array($message) : $message).'</p>';


		if (defined('ENVIRONMENT') && ENVIRONMENT == 'production') $template = 'error_404';

		if($template == 'error_404')
		{
			$_ci =& load_class('Config', 'core');
			$site_url = $_ci->site_url();
			$login_url = $_ci->site_url('','login');
			$tizi_url = $_ci->site_url('','tizi');
			$ziyuan_url = $_ci->site_url('','ziyuan');
			if(!isset($redirect)) $redirect = '';
			if(strpos($redirect,'http://') === false) $redirect='';
			$redirect = $redirect?$redirect:$site_url;

			$static_version = '';
			if(!file_exists($file_path = APPPATH.'config/'.ENVIRONMENT.'/version.php'))
			{
				if(!file_exists($file_path = APPPATH.'config/version.php'))
				{
					if(!file_exists($file_path = LIBPATH.'config/'.ENVIRONMENT.'/version.php'))
					{
						if(!file_exists($file_path = LIBPATH.'config/version.php'))
						{
							$file_path = '';
						}
					}
				}
			}

			if($file_path)
			{
				include($file_path);
				if (isset($config['static_version']))
				{
					$static_version = $config['static_version'].'/';
				}
			}

			if(isset($_COOKIE['TZU'])) $uname = true;
			else $uname = false;

			if (defined('ENVIRONMENT') && ENVIRONMENT == 'production') 
			{
				if(!isset($settimeout)) $settimeout = true;
			}
			else
			{
				$settimeout = false;
			}
		}
		
		if (ob_get_level() > $this->ob_level + 1)
		{
			ob_end_flush();
		}
		ob_start();
		if(!file_exists($file_path = APPPATH.'errors/tizi_'.$template.'.php'))
		{
			if(!file_exists($file_path = LIBPATH.'errors/tizi_'.$template.'.php'))
			{
				if(!file_exists($file_path = APPPATH.'errors/'.$template.'.php'))
				{
					$file_path = '';
				}
			}
		}
		if($file_path) include($file_path);
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}

	function show_php_error($severity, $message, $filepath, $line)
	{
		$severity = ( ! isset($this->levels[$severity])) ? $severity : $this->levels[$severity];

		$filepath = str_replace("\\", "/", $filepath);

		// For safety reasons we do not show the full file path
		if (FALSE !== strpos($filepath, '/'))
		{
			$x = explode('/', $filepath);
			$filepath = $x[count($x)-2].'/'.end($x);
		}

		if (ob_get_level() > $this->ob_level + 1)
		{
			ob_end_flush();
		}
		ob_start();
		//include(APPPATH.'errors/error_php.php');
		$template = 'error_php';
		if(!file_exists($file_path = APPPATH.'errors/tizi_'.$template.'.php'))
		{
			if(!file_exists($file_path = LIBPATH.'errors/tizi_'.$template.'.php'))
			{
				if(!file_exists($file_path = APPPATH.'errors/'.$template.'.php'))
				{
					$file_path = '';
				}
			}
		}
		if($file_path) include($file_path);
		$buffer = ob_get_contents();
		ob_end_clean();
		echo $buffer;
	}

}
// END Exceptions Class

/* End of file MY_Exceptions.php */
/* Location: ./application/core/MY_Exceptions.php */