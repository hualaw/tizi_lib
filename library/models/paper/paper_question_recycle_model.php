<?php

class Paper_Question_Recycle_Model extends MY_Model {

	private $_table="paper_question_recycle";

    function __construct()
    {
        parent::__construct();
    }

    // 从试卷中回收多条试题
    function add_recycle_records ($paper_question_id_list, $test_paper_id) {
        $this->load->helper('date');

        $this->db->where_in('id', $paper_question_id_list);
        $question_id_list = $this->db->get('paper_question')->result();
        $data_arr = array();
        foreach($question_id_list as $question){
            $now = date("Y-m-d H:i:s");
            $data = array(
                'testpaper_id' => $test_paper_id,
                'qtype_id' => $question->qtype_id,
                'question_id' => $question->question_id,
                'delete_time' => $now,
                'paper_question_id' => $question->id,
                'is_remove' => FALSE
            );
            array_push($data_arr, $data);
        }
        $this->db->insert_batch($this->_table, $data_arr);
    }

    /* 将从试卷钟删除的试题添加到回收站 */
    function add_recycle_record($paper_question_id, $test_paper_id)
    {
        $this->load->helper('date');
        $now = date("Y-m-d H:i:s");

        $this->db->where('id', $paper_question_id);
        $question_id = $this->db->get('paper_question')->row();

        $data = array(
            'testpaper_id' => $test_paper_id,
            'qtype_id'=>$question_id->qtype_id,	
            'question_id' => $question_id->question_id,
            'delete_time' => $now,
            'paper_question_id' => $paper_question_id,
            'is_remove' => FALSE
        );

        $this->db->insert($this->_table, $data);
    }

    /* 将选择的试题从回收站中删除 */
    function delete_records($record_id_list)
    {
        $this->db->trans_start();
        foreach ($record_id_list as $record_id)
        {
            $data = array('is_remove', TRUE);
            $this->db->where('id', $record_id);
            $this->db->update($this->_table, $data);
        }
        $this->db->trans_complete();

        if ($this->db->trans_status === FALSE)
        {
            /* log error message */ 
        }
    
    }

    /* 查询得到回收站中的试题 */
    function get_recycle_records($test_paper_id, $column=null)
    {
        $this->db->where('testpaper_id', $test_paper_id);
        $this->db->where('is_remvoe', FALSE);
        if(!is_null($column)) {
            $this->db->select('id');
        }
        $recycled_questions = $this->db->get($this->_table)->result();
        $data = array();

        foreach ($recycled_questions as $recycled_question)
        {
            $q_id = $recycled_question->question_id;
            $this->db->where('id', $q_id);
            $question = $this->db->get('question')->row();
            array_push($data, $question);
        }

        return $data;
    }


    /* 恢复选中的试题 */
    function recover_records($record_id_list)
    {
        $data = array();

        $this->db->trans_start();
        $this->delete_record($record_id_list);

        foreach ($record_id_list as $record_id)
        {
            $this->db->where('id', $record_id);
            $recycled = $this->db->get($this->_table)->row();
            $paper_question_id = $recycled->paperquestion_id;

            /* 如果要恢复试题的题型已删除，则也须恢复 */
            $this->db->where('id', $paper_question_id);
            $question_type_id = $this->db->get('paper_question')->row()->qtype_id;
            $this->db->where('id', $question_type_id);
            $this->db->where('is_delete', TRUE);
            $this->db->update('paper_question', array('is_delete' => FALSE));

            $sub_data = array(
                'id' => $paper_question_id,
                'is_delete' => FALSE
            );
            array_push($data, $sub_data);
        }

        $this->db->update_batch('paper_question', $data, 'id');

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE)
        {
            /* log error message */
        }
    
    }
}

/* end of question_recycle.php */
