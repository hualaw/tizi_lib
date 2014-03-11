<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class LI_Exceptions extends CI_Exceptions {
	
	public function __construct()
	{
		parent::__construct();
	}

	function show_error($heading, $message, $template = 'error_general', $status_code = 500)
	{
		set_status_header($status_code);

		$message = '<p>'.implode('</p><p>', ( ! is_array($message)) ? array($message) : $message).'</p>';


		if (defined('ENVIRONMENT') && ENVIRONMENT == 'production') $template = 'error_404';

		if($template == 'error_404')
		{
			$_ci =& load_class('Config', 'core');
			$site_url = $_ci->site_url();
			$login_url = $_ci->site_url('','login');
			$tizi_url = $_ci->site_url('','tizi');

			$settimeout = false;
			$static_version = '';
			if(file_exists(APPPATH.'config'.DS.ENVIRONMENT.DS.'version.php'))
			{
				require_once(APPPATH.'config'.DS.ENVIRONMENT.DS.'version.php');
				if (isset($config['static_version']))
				{
					$static_version = $config['static_version'].'/';
				}
			}

			if(isset($_COOKIE['TZU'])) $uname = true;
			else $uname = false;

			if (defined('ENVIRONMENT') && ENVIRONMENT == 'production') $settimeout = true;
		}
		
		if (ob_get_level() > $this->ob_level + 1)
		{
			ob_end_flush();
		}
		ob_start();
		include(APPPATH.'errors/'.$template.'.php');
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}

}
// END Exceptions Class

/* End of file MY_Exceptions.php */
/* Location: ./application/core/MY_Exceptions.php */