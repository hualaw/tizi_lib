<?php

class Question_level_Model extends MY_Model {
    
    function __construct()
    {
        parent::__construct();
    }

    /* 得到所有难度的名字 */
    function get_question_level_names()
    {
        $this->db->select('id, name, level');
        $query = $this->db->get('question_level');
        return $query->result();
    }

}

/* end of question_level.php */
