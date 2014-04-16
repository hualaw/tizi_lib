<?php

class Oauth_Model extends MY_Model{

    private $_table;

    public function __construct(){
        
        parent::__construct();

        $this->_table = 'session_oauth';

    }
    /**
     * @param platform  tinyint(1:qq, 2:sina_weibo)
     *
     */

    public function getData($open_id, $platform){
        
        $where_data = array(
            'open_id' => $open_id,
            'platform' => $platform,  
        );

        $result = $this->db
                ->get_where($this->_table, $where_data)
                ->row_array();

        return $result;

    }

    /**
     * 
     * @param open_id int
     * @param platform  tinyint(1:qq, 2:sina_weibo)
     * @param user_id int
     * @param access_token char
     */
    public function save($data){
        
        return $this->db->replace($this->_table, $data);

    }



}  

