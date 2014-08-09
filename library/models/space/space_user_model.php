<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class  Space_User_Model  extends MY_Model {

    public function __construct(){
        parent::__construct();
    }
	/**
	 * @param $user_id
	 * @return mixed
	 */
	public function get_user_data_by_user_id ($user_id) {
		$sql = "SELECT su.id space_id, su.uid, su.nickname, su.gender, su.avatar, su.attention_course, si.space_bg, si.domain, si.title, si.visit_num FROM space_user_data su LEFT JOIN space_info si ON su.id = si.space_user_id WHERE uid = {$user_id} AND su.status = 1";
		return $this->db->query($sql)->row();
	}

	public function open_space_by_user_id ($user_id) {
		$this->load->model('login/register_model');
		$users = $this->register_model->get_user_info($user_id);

		//不存在用户 直接返回
		if (!$users['errorcode']) return array('errorcode' => false);

		$user_info = $users['user'];
		//根据不同用户开通不同空间

		$space_data = null;
		if ($user_info->user_type == Constant::USER_TYPE_TEACHER) {
			$space_data = $this->open_teacher_space($user_info);
			$errorcode = true;
		} elseif ($user_info->user_type == Constant::USER_TYPE_STUDENT) {
			//以后有了再加
			$errorcode = false;
		} else {
			$errorcode = false;
		}
		return array('space_data' => $space_data, 'errorcode' => $errorcode);
	}

	private function open_teacher_space ($user_info) {
		$t_space = $this->get_user_data_by_user_id($user_info->id);
		if (!empty($t_space)) return $t_space;
		//下面是没有开通空间的用户
		$nickname = $user_info->name . '#' . mt_rand(1000, 10000);
		$grade_subject = $this->db->query("SELECT name FROM subject WHERE id = {$user_info->register_subject}")->row();
		$attention_course = mb_substr($grade_subject->name, 2, mb_strlen($grade_subject->name));

		$data  = array(
			'uid' => $user_info->id,
			'nickname' => $nickname,
			'attention_course' => $attention_course,
		);
		$this->db->insert('space_user_data', $data);
		$ret = $this->db->insert_id();
		$data = array(
			'space_user_id' => $ret,
			'title' => $nickname . '的空间',//空间名先弄成和昵称一样的
		);
		$article['space_user_id'] = $ret;
		$article['title'] = Constant::SPACE_ARTICLE_TITLE;
		$article['content'] = Constant::get_content($ret);
		$article['catid']= 1;
		$article['updatetime']= date('Y-m-d H:i:s');

		if($ret){
			$this->db->insert('space_article', $article);
			$this->db->insert('space_info', $data); //向空间插入资料
		}
		//增加默认关注
		if($ret){
			$data  = array(
				'from_user_id' => $user_info->id,
				'to_user_id' => Constant::SPACE_SUBSCRIBE_ACCOUNT,//默认关注此空间
			);
			if($data['from_user_id'] != $data['to_user_id'])
				$this->db->insert('space_subscribe', $data);
		}
		return $this->get_user_data_by_user_id($user_info->id);
	}

}