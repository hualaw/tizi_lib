<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require(dirname(__FILE__)."/../../libraries/".'Mail.php');//如何正确的引用Mail.php？
/*有奖邀请*/
class Invite_Model extends LI_Model {
    protected $_log_table="user_invite_log"; //每次发送邀请的记录
    protected $_succ_reg_table="user_invite_register"; //成功注册的邀请

    function __construct(){
        parent::__construct();
    }

    /*点击发送后，记录到user_invite_log中*/
    function insert_log($data){
        return $this->db->insert($this->_log_table,$data);
    }

    /*成功注册的邀请
        $data = array();
        $data['reg_invite'] = 邀请人uid
        $data['user_id'] = 新用户的uid
        $data['name']  
        $data['invite_way']   1.text 2.email  3.qq
        $data['reg_time'] = 注册时间戳;
    */
    function insert_succ_reg($data){
        return $this->db->insert($this->_succ_reg_table,$data);   
    }

    function get_succ_reg($user_id){
        $this->db->where('reg_invite' , $user_id);
        $query = $this->db->get($this->_succ_reg_table)->result_array();
        return $query;
    }

    /*
        邀请来的人，通过认证后，填写邀请人获得的积分

    */
    function update_credit($invitor,$new_comer,$credit){
        $credit = intval($credit);
        if(!$credit){
            return false;
        }
        $this->db->where('user_id', $new_comer);
        $this->db->where('reg_invite', $invitor);
        $data = array(
               'credit' => $credit
        );

        return $this->db->update($this->_succ_reg_table, $data); 
    }

    /*发送邀请邮件*/
    function send_invite_email($email,$my_name,$to_name,$user_id){
        $subject = "{$my_name}邀请您注册梯子网－中小学优质教学资源服务平台";
        $url  = site_url()."register/teacher/invite/".alpha_id($user_id);
        $msg = "尊敬的{$to_name}老师您好：<br />我是{$my_name}，我向您推荐梯子网，这里有很多免费的备课和试题资源可以下载，请点击以下链接进行注册并完成教师认证：<a href='{$url}'>{$url}</a>";
        $ret = Mail::send($email, $subject, $msg);
        if($ret['ret']==1){
            $errorcode=true;
            log_message('info_tizi','170101:Email send success',$ret);  
        }
        else{
            $errorcode=false;
            log_message('error_tizi','17010:Email send failed',$ret);
        }
        return $ret['ret'];
    }

 
}