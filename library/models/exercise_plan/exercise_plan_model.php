<?php
class Exercise_Plan_Model extends LI_Model{
    private $_table = 'homework_assign';
    protected $_redis = false;
    const TEACHER_HW_TOTAL = "teacher_hw_total";

    function __construct(){
        parent::__construct();
    }

    function teacher_ex_total($user_id,$incr=0){
        if($this->redis_model->connect('statistics')){
            $this->_redis=true;
        }
        if($this->_redis){
            $value = $this->cache->hget(self::TEACHER_HW_TOTAL, $user_id);
            if($value === false){  //redis中没有相应数据就执行sql
                $sql = "select count(*) as num from $this->_table where user_id = ? and is_assigned = 1";
                $arr = array($user_id);
                $value = $this->db->query($sql,$arr)->row(0)->num;
                $this->cache->hset(self::TEACHER_HW_TOTAL, $user_id, $value);
            }
            if($incr!=0){
                $value += $incr;
                $this->cache->hset(self::TEACHER_HW_TOTAL, $user_id, $value);   
            }
        }else{
            //没有redis 执行sql
            $sql = "select count(*) as num from $this->_table where user_id = ? and is_assigned = 1";
            $arr = array($user_id);
            $value = $this->db->query($sql,$arr)->row(0)->num;
        }
        return $value;
    }
     

}

/*end of homework_assign_model.php*/
