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
require_once GEN_DIR . '/zujuan/ChoiceQuestionService.php';
require_once GEN_DIR . '/zujuan/Types.php';

use Thrift\Transport\TSocket;
use Thrift\Transport\TBufferedTransport;
use Thrift\Protocol\TBinaryProtocol;
use addQuestion\ChoiceQuestionServiceIf;
use addQuestion\ChoiceQuestionServiceClient;
use addQuestion\ChoiceQuestionService_addQuestion_args;
use addQuestion\ChoiceQuestionService_addQuestion_result;
use addQuestion\AddPaperQuestion;
use addQuestion\RemovePaperQuestion;
use Thrift\Exception\TException;

class Thrift_Zujuan {
	
	private $_socket;
	
	private $_transport;
	
	private $_protocol;
	
	private $_client;
	
	private $_host;
	
	private $_port;
	
	public function __construct(){
		$ci =& get_instance();
		$ci->load->config("thrift");
		$this->_host = $ci->config->item("thrift_zujuan_host");
		$this->_port = $ci->config->item("thrift_zujuan_port");
		try {
			$this->_socket = new TSocket($this->_host, $this->_port);
			$this->_transport = new TBufferedTransport($this->_socket, 1024, 1024);
			$this->_protocol = new TBinaryProtocol($this->_transport);
			$this->_client = new ChoiceQuestionServiceClient($this->_protocol);
			$this->_transport->open();
		} catch (TException $te){
			
		}
	}
	
	public function add_question($question_id,$paper_id,$question_origin,$category_id,$course_id){
		$args = new \addQuestion\AddPaperQuestion();
		$args->question_id = $question_id;
		$args->paper_id = $paper_id;
		$args->question_origin = $question_origin;
		$args->category_id = $category_id;
		$args->course_id = $course_id;
		try {
			$status = $this->_client->addQuestion($args);
		} catch (TException $tx){
			$status = -127;
			//$this->log_err("thrift errcode 2:add uid phone connect server failed!", array('uid'=>$uid, 'phone'=>$phone));
		}
		return $status;
	}

	public function del_question($paper_id,$paper_question_id_list,$question_origin,$is_paper_question_id){
		$args = new \addQuestion\RemovePaperQuestion();
		$args->paper_id = $paper_id;
		$args->paper_question_id_list = $paper_question_id_list;
		$args->question_origin = $question_origin;
		$args->is_paper_question_id = $is_paper_question_id;
		try {
			$status = $this->_client->removeQuestion($args);
		} catch (TException $tx){
			$status = -127;
			//$this->log_err("thrift errcode 2:add uid phone connect server failed!", array('uid'=>$uid, 'phone'=>$phone));
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