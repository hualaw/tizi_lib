<?php
if(!defined('BASEPATH')) exit('No direct script access allowed');

//配置thrift
define('THRIFT_ROOT', dirname(__FILE__).DIRECTORY_SEPARATOR.'Thrift');
define('GEN_DIR', dirname(__FILE__).DIRECTORY_SEPARATOR.'gen-php');

//包含
require_once THRIFT_ROOT . '/Transport/TTransport.php';
require_once THRIFT_ROOT . '/Transport/TSocket.php';
require_once THRIFT_ROOT . '/Protocol/TProtocol.php';
require_once THRIFT_ROOT . '/Protocol/TBinaryProtocol.php';
require_once THRIFT_ROOT . '/Transport/TBufferedTransport.php';
require_once THRIFT_ROOT . '/Factory/TStringFuncFactory.php';
require_once THRIFT_ROOT . '/StringFunc/TStringFunc.php';
require_once THRIFT_ROOT . '/StringFunc/Core.php';
require_once THRIFT_ROOT . '/Type/TMessageType.php';
require_once THRIFT_ROOT . '/Type/TType.php';
require_once THRIFT_ROOT . '/Exception/TException.php';
require_once THRIFT_ROOT . '/Exception/TTransportException.php';
require_once THRIFT_ROOT . '/Exception/TProtocolException.php';
require_once GEN_DIR . '/facebook/fb303/FacebookService.php';
require_once GEN_DIR . '/facebook/fb303/Types.php';
require_once GEN_DIR . '/yiduoyun/ydy303/YdyService.php';
require_once GEN_DIR . '/yiduoyun/ydy303/Types.php';
require_once GEN_DIR . '/yiduoyun/phone/PhoneService.php';
require_once GEN_DIR . '/yiduoyun/phone/Types.php';

use Thrift\Transport\TSocket;
use Thrift\Transport\TBufferedTransport;
use Thrift\Protocol\TBinaryProtocol;
use yiduoyun\phone\PhoneServiceClient;
use yiduoyun\phone\AppNameInvalidException;
use yiduoyun\phone\NotFoundException;
use yiduoyun\phone\AlreadyExistsException;
use Thrift\Exception\TException;

class Thrift {
	
	private $_socket;
	
	private $_transport;
	
	private $_protocol;
	
	private $_client;
	
	private $_host;
	
	private $_port;
	
	private $_appname;
	
	public function __construct(){
		$ci =& get_instance();
		$ci->load->config("thrift");
		$this->_host = $ci->config->item("thrift_host");
		$this->_port = $ci->config->item("thrift_port");
		$this->_appname = $ci->config->item("thrift_appname");
		try {
			$this->_socket = new TSocket($this->_host, $this->_port);
			$this->_transport = new TBufferedTransport($this->_socket, 1024, 1024);
			$this->_protocol = new TBinaryProtocol($this->_transport);	
			$this->_client = new PhoneServiceClient($this->_protocol);
			$this->_transport->open();
		} catch (TException $te){
			
		}
	}
	
	/**
	 * 添加一个用户和手机
	 * @param integer $uid
	 * @param string $phone
	 * @return integer
	 * 1：添加成功
	 * -127：通讯失败
	 * -1：用户UID或手机已经存在
	 */
	public function add_phone($uid, $phone){
		try {
			$this->_client->add_phone($this->_appname, $uid, $phone);
			$status = 1;
		} catch (AlreadyExistsException $tx){
			$status = -1;
			$this->log_err("thrift errcode 1:uid or phone already exists!", array('uid'=>$uid, 'phone'=>$phone));
		} catch (TException $tx){
			$status = -127;
			$this->log_err("thrift errcode 2:add uid phone connect server failed!", array('uid'=>$uid, 'phone'=>$phone));
		}
		return $status;
	}
	
	/**
	 * 获取一个用户的手机
	 * @param integer $uid
	 * @return string|integer
	 * string：返回的用户手机
	 * -127：通讯失败
	 * -1：没有找到该用户的信息
	 */
	public function get_phone($uid){
		try {
			$rs = $this->_client->get_phone($this->_appname, $uid);
			return $rs;
		} catch (NotFoundException $tx){
			$status = -1;
			$this->log_err("thrift errcode 3:uid phone does not found", array('uid'=>$uid));
		} catch (TException $tx){
			$status = -127;
			$this->log_err("thrift errcode 4:get uid phone connect server failed!", array('uid'=>$uid));
		}
		return $status;
	}
	
	/**
	 * 修改一个用户的手机
	 * @param integer $uid
	 * @param string $phone
	 * @return integer
	 * 1：修改成功
	 * -127：通讯失败
	 * -1：用户不存在
	 * -2：手机已经存在
	 */
	public function change_phone($uid, $phone){
		try {
			$rs = $this->_client->change_phone($this->_appname, $uid, $phone);
			$status = 1;
		} catch (NotFoundException $tx){
			$status = -1;
			$this->log_err("thrift errcode 5:change uid phone,this uid does not found!", array('uid'=>$uid, 'phone'=>$phone));
		} catch (AlreadyExistsException $tx){
			$status = -2;
			$this->log_err("thrift errcode 6:change uid phone,phone already exists!", array('uid'=>$uid, 'phone'=>$phone));
		} catch (TException $tx){
			$status = -127;
			$this->log_err("thrift errcode 7:change uid phone connect server failed!", array('uid'=>$uid, 'phone'=>$phone));
		}
		return $status;
	}
	
	/**
	 * 通过手机号吗获取用户UID
	 * @param string $phone
	 * @return integer|string
	 * string：用户的UID
	 * -127：通讯失败
	 * -1：每有找到该手机绑定的用户
	 */
	public function get_uid($phone){
		try {
			$rs = $this->_client->get_uid($this->_appname, $phone);
			return $rs;
		} catch (NotFoundException $tx){
			$status = -1;
			$this->log_err("thrift errcode 8:phone does not bind user!", array('phone'=>$phone));
		} catch (TException $tx){
			$status = -127;
			$this->log_err("thrift errcode 9:get uid phone connect server failed!", array('phone'=>$phone));
		}
		return $status;
	}
	
	protected function log_err($msg, $env_variable = false){
		//return $this->_log->error($msg);
		log_message('error_tizi', $msg, $env_variable);
	}
	
	public function __destruct(){
		$this->_transport->close();
	}
}