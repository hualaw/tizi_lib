<?php
require_once('paper_save_log.php');

class Homework_Save_Log extends Paper_Save_Log {

    function __construct()
    {
        parent::__construct();
		$this->_table="homework_save_log";
		$this->_paper_table="homework_paper";
        $this->_paper_question_table="homework_question";
		$this->_paper_id="paper_id";
    }

    //获取作业存档
    private function get_homework_archive($user_id,$time=null){
        $select = "select id, logname, save_time,paper_id ";
        $where = " from $this->_table where user_id=$user_id and is_delete = 0 ";
        if($time){
            $time = " and save_time between {$time[0]} and {$time[1]} ";
        }
        $limit = " order by id ";
        $sql = $select.$where.$time.$limit;
        $res = $this->db->query($sql)->result_array();

        $select = "select count(1) as counts ";
        if(!$time){
            $time = " ";
        }
        $sql_count = $select.$where.$time;
        $res['total_counts'] = $this->db->query($sql_count)->row(0)->counts;
        return $res;
    }

    private function delete_archive($id){
        $this->db->where('id',$id);
        $this->db->set('is_delete',1);
        $this->db->update($this->_table);
        $affected_row=$this->db->affected_rows();
        return $affected_row;
    }

}

/* end of homework_save_log.php */
