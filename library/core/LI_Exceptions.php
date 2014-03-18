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
			if(!isset($redirect)) $redirect = '';
			if(strpos($redirect,'http://') === false) $redirect='';
			$redirect = $redirect?$redirect:$site_url;

			$static_version = '';
			if(file_exists(APPPATH.'config/'.ENVIRONMENT.'/version.php'))
			{
				include(APPPATH.'config/'.ENVIRONMENT.'/version.php');
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
				$file_path = APPPATH.'errors/'.$template.'.php';
			}
		}
		include($file_path);
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}

}
// END Exceptions Class

/* End of file MY_Exceptions.php */
/* Location: ./application/core/MY_Exceptions.php */