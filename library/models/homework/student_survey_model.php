<?php

class Student_Survey_Model extends LI_Model{

    private $_tb_name;

    public function __construct(){

        parent::__construct();
        $this->_db_name = 'survey';

    }

    public function add($data){

        return $this->db->insert(
            $this->_db_name,
            $data
        );
    }

    public function getData($id){
               
        return $this->db
            ->query("select * from `survey` where `id` = {$id}")
            ->row_array();

    }


}
