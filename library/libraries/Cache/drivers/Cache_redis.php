<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.2.4 or newer
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Open Software License version 3.0
 *
 * This source file is subject to the Open Software License (OSL 3.0) that is
 * bundled with this package in the files license.txt / license.rst.  It is
 * also available through the world wide web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world wide web, please send an email to
 * licensing@ellislab.com so we can send you a copy immediately.
 *
 * @package		CodeIgniter
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2013, EllisLab, Inc. (http://ellislab.com/)
 * @license		http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link		http://codeigniter.com
 * @since		Version 3.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter Redis Caching Class
 *
 * @package	   CodeIgniter
 * @subpackage Libraries
 * @category   Core
 * @author	   Anton Lindqvist <anton@qvister.se>
 * @link
 */
class CI_Cache_redis extends CI_Driver
{
	/**
	 * Default config
	 *
	 * @static
	 * @var	array
	 */
	protected static $_default_config = array(
		'host' => '127.0.0.1',
		'password' => NULL,
		'port' => 6379,
		'timeout' => 0
	);

	/**
	 * Redis connection
	 *
	 * @var	Redis
	 */
	protected $_redis;

	protected $_slave;

	// ------------------------------------------------------------------------

	/**
	 * Get cache
	 *
	 * @param	string	Cache key identifier
	 * @return	mixed
	 */
	public function get($key)
	{
		if ($this->_slave)
		{
			return $this->_slave->get($key);
		}
	}

	public function mget($keys)
	{
		if ($this->_slave)
		{
			return $this->_slave->mget($keys);
		}
	}
	// ------------------------------------------------------------------------

	/**
	 * Save cache
	 *
	 * @param	string	Cache key identifier
	 * @param	mixed	Data to save
	 * @param	int	Time to live
	 * @return	bool
	 */
	public function save($key, $value, $ttl = NULL)
	{
		if ($this->_redis)
		{
			return ($ttl)?$this->_redis->setex($key, $ttl, $value):$this->_redis->set($key, $value);
		}	
	}

	public function set($key, $value, $ttl = NULL)
	{
		return $this->save($key, $value, $ttl);
	}
	// ------------------------------------------------------------------------

	/**
	 * Delete from cache
	 *
	 * @param	string	Cache key
	 * @return	bool
	 */
	public function delete($key)
	{
		if ($this->_redis)
		{
			return ($this->_redis->delete($key) === 1);
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Clean cache
	 *
	 * @return	bool
	 * @see		Redis::flushDB()
	 */
	public function clean()
	{
		if ($this->_redis)
		{
			return $this->_redis->flushDB();
		}	
	}

	// ------------------------------------------------------------------------

	/**
	 * Get cache driver info
	 *
	 * @param	string	Not supported in Redis.
	 *			Only included in order to offer a
	 *			consistent cache API.
	 * @return	array
	 * @see		Redis::info()
	 */
	public function cache_info($type = NULL)
	{
		if ($this->_redis)
		{
			return $this->_redis->info();
		}	
	}

	// ------------------------------------------------------------------------

	/**
	 * Get cache metadata
	 *
	 * @param	string	Cache key
	 * @return	array
	 */
	public function get_metadata($key)
	{
		$value = $this->get($key);

		if ($value)
		{
			return array(
				'expire' => time() + $this->_redis->ttl($key),
				'data' => $value
			);
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Check if Redis driver is supported
	 *
	 * @return	bool
	 */
	public function is_supported()
	{
		if (extension_loaded('redis'))
		{
			$this->_setup_redis();
			return TRUE;
		}
		else
		{
			log_message('debug', 'The Redis extension must be loaded to use Redis cache.');
			return FALSE;
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Setup Redis config and connection
	 *
	 * Loads Redis config file if present. Will halt execution
	 * if a Redis connection can't be established.
	 *
	 * @return	bool
	 * @see		Redis::connect()
	 */
	protected function _setup_redis($master='default', $slave='slave', $backup='backup')
	{
		$this->_redis = $this->_redis_connect($master);		
		$this->_slave = $this->_redis_connect($slave);

		if(!$this->_redis && $this->_slave)
		{
			$this->_redis = $this->_slave;
		}
		if(!$this->_slave && $this->_redis)
		{
			$this->_slave = $this->_redis;
		}

		/*
		if(!$this->_redis && $backup)
		{
			$this->_redis = $this->_redis_connect($backup);
		}
		if(!$this->_slave && $backup)
		{
			$this->_slave = $this->_redis_connect($backup);
		}
		*/
	}

	protected function _redis_connect($redis_type='default')
	{
		$config = array();
		$CI =& get_instance();

		if ($CI->config->load('redis', TRUE, TRUE))
		{
			$redis_config = $CI->config->item('redis');
			if(isset($redis_config['redis_'.$redis_type])) $config = $redis_config['redis_'.$redis_type];
		}

		$config = array_merge(self::$_default_config, $config);
		
		$redis = new Redis();
		
		try
		{
			$return = $redis->pconnect($config['host'], $config['port'], $config['timeout']);
		}
		catch (RedisException $e)
		{
			log_message('error_tizi', '10070:Redis '.$redis_type.' connection refused. '.$e->getMessage());
			$return = false;
		}

		if ($return && isset($config['password']))
		{
			try
			{
				$redis->auth($config['password']);
			}
			catch (RedisException $e)
			{
				log_message('error_tizi', '10072:Redis '.$redis_type.' auth refused. '.$e->getMessage());
				$return = false;
			}
		}
		
		if(!$return)
		{
			$redis = NULL;
			log_message('error_tizi', '10071:Redis '.$redis_type.' connection failed.');
		}	
		return $redis;
	}

	// ------------------------------------------------------------------------

	/**
	 * Class destructor
	 *
	 * Closes the connection to Redis if present.
	 *
	 * @return	void
	 */
	public function __destruct()
	{
		$return = false;
		if ($this->_redis)
		{
			$return = $this->_redis->close();
		}

		if ($this->_slave)
		{
			$return &= $this->_slave->close();
		}
		return $return;
	}

	public function select($db = 0)
	{
		$return = false;
		if ($this->_redis)
		{
			$return = $this->_redis->select($db);
		}
		
		if ($this->_slave)
		{
			$return &= $this->_slave->select($db);
		}
		return $return;
	}

	public function incr($key)
	{
		if ($this->_redis)
		{
			return $this->_redis->incr($key);
		}
	}

	public function decr($key)
	{
		if ($this->_redis)
		{
			return $this->_redis->decr($key);
		}
	}

	/**
	 * 封装redis zadd方式，将元素加入zset
	 * @param string $key 		有序集zset标识关键字
	 * @param int    $score		$member的优先级值
	 * @param string $member    存储的元素值
	 */
	public function zadd($key, $score, $member)
	{
		if ($this->_redis)
		{
			return $this->_redis->zadd($key, $score, $member);
		}
	}

	/**
	 * 封装redis zcard方式,返回有序集zset的元素个数
	 * @param  string   $key 	有序集zset标识关键字
	 * @return int		有序集zset的元素个数
	 */
	public function zcard($key)
	{
		if ($this->_slave)
		{
			return $this->_slave->zcard($key);
		}
	}

    public function zscore($key,$field){
        if ($this->_slave)
        {
            return $this->_slave->zscore($key,$field);
        }
    }

    public function zrem($key,$field){
        if ($this->_redis)
        {
            return $this->_redis->zrem($key,$field);
        }
    }

    public function del($key){

        if($this->_redis){
            return $this->_redis->del($key);       
        }
    }

    public function exists($key){
        if ($this->_slave)
        {
			return $this->_slave->exists($key);
        }
    }

    public function expire($key,$time){
        if($this->_redis){
            return $this->_redis->expire($key,$time);       
        }
    }
    
    /*hash*/
    public function hset($key,$field,$value){

        if($this->_redis){
            return $this->_redis->hset($key,$field,$value);       
        }

    }

    public function hget($key,$field){

        if ($this->_slave)
        {
			return $this->_slave->hget($key,$field);
        }       
    }

    public function hincrby($key,$field,$value){
    	if($this->_redis){
            return $this->_redis->hincrby($key,$field,$value);       
        }
    }

    public function hgetall($key){

        if ($this->_slave)
        {
			return $this->_slave->hgetall($key);
        } 
    }

    public function hmset($key,$data){

        if($this->_redis){
            return $this->_redis->hmset($key,$data);       
        }
    }

    public function hmget($key,$data){

        if($this->_slave){
            return $this->_slave->hmget($key,$data);       
        }
    }

    public function hexists($key,$field){

        if ($this->_slave)
        {
			return $this->_slave->hexists($key,$field);
        }
    }

    public function keys($key){

        if ($this->_slave)
        {
			return $this->_slave->keys($key);
        }
    }
    public function zrank($key,$field){

        if ($this->_slave)
        {
			return $this->_slave->zrank($key,$field);
        }
    }
    public function zrevrank($key,$field){

        if ($this->_slave)
        {
			return $this->_slave->zrevrank($key,$field);
        }
    }
    public function sadd($key,$data){

        if($this->_redis){
            return $this->_redis->sadd($key,$data);  
        }
    }
    public function srem($key,$data){

        if($this->_redis){
            return $this->_redis->srem($key,$data);  
        }
    }
    public function smembers($key){
        if ($this->_slave)
        {
            return $this->_slave->smembers($key);
        }       
    }

    public function srandmember($key,$count=0){
        if ($this->_slave)
        {
        	if($count > 0)
            	return $this->_slave->srandmember($key,$count);
        	else
        		return $this->_slave->srandmember($key);
        }       
    }

    public function scard($key){
        if ($this->_slave)
        {
            return $this->_slave->scard($key);
        }       
    }

    public function ttl($key){
        if ($this->_slave)
        {
            return $this->_slave->ttl($key);
        }
    }

    public function lrange($key,$start,$end){
        if ($this->_slave)
        {
            return $this->_slave->lrange($key,$start,$end);
        }   
    }

    public function llen($key){
        if ($this->_slave)
        {
            return $this->_slave->llen($key);
        }   
    }   

    /*hash end*/
	
	/**
	 * 封装redis ZCOUNT方式,返回有序集zset的元素个数
	 * @param  string   $key 	有序集zset标识关键字
	 * @param  int 		$min	最小score
	 * @param  int      $max	最大score
	 * @return int		有序集zset的元素个数
	 */
	 public function zcount($key, $min, $max){
		if ($this->_slave){
			return $this->_slave->zcount($key, $min, $max);
		}
	 }
	 
	/**
	 * 封装redis ZINCRBY方式,增加元素的优先级score值
	 * @param  string $key 	       有序集zset标识关键字
	 * @param  int    $increment   增加该元素的优先级值
	 * @param  string $member      存储的元素值
	 */
	 public function zincrby($key, $increment, $member){
		if ($this->_redis){
			$this->_redis->zincrby($key, $increment, $member);
		}
	 } 
	 
	/**
	 * 封装redis ZREVRANGE方式，按score值从大到小排列该有序集的元素并返回
	 * @param  string $key 	 	     有序集zset标识关键字 
	 * @param  int    $start	 	 有序集zset开始的成员位置
	 * @param  int    $stop		     有序集zset结束的成员位置
	 * @param  BOOL   $WITHSCORES	 为1则返score
	 */
	 public function zrevrange($key, $start, $stop, $WITHSCORES=1){
		if ($this->_slave){
			return $this->_slave->zrevrange($key, $start, $stop, $WITHSCORES);
		}
	 } 

	 public function zrange($key, $start, $stop, $WITHSCORES=1){
		if ($this->_slave){
			return $this->_slave->zrange($key, $start, $stop, $WITHSCORES);
		}
	 } 

	 
	/**
	 * 封装redis ZREVRANGEBYSCORE方式，按score值从大到小排列该有序集的元素并返回
	 * @param  string $key 	 	     有序集zset标识关键字 
	 * @param  int    $max			 获取记录的最大score
	 * @param  int    $min			 获取记录的最小score
	 * @param  int    $offset	 	 有序集zset开始的成员位置
	 * @param  int    $count	     获取有序集zset的成员数量
	 * @param  BOOL   $WITHSCORES	 为1则返score
	 */
	 public function zrevrangebyscore($key, $max, $min, $offset, $count, $WITHSCORES=1){
		if ($this->_slave){
			return $this->_slave->zrevrangebyscore($key,  $max, $min, array('withscores'=>$WITHSCORES,'limit'=>array($offset,$count)));
		}
	 }
	 
	 /**
	 * 封装redis lPush方式,左侧入队
	 * @param  string $key 	       队列键名
	 * @param  string $value   	   入队内容
	 */
	 public function lpush($key, $value){
		if ($this->_redis){
			$this->_redis->lpush($key, $value);
		}
	 }
	 
	 
	 /**
	 * 封装redis rPop方式,右侧出队
	 * @param  string $key 	       队列键名
	 */
	 public function rpop($key){
		if ($this->_slave){
			$this->_slave->rpop($key);
		}
	 }
}

/* End of file Cache_redis.php */
/* Location: ./system/libraries/Cache/drivers/Cache_redis.php */
