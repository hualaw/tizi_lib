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

}
