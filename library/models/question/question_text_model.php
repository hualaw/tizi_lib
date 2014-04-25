<?php
/**
 * 获取组卷题目。手机apps调用。限制访问频率
 * @author jiangwuzhang
 *
 */
class Question_Text_Model extends MY_Model{
	
	const REDIS_POINT_COUNT = 'point_question_count_';
	
	const REDIS_SUBJECT_COUNT = 'subject_question_count_';
	
	const REDIS_TOP = 'question_top';
	
	const REDIS_COUNT_CACHE = 864000;		//缓存10天
	
	public function point_get($point_id, $offset = 0, $pegesize = 10){
		$this->load->model('question/question_category_model');
		$node_tree = $this->question_category_model->get_node_tree($point_id);
		$ids = array();
		foreach ($node_tree as $value){
			$ids[] = $value->id;
		}
		$ids_str = implode(',', $ids);
		$question = $this->point_question($ids_str, $offset, $pegesize);
		
		$this->load->model('redis/redis_model');
		if($this->redis_model->connect('apps_oauth')){
			$redis_key = self::REDIS_POINT_COUNT.$point_id;
			$total = $this->cache->get($redis_key);
			if (!$total){
				$total = $this->point_count($ids_str);
				$this->cache->set($redis_key, $total, self::REDIS_COUNT_CACHE);
			}
		} else {
			$total = $this->point_count($ids_str);
		}
		$data = array('count'=>$total, 'question'=>$question);
		return $data;
	}
	
	public function point_count($ids){
		$sql_str = "select count(*) as total from question_category where category_id in ({$ids})";
		$result = $this->db->query($sql_str, array($ids))->result_array();
		return isset($result[0]['total']) ? $result[0]['total'] : 0;
	}
	
	public function point_question($ids_str, $offset, $pegesize){
		if (($offset + $pegesize) > Constant::AQ_GET_QUESTION_MAX){
			return null;
		}
		$sql_str = "select a.* from question_text as a left join question_category as b 
				on a.id=b.question_id where b.category_id in (?) order by a.id desc limit ?,?";
		$result = $this->db->query($sql_str, array($ids_str, $offset, $pegesize))->result_array();
		return $result;
	}
	
	public function count_subject($subject_id){
		$this->load->model('redis/redis_model');
		if($this->redis_model->connect('apps_oauth')){
			$redis_key = self::REDIS_SUBJECT_COUNT.$subject_id;
			$total = $this->cache->get($redis_key);
		}
		if (!isset($total) || !$total){
			$subject_ids = $this->db->query('select id from subject where type=?', array($subject_id))->result_array();
			
			$this->load->model('question/question_category_model');
			$root_ids = array();
			foreach ($subject_ids as $value){
				$temps = $this->question_category_model->get_root_id($value['id']);
				if (isset($temps[0]->id)){
					$root_ids[] = $temps[0]->id;
				}
			}			
			$subtree_ids = array();
			$subtree = $this->db->query("select node.id from category as node,category as parent 
             		where (node.lft between parent.lft and parent.rgt) and parent.id in (".implode(',', $root_ids).") 
             		order by node.lft")->result_array();
			foreach ($subtree as $value){
				$subtree_ids[] = $value['id'];
			}
			
			$sql_str = "select count(*) as total from question_category where category_id in (".implode(',', $subtree_ids).")";
			$result = $this->db->query($sql_str)->result_array();
			$total = isset($result[0]['total']) ? $result[0]['total'] : 0;
			
			if($this->redis_model->connect('apps_oauth')){
				$redis_key = self::REDIS_SUBJECT_COUNT.$subject_id;
				$this->cache->set($redis_key, $total, self::REDIS_COUNT_CACHE);
			}
		}
		return $total;
	}
	
	public function get_top($page, $pagesize){
		$this->load->model('redis/redis_model');
		$redis_key = self::REDIS_TOP;
		$offset = ($page - 1) * $pagesize;
		if($this->redis_model->connect('apps_oauth')){
			$question_ids = $this->cache->zrevrange(self::REDIS_TOP, $offset, $pagesize+$offset);
		}
		if (!isset($question_ids) || ($offset == 0 && empty($question_ids))){
			$question_ids = $this->build_top($page, $pagesize);
		}
		$question_ids = array_keys($question_ids);
		if (!empty($question_ids)){
			$questions = $this->db->query("select id,body,answer,analysis from question_text where 
				id in (".implode(',', $question_ids).")")->result_array();
		} else {
			$questions = array();
		}
		return $questions;
	}
	
	private function build_top($page, $pagesize){
		$offset = ($page - 1) * $pagesize;
		//获取所有科目的所有知识点
		$this->load->model('aq/aq_point_model');
		$points = $this->aq_point_model->all();
		$points_str = '';
		foreach ($points as $value){
			$points_str .= $points_str === '' ? '' : ',';
			$points_str .= $value['aq_point_id'];
		}
		$sql_str = "select node.id from category as node,category as parent
						where (node.lft between parent.lft and parent.rgt) and parent.id in ({$points_str})
						order by node.lft";
		$node_points = $this->db->query($sql_str)->result_array();
		$question_ids = array();
		foreach ($node_points as $value){
			$limit = $this->db->query("select question_id from question_category where 
					category_id=".$value['id']." order by id desc limit 0,10")->result_array();
			foreach ($limit as $value){
				$question_ids[] = $value['question_id'];
			}
		}
		$this->load->model('redis/redis_model');
		if ($this->redis_model->connect('apps_oauth') && is_array($question_ids)){
			foreach($question_ids as $value){
				$this->cache->zadd(self::REDIS_TOP, 0, $value);
			}
		}
		return $question_ids;
	}
	
	public function click($question_id){
		$this->load->model('redis/redis_model');
		if ($this->redis_model->connect('apps_oauth')){
			$this->cache->zincrby(self::REDIS_TOP, 1, $question_id);
		}
	}
}
