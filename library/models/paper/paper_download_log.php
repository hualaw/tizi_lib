<?php

class Paper_Download_Log extends MY_Model {
	
	protected $_table="paper_download_log";
	protected $_paper_table="paper_testpaper";
	protected $_paper_id="testpaper_id";

    function __construct()
    {
        parent::__construct();
    }

    /* 添加下载纪录 */
    function add_download_record($user_id, $logname, $paper_id, $word_version, $paper_size, $paper_type, $download_link)
    {
        $ip = $this->input->ip_address();
        if ($this->input->valid_ip($ip));
        {
            $valid_ip_address = $ip;
        }
        $valid_ip_address = get_remote_ip();
        $this->load->helper('date');
        $now = date("Y-m-d H:i:s");
        
        $data = array(
            'user_id' => $user_id,
            'word_version' => $word_version,
            'paper_size' => $paper_size,
            'paper_type' => $paper_type,
            'ip' => $valid_ip_address,
            'download_time' => $now,
            'download_link' => $download_link,
            'logname' => $logname,
            $this->_paper_id => $paper_id,
            'is_delete' => FALSE
        );

        return $this->db->insert($this->_table, $data);
    }

    /* 删除下载记录 */
    function delete_download_log($download_log_id)
    {
        $data = array('is_delete' => TRUE); 
        $this->db->trans_start();
        $this->db->where('id', $download_log_id);
        $this->db->update($this->_table, $data);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE)
        {
            /* log error message */
        }
    }

    function get_paper_id_from_log($download_log_id,$user_id)
    {
        $this->db->select($this->_paper_id);
        $this->db->where('user_id',$user_id);
        $this->db->where('id',$download_log_id);
        $query=$this->db->get($this->_table);
        if($query->num_rows()==1)
        {
            return $query->row()->{$this->_paper_id};
        }
        else
        {
            return false;
        }   
    }

	function get_log_by_id($download_log_id,$user_id)
    {
        $this->db->where('user_id',$user_id);
        $this->db->where('id',$download_log_id);
        $query=$this->db->get($this->_table);
        if($query->num_rows()==1)
        {
            return $query->row();
        }
        else
        {
            return false;
        }
    }
    
    // 查询下载记录 
    function get_download_logs($subject_id,$user_id,$page_num=1,$where_type=0,$total=false)
    {
        if($subject_id)
        {
            $this->db->join($this->_paper_table,"{$this->_table}.{$this->_paper_id}={$this->_paper_table}.id",'left');
            $this->db->where("{$this->_paper_table}.subject_id",$subject_id);
        }   
        $this->db->select("{$this->_table}.*");
        //$this->db->where("{$this->_table}.is_delete",0);
        $this->db->where("{$this->_table}.user_id",$user_id);
        $this->db->order_by("{$this->_table}.download_time","desc");

        if($where_type)
        {
            switch($where_type)
            {
                case 1: $this->db->where("{$this->_table}.download_time >=",date("Y-m-d")." 00:00:00");
                        break;
                case 2: $yesterday=date("Y-m-d",strtotime("yesterday"));    
                        $this->db->where("{$this->_table}.download_time >=",$yesterday." 00:00:00");
                        $this->db->where("{$this->_table}.download_time <=",$yesterday." 23:59:59");
                        break;
                case 3: $week=date("Y-m-d",strtotime("this week")); 
                        $this->db->where("{$this->_table}.download_time >=",$week." 00:00:00"); 
                        break;
                case 4: $this->db->where("{$this->_table}.download_time >=",date("Y-m")."-1 00:00:00");
                        break;
                default:break;
            }
        }

        if($total)
        {
            $query=$this->db->get($this->_table);   
            return $query->num_rows();
        }

        $limit=Constant::QUESTION_PER_PAGE;
        if($page_num<=0) $page_num=1;
        $offset=($page_num-1)*$limit;
        $this->db->limit($limit,$offset);
        return $this->db->get($this->_table)->result();
    }
}

/* end of paper_download_log.php */
