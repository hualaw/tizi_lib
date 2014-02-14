<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once('Fluent/Fluent/Autoloader.php');

class CI_Fluent{

    private $_CI;

    protected $fluent = null;

    protected $access = '';

    protected $business_allow = array();

    protected static $default_config = array(
        'host' => 'localhost',
        'port' => '24224',
        'access' => 'tizi.access',
        'business_allow' => array()
    );

    protected $default_data = array(
        'uid' => 'nginx_uid',
        'userid' => 'userid',
        'host' => 'www.tizi.com',
        'status' => 1
    );

    function __construct() 
    {                
        $this->_CI = &get_instance();

        if ($this->_CI->config->load('fluent', true, true))
        {
            $config = $this->_CI->config->item('fluent');
        }
        else
        {
            $config = array();
        }

        $config = array_merge(self::$default_config, $config);

        Fluent\Autoloader::register();
        $this->fluent = Fluent\Logger\FluentLogger::open($config['host'],$config['port']);

        $this->access = $config['access'];
        $this->business_allow = $config['business_allow'];
    }

    public function post($data)
    {
        if(isset($data['business']))
        {
            if(!empty($this->business_allow) && in_array($data['business'], $this->business_allow))
            {
                return array('code' => -1, 'msg' => 'business is not allowed');
            }
            $data = array_merge($this->default_data, $data);
            $this->fluent->post($this->access, $data);
            return array('code' => 1, 'msg' => '');
        }
        else
        {
            return array('code' => -2, 'msg' => 'business is invalid');
        }
    }

}
