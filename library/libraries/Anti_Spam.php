<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Anti_Spam
{
    static $connection = null;

    const TYPE_MINUTE = 'minute';
    const TYPE_HOUR = 'hour';
    const TYPE_DAY = 'day';
    const TYPE_RES_DOWN_MINUTE = 'resdown_minute'; //include /download/paper, /download/doc
    const TYPE_RES_DOWN_HOUR = 'resdown_hour'; //include /download/paper, /download/doc
    const TYPE_RES_DOWN_DAY = 'resdown_day'; //include /download/paper, /download/doc
    const TYPE_REGISTER_MINUTE = 'register_minute'; 
    const TYPE_REGISTER_HOUR = 'register_hour'; 
    const TYPE_REGISTER_DAY = 'register_day'; 
    const TEMP_FILE_DIR = '/space1/logs_tizi/antispam/';
    const TEMP_KEY_FILENAME= 'key_tizi_antispam.txt';
    const TEMP_IP_FILENAME= 'ip_tizi_antispam.txt'; //include ip, uri
    const TEMP_FORBID_FILENAME="forbid_tizi_antisapm.txt";

    static $data = array(
        self::TYPE_MINUTE => array(
            'timeout' => 60, //计数失效时间
            'limit' => 5000, //频率限制
            'expire' => 600, //封禁时间
        ),
        self::TYPE_HOUR => array(
            'timeout' => 3600,
            'limit' => 50000,
            'expire' => 7200,
        ),
        self::TYPE_DAY => array(
            'timeout' => 86400,
            'limit' => 500000,
            'expire' => 86400,
        ),
    );

    static $uriData = array(
        self::TYPE_RES_DOWN_MINUTE => array(
	    'timeout' => 60,
	    'limit' => 30,
	    'expire' => 600,
	),
        self::TYPE_RES_DOWN_HOUR=> array(
	    'timeout' => 3600,
	    'limit' => 100,
	    'expire' => 7200,
	),
        self::TYPE_RES_DOWN_DAY=> array(
	    'timeout' => 86400,
	    'limit' => 500,
	    'expire' => 86400,
	),
    );
    
    static $registerData = array(
        self::TYPE_REGISTER_MINUTE => array(
	    'timeout' => 60,
	    'limit' => 80,
	    'expire' => 600,
	),
        self::TYPE_REGISTER_HOUR=> array(
	    'timeout' => 3600,
	    'limit' => 200,
	    'expire' => 7200,
	),
        self::TYPE_REGISTER_DAY=> array(
	    'timeout' => 86400,
	    'limit' => 300,
	    'expire' => 86400,
	),
    );

    private static $whiteLists = array(
	//公司的3个出口IP，不能去掉
    '114.112.172.218',
	'124.193.172.66',
	'124.193.177.194',
	'223.72.244.154',
	
	//浙江丽水-刘浩
	'60.190.124.45',
    );

    private static $uriLists = array(
	'/download/paper',
	'/download/doc',
	);
	
    public static function check()
    {
        if(!self::getConnection()){
            return true;
        }

        //如果在白名单里面，直接返回true
        $clientIp = self::getClient();
        if (in_array($clientIp, self::$whiteLists)) {
            return true;
        }
        
        if (!self::check_ip()) {
            return false;
        }
        if (!self::check_uri()) {
            return false;
        }
        if (!self::check_register()) {
            return false;
        }
 	    return true;
    }

    /**
     * 检查是否封禁，一次检查分钟，小时，天 的访问频率是否超限，如是则封禁，否则计数加1
     * @return boolean
     */
    public static function check_ip()
    {
        if (self::isForbidden()) {
            return false;
        }

        foreach (self::$data as $type => $data) {
            if (self::get($type) > $data['limit']) {
                self::setForbidden($type, self::$data);
                return false;
            } else {
                self::increment($type, self::$data);
            }
        }
        return true;
    }

    public static function check_uri()
    {

	    $ret = self::getUriType();

	    if( !$ret )  return true; //如果不命中uriLists，则不需要禁止访问

        if (self::isForbidden('download')) {
            return false;
        }

        foreach (self::$uriData as $type => $data) {
            if (self::get($type) > $data['limit']) {
                self::setForbidden($type, self::$uriData, 'download');
                return false;
            } else {
                self::increment($type, self::$uriData);
            }
        }
	    return true;
    }

    public static function check_register()
    {
    	$query_string = $_SERVER['REQUEST_URI'];
    	if (strpos($query_string, '/register/submit/teacher') === false) {
            return true;
    	}
        if (self::isForbidden('register')) {
            return false;
        }
        foreach (self::$registerData as $type => $data) {
            if (self::get($type) > $data['limit']) {
                self::setForbidden($type, self::$registerData, 'register');
                return false;
            } else {
                self::increment($type, self::$registerData);
            }
        }
        return true;
    }
    /**
     * 获取访问次数计数
     * @param string $type
     * @return int
     */
    public static function get($type = self::TYPE_MINUTE)
    {
        $key = 'visited_' . $type . '_' . self::getClient();
        $result = self::$connection->get($key);
        //echo $key . " " . $result . "\n";
        return intval($result);
    }

    /**
     * 设置访问次数，如果存在则加1，反之创建
     * @param type $type
     * @return type
     */
    public static function increment($type = self::TYPE_MINUTE, $data)
    {
        $key = 'visited_' . $type . '_' . self::getClient();
	self::logmsg("antispam_{$key}", self::TEMP_KEY_FILENAME);
        $expire = $data[$type]['timeout'];
        if (self::get($type)) {
            return self::$connection->increment($key, 1);
        } else {
            return self::$connection->set($key, 1, $expire);
        }
    }

    /**
     * 检查是否被封禁
     * @return type
     */
    public static function isForbidden($forbidden_key="")
    {
        $key = 'forbidden_' . self::getClient() . '_' . $forbidden_key;
        return self::$connection->get($key);
    }

    /**
     * 设置封禁，并记录日志，不同类型的过期时间不尽相同
     * @param string $type
     * @return type
     */
    public static function setForbidden($type = self::TYPE_MINUTE, $data, $forbidden_key="")
    {
        $key = 'forbidden_' . self::getClient() . '_' . $forbidden_key;
        $expire = $data[$type]['expire'];

	//log forbidden
        $message = "forbidden\tantispam_{$key}". "\t" . $expire;
	self::logmsg($message, self::TEMP_FORBID_FILENAME);

        return self::$connection->set($key, 1, $expire);
    }

    /**
     * 解禁
     * @return boolean
     */
    public static function unForbidden($forbidden_key="")
    {
        $key = 'forbidden_' . self::getClient() . '_' . $forbidden_key;

	//log unforbidden
        $message = "unforbidden\tantispam_{$key}";
	self::logmsg($message, self::TEMP_FORBID_FILENAME);

        return self::$connection->delete($key, 0);
    }

    /**
     * 获取需要判断的基本信息，可以是 ip， username，action的组合
     * @staticvar null $client
     * @return type
     */
    private static function getClient()
    {
        static $client = null;
        if (!$client) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $client = $_SERVER["HTTP_X_FORWARDED_FOR"];
                $pos = strrpos($client, ","); // 防止XFF为多个IP的情况，多个IP时取最后一个IP 这里的IP可能是proxy的ip 
                // XFF的第一个IP可以伪造
                if( $pos !== false ) {
                    $client = trim(substr($client,$pos+1));
                }
            } else if (isset($_SERVER["REMOTE_ADDR"])) {
                $client = $_SERVER["REMOTE_ADDR"];
            }
//            if (isset($_COOKIE['username'])) {
//                $client = $client . ':' . md5($_COOKIE['username']);
//            }
	    $message = "$client\t{$_SERVER['HTTP_X_FORWARDED_FOR']}\t{$_SERVER['REMOTE_ADDR']}";
	    self::logmsg($message, self::TEMP_IP_FILENAME);
        }
        return $client;
    }

    private static function getUriType()
    {
	$query_string = $_SERVER['REQUEST_URI'];
	$uri = $query_string;
	if( $pos = strpos($query_string, '?') )
	{
	   $uri = substr($query_string, 0, $pos);
	}
	self::logmsg($uri, self::TEMP_IP_FILENAME);
	if( in_array($uri, self::$uriLists) )
        {
	   return true;
	}
	return false;
    }

    /**
     * 获取Memcached连接
     * @staticvar null $connection
     * @return \Memcached
     */
    private static function getConnection()
    {
        if (!self::$connection && extension_loaded('memcached')) {
            self::$connection = new Memcached();
            self::$connection->setOption(Memcached::OPT_COMPRESSION, true);
            self::$connection->setOption(Memcached::OPT_DISTRIBUTION, true);
            self::$connection->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
            self::$connection->setOption(Memcached::OPT_NO_BLOCK, true);
            self::$connection->setOption(Memcached::OPT_CONNECT_TIMEOUT, 50);
            self::$connection->setOption(Memcached::OPT_POLL_TIMEOUT, 50);
            self::$connection->setOption(Memcached::OPT_PREFIX_KEY, 'antispam_');

            $file_path = LIBPATH .'config/memcached.php';
            if (file_exists($file_path)) include($file_path);
            else exit('The configuration file memcached.php does not exist.');
            
            $conf = $config['memcached'];
            self::$connection->addServers($conf);
            return true;
        }
        else
        {
            $_nmc = isset($_COOKIE['_nmc'])?$_COOKIE['_nmc']:0;
            if(!$_nmc && $_COOKIE['_nmc'] != 1) setcookie('_nmc',1,0,'/','.tizi.com');
            return false;
        }
        //return $connection;
    }

    private static function logmsg($message, $filename)
    {
        error_log(date('Y-m-d H:i:s')."\t".$message."\n", 3, self::TEMP_FILE_DIR.$filename);
    }
}

//$_SERVER['REMOTE_ADDR'] = "192.168.46.130";
//$_SERVER['HTTP_X_FORWARDED_FOR'] = "192.168.1.13, 192.168.11.33";
//$_SERVER['REQUEST_URI'] = "/download/paper?register/submit/teacher";
//var_dump(Anti_Spam::check());
//var_dump(Anti_Spam::unForbidden('download'));
//var_dump(Anti_Spam::unForbidden('register'));
