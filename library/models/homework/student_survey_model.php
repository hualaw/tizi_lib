<?php

class Student_Survey_Model extends LI_Model{

    private $_tb_name;

    public function __construct(){

        parent::__construct();
        $this->_tb_name = 'student_survey';

    }

    public function add($data){

        return $this->db->insert(
            $this->_tb_name,
            $data
        );
    }

    public function getData($id){
               
        return $this->db
            ->query("select * from {$this->_tb_name} where `id` = {$id}")
            ->row_array();

    }


}
