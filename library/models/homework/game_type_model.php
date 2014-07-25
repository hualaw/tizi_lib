<?php

class Game_Type_Model extends MY_Model {

    function __construct(){
        parent::__construct();
    }

    //获取game id 和 游戏类型（game type）
    function get_game_with_game_type($game_id){
        $select  =  "game.id as game_id, gt.name as type_name ";
        $sql="select $select from game LEFT JOIN game_type gt on gt.id=game.game_type where game.id=$game_id";
        $res = $this->db->query($sql)->row_array();
        return $res;
    }
     

}

