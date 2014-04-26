<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CI_Notification {

  protected $_CI; // CI object
  protected $_uid;
  protected $_urname;

  public function __construct () 
  {
    $this->_CI = & get_instance();
    $this->_uid = $this->_CI->session->userdata('user_id');
    $this->_urname = $this->_CI->session->userdata('urname');
  }

  public function add_notice($notice_type, $notice_data)
  {
    
  }

  public function getNewNotifyCount()
  {
    if($this->_uid>0)
    {
      $this->_CI->load->model('notice/notice_model');
      $num = $this->_CI->notice_model->getNewNoticeNums($this->_uid);
      $data = array('status'=>99,'msg'=>(int)$num);      
    }
    else
    {
      $data = array('status'=>0,'msg'=>0);
    }
    
    exit(json_ntoken($data));
  }

  function redis_notice(){
    $this->_CI->load->model("redis/redis_model");
    $this->_CI->redis_model->connect('notice');
  }

  /**
   * 处理存储的消息
   * @params string $type   消息存储的类型，在lang中存贮的key
   * @params array  $params 消息变量数组
   * @return string 返回处理后的消息文本
   */
  public function getNoticeMsg($type, $params){
    if(!is_array($params)){
      return '';
    }
  
    array_unshift($params, $this->_CI->lang->line($type));
    $msgs = call_user_func_array("sprintf", $params);
    // log_message('error_tizi',$msgs);    
    $msgs = urlencode($msgs);
    
    return $msgs;
  }

  /**
   *   截取至指定长度字符串，如果超过则以... 收尾
   *   @param  string $str       要截取的字符串
   *   @param  string $max_length  字符串最大长度
   *   @param  string $suffix    字符串超过$max_length则结尾$suffix
   *   @return string
   */
  public function subMaxNums($str, $max_length, $suffix='...'){
    $str = trim($str);
    if(mb_strlen($str, "UTF-8") > $max_length){
      $str = mb_substr($str, 0, $max_length, "UTF-8") . $suffix;
    }
    
    return $str;
  }

}
