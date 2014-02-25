<?php

/**
 * Description of JXT_Model
 *
 * @author caohaihong <caohaihong@91waijiao.com>
 */
class JXT_Model extends CI_Model{
    protected $database;

    public function __construct() {
        parent::__construct();
        $this->set_database();
    }
    
    private function set_database(){
        $this->database = $this->load->database('jxt', TRUE);
    }

}
