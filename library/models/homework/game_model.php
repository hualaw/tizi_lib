<?php

class Game_Model extends MY_Model {

    function __construct(){

        parent::__construct();

    }

    public function get_game_info($game_id){
    
        return $this->db
            ->query("select * from `game` where `id` = {$game_id}")
            ->row_array();
        
    }

	public function get_question($category_id, $game_id, $question_num = ''){
		
        $sql = "select * from `game_question` where `category_id` = {$category_id} and `game_type` = {$game_id} order by rand()";
        if($question_num){
            $sql .= " limit 0, $question_num ";
        }
		$result = $this->db
			->query($sql)
			->result_array();
		$game_data = array();
		foreach($result as $val){
			$game_data[] = json_decode($val['content'], true);
		}
		return $game_data;
		
	}

	public function game_word($word_id){
		
		$word = $this->db
			->get_where('game_word', array('id' => $word_id,'is_online' => 1))
			->row_array();

		return $word;
		
	}

    public function game_word_image($word){
        
		$word = $this->db
			->query("select * from `game_word_image` where `image` like '{$word}.%'")
			->row_array();

		return isset($word['image'])? $word['image'] : false;
        
    }

	






}

