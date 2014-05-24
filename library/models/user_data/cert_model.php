<?php
/*教师认证的model*/
class Cert_Model extends MY_Model {
    protected $_table="user_teacher_certification";
    protected $_paper_table="paper_testpaper";

    function __construct(){
        parent::__construct();
    }

    //老师提交申请
    function apply_insert($data){
        $sql = "select * from $this->_table where user_id = ? ";
        $record = $this->db->query($sql,array($data['user_id']))->result_array();
        if($record and is_array($record)){//有历史申请记录
            foreach($record as $key=>$val){
                if(!$val['is_del']){
                    return array('code'=>false,'msg'=>'不能重复提交申请');//如果有未删除的老记录，就不允许申请；
                }
            }
        }
        if(!$data['real_name'] or !$data['gender'] or !($data['school_id'] or $data['school_define_id']) or !$data['grade_subject'] or !$data['title'] ){
            return array('code'=>false,'msg'=>'缺少参数');
        }
        $res = $this->db->insert($this->_table, $data);
        if($res) {
            return array('code'=>true);
        }else{
            return array('code'=>false,'msg'=>'申请失败');
        }
    }

    //审核失败后，重新申请前的置位操作
    function reapply($data){
        $sql = "update $this->_table set is_del=1 where user_id = ?";
        return $this->db->query($sql,array($data['user_id']));
    }

    //审核接口,
    function edit_apply_status($_data){
        $data['apply_status'] = $_data['apply_status'];
        $data['verify_time'] = time();
        $data['reject_msg'] = $_data['reject_msg'];
        $data['cert_type'] = $_data['cert_type'];
        $data['cert_num'] = $_data['cert_num'];
        $this->db->trans_start();
        $this->db->where('id',$_data['id']);
        $res = $this->db->update($this->_table, $data); 
        if($_data['apply_status'] == Constant::APPLY_STATUS_SUCC and $res){
            $sql = "update user set certification = 1 where id = ?";
            $this->db->query($sql,array($_data['user_id']));
            if ($this->db->affected_rows() === 1){
				$this->load->library("credit");
				$this->credit->exec($_data['user_id'], "certificate_teacher");
				
				//给邀请人发积分
				$this->load->model("login/register_model");
				$user_info = $this->register_model->get_user_info($_data["user_id"]);
				$register_invite = $user_info["user"]->register_invite;
				if ($register_invite > 0){
					$this->load->model("login/register_model");
					$user_info = $this->register_model->get_user_info($register_invite);
					$certificate = $user_info["user"]->certification;
					$score = $this->credit->exec($register_invite, "invite_and_certificate", 
						$certificate, "", array($_data["user_id"]));
					if ($score > 0){
						$this->load->model("user_data/invite_model");
						$this->invite_model->update_credit($register_invite, $_data["user_id"], $score);
					}
				}
			}
        }
        $this->db->trans_complete();
        if($this->db->trans_status() === false){
            return false;
        }
        return $res;
    }

    function get_apply_status($user_id){
        $sql = "select * from $this->_table where user_id=$user_id and is_del=0 order by id desc limit 1";
        $res = $this->db->query($sql)->result_array();
        if(!$res){
            return null;
        }
        return $res;
    }

    //认证前，判断是否有过组卷
    function get_save_logs($user_id){
        $_table="paper_save_log";
        $this->db->select("count(*) as num ");
        $this->db->where("{$_table}.is_delete",0);
        $this->db->where("{$_table}.user_id",$user_id);
        $this->db->order_by("{$_table}.save_time","desc");

        $query=$this->db->get($_table);   
        return $query->row(0)->num;
    }

    //按照条件搜索
    function search($data){
        $this->db->select("count(*) as num ");
        $this->db->where($data);
        $query = $this->db->get($this->_table)->row(0)->num;
        return $query;
    }
}