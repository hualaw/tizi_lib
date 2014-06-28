<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('load_class'))
{
	function &load_class($class, $directory = 'libraries', $prefix = 'CI_')
	{
		static $_classes = array();

		// Does the class exist?  If so, we're done...
		if (isset($_classes[$class]))
		{
			return $_classes[$class];
		}

		$name = FALSE;

		// Look for the class first in the local application/libraries folder
		// then in the native system/libraries folder
		foreach (array(APPPATH, LIBPATH, BASEPATH) as $path)
		{
			if (file_exists($path.$directory.'/'.$class.'.php'))
			{
				$name = $prefix.$class;

				if (class_exists($name) === FALSE)
				{
					require($path.$directory.'/'.$class.'.php');
				}

				break;
			}
		}

		// Is the request a class extension?  If so we load it too
		if (file_exists(LIBPATH.$directory.'/'.config_item('libclass_prefix').$class.'.php'))
		{
			$name = config_item('libclass_prefix').$class;

			if (class_exists($name) === FALSE)
			{
				require(LIBPATH.$directory.'/'.config_item('libclass_prefix').$class.'.php');
			}
		}

		// Is the request a class extension?  If so we load it too
		if (file_exists(APPPATH.$directory.'/'.config_item('subclass_prefix').$class.'.php'))
		{
			$name = config_item('subclass_prefix').$class;

			if (class_exists($name) === FALSE)
			{
				require(APPPATH.$directory.'/'.config_item('subclass_prefix').$class.'.php');
			}
		}

		// Did we find the class?
		if ($name === FALSE)
		{
			// Note: We use exit() rather then show_error() in order to avoid a
			// self-referencing loop with the Excptions class
			exit('Unable to locate the specified class: '.$class.'.php');
		}

		// Keep track of what we just loaded
		is_loaded($class);

		$_classes[$class] = new $name();
		return $_classes[$class];
	}
}

if ( ! function_exists('get_config'))
{
	function &get_config($replace = array())
	{
		static $_config;

		if (isset($_config))
		{
			return $_config[0];
		}

		// Is the config file in the environment folder?
		if ( ! defined('ENVIRONMENT') OR ! file_exists($lib_file_path = LIBPATH.'config/'.ENVIRONMENT.'/config.php'))
		{
			$lib_file_path = LIBPATH.'config/config.php';
		}

		if (file_exists($lib_file_path))
		{
			require($lib_file_path);
		}

		$exist_lib = false;
		if ( isset($config) AND is_array($config))
		{
			$exist_lib = true;
		}

		// Is the config file in the environment folder?
		if ( ! defined('ENVIRONMENT') OR ! file_exists($file_path = APPPATH.'config/'.ENVIRONMENT.'/config.php'))
		{
			$file_path = APPPATH.'config/config.php';
		}

		// Fetch the config file
		if (! file_exists($file_path))
		{
			if( ! $exist_lib) exit('The configuration file does not exist.');
		}
		else
		{
			require($file_path);
		}

		// Does the $config array exist in the file?
		if ( ! isset($config) OR ! is_array($config))
		{
			exit('Your config file does not appear to be formatted correctly.');
		}

		// Are any values being dynamically replaced?
		if (count($replace) > 0)
		{
			foreach ($replace as $key => $val)
			{
				if (isset($config[$key]))
				{
					$config[$key] = $val;
				}
			}
		}

		return $_config[0] =& $config;
	}
}

if ( ! function_exists('show_404'))
{
	function show_404($page = '', $log_error = TRUE, $data = array())
	{
		$_error =& load_class('Exceptions', 'core');
		$_error->show_404($page, $log_error, $data);
		exit;
	}
}

/* End of file Common.php */
/* Location: ./system/core/Common.php */