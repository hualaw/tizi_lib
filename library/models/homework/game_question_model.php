<?php

class Game_Question_Model extends MY_Model {

    function __construct(){
        parent::__construct();
    }

    //一个category下是否有题目
    public function get_q_count($cat_id){
        $sql = "select count(1) from `game_question` where `category_id` = {$cat_id} ";
        $res = $this->db->query($sql)->row_array();        
        return $res;
    }

     
    






}

