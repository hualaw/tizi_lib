<?php

class Document_Download_Model extends MY_Model {

	public $_table="lesson_download_log";
    private $_redis=false;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取用户下载文档记录
     * @param  int $user_id 用户ID
     * @return 
     */
    public function get_user_downloads($user_id)
    {
        $query = $this->db->get_where($this->_table, array('user_id' => $user_id,'is_delete'=>0));
        return $query->result();
    }

    public function get_lesson_down_statistics($user_id,$type = 'd')
    {
        $this->load->model('redis/redis_model');
        $count = 0;
        if($this->redis_model->connect('download'))
        {
            $this->_redis=true;
            $key = '';
            switch ($type) {
                case 'd':
                    $key = date('Y-m-d').'_lesson_doc_key_'.$user_id;
                    break;
                case 'm':
                    $key = date('Y-m').'_lesson_doc_key_'.$user_id;
                    break;
                default:
                    return 0;
                    break;
            }
        }   
        if($this->_redis)
        {
            $count = $this->cache->get($key);
            if(false===$count){
                $count = $this->get_user_download_cout($user_id,$type);
                $this->cache->save($key,$count,86400);
            }
        }else{
            $count = $this->get_user_download_cout($user_id,$type);
        }
        return $count;
    }

    /**
     * 查询用户下载文档数量
     * @param  int  $user_id 用户ID
     * @param  boolean $is_all  true：全部数量，false:当天数量
     * @return int 数量
     */
    public function get_user_download_cout($user_id, $type = 'd')
    {
        $where_arr = array('user_id' => $user_id);
        switch ($type) {
            case 'd':
                $where_arr['download_time >'] = strtotime(date('Ymd'));
                $where_arr['download_time <='] = strtotime(date('Ymd'))+86400;
                break;
            case 'm':
                $begin_this_month=mktime(0,0,0,date('m'),1,date('Y'));
                $end_this_month=mktime(23,59,59,date('m'),date('t'),date('Y'));
                $where_arr['download_time >'] = $begin_this_month;
                $where_arr['download_time <='] = $end_this_month;
                break;
            default:
                break;
        }
        $this->db->select('count(*) as num');
        $query = $this->db->get_where($this->_table, $where_arr)->row();
        return $query->num;
    }

    /**
     * 添加下载记录
     * @param int  $user_id 用户ID
     * @param array $insert_data 下载文档数据
     * @return array 状态信息
     */
    public function add_download_info($user_id, $insert_data)
    {
        if($this->is_exits_download($user_id, intval($insert_data->id)))
        {
            $errorcode = true;
            $error = '';
        }
        else
        {
            $insert_array = array(
                'user_id'=>$user_id,
                'download_name'=>$insert_data->file_name,
                'download_time'=>$_SERVER['REQUEST_TIME'],
                'download_ip'=>ip2long(get_remote_ip()),
                'download_link'=>$insert_data->file_path,
                'doc_id'=>intval($insert_data->id),
                'doc_type'=>$insert_data->doc_type
                ); 
            $this->db->insert($this->_table, $insert_array);
            $insert_id = $this->db->insert_id(); 
            if($insert_id)
            {
                $this->load->model('lesson/document_model');
                $status = $this->document_model->update_statistics(intval($insert_data->id), 'downloads');
                $errorcode = true;
                $error = '';
            }
            else
            {
                $errorcode = false;
                $error = '';
            }
        }
        return array('errorcode'=>$errorcode,'error'=>$error);
    }

    function is_exits_download($user_id, $doc_id)
    {
        $query = $this->db->get_where($this->_table, array('user_id'=>$user_id, 'doc_id'=>$doc_id));
        return $query->row() ? TRUE : FALSE;
    }

}

/* end of document_download_model.php */
