<?php

class Document_Model extends MY_Model {
	
	const HOMEPAGE_PS_LESSON = "homepage_ps_lesson";

	public $table="lesson_document";
    public $_preview_table = "lesson_preview_doc";
    public $category="category";
    public $category_table="lesson_category";
	private $_doc_type_table="lesson_document_type";
    private $_assess_log_table="lesson_assess_log";
    private $_redis=false;

    public function __construct()
    {
        parent::__construct();
    }


    /*get document*/
    public function get_docs_list($category_id_list,$page_num,$doc_type,$subject_id=0,$total=false,$order_type = 'upload_time',$is_desc="desc")
    {
        $this->load->model("redis/redis_model");
        if($this->redis_model->connect('doc_count'))   
        {
            $this->_redis=true;
        }
        $doc_key = $this->table.'_'.$this->category.'_'.$subject_id.'_'.$doc_type.'_'.$order_type.'_'.$is_desc.'_'.md5(implode('_',$category_id_list));               
        $doc_count=0;
        $doc_content='';
        if($this->_redis)
        {
            if($total)
            {
                $doc_count=$this->cache->get($doc_key);
                if($doc_count) return $doc_count;
            }
            else
            {
                $doc_content=$this->cache->get('doc'.$page_num.'_'.$doc_key);
                if($doc_content) return json_decode($doc_content);
            }
        }
        $this->db->join($this->category_table,$this->category_table.'.doc_id='.$this->table.'.id','left');
        $this->db->where_in($this->category_table.'.'.$this->category.'_id',$category_id_list);
		
        if ($doc_type)
		{
			$sub_types = Constant::new_doc_type($doc_type);
			if($sub_types && is_array($sub_types))$this->db->where_in($this->table.'.doc_type',$sub_types);
            if($sub_types && !is_array($sub_types))$this->db->where($this->table.'.doc_type',$sub_types);
		}

        $this->db->where($this->table.'.status',Constant::SUCCESS_VERIFY);
        $this->db->where($this->table.'.user_operation',1);
        
        if($total&&!$doc_count)
        {
            $this->db->select('count( DISTINCT '.$this->table.'.id) as total');
            $query=$this->db->get($this->table);
            //print_r($this->db);die;
            $doc_count=isset($query->row()->total)?$query->row()->total:0;
            if($this->_redis)
            {
                $this->cache->save($doc_key,$doc_count,Constant::REDIS_DOCCOUNT_TIMEOUT);
            }
            return $doc_count;
        }
        else if(!$total&&!$doc_content)
        {
            $this->db->select($this->table.".*,".$this->category.".name");
            $this->db->join($this->category,$this->category_table.'.'.$this->category.'_id='.$this->category.'.id','left');
            $limit=Constant::LESSON_PER_PAGE;
            if($page_num<=0) $page_num=1;
            $offset=($page_num-1)*$limit;
            $this->db->order_by($this->table.".".$order_type,$is_desc);
            $this->db->order_by($this->table.".id","desc");
            $this->db->group_by($this->table.'.id');
            $this->db->limit($limit,$offset);
            $query=$this->db->get($this->table);
            $doc_content=$query->result();
            if($this->_redis)
            {
                $expire=Constant::REDIS_DOCCOUNT_TIMEOUT;
                $this->cache->save('doc'.$page_num.'_'.$doc_key,json_encode($doc_content),$expire);
            }
            return $doc_content;
        }       
    }

    /**
     * 根据文档ID列表获取文档信息
     * @param  array $doc_id_list 文档ID数组
     * @return type              
     */
    public function get_doc_by_ids($doc_id_list)
    {
		if(is_array($doc_id_list)&&!empty($doc_id_list))
		{
     		$this->db->where_in('id',$doc_id_list);
			$query=$this->db->get($this->table);
        	return $query->result();
		}
		else
		{
			return false;
		}
    }

    /**
     * 获取一个文档的所有信息
     * @param  int $doc_id 文档id
     * @return $source_file
     */
    public function get_single_doc_info($doc_id, $is_add_sub_files = false)
    {
        $this->db->select($this->table.".*,".$this->category_table.".category_id");
        $this->db->join($this->category_table,$this->category_table.'.doc_id='.$this->table.'.id','left');
        $this->db->where(array($this->table.'.id'=>$doc_id,$this->table.'.status'=>1));
        $this->db->limit(1);
        $source_file = $this->db->get($this->table)->row();
        if($source_file && $is_add_sub_files)
        {
            $this->load->model('lesson/document_preview_model', 'doc_split');
            $source_file->split_files = $this->doc_split->get_split_files($doc_id);
            return $source_file;
        }
        else
        {
            return $source_file;
        }
    }

    /**
     * 更新文档浏览量、下载量、收藏量
     * @param  int $doc_id 文档ID
     * @param  string $field  更新的字段
     * @return boolean
     */
    public function update_statistics($doc_id, $field)
    {
        $sql_str = "UPDATE {$this->table} SET {$field}={$field}+1 WHERE `id`={$doc_id}";
        $this->db->query($sql_str);
        $affected_row = $this->db->affected_rows(); 
        if($affected_row > 0) return TRUE;
        else return FALSE;
    }

    /**
     *根据源文档ID获取分割后文件列表
     * @param  int $doc_id 源文件ID
     * @return array    
     */
    public function get_preview_files($doc_id,$uri_api)
    {
        $this->db->select('load_path');
        $this->db->order_by('begin_page','asc');
        $query = $this->db->get_where($this->_preview_table, array('doc_id'=>$doc_id));
        $swf_arr = array();
        if($query->result())
        {
            foreach($query->result() as $item)
            {
                $swf_arr[] = $uri_api.$item->load_path;
            }
        }
        return $swf_arr;
    }

    public function get_preview_files_new($doc_id,$uri_api)
    {
        $this->db->limit(1);
        $preview_data = $this->db->get_where('lesson_preview_doc_new', array('doc_id'=>$doc_id))->row();
        $swf_list = array();
        if($preview_data)
        {
            $i = 0;
            while ( $i < $preview_data->page_count) {
                $page = $i+1;
                $swf_list[$i] = $uri_api.$preview_data->swf_folder_path."/preview_{$page}.swf";
                $i++;
            }
        }
        return $swf_list;
    }

    /**
     * 判断文档是否存在
     * @param  int $doc_id 源文件ID
     * @return boolean        
     */
    public function is_exist_doc($doc_id)
    {
        $query = $this->db->get_where($this->table, array('id'=>$doc_id));
        return $query->row() ? TRUE : FALSE;
    }

    /**
     * 判断用户是否已经评价过文档
     * @param  int  $uid
     * @param  int  $doc_id
     * @return void
     */
    private function is_exist_assess_log($uid,$doc_id)
    {
        $this->db->where(array('uid'=>$uid,'doc_id'=>$doc_id));
        $data = $this->db->get($this->_assess_log_table)->result();
        if($data) return false;
        else return true;
    }

    /**
     * 添加用户评分记录
     * @param int  $uid
     * @param int  $doc_id
     * @param int $score 评分
     * @return void
     */
    public function set_doc_assess_log($uid,$doc_id,$score)
    {
        if(!self::is_exist_assess_log($uid,$doc_id)){
            return -1;
        }

        $this->db->trans_start();
        $this->db->insert($this->_assess_log_table,array('uid'=>$uid,'doc_id'=>$doc_id,'score'=>$score));
        $this->db->query("UPDATE {$this->table} SET `score`=`score`+? WHERE `id`=?",array($score,$doc_id));
        $this->db->trans_complete();
        if (false === $this->db->trans_status()){
            return -2;
        }
        $this->load->model("redis/redis_model");
        if($this->redis_model->connect('doc_count'))   
        {
            $this->_redis=true;
        }
        $assess_key = $this->_assess_log_table.'_'.$doc_id; 
        $expire=Constant::REDIS_DOCCOUNT_TIMEOUT;              
        $assess_count = 0;
        if($this->_redis)
        {
            $assess_info=$this->cache->get($assess_key);
            $assess_info = json_decode($assess_info,true);
            $assess_info['count']+=1;
            $assess_info['score']+=$score;
            $this->cache->save($assess_key,json_encode($assess_info),$expire);
        }
        return $this->db->insert_id();
    }

    /**
     * 获取文档被评星次数
     * @param int  $doc_id
     * @return void
     */
    public function get_doc_assess_log($doc_id,$score)
    {
        $this->load->model("redis/redis_model");
        if($this->redis_model->connect('doc_count'))   
        {
            $this->_redis=true;
        }
        $assess_key = $this->_assess_log_table.'_'.$doc_id; 
        $expire=Constant::REDIS_DOCCOUNT_TIMEOUT;              
        if($this->_redis)
        {
            $assess_info=$this->cache->get($assess_key);
            if(false===$assess_info){
                $this->db->select("COUNT(`ID`) AS num");
                $assess_count = $this->db->get_where($this->_assess_log_table,array('doc_id'=>$doc_id))->row()->num;
                $assess_info = array('count'=>$assess_count,'score'=>$score);
                $this->cache->save($assess_key,json_encode($assess_info),$expire);
            }else{
                $assess_info = json_decode($assess_info,true);
            }
            return $assess_info;

        }else{
            $this->db->select("COUNT(`ID`) AS num");
            $assess_count = $this->db->get_where($this->_assess_log_table,array('doc_id'=>$doc_id))->row()->num;
            $assess_info = array('count'=>$assess_count,'score'=>$score);
            return $assess_info;
        }
    }


    public function get_relation_doc_by_course($category_id,$doc_id)
    {
        $this->db->select("lesson_document.id,lesson_document.file_name,lesson_document.file_ext,
            lesson_document.hits,lesson_document.page_count,lesson_document.rand_num");
        $this->db->join('lesson_category','lesson_category.doc_id=lesson_document.id','left');
        $this->db->where(array(
            'lesson_category.category_id'=>$category_id,
            'lesson_document.status'=>1,
            'lesson_document.id !='=>$doc_id));
        $this->db->order_by('lesson_document.hits','desc');
        $this->db->order_by('lesson_document.upload_time','desc');
        $this->db->limit(Constant::RELATED_TOP_DOC_NUM);
        return $this->db->get('lesson_document')->result();
    }  
    
    // 首页推荐
    public function homepage_position(){
		$fields = "default";
		$this->load->model("redis/redis_model");
        $data = array();
    	if($this->redis_model->connect("statistics")){
			$data = $this->cache->hget(self::HOMEPAGE_PS_LESSON, $fields);
			if ($data){
				$data = unserialize($data);
			}
		}
		if (!$data or $data["last_update"] != date("Y-m-d")){
			/**
			$data = array();
			$this->db->select("id,doc_type,file_name,file_ext,hits,subject_id");
			$this->db->where("subject_id > 0");
			$this->db->where("doc_type != 8");		//不要其它类型的课件
			$this->db->where("status", "1");
			$this->db->order_by("hits", "desc");
			$this->db->limit(100, 0);
			$res = $this->db->get($this->table);
			$alldata = $res->result_array();
			if ($alldata){
				if (count($alldata) > 7){
					$rand = array_rand($alldata, 7);
					foreach ($rand as $value){
						$data["data"][] = $alldata[$value];
					}
				} else {
					$data["data"] = $alldata;
				}
			} else {
				$data["data"] = array();
			}
			*/
			
			$date = date("Y-m-d H:i:s");
			$data = array();
			$this->db->select("doc_id as id,doc_type,file_name,file_ext,hits,subject_id");
			$this->db->where("start_date <= '{$date}'");
			$this->db->where("last_date > '{$date}'");
			$this->db->limit(7, 0);
			$data["data"] = $this->db->get("lesson_position")->result_array();
			if (count($data["data"]) < 7){
				$this->db->select("doc_id as id,doc_type,file_name,file_ext,hits,subject_id");
				$this->db->limit(7, 0);
				$data["data"] = $this->db->get("lesson_position")->result_array();
			}
			
			$data["last_update"] = date("Y-m-d");
            if($this->redis_model->connect("statistics")){
                $this->cache->hset(self::HOMEPAGE_PS_LESSON, $fields, serialize($data));
            }
		}
    	return $data;
	}
	
	/* 通过ids获取文档 */
	public function get_by_ids($ids, $offset, $pagesize, $fields = "*"){
		$res = $this->db->query("select {$fields} from lesson_document where id in ({$ids}) 
			order by upload_time desc limit {$offset},{$pagesize}")->result_array();
		return $res;
	}

    /*获取备课文件所属用户id*/
    public function get_file_owner($file_id)
    {
        $file_data = $this->db->get_where('lesson_document',array('id'=>$file_id))->row();
        return isset($file_data->user_id)?$file_data->user_id:0;
    }
}

/* end of document_model.php */
