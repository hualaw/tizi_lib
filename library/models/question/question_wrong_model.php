<?php
class Question_Wrong_Model extends MY_Model {
	private $_question_table = 'exercise';//'question';//练习题表
	private $_question_category_table = 'exercise_category';//"question_category";//练习题知识点表
	private $_category_table = "category";//知识点表

    function __construct()
    {
        parent::__construct();
    }
    /**
	 * 根据id获取问题难度
	 * @param  mixed $id_list id集合
	 * @return array 问题id集合数组
	 */
	public function get_questions_in_ids($id_list){
		if(is_array($id_list)){
			$id_list = implode(",", $id_list);
		}
		
		$sql = "SELECT id,level_id FROM " . $this->_question_table . " WHERE id in (" . $id_list . ")";
		$query   = $this->db->query($sql);
		$results = $query->result_array();
		return $results;
	}
	
	/**
	 * 根据id获取问题知识点
	 * @param  mixed $question_ids 问题id集合，数组或以,分割的字符串
	 * @return array 问题id集合知识点数组
	 */
	public function getQuestionsCategory($question_ids){
		if(is_array($question_ids)){
			$question_ids = implode(",", $question_ids);
		}
		
		$sql      = 'SELECT question_id,category_id FROM ' . $this->_question_category_table . ' WHERE question_id in (' . $question_ids . ")";
		$query    = $this->db->query($sql);
		$results  = $query->result_array();	
		
		return $results;
	}
	
	/**
	 * 获取若干节点
	 */
	public function getInNodes($category_ids){
		if(empty($category_ids)){
			return array();
		}
		
		if(is_array($category_ids)){
			$category_ids = implode(",", $category_ids);
		}
		$query  = $this->db->query("select id,name,lft,rgt from " . $this->_category_table . " where id in (" . $category_ids . ")");
		
		$categories = $query->result_array();
		$results    = array();
		foreach($categories as $category){
			$results[$category["id"]] = $category;
		}
		
		return $results;
	}
	
    /**
	 * 验证科目当前根知识点id
	 * @param  int $subject_id 科目id
	 * @param  int $cid		   当前根知识点id，若存在且>0，只用于ajax下层知识点，不能和科目id的知识点id重复
	 * @return int 科目当前根知识点
	 */
	public function getCategoryId($subject_id, $cid=0){
		$cid 		= intval($cid);
		$subject_id = intval($subject_id);
		
		if($cid > 0){
			$sql   = "select id,lft,rgt from " . $this->_category_table . " where (subject_id=$subject_id  and depth=1) or id=$cid";
			$query = $this->db->query($sql);
			$cats  = $query->result_array();
			$count = count($cats);
			if($count == 2){
				if(($cats[0]['lft']<$cats[1]['lft'] && $cats[0]['rgt']>$cats[1]['rgt']) || ($cats[0]['lft']>$cats[1]['lft'] && $cats[0]['rgt']<$cats[1]['rgt'])){
					return $cid;
				}
			}elseif($count > 2){//暂时还有多个库则不验证
				return $cid;
			}

		}else{
			$sql   = "select id from " . $this->_category_table . "  where subject_id=$subject_id and depth=1";
			$query = $this->db->query($sql);
			$row   = $query->row();
			if(!empty($row)){
				return $row->id;
			}
		}
		
		return 0;
	}
	
	/**
	 *	判断若干节点是否为叶子节点
	 *  @param  mixed $nodes 节点id数组或以,分割的字符串
	 *  @return array 是否叶子节点的数组
	 */
	public function is_leaf_nodes($nodes){
		if(is_array($nodes)){
			$nodes = implode(',', $nodes);
		}
		
		$sql = "SELECT id,lft,rgt FROM " . $this->_category_table . "  WHERE id in (" . $nodes . ")";
		$query  = $this->db->query($sql);
	    $cats   = $query->result_array();
		$result = array();
		foreach($cats as $cat){
			if($cat['rgt'] == $cat['lft']+1){//叶子节点
				$result[$cat['id']] = 1;
			}else{
				$result[$cat['id']] = 0;
			}
		}
		
		return 	$result;	
	}
}

/* end of question_wrong_model.php */