<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class LI_Exceptions extends CI_Exceptions {
	
	public function __construct()
	{
		parent::__construct();
	}

	function show_404($page = '', $log_error = TRUE)
	{
		$heading = "404 Page Not Found";
		$message = "The page you requested was not found.";

		$_ci =& load_class('Config', 'core');
		$site_url = $_ci->site_url();

		// By default we log this, but allow a dev to skip it
		if ($log_error)
		{
			log_message('error', '404 Page Not Found --> '.$page);
		}

		echo $this->show_error($heading, $message, 'error_404', 404);
		exit;
	}

	function show_error($heading, $message, $template = 'error_general', $status_code = 500)
	{
		set_status_header($status_code);

		$message = '<p>'.implode('</p><p>', ( ! is_array($message)) ? array($message) : $message).'</p>';

		$_ci =& load_class('Config', 'core');
		$site_url = $_ci->site_url();

		if (defined('ENVIRONMENT') && ENVIRONMENT == 'production') $template = 'error_404';
		
		if(isset($_COOKIE['TZU'])) $uname = true;
		else $uname = false;
		
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