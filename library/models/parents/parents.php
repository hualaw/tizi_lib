<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Parents extends CI_Model {

    // private $_table = 'parents'; // no more TABLE `parents`

    public function __construct(){
        parent::__construct();
    }


    public function edit_name($parent_id, $realname){
        // $sql = "replace into $this->_table values(0,$parent_id,'$realname')  ";
        $sql = "update user set name = '$realname' where id = '$parent_id' ";
        $res = $this->db->query($sql);
        return $res;
    }

    // 获取家长信息
    public function get_info($user_id){
        return $this->db->query("select name,phone,email from user  where id = $user_id limit 1 ")->result_array();
    }

    //修改用户email 
    public function edit_email($user_id,$email){
        $sql = "select count(1) as num from user where email = '$email' ";
        $num = $this->db->query($sql)->row(0)->num;
        $return = array('errorcode'=>false,'error'=>'');
        if($num){
            $return['error'] = $this->lang->line('dupli_email');
            return $return;
        }
        $data = array('email'=>$email);
        $this->db->where('id',$user_id);
        $res = $this->db->update('user',$data);
        if($res){
            $return['error'] = $this->lang->line('succ_update');$return['errorcode'] = $res;
        }else{
            $return['error'] = $this->lang->line('fail_update');
        }
        return $return;
    }

    public function get_pwd_by_id($user_id){
        return $this->db->query("select password from user where id=$user_id limit 1")->row(0)->password;
    }

    // public function edit_pwd($user_id,$pwd){
    //     $data = array('password'=>$pwd);
    //     $this->db->where('id',$user_id);
    //     return $this->db->update('user',$data);
    // }

    //从user表中获取信息
    public function get_user_info($user_id){
        return $this->db->query("select * from user where id=$user_id limit 1")->result_array();
    }

}
