<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


Class Tiku_model extends LI_Model
{

    public function __construct() 
	{
        parent::__construct();
		$this->load->database();
    }
	
	/*
	 * 添加战友
	 */
	public function addComrade($userInfo,$comrade_userId)
	{
		$this->db->insert('study_user_relation', array('userId'=>$userInfo['user_id'],'friendId' => $comrade_userId));
		$return = $this->db->affected_rows();
		return $return > 0 ? true :false;
	}
	
	/*
	 * 移除战友
	 */
	public function deleteComrade($userInfo,$comrade_userId)
	{
		$data = array('userId'=>$userInfo['user_id'],'friendId' => $comrade_userId);
		$this->db->delete('study_user_relation', $data);
		$result = $this->db->affected_rows();
		return $result > 0 ? true : false;
	}
	
	/*
	 * 搜索战友
	 */
	public function searchComrade($search)
	{
            $isEmail = preg_email($search);
            if($isEmail) {
                #根据邮箱搜索战友
                $sql = "SELECT u.id AS user_id, u.name AS nick_name, sd.pet_id, sd.subject_type, sd.location_id
                        FROM user_data sd LEFT JOIN user u ON sd.user_id=u.id WHERE u.email='{$search}'";
            } else {
                $sql = "SELECT u.id AS user_id,u.name AS nick_name,sd.pet_id,sd.subject_type,sd.location_id FROM user_data sd
                        LEFT JOIN user u ON u.id=sd.user_id WHERE u.name LIKE '".$search."%'";
            }
            $result = $this->db->query($sql)->result_array();
            return $result;
	}
	

	
	/*
	 * 战友排行
	 */
	public function rankComrade($userInfo,$type)
	{

		$return = array();	
		$friend_ids = $this->getComradeUserId($userInfo['user_id']);
		//var_dump($friend_ids);die;
		if ($friend_ids)
		{
			foreach($friend_ids as $k=>$v)
			{
				if ($type == 1)
				{
					$return[] = $this->weekRanking($v['friendId']);
				} else if ($type == 2) {
					$return[]  = $this->totleRanking($v['friendId']);
				}
			}
		}
		
		//排行要添加自己
		if ($type == 1) {
			$return[] = $this->weekRanking($userInfo['user_id']);
		} else if ($type == 2) {
			$return[]  = $this->totleRanking($userInfo['user_id']);
		}
		
		foreach ($return as $key=>$val)
		{
			if (empty($return[$key])){
				unset($return[$key]);
			}
		}
		return $return;
	}
	
	/*
	 * 用户的周排行
	 */
	public function weekRanking($user_id){
		$result = $this->db->query("select sd.user_id,sd.pet_id,sd.subject_type,
				sd.location_id,suws.exp as experience,u.name as nick_name from user_data sd
				left join user u on sd.user_id = u.id
				left join study_user_week_stat suws on sd.user_id = suws.userId
				where sd.user_id = ".$user_id)->row_array();
		return $result;
	}
	
	/*
	 * 用户的总排行
	 */
	public function totleRanking($user_id){
		$result = $this->db->query("select sd.user_id,sd.pet_id,sd.subject_type,sd.location_id,
				sd.exp as experience,u.name as nick_name from user_data sd
				left join user u on sd.user_id = u.id
				where sd.user_id = ".$user_id)->row_array();
		return $result;
	}
	
	/*
	 * 获取学霸推荐列表
	 */
	public function getTopstudentList($userInfo,$rows,$offset)
	{
		$result = $this->db->query("select sd.user_id,u.name as nick_name,sd.pet_id,sd.exp as experience 
				from user_data sd left join user u on sd.user_id = u.id 
				order by sd.exp desc limit ".$offset.','.$rows)->result_array();
		return $result;
	}
	
	
	/**
	 * 判断我是否已关注某个用户
         * @param int $myId      本用户的用户Id
         * @param int $othersId  其他人的用户Id
	 */
	 public function isFollow($myId,$othersId)
	 {
             $nums = $this->db->query("
                    select id from study_user_relation 
                    where userId = ".$myId." and friendId = ".$othersId)->num_rows();
             if ($nums > 0) {
                 $result = 1;
             } else {
                 $result = 0;
             }
             return $result;	
	 }
	 
	/*
	 * 个人主页        获取用户信息
	 * user_id 为空,查看自己的主页
	 * user_id 不为空,查看别人的主页
	 */
	public function getUserInfo($user_id,$user)
	{
		$person = array();
		//自己去查看别人的主页，看自己和别人是否是好友
		if ($user_id !=$user)
		{
		    $record= $this->db->query("select id from study_user_relation where userId = ".$user." and friendId=".$user_id)->num_rows();
		    $person['is_follow'] = $record > 0 ? 1 : 0;
		}
		//用户id和昵称
		$info = $this->db->query("select id ,name from user where id = ".$user_id)->row_array();
		$person['user_id'] = $info['id'];
		$person['nick_name'] = $info['name'];
		//用户当前拥有的经验、宠物id
		$tmp = $this->db->query('select exp as experience,pet_id from  user_data where user_id = '.$user_id)->row_array();
		$person['experience'] = round($tmp['experience']);
		$person['pet_id'] = $tmp['pet_id'];

		//朋友总数
		$friends =  $this->db->query('select count(id) as nums from study_user_relation where userId = '.$user_id)->row_array();
		$person['total_friend'] =  empty($friends['nums']) ? 1 : $friends['nums']+1;
		//排名 一周内经验值得排名
		$friend_ids = $this->getComradeUserId($user_id);
		if ($friend_ids) 
		{
			$list_exp = array();
			foreach($friend_ids as $key=>$val){
				$row = $this->db->query('select exp from  study_user_week_stat where userId = '.$val['friendId'])->row_array();
				$list_exp[]  = empty($row) ? 0 : $row['exp'];
			}
			$myRow = $this->db->query('select exp from  study_user_week_stat where userId = '.$user_id)->row_array();
		
			$myRow['exp'] = empty($myRow) ? 0 : $myRow['exp'];
			$list_exp[] = $myRow['exp'];
			rsort($list_exp);
			$num = array_search($myRow['exp'], $list_exp);
			$person['rank'] = $num + 1;
		} else {
			$person['rank'] =  1;
		}
		$return = array();
		$return['user_info'] = $person;
		return $return;
	}
	
	/*
	 * 获取战友user_id
	 */
	function getComradeUserId($user_id)
	{
		$friends = $this->db->query("select friendId from study_user_relation where userId = ".$user_id)->result_array();
		return $friends;
	}
	
	/*
	 * 根据uid判断该用户是否存在
	*/
	function checkUserExists($user_id)
	{
		$num = $this->db->query("select id from user where id = ".$user_id)->num_rows();
		$row = $this->db->query("select id from user_data where user_id = ".$user_id)->num_rows();
		return ($num > 0 && $row > 0) ? true : false;
	}
	
        
        /**
         * 获取用户的地域Id,经验值,所选学科类型这些基本数据
         */
        public function getUserBaseInfo($userId)
        {
            if(!$userId) {
                return false;
            }

            $sql = "SELECT exp AS experience, pet_id AS petId, location_id AS locationId, subject_type AS subjectType
                    FROM user_data WHERE user_id={$userId}";
            $userInfo = $this->db->query($sql)->row_array();

            return $userInfo;
        }
        
        /**
         * 设置用户的地区和学科分类信息
         * @param int $userId 用户Id
         * @param int $locationId 地区Id
         * @param int $subjectType 学科分类
         * @return bool TRUE|FALSE
         */
        public function setUserRegionCatalog($userId, $locationId, $subjectType)
        {
            $sql = "UPDATE user_data SET location_id={$locationId}, subject_type={$subjectType}
                    WHERE user_id={$userId}";

            return $this->db->query($sql);
        }
        
        /**
         * 更新用户的经验值
         * @param array $userData  用户要更新的数据
         * @reutnr int|bool  成功后返回用户当前经验值,失败返回false
         */
        public function updateUserExp($userData)
        {
            $sql = "SELECT exp FROM user_data WHERE user_id={$userData['userId']}";
            $userInfo = $this->db->query($sql)->row_array($sql);
            $currentExp = $userData['exp'] + $userInfo['exp'];
            $sql = "UPDATE user_data SET exp={$currentExp} WHERE user_id={$userData['userId']}";
            $res = $this->db->query($sql);
            if($res) {
                return $currentExp;
            } else {
                return false;
            }
        }

        /**
         * 获取用户学科类型
         * @param int userId 用户id
         * @reutrn int $subjectType  学科类型,1理科,2文科 
         */
        public function getUserSubjectType($userId)
        {
            $sql = "SELECT subject_type FROM user_data WHERE user_id={$userId}";
            $userInfo = $this->db->query($sql)->row_array();

            return $userInfo['subject_type'];
        }
}