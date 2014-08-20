<?php

class Feedback_Model extends MY_Model{
    private $_table = 'feedback';
    private $_q_table ='feedback_question_wrong';
    private $_v_table ='feedback_voice_wrong';

    function __construct(){
        parent::__construct();
    }

    // 填写 反馈
    function send_feedback($param){
        return $this->db->insert($this->_table,$param);
    }

    //问题纠错
    function add_wrong_question($param){
        return $this->db->insert($this->_q_table,$param);
    }

    //问题纠错
    function add_voice_question($param){
        return $this->db->insert($this->_v_table,$param);
    }

    function get_question($time){
        $sql = "select * from $this->_q_table where add_time > ?";
        $res = $this->db->query($sql,array($time))->result_array();
        return $res;
    }

    function update_q($where,$data){
        $this->db->where($where);
        return $this->db->update($this->_q_table,$data);
    }

}