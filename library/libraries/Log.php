<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Logging Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Logging
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/general/errors.html
 */
class CI_Log {
	//tizi
	protected $_allow_error_level = array('error_tizi', 'info_tizi', 'trace_tizi');	
	protected $_log;

	//ci
	protected $_log_path;
	protected $_threshold	= 1;
	protected $_date_fmt	= 'Y-m-d H:i:s';
	protected $_enabled	= TRUE;
	protected $_levels	= array('ERROR' => '1', 'DEBUG' => '2',  'INFO' => '3', 'ALL' => '4');

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->_log = Logger::getLogger(__CLASS__);

		$config =& get_config();		

		if (is_numeric($config['log_threshold']))
		{
			$this->_threshold = $config['log_threshold'];

			if($this->_threshold > 0)
			{
				$this->_log_path = ($config['log_path'] != '') ? $config['log_path'] : APPPATH.'logs/';

				if ( ! is_dir($this->_log_path) OR ! is_really_writable($this->_log_path))
				{
					$this->_enabled = FALSE;
				}

				if ($config['log_date_format'] != '')
				{
					$this->_date_fmt = $config['log_date_format'];
				}

				if ($this->_enabled === FALSE)
				{
					return FALSE;
				}
			}
			else
			{
				return FALSE;
			}
		}		
	}

	// --------------------------------------------------------------------

	/**
	 * Write Log File
	 *
	 * Generally this function will be called using the global log_message() function
	 *
	 * @param	string	the error level
	 * @param	string	the error message
	 * @param	bool	whether the error is a native PHP error
	 * @return	bool
	 */
	public function write_log($level = 'error_tizi', $msg, $php_error = FALSE)
	{	
		//local debug
		if($this->_threshold > 0) $this->ci_write_log(str_replace('_tizi', '', $level), $msg);

		//php4log
		if (!in_array($level, $this->_allow_error_level)){
			return;
		}
		$ci =& get_instance();
		if(isset($ci->session)) $user_id = $ci->session->userdata("user_id");
		else $user_id = 0;
		
		$body = date("Y-m-d H:i:s", $_SERVER['REQUEST_TIME']);
		$body .= "\t" . $ci->input->ip_address();
		$body .= "\t";
		$body .= $user_id > 0 ? "UID:".$user_id : "UID:NONE";
		$body .= "\t" . $msg;
		$body .= $php_error !== false ? "\t" . json_encode($php_error) : "";
		switch ($level){
			case 'error_tizi' : $this->_log->error($body);break;
			case 'info_tizi' : $this->_log->info($body);break;
			case 'trace_tizi' : $this->_log->trace($body);break;
			default:"";break;
		}
	}

	/**
	 * Write Log File
	 *
	 * Generally this function will be called using the global log_message() function
	 *
	 * @param	string	the error level
	 * @param	string	the error message
	 * @param	bool	whether the error is a native PHP error
	 * @return	bool
	 */
	public function ci_write_log($level = 'error', $msg, $php_error = FALSE)
	{
		if(in_array($level,array('trace'))) $level = 'error';
		$msg .= $php_error !== false ? "\t" . json_encode($php_error) : "";

		$level = strtoupper($level);

		if ( ! isset($this->_levels[$level]) OR ($this->_levels[$level] > $this->_threshold))
		{
			return FALSE;
		}

		$filepath = $this->_log_path.'log-'.date('Y-m-d').'.php';
		$message  = '';

		if ( ! file_exists($filepath))
		{
			$message .= "<"."?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?".">\n\n";
		}

		if ( ! $fp = @fopen($filepath, FOPEN_WRITE_CREATE))
		{
			return FALSE;
		}

		$message .= $level.' '.(($level == 'INFO') ? ' -' : '-').' '.date($this->_date_fmt). ' --> '.$msg."\n";

		flock($fp, LOCK_EX);
		fwrite($fp, $message);
		flock($fp, LOCK_UN);
		fclose($fp);

		@chmod($filepath, FILE_WRITE_MODE);
		return TRUE;
	}
}
// END Log Class

/* End of file Log.php */
/* Location: ./system/libraries/Log.php */