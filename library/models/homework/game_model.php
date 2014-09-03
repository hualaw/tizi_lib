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

    public function get_game_type($game_id, $category_id) {
        
        $game_type =  $this->db
            ->query("select * from `game_type_info` as a left join `game_type_unit` as b on a.`gtu_id` = b.`id` where a.`game_id` = {$game_id}  and b.`unit_id` = {$category_id}")
            ->row_array();

        return isset($game_type['game_type_id']) ? $game_type['game_type_id'] : false;
    }

	public function get_question($category_id, $game_type, $question_num = ''){
		
        $sql = "select * from `game_question` where `category_id` = {$category_id} and `game_type` = {$game_type} and is_online=1";
		$result = $this->db
			->query($sql)
			->result_array();
        shuffle($result);
        if($question_num && count($result) > $question_num){
            $result = array_slice($result, 0, $question_num);
        }
        $game_data = array();
		foreach($result as $val){
            $game_data[] = array_merge(array('id'=>$val['id']),
                json_decode($val['content'], true)
            );
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

