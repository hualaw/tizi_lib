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
    public function save($data, $oauth_id=''){

        if(empty($oauth_id)){
            $open_id = $data['open_id'];
            $platform = $data['platform'];
            $result = $this->getData($open_id, $platform);
            if(empty($result)){
                $this->db->insert($this->_table, $data);
                $oauth_id = $this->db->insert_id();
                $user_id = '';
            }else{
                $this->db->where('open_id', $open_id);
                $this->db->where('platform', $platform);
                $this->db->update($this->_table, $data);
                $user_id = $result['user_id'];
                $oauth_id = $result['id'];
            }
        }else{
            if(!isset($data['user_id']) || !$data['user_id']){
                return false;
            }
            $this->db->where('id', $oauth_id);
            $this->db->update($this->_table, $data);
            $user_id = $data['user_id'];
        }
        return array('oauth_id'=>$oauth_id, 'user_id'=>$user_id);

    }
	
	/**
	 * @oauth_id int
	 */ 
	public function id_get($oauth_id){
		$res = $this->db->query("select * from session_oauth where id=?", array($oauth_id))->row_array();
		return $res;
	}

}  

