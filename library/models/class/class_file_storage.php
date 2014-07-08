<?php
/* tizi 4.0  还没用到  ；  班级空间 分享的文件  容量相关方法 */
class Class_File_Storage extends LI_Model {
    private $_redis = false;
    public function __construct(){
        parent::__construct();
        $this->load->model("redis/redis_model");
        if($this->redis_model->connect('cloud_statistics'))   
        {
            $this->_redis=$this->cache->redis;
        }
    }

    function get_used_size($class_id){
        
    }
     
}

 