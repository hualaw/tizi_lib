<?php
/**
 *  封装reids方法，用于消息通知
 *  @package models
 *  @author  huangjun
 *	@version 1.0
 */
class Notice_Model extends CI_Model {  
	/**
	 * 消息已读和未读记录的score分界值
	 */
	private $_split_read_limits = 1000000000;
	
	private $_redis=false;

	private $_redis_db=false;

    /**
	 * 构造函数
	 * @access public
	 */
	 public function __construct(){
        parent::__construct();
		$this->load->model("redis/redis_model");
		$this->_redis_db=$this->redis_model->connect('notice');
		if($this->_redis_db)
		{
			$this->_redis=true;
		}
     }
	 
	 
	/**
	 * 添加一条通知消息
	 * @param mixed    $uid 	 老师/家长/学生用户id或uid后带唯一标识符
	 * @param string   $msg 	 消息通知内容
	 * @param int      $create   消息时间
	 */
	 public function addNotify($uid, $msg, $create){
		if($this->_redis){
			$this->cache->redis->select($this->_redis_db);
			return $this->cache->redis->zadd($uid, $create, $msg);
		}
	 }
	 
	/**
	 * 给多用户添加内容相同通知消息
	 * @param array    $uids 	 老师/家长/学生用户id集合数组
	 * @param string   $msg 	 消息通知内容
	 * @param int      $create   消息时间
	 */
	 public function addNotifies($uids, $msg, $create){
		if($this->_redis){
			$this->cache->redis->select($this->_redis_db);
			foreach($uids as $uid){
				$this->cache->redis->zadd($uid, $create, $msg);	
			}
		}
	 }
	 
	/**
	 *	获取用户的通知列表
	 *  @param  int   $uid 	 	老师/家长/学生用户id
	 *  @param  int   $page	 	当前显示的分页数
	 *  @param  int   $per_page 每页显示记录数
	 *  @return array 显示的消息记录数组
	 */
	 public function listMsgs($uid, $page=1, $per_page=10){
		$result = array();
		if($this->_redis){
			$this->cache->redis->select($this->_redis_db);
			//获取$page分页显示的记录，带score值（和消息时间关联）,只获取当前时间以前的通知
			$offset = $per_page * ($page -1);
			$msgs = $this->cache->redis->zrevrangebyscore($uid, time(), 0, $offset, $per_page, 1);
		
			if(is_array($msgs)){						
				foreach($msgs as $msg=>$ctime){
					if($ctime > $this->_split_read_limits){
						$this->cache->redis->zincrby($uid, (0-$this->_split_read_limits), $msg);    //没读记录则设置已读标识
						$read   = 0;								 //本次读取前为没读状态
						$create = $ctime;							 //创建时间
					}else{
						$read   = 1;
						$create = $ctime + $this->_split_read_limits;
					}
			
					$create = date("Y-m-d H:i:s", $create);
					$result[] = array("msg"=>urldecode($msg), "created"=>$create, "read"=>$read);
				}
			}
		}
		
		return $result;
	 }
	 
	/**
	 * 获取指定会员的最新消息个数
	 * @param  int   $uid 	用户uid
	 * @return int	 会员的最新消息个数
	 */
	 public function getNewNoticeNums($uid){
		if($this->_redis){
			$this->cache->redis->select($this->_redis_db);
			return $this->cache->redis->zcount($uid, $this->_split_read_limits, time());
		}
	 }
	 
	 /**
	 * 封装redis ZCOUNT方式,返回指定会员当前时间有效消息总数
	 * @param  string   $uid 	用户id
	 * @param  int 		$min	开始时间戳
	 * @param  int      $time	当前时间
	 * @return int		当前时间有效消息总数
	 */
	 public function zcount($uid, $min, $time){
		if($this->_redis){
			$this->cache->redis->select($this->_redis_db);
			return $this->cache->redis->zcount($uid, $min, $time);
		}
	 }

	 //删除
	 public function zrem($uid,$member){
	 	if($this->_redis){
	 		$this->cache->redis->select($this->_redis_db);
			return $this->cache->redis->zrem($uid, $member);
		}
	 }
}
/* end of redis_model.php */
