<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class LI_Session extends CI_Session {

	private $_md5_encrypt = false;
	private $_redis = null;

	public function __construct($params = array())
	{
		log_message('debug', "Session Class Initialized");

		// Set the super object to a local variable for use throughout the class
		$this->CI =& get_instance();

		//tizi load redis
		$this->_redis_load();

		// Set all the session preferences, which can either be set
		// manually via the $params array above or via the config file
		foreach (array('sess_encrypt_cookie', 'sess_use_database', 'sess_table_name', 'sess_expiration', 'sess_expire_on_close', 'sess_match_ip', 'sess_match_useragent', 'sess_cookie_name', 'cookie_path', 'cookie_domain', 'cookie_secure', 'sess_time_to_update', 'time_reference', 'cookie_prefix', 'encryption_key') as $key)
		{
			$this->$key = (isset($params[$key])) ? $params[$key] : $this->CI->config->item($key);
		}

		if ($this->encryption_key == '')
		{
			show_error('In order to use the Session class you are required to set an encryption key in your config file.');
		}

		// Load the string helper so we can use the strip_slashes() function
		$this->CI->load->helper('string');

		// Do we need encryption? If so, load the encryption class
		if ($this->sess_encrypt_cookie == TRUE)
		{
			$this->CI->load->library('encrypt');
		}

		// Are we using a database?  If so, load it
		if ($this->sess_use_database === TRUE AND $this->sess_table_name != '')
		{
			$this->CI->load->database();
		}

		// Set the "now" time.  Can either be GMT or server time, based on the
		// config prefs.  We use this to set the "last activity" time
		$this->now = $this->_get_time();

		// Set the session length. If the session expiration is
		// set to zero we'll set the expiration two years from now.
		if ($this->sess_expiration == 0)
		{
			$this->sess_expiration = (60*60*24*365*2);
		}

		// Set the cookie name
		$this->sess_cookie_name = $this->cookie_prefix.$this->sess_cookie_name;

		// Run the Session routine. If a session doesn't exist we'll
		// create a new one.  If it does, we'll update it.
		if ( ! $this->sess_read())
		{
			$this->sess_create();
		}
		else
		{
			$this->sess_update();
		}

		// Delete 'old' flashdata (from last request)
		$this->_flashdata_sweep();

		// Mark all new flashdata as old (data will be deleted before next request)
		$this->_flashdata_mark();

		// Delete expired sessions if necessary
		$this->_sess_gc();

		log_message('debug', "Session routines successfully run");
	}

	private function _redis_load()
	{
		if($this->_redis !== false && !$this->CI->input->cookie('_nrd') && extension_loaded('redis'))
		{
			$this->CI->config->load('redis', TRUE, TRUE);
			$redis_config = $this->CI->config->item('redis');
			$config = $redis_config['redis_default'];
			//$config['timeout'] = 0.1;

			$redis = new Redis();
			try
			{
				$return = $redis->connect($config['host'], $config['port'], $config['timeout']);
			}
			catch (RedisException $e)
			{
				log_message('error_tizi', '10070:Redis session connection refused. '.$e->getMessage());
				$return = false;
			}
			if($return)
			{
				$redis->auth($config['password']);
				$redis->select($redis_config['redis_db']['session']);
			}
			else
			{
				$this->CI->input->set_cookie('_nrd','1',0);
				$redis = false;
			}
			$this->_redis = $redis;
		}
	}

	private function _redis_set($key, $value, $ttl)
	{
		if($this->_redis) return $this->_redis->set($key, $value, $ttl);
		else log_message('error_tizi', '100706:session redis set failed');
	}

	private function _redis_get($key)
	{
		if($this->_redis) return $this->_redis->get($key);
		else log_message('error_tizi', '100707:session redis get failed');
	}

	private function _redis_del($key)
	{
		if($this->_redis) return $this->_redis->del($key);
		else log_message('error_tizi', '100708:session redis del failed');
	}

	private function _redis_close()
	{
		if($this->_redis) return $this->_redis->close();
		else log_message('error_tizi', '100709:session redis close failed');
	}

	function sess_read()
	{
		// Fetch the cookie
		$session = $this->CI->input->cookie($this->sess_cookie_name);
		
		//tizi check post session_id
		$segment = $this->CI->uri->segment_array();
		$session_post = false;
		if(!empty($segment) && $segment[1] == 'download') $session_post = $this->CI->input->get('session_id',true);
		else if(!empty($segment) && $segment[1] == 'upload') $session_post = $this->CI->input->post('session_id',true);
		if ($session === FALSE) $session = $session_post;

		// No cookie?  Goodbye cruel world!...
		if ($session === FALSE)
		{
			log_message('trace_tizi','session_cookie_not_found');
			log_message('debug', 'A session cookie was not found.');
			return FALSE;
		}

		//tizi do not match useragent when post session id
		if ($session === $session_post) $this->sess_match_useragent = false;

		// Decrypt the cookie data
		if ($this->sess_encrypt_cookie == TRUE)
		{
			$session = $this->CI->encrypt->decode($session);
		}
		else if($this->_md5_encrypt)
		{
			// encryption was not used, so we need to check the md5 hash
			$hash	 = substr($session, strlen($session)-32); // get last 32 chars
			$session = substr($session, 0, strlen($session)-32);

			// Does the md5 hash match?  This is to prevent manipulation of session data in userspace
			if ($hash !==  md5($session.$this->encryption_key))
			{
				log_message('error', 'The session cookie data did not match what was expected. This could be a possible hacking attempt.');
				$this->sess_destroy();
				return FALSE;
			}
		}

		//tizi
		if ($this->sess_use_database === TRUE)
		{
			$session_id = $session;
			$session = array();
			//redis test
			if($this->_redis)
			{
				$userdata = $this->_redis_get($session_id);
				if(empty($userdata)) $userdata = json_decode($userdata,true);
				$session = $userdata;
			}
			
			if(empty($session))
			{
				$this->CI->db->where('session_id',$session_id);
				$query = $this->CI->db->get($this->sess_table_name);

				// No result?  Kill it!
				if ($query->num_rows() == 0)
				{
					log_message('trace_tizi','session_destroy_unload_session');
					$this->sess_destroy();
					return FALSE;
				}

				// Is there custom data?  If so, add it to the main session array
				$session = $query->row_array();
			}

			if (isset($session['user_data']) AND $session['user_data'] != '')
			{
				$custom_data = $this->_unserialize($session['user_data']);

				if (is_array($custom_data))
				{
					foreach ($custom_data as $key => $val)
					{
						$session[$key] = $val;
					}
				}
				unset($session['user_data']);
			}
		}
		else
		{
			// Unserialize the session array
			$session = $this->_unserialize($session);
		}

		// Is the session data we unserialized an array with the correct format?
		if ( ! is_array($session) OR ! isset($session['session_id']) OR ! isset($session['ip_address']) OR ! isset($session['user_agent']) OR ! isset($session['last_activity']))
		{
			log_message('trace_tizi','session_destroy_session_info');
			$this->sess_destroy();
			return FALSE;
		}

		// Is the session current?
		if (($session['last_activity'] + $this->sess_expiration) < $this->now)
		{
			log_message('trace_tizi','session_destroy_last_activity');
			$this->sess_destroy();
			return FALSE;
		}

		// Does the IP Match?
		if ($this->sess_match_ip == TRUE AND $session['ip_address'] != $this->CI->input->ip_address())
		{
			log_message('trace_tizi','session_destroy_ip_address');
			$this->sess_destroy();
			return FALSE;
		}

		// Does the User Agent Match?
		if ($this->sess_match_useragent == TRUE AND trim($session['user_agent']) != trim(substr($this->CI->input->user_agent(), 0, 120)))
		{
			log_message('trace_tizi','session_destroy_ip_address');
			$this->sess_destroy();
			return FALSE;
		}

		// Session is valid!
		$this->userdata = $session;
		unset($session);

		return TRUE;
	}

	function sess_write()
	{
		// Are we saving custom data to the DB?  If not, all we do is update the cookie
		if ($this->sess_use_database === FALSE)
		{
			$this->_set_cookie();
			return;
		}

		// set the custom userdata, the session data we will set in a second
		$custom_userdata = $this->userdata;
		$cookie_userdata = array();

		// Before continuing, we need to determine if there is any custom data to deal with.
		// Let's determine this by removing the default indexes to see if there's anything left in the array
		// and set the session data while we're at it
		foreach (array('session_id','ip_address','user_agent','last_activity') as $val)
		{
			unset($custom_userdata[$val]);
			$cookie_userdata[$val] = $this->userdata[$val];
		}

		// Did we find any custom data?  If not, we turn the empty array into a string
		// since there's no reason to serialize and store an empty array in the DB
		if (count($custom_userdata) === 0)
		{
			$custom_userdata = '';
		}
		else
		{
			// Serialize the custom data array so we can store it
			$custom_userdata = $this->_serialize($custom_userdata);
		}

		// Run the update query
		$this->CI->db->where('session_id', $this->userdata['session_id']);
		$this->CI->db->update($this->sess_table_name, array('last_activity' => $this->userdata['last_activity'], 'user_data' => $custom_userdata));

		$userdata = $this->_redis_get($this->userdata['session_id']);
		$userdata = json_decode($userdata,true);
		$userdata['last_activity'] = $this->userdata['last_activity'];
		$userdata['user_data'] = $custom_userdata;
		$this->_redis_set($this->userdata['session_id'],json_encode($userdata),$this->sess_expiration);
		// Write the cookie.  Notice that we manually pass the cookie data array to the
		// _set_cookie() function. Normally that function will store $this->userdata, but
		// in this case that array contains custom data, which we do not want in the cookie.
		
		//tizi 更新数据不再更新cookie
		//$this->_set_cookie($cookie_userdata);
	}

	function sess_create()
	{
		$sessid = '';
		while (strlen($sessid) < 32)
		{
			$sessid .= mt_rand(0, mt_getrandmax());
		}

		// To make the session ID even more secure we'll combine it with the user's IP
		$sessid .= $this->CI->input->ip_address();

		$this->userdata = array(
							'session_id'	=> md5(uniqid($sessid, TRUE)),
							'ip_address'	=> $this->CI->input->ip_address(),
							'user_agent'	=> substr($this->CI->input->user_agent(), 0, 120),
							'last_activity'	=> $this->now,
							'user_data'		=> ''
							);


		// Save the data to the DB if needed
		if ($this->sess_use_database === TRUE)
		{
			$this->CI->db->query($this->CI->db->insert_string($this->sess_table_name, $this->userdata));
			$this->_redis_set($this->userdata['session_id'],json_encode($this->userdata),$this->sess_expiration);
		}

		// Write the cookie
		$this->_set_cookie();
	}

	function sess_update()
	{
		// We only update the session every five minutes by default
		if (($this->userdata['last_activity'] + $this->sess_time_to_update) >= $this->now)
		{
			return;
		}

		// Save the old session id so we know which record to
		// update in the database if we need it
		$old_sessid = $this->userdata['session_id'];
		$new_sessid = '';
		while (strlen($new_sessid) < 32)
		{
			$new_sessid .= mt_rand(0, mt_getrandmax());
		}

		// To make the session ID even more secure we'll combine it with the user's IP
		$new_sessid .= $this->CI->input->ip_address();

		// Turn it into a hash
		$new_sessid = md5(uniqid($new_sessid, TRUE));

		// Update the session data in the session data array
		$this->userdata['session_id'] = $new_sessid;
		$this->userdata['last_activity'] = $this->now;

		// _set_cookie() will handle this for us if we aren't using database sessions
		// by pushing all userdata to the cookie.
		$cookie_data = NULL;

		// Update the session ID and last_activity field in the DB if needed
		if ($this->sess_use_database === TRUE)
		{
			// set cookie explicitly to only have our session data
			$cookie_data = array();
			foreach (array('session_id','ip_address','user_agent','last_activity') as $val)
			{
				$cookie_data[$val] = $this->userdata[$val];
			}

			$this->CI->db->query($this->CI->db->update_string($this->sess_table_name, array('last_activity' => $this->now, 'session_id' => $new_sessid), array('session_id' => $old_sessid)));
			
			$userdata = $this->_redis_get($old_sessid);
			$userdata = json_decode($userdata,true);
			$userdata['last_activity'] = $this->now;
			$userdata['session_id'] = $new_sessid;
			$this->_redis_set($new_sessid,json_encode($userdata),$this->sess_expiration);
			//$this->_redis_del($old_sessid);
			$this->_redis_set($old_sessid,json_encode($userdata),120);
		}

		// Write the cookie
		$this->_set_cookie($cookie_data);
	}

	function sess_destroy()
	{
		// Kill the session DB row
		if ($this->sess_use_database === TRUE && isset($this->userdata['session_id']))
		{
			$this->CI->db->where('session_id', $this->userdata['session_id']);
			$this->CI->db->delete($this->sess_table_name);

			$this->_redis_del($this->userdata['session_id']);
		}

		// Kill the cookie
		setcookie(
					$this->sess_cookie_name,
					addslashes(serialize(array())),
					($this->now - 31500000),
					$this->cookie_path,
					$this->cookie_domain,
					0
				);

		// Kill session data
		$this->userdata = array();
	}

	function _set_cookie($cookie_data = NULL)
	{
		if (is_null($cookie_data))
		{
			$cookie_data = $this->userdata;
		}

		//tizi
		if ($this->sess_use_database == TRUE && is_array($cookie_data) && isset($cookie_data['session_id']))
		{
			$cookie_data = $cookie_data['session_id'];
		}
		else
		{
			// Serialize the userdata for the cookie
			$cookie_data = $this->_serialize($cookie_data);
		}

		if ($this->sess_encrypt_cookie == TRUE)
		{
			$cookie_data = $this->CI->encrypt->encode($cookie_data);
		}
		else if ($this->_md5_encrypt)
		{
			// if encryption is not used, we provide an md5 hash to prevent userside tampering
			$cookie_data = $cookie_data.md5($cookie_data.$this->encryption_key);
		}

		$expire = ($this->sess_expire_on_close === TRUE) ? 0 : $this->sess_expiration + time();

		// Set the cookie
		setcookie(
					$this->sess_cookie_name,
					$cookie_data,
					$expire,
					$this->cookie_path,
					$this->cookie_domain,
					$this->cookie_secure
				);
	}

}
// END Session Class

/* End of file MY_Session.php */
/* Location: ./application/libraries/MY_Session.php */