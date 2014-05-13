<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Parent_Model extends LI_Model {

    private $_parent_kid_table = 'parents_kids';

    public function __construct(){
        parent::__construct();
    }

    // 绑定
    public function add_kid($user_id, $kid_id, $relation_ship=3,$operater=Constant::USER_TYPE_PARENT){
        $relation_ship = intval($relation_ship);
        //先判断家长的帐号是不是家长身份（老师也不能bind kid）
        $this->load->model('login/register_model');
        $p_info = $this->register_model->get_user_info($user_id);
        if(!isset($p_info['user']->user_type) or $p_info['user']->user_type!=Constant::USER_TYPE_PARENT){
            return array('status'=>false, 'msg'=>$this->lang->line('no_teacher'));
        }
        //判断被bind的是否为学生帐号
        $stu_info = $this->register_model->get_user_info($kid_id);
        if(!isset($stu_info['user']->user_type) or $stu_info['user']->user_type!=Constant::USER_TYPE_STUDENT){
            return array('status'=>false, 'msg'=>$this->lang->line('only_child_can_be_bind'));
        }
        if(!$relation_ship) $relation_ship=3;

        // 一个家长最多能绑定的孩子的数量
        $this->bind_exceed($user_id,Constant::USER_TYPE_PARENT);

        // 重复绑定孩子判断
        $sql = "select count(1) as count from $this->_parent_kid_table where parent_user_id = $user_id and kid_user_id = $kid_id and is_del = 0 ";
        $count = $this->db->query($sql)->row(0)->count ;
        if($count){ 
            return array('status'=>false, 'msg'=>$this->lang->line('dupli_kid'));
        }

        // 一个孩子对多能被x个家长绑定
        $this->bind_exceed($kid_id,Constant::USER_TYPE_STUDENT);

        //一个孩子只能有一个爸爸and一个妈妈, 后来的自动绑定成 其他
        $sql = "select count(1) as count from $this->_parent_kid_table where kid_user_id=$kid_id and relation_ship=$relation_ship and is_del=0";
        $count = $this->db->query($sql)->row(0)->count;
        if($count>=1){ 
            $relation_ship = 3;//后来的自动绑定成   其他监护人
        }

        //replace into 
        $sql = "replace into $this->_parent_kid_table values(0,$user_id,$kid_id,'$relation_ship',0)  ";
        $res = $this->db->query($sql);

        if($res){
            $this->send_binding_notice($user_id,$kid_id,$operater,'bind');
            return array('status'=>true,'msg'=>$this->lang->line('succbind'));
        }
        return array('status'=>false,'msg'=>$this->lang->line('failbind'));
    }

    /*检查绑定数目是否超过,  role*/
    function bind_exceed($user_id,$role = Constant::USER_TYPE_STUDENT){
        if($role == Constant::USER_TYPE_STUDENT){ //学生最多能绑6个家长
            $sql = "select count(1) as count from $this->_parent_kid_table where kid_user_id=$user_id and is_del=0";
            $count = $this->db->query($sql)->row(0)->count;
            if($count>=Constant::ONE_KID_IS_BINDED_MAX){ 
                $msg = sprintf($this->lang->line('too_many_parent_bind_kid'),Constant::ONE_KID_IS_BINDED_MAX);
                return array('status'=>false,'errorcode'=>false, 'msg'=>$msg,'error'=>$msg);
            }
            // return array('status'=>true,'errorcode'=>true);
        }elseif($role == Constant::USER_TYPE_PARENT){
            $totalSql = "select count(1) as total from $this->_parent_kid_table where parent_user_id=$user_id and is_del=0";
            $total = $this->db->query($totalSql)->row(0)->total;
            if($total>=Constant::ONE_PARENT_BIND_KID_MAX){ 
                $msg = sprintf($this->lang->line('bindlimit'),Constant::ONE_PARENT_BIND_KID_MAX);
                return array('status'=>false,'errorcode'=>false, 'msg'=>$msg,'error'=>$msg);
            }
            // return array('status'=>true,'errorcode'=>true);
        }else{
            $msg = '此帐号类型不支持绑定'; 
            return array('status'=>false,'msg'=>$msg,'errorcode'=>false,'error'=>$msg);
        }

    }

    // 家长移除（解绑）孩子
    public function remove_kid($user_id, $kid_id,$operater=Constant::USER_TYPE_PARENT){
        $data = array('is_del'=>TRUE);
        $this->db->where('kid_user_id', $kid_id); 
        $this->db->where('parent_user_id', $user_id); 
        $res = $this->db->update($this->_parent_kid_table, $data);
        if($res){
            $this->send_binding_notice($user_id,$kid_id,$operater,'unbind');
        }
        return $res;
    }

    // 修改关系
    public function edit_relation_ship($parent_id, $kid_id, $relation_ship){
        $data = array('relation_ship' => $relation_ship);
        $this->db->where('kid_id', $kid_id); 
        $this->db->where('parent_id', $parent_id); 
        return $this->db->update($this->_parent_kid_table, $data);
    }


    /*获取某个家长下的所有孩子信息
        孩子的userid，姓名，学号
    */
    public function get_kids($user_id){
        $sql="select u1.id, u1.student_id,u1.name from user u left join $this->_parent_kid_table pk on u.id = pk.parent_user_id left join user u1 on u1.id=pk.kid_user_id where u.id = $user_id and pk.is_del = 0";

        // 搜出：家长的孩子的 姓名，性别，学号，id，班级id、名字、年级、年份，学校id、名字，
//         $sql = "select u1.id, u1.student_id,u1.name,sdata.sex,cs.class_id,cls.classname,cls.class_grade,
// cls.class_year, cls.school_id,csch.schoolname from user u left join parents_kids pk on u.id = pk.parent_user_id 
// left join user u1 on u1.id=pk.kid_user_id left join student_data sdata on sdata.uid=pk.kid_user_id left join classes_student cs on cs.user_id=pk.kid_user_id left join classes cls on cls.id = cs.class_id left join classes_schools csch on csch.id = cls.school_id where u.id = '$user_id' and pk.is_del = 0";
        $res = $this->db->query($sql)->result_array();
        return $res;
    }

    //取出某个孩子的所有家长
    //$except可以是某个家长id，结果中不会包含此家长
    public function get_parents($kid, $except=false){
        $sql = "select u.name realname, u.email, u.phone_mask, pd.bind_phone parent_phone, pk.relation_ship relation, pk.parent_user_id pid from user u "
            ." left join user_parent_data pd on pd.user_id=u.id"
            ." left join $this->_parent_kid_table pk on pk.parent_user_id=u.id where pk.kid_user_id='$kid' and pk.is_del=0 ";
        if($except){
            $sql .= " and pk.parent_user_id != $except ";
        }      
        $res = $this->db->query($sql)->result_array();
        return $res;      
    }

    /*
    获取学生们的家长的id
    $kids是学生的user_id的数组
    */
    function get_kids_parents($kids){
        if(is_array($kids)){
            $kid_str = implode(',',$kids);
            $sql = "select pk.kid_user_id as sid, pk.parent_user_id as pid from $this->_parent_kid_table pk where pk.kid_user_id in ( $kid_str ) and pk.is_del = 0";
            $parents = $this->db->query($sql)->result_array();
            $results = array();
            foreach($parents as $parent){
                $results[$parent['sid']][] = $parent['pid'];
            }
            
            return $results;
        }else{
            return false;
        }
    }

    /*
    获取学生们的姓名
    $kids是学生的user_id=>username的数组
    */
    function get_kids_names($kids){
        if(is_array($kids)){
            $kid_str = implode(',',$kids);
            $sql = "select id,name from `user` where id in ( $kid_str )";
            $parents = $this->db->query($sql)->result_array();
            $results = array();
            foreach($parents as $parent){
                $results[$parent['id']] = $parent['name'];
            }
            
            return $results;
        }else{
            return false;
        }
    }

    //根据家长和学生id获取两者关系
    // $is_del 
    function get_relationship($pid, $kid, $is_del=false){
        $sql = "select p.name as p_name , k.name as kid_name , relation_ship as relation from user p , user k ,$this->_parent_kid_table pk where pk.parent_user_id = '$pid' and p.id='$pid' and pk.kid_user_id='$kid' and k.id='$kid' ";
        if($is_del){
            $sql .= " and is_del = 1";
        }else{
            $sql .= " and is_del = 0";
        }
        $res = $this->db->query($sql)->result_array();
        if($res){
            foreach($res as $k=>$v){
                if(!$v['relation'] || !$v['p_name']){
                    $v['relation'] = '家长';
                    $res[$k] = $v;
                }
            }
        }
        return $res;
    }

    //给新注册的家长帐号绑定演示用的学生帐号
    function add_demo_kid_for_new_parent($parent_id){
        $relation = $this->lang->line('other_relative');
        $res = $this->parents_kids->add_kid($parent_id,Constant::DEMO_KID_USER_ID,$relation);
    }
    
    /**
     * 获取一个学生的最新的一个家长的信息
     */ 
    public function get_lastinfo($user_id, $fields = "b.*"){
        $result = $this->db->query("select {$fields} from $this->_parent_kid_table as a left join user as b on 
            a.parent_user_id=b.id where a.kid_user_id=? order by a.id desc limit 0,1", array($user_id))->result_array();
        return isset($result[0]) ? $result[0] : null;
    }

    function get_user_id_by_student_id($student_id){
        $student_id = intval($student_id);
        $sql = "select id from user where student_id = $student_id limit 1";
        return $this->db->query($sql)->result_array();
    }

    // 获取家长信息
    public function get_info($user_id){
        return $this->db->query("select name,phone,email from user  where id = $user_id limit 1 ")->result_array();
    }

    /*绑定/取绑 操作后，给双方发送通知*/
    function send_binding_notice($p_id,$kid_id,$operater=Constant::USER_TYPE_PARENT,$op='bind'){
        $this->load->library("notice");
        if($operater == Constant::USER_TYPE_PARENT){
            $p = $this->get_info($p_id);
            $p_name = isset($p[0]['name'])?$p[0]['name']:'';
            $msg_data = array("p_name" => $p_name);
            if($op == 'bind'){//孩子收到一条 : 家长{p_name}已成功绑定你的帐号
                $this->notice->add($kid_id, "bind_kid_succ", $msg_data);
            }elseif($op == 'unbind'){
                $this->notice->add($kid_id, "remove_bind_kid", $msg_data);
            }
        }elseif($operater == Constant::USER_TYPE_STUDENT){//kid_bind_succ 
            $s = $this->get_info($kid_id);
            $s_name = isset($s[0]['name'])?$s[0]['name']:'';
            $msg_data = array("s_name" => $s_name);
            if($op == 'bind'){
                $this->notice->add($p_id, "kid_bind_succ", $msg_data);
            }
        }
    }
}
