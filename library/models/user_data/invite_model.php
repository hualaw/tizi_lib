<?php
/*有奖邀请*/
class Invite_Model extends MY_Model {
    protected $_log_table="user_invite_log"; //每次发送邀请的记录
    protected $_paper_table="user_invite_register"; //成功注册的邀请

    function __construct(){
        parent::__construct();
    }

    /*点击发送后，记录到user_invite_log中*/
    function insert_log($data){
        $this->db->insert($this->_log_table,$data);
    }

 
}