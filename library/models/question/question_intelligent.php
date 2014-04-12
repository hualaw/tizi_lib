<?php
/**
 *  智能选题模块
 *  @package models
 *  @author huangjun
 *	@version 1.0
 */
class Question_Intelligent extends MY_Model {	
    /**
	 *	各难度系数值的数组，等值增加
	 *  @access public
	 */
	public $_difficultArr        = array(0.2, 0.4, 0.6, 0.8, 1.0);//如果init方法没有接受对应参数，则使用该默认值
	
	/**
	 *	$_difficultArr各难度系数对应的level id
	 *  @access public
	 */
	public $_difficultLevelIdArr = array(1, 2, 3, 4, 5);			//如果init方法没有接受对应参数，则使用该默认值	
	
	/**
	 *	$_difficultArr各难度系数对应的level
	 *  @access public
	 */
	public $_difficultLevelArr   = array(1, 2, 3, 4, 5);			//如果init方法没有接受对应参数，则使用该默认值
	
	/**
	 *	总体难度
	 *  @access public
	 */
	public $_totalDifficult;
	
	/**
	 *	题型的难度区间数组，"l"=>左边界难度值在$_difficultArr的索引,"r"=>右边界难度值在$_difficultArr的索引
	 *  @access public
	 */
	public $_difficultAreas;
	
	//排除的问题id
	public $_excludeQuestions = array();
	/**
	 *	存储考察范围category id集合，可以为数组（推荐，会整数转化每个数组元素），否则为按,分割的字符串,不能为空
	 *  @access public
	 */
	public $_categorys;
	
	/**
	 *	存储应获取当前题型的结果数据数组，包含各难度系数需要获取的记录条数
	 *  @access public
	 */
	public $_result;		
			 
	
	/**
	 *	存储当前题型从数据库读取的结果数据数组，包含各难度系数需要获取的记录数据
	 *  @access public
	 */
	public $_resultData;
	
	/**
	 *	存储实际获取当前题型的结果数量数组，包含各难度系数实际获取的合法记录数量
	 *  @access public
	 */
	public $_resultLevels;	
	
	/**
	 *	存储所有题型最终结果数据数组，包含选取范围下所有题型各难度系数需要获取的记录数据
	 *  @access public
	 */
	public $_resultsArr;
	
	/**
	 *	题型的获取数据统计信息
	 *  @access public
	 */
	public $_total;
	
    /**
	 * 构造函数
	 * @access public
	 */
	 public function __construct(){
        parent::__construct();		
     }

	/**
	 * 在给定试卷难度下各难度区间需要获取的记录数
	 * @access public
	 * @param  int   $start          难度值区间开头索引,必须有效
	 * @param  int   $end            难度值区间结束索引,必须有效
	 * @param  int   $counts		 分配的总记录数
	 */
	 public function getAreaRecords($start, $end, $counts){
		$difficultArr   = $this->_difficultArr;
		
		if($difficultArr[$end] != $difficultArr[$start]){//保证值偏小,直线近似计算
			$temp = intval(ceil(($difficultArr[$end] - $this->_totalDifficult) * $counts / ($difficultArr[$end] - $difficultArr[$start])));
			$this->_result[$start]  = $temp;		
			$this->_result[$end]    = $counts - $temp;	
		}else{//难度区间只有一个难度值
			$this->_result[$start]  = $counts;
		}
	 }
	 
	 /**
	 * 初始化结果数组，各难度key的value值为0
	 * @access public
	 */
	 public function initResult(){
		$this->_resultData = array();					//每个题型都初始化从数据库获取的结果数据数组为空数组	
		
		foreach($this->_difficultArr as $key=>$val){
			$this->_result[$key]	   = 0;
			$this->_resultLevels[$key] = 0;
		}	

	 }
	 
	 /**
	 * 总体难度在难度区域内的情况下获取区域最小的难度值key
	 * @access public
	 */
	 public function getDifficultKey(){
		foreach($this->_difficultArr as $key=>$val){
			if($this->_totalDifficult < $val){
				return $key-1;
			}else if($this->_totalDifficult == $val){
				return $key;
			}
		}
	 }

	 /**
	  * 获取难度区域左边界难度值数量和右边界难度值数量的合法比例
	  * @param   array $area 难度区域数组，"l"=>左边界难度值，"r"=>右边界难度值
	  * @return  float 返回右边界难度值和左边界难度值的比例
	  */
	 public  function getScale($area){

		if($this->_difficultArr[$area['r']] != $this->_difficultArr[$area['l']]){
			return ($this->_totalDifficult - $this->_difficultArr[$area['l']]) / ($this->_difficultArr[$area['r']] - $this->_totalDifficult);
		}else{
			return 1;
		}
		
	 }
	 
	  /**
	  * 按比例调整难度区域边界难度数量，使边界难度数量符合难度比例并不超过分配的数量
	  * @param   array $area 难度区域数组，"l"=>左边界难度值，"r"=>右边界难度值
	  * @return  bool  数据已经取满则返回true,否则返false
	  */
	 public function filterDataRecords($area){
		if($this->_resultLevels[$area['l']]>=$this->_result[$area['l']] && $this->_resultLevels[$area['r']]>=$this->_result[$area['r']]){//取满数据
			$this->_resultLevels[$area['l']] = $this->_result[$area['l']];
			$this->_resultLevels[$area['r']] = $this->_result[$area['r']];
			return true;
		}else{
			$rtemp 		= intval(ceil($this->getScale($area) * $this->_resultLevels[$area['l']]));
			if($rtemp > $this->_resultLevels[$area['r']]){						  //按区域右边界难度值数量计算该难度区域合法数量
				$temp   = intval(ceil($this->_resultLevels[$area['r']] / $this->getScale($area)));
				$this->_resultLevels[$area['l']] = $temp;
			}else if($rtemp < $this->_resultLevels[$area['r']]){				  //按区域左边界难度值数量计算该难度区域合法数量
				$this->_resultLevels[$area['r']] = $rtemp ;
			}
			return false;
		}
	 }
	 
	/**
	  * 获取题目level_id的在难度系数数组对应的key值
	  * @access  public	 
	  * @param   int     $id	题目的level_id
	  * @return  int     题目level_id的在难度系数数组对应的key值
	  */
	 public function getKeyByLevelId($id){
			return array_search($id, $this->_difficultLevelIdArr);
	 }
	 
	 /**
	  * 根据随机id获取选取范围内指定题型在难度区间内记录数据
	  * @access public	 
	  * @param  mixed   $randId			 题目随机id
	  * @param  mixed   $qtype			 知识点题型，默认空字符串为所有知识点题型,不能为空字符串
	  * @param  array   $area            难度区域数组，"l"=>左边界难度值，"r"=>右边界难度值
	  * @param  array   $big             true为取大于随机id的记录，false为取小于随机id的记录
	  */
	 public function getRandomDataRecords($randId, $qtype, $area, $big=true){
		$randWhere = $big ? " AND a.id>=" . $randId : " AND a.id<" . $randId;//获取题目id搜寻条件
		$where    = ' a.qtype_id =' . $qtype . ' AND a.online = 1 AND b.category_id in (' . $this->_categorys . ')';
		if(count($this->_excludeQuestions) > 1){
 			$where .= ' AND a.id NOT IN("'.implode('","',$this->_excludeQuestions).'")';
 		}else if(count($this->_excludeQuestions) == 1){
	 		$where .= ' AND a.id <> '.$this->_excludeQuestions[0];
 		}
		//$field    = 'a.id,a.level_id,a.date,a.title'; //获取的字段
		$field 	  = 'a.*, c.name, c.id as category_id';
		$sql 	  = "SELECT " . $field
					." FROM `question` AS a LEFT JOIN `question_category` AS b ON a.id = b.question_id "
					." LEFT JOIN `category` AS c ON b.category_id = c.id "
					." WHERE " . $where;
		$unionSql = '';
		if($area['l'] == $area['r']){//难度区间仅有一个难度
			if($this->_result[$area['l']] > 0){
				$unionSql .= $sql . " AND a.level_id = " . $this->_difficultLevelIdArr[$area['l']] . $randWhere . " order by rand() limit " . $this->_result[$area['l']];
			}
		}else{
			$lLimit = $this->_result[$area['l']] + 1;//解决1条记录的偏移
			$rLimit = $this->_result[$area['l']] + 1;
            $rows = $lLimit+$rLimit;
            $unionSql = $sql." AND a.level_id in (" . $this->_difficultLevelIdArr[$area['l']] .','.$this->_difficultLevelIdArr[$area['r']].") order by rand()";
            //限制选题数量
            $unionSql .= " limit 5000";
			//$unionSql .= "(" . $sql . " AND a.level_id = " . $this->_difficultLevelIdArr[$area['l']] . $randWhere . " limit " . $lLimit . ") UNION ALL ";
			//$unionSql .= "(" . $sql . " AND a.level_id = " . $this->_difficultLevelIdArr[$area['r']] . $randWhere . " limit " . $rLimit . ")";
		}

		$query 	= $this->db->query($unionSql);
		$results  = $query->result_array();
		foreach($results as $result){
			$dif = $this->getKeyByLevelId($result["level_id"]);
			$result['qtype']      = $qtype;          					//增加题型id
			$result['level']	  = $this->_difficultLevelArr[$dif];	//增加题型level	
			$this->_resultData[]  = $result;
			$this->_resultLevels[$dif]  += 1;
		}		
	 }	

		
	 
	 /**
	  *	根据该题型下从数据库读取的结果数据$_resultData生成显示的最后结果数据
	  * @param  int     $records		     在给定试卷难度下各难度类型需要获取的记录数
	  * @return array   该题型下最接近总体计划难度的记录数组
	  */
	 public function getResult($records){
		$firstArea   	 = $this->getFirstArea();					//获取难度边界值不等的第一个难度区间
		$back_result 	 = array();		
		if(!empty($this->_resultData)){
			foreach($this->_resultData as $val){	
					if($records <= 0){//该题型记录数已经取完
						break;
					}
					$diffKey = $this->getKeyByLevelId($val["level_id"]);
					if($this->_resultLevels[$diffKey] <= 0){//该难度级别合法数量已经取完
						if(isset($firstArea["l"]) && ($diffKey == $firstArea["l"] || $diffKey == $firstArea["r"])){//属于边界难度值不等的第一个难度区间则非法记录进备份数组
							$back_result[] = $val;
						}
						continue;
					} 
					$records--;
					$this->_resultLevels[$diffKey]--;
					$this->_resultsArr[] = $val;
			}
			
			if($records > 0 && !empty($back_result)){		
				foreach($back_result as $val){
					if($records <= 0){//该题型记录数已经取完
						break;
					}
					$records--;
					$this->_resultsArr[] = $val;
				}
			}
		}		
	 }
	 
	 /**
	  * 获取难度边界值不等的第一个难度区间（难度区间的边界值相等则为下一个难度区间）
	  * @return array 难度区间，不存在则为空数组
	  */
	 public function getFirstArea(){
		$firstArea = array();
		if($this->_totalDifficult!=$this->_difficultArr[0] && $this->_totalDifficult!=end($this->_difficultArr)){
			$firstArea = $this->_difficultAreas[0];
			if($firstArea['l'] == $firstArea['r'] ){
				$firstArea = $this->_difficultAreas[1];
			}
		}
		return $firstArea;
	 }

	/**
	 * 设置题目所有难度的相关数组
	 * @param  array   $questionLevels	 题目所有难度数据的数组
	 */
	 public function setQuestionLevels($questionLevels = array()){
		if(!empty($questionLevels)){						      //参数非空则设置难度数组
			$countLevels = count($questionLevels);
			$this->_difficultArr = $this->_difficultLevelArr = $this->_difficultLevelIdArr = array();
			foreach($questionLevels as $levels){
				$this->_difficultArr[]        = $levels->level / $countLevels;
				$this->_difficultLevelArr[]   = $levels->level;
				$this->_difficultLevelIdArr[] = $levels->id;
			}
		}
	 }
	 //设置排除的qid
	 public function setExcludeQuestions($excludeQuestions = array()){
	 	if(!empty($excludeQuestions)){
	 		$this->_excludeQuestions = $excludeQuestions;
	 	}
	 }
	 /**
	 * 设置试卷总体难度和难度区间
	 * @access public
	 * @param  int     $totalDifficult   试卷总体难度
	 * @param  mixed   $categorys		 考察范围category id集合，可以为数组（推荐，会整数转化每个数组元素），否则为按,分割的字符串,不能为空
	 * @param  array   $questionLevels	 题目所有难度数据的数组
	 */
	 public function init($totalDifficult, $categorys, $questionLevels = array(), $excludeQuestions = array()){		
		$this->_resultsArr = array();							  //初始化最终结果数据数组为空数组	
		$this->_total 	   = array('nums'=>0, 'difficult'=>0);	  //初始化统计信息数值都为0
		$this->setQuestionLevels($questionLevels);				  //设置题目所有难度的相关数组
		$this->setExcludeQuestions($excludeQuestions);

	    if($totalDifficult < $this->_difficultArr[0]){		     //小于最小难度值则取最小难度值
			$totalDifficult    = $this->_difficultArr[0];
		}else if($totalDifficult > end($this->_difficultArr)){   //大于最大难度值则取最大难度值
			$totalDifficult    = end($this->_difficultArr);
		}		
		$this->_totalDifficult = $totalDifficult;			     //设置总体计划难度		
		
		$currentKey = $this->getDifficultKey();
		$l 			= $currentKey;		
		$r 			= in_array($totalDifficult, $this->_difficultArr) ? $currentKey : ($currentKey+1);//刚好为难度区域边界值则右边界和左边相等，否则取最近的难度区域	
		while($l >= 0 && $r < count($this->_difficultArr)){	     //碰到边界即停止
			$this->_difficultAreas[] = array("l"=>$l, "r"=>$r);  //设置总体计划难度对应的难度区域
			$l--;
			$r++;			
		}		
		
		if(is_array($categorys)){//转化非空数组为按,分割的字符串
			$categorys = array_map(function ($var) {return intval($var);}, $categorys);//过滤
			$categorys = implode(',', array_unique($categorys)); //设置选取范围			
		}
		$this->_categorys = $categorys;		
	}	 

	/**
	 * 获取该题库下该知识点题型在考察范围中的最大和最小题目id
	 * @access public	 
	 * @param  mixed   $qtype			 知识点题型，默认空字符串为所有知识点题型,不能为空字符串	
	 * @return true    成功返包含各难度系数需要获取的记录条数的数组,返回true表示没有记录数据
	 */
	 public function getWiseRangeIds($qtype){
		$where = '';
		$qtype = intval($qtype);//过滤
		if(empty($qtype) || empty($this->_categorys)){//考察范围和题型不能为空
			return constant::ERROR_WISE_MAXID_PARAM_EMPTY;
		}				
		
		$where .= ' a.qtype_id =' . $qtype . ' AND b.category_id in (' . $this->_categorys . ')';
		$sql 	= "SELECT min(a.id) as minWiseId,max(a.id) as maxWiseId FROM `question` AS a JOIN `question_category` AS b ON a.id = b.question_id WHERE " . $where;
		$query 	= $this->db->query($sql);

		if($row = $query->row()){
			$maxWiseId = intval($row->maxWiseId);
			if($maxWiseId <= 0) return true;
			$minWiseId = intval($row->minWiseId);
			return array('minWiseId'=>$minWiseId, "maxWiseId"=>$maxWiseId);
		}
		return true;
	 }
	 
	/**
	 * 从数据库获取近似总体难度的该题库下该知识点题型在考察范围中记录集合
	 * @access public	 
	 * @param  int     $records		     在给定试卷难度下各难度类型需要获取的记录数
	 * @param  mixed   $randId			 题目随机id
	 * @param  mixed   $qtype			 知识点题型，默认空字符串为所有知识点题型,不能为空字符串	 
	 */
	 public function getDataRecords($records, $randId, $qtype){
		$difficultCounts = $records;		 //分配难度区间的初始化数量
		$this->initResult();				//初始化结果数组
		foreach($this->_difficultAreas as $area){//生成数据	
			if($difficultCounts <= 0){
				break;
			}
			
			$this->getAreaRecords($area['l'], $area['r'], $difficultCounts);//分配该难度区间边界难度值应获取的记录数
			$this->getRandomDataRecords($randId, $qtype, $area);			//选取比随机id大的记录数组
			if($this->_resultLevels[$area['l']] < $this->_result[$area['l']] || $this->_resultLevels[$area['r']] < $this->_result[$area['r']]){
				$this->getRandomDataRecords($randId, $qtype, $area, false); //若数据没有取满则选取比随机id小的记录数组
			}
			
			if($this->filterDataRecords($area) === true){					//数量已经取满
				break;
			}else{															//扣除已经获取的合法数量后进入下一难度区间
				$difficultCounts -= $this->_resultLevels[$area['l']]; 		//扣除该难度区域左边界难度值已经获取的合法数量
				if($area['l'] != $area['r']){
					$difficultCounts -= $this->_resultLevels[$area['r']];   //若难度区域左右边界值不等则扣除该难度区域右边界难度值已经获取的合法数量
				}
			}
		}
		
		$this->getResult($records);			
	}

	/**
	 * @return array   返回近似总体难度的该题库下所有知识点题型在考察范围中记录集合的数组
	 */
	 public function getAllResults(){
		return $this->_resultsArr;
	 }
	 
	/**
	  *	@return array 返回统计信息
	  */
	 public function getTotal(){
		if(!empty($this->_resultsArr)){
			foreach($this->_resultsArr as $val){//生成统计信息
				$this->_total['nums']++;
				if(isset($this->_total[$val['qtype']])){
					$this->_total[$val['qtype']]++;
				}else{
					$this->_total[$val['qtype']] =1;
				}
				
				$diffKey = $this->getKeyByLevelId($val["level_id"]);
				$this->_total['difficult'] += $this->_difficultArr[$diffKey];				
			}
		}
		return $this->_total;
	 }	 
}
/* end of question_intelligent.php */
