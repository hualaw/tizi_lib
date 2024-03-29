<?php

/**
 * 家校通通讯录公共库model
 *
 * @author caohaihong <caohaihong@91waijiao.com>
 */
require_once LIBPATH . "core/JXT_Model.php";

class Ab_Jxt_Model extends JXT_Model {

    /**
     * 根据手机号设置通讯录用户id，student_name，parent_name,initial
     * 
     * @param string $phone
     * @param mixed $data 相关数据array('user_id' => 1, 'student_name' => 'sss', 'parent_name' => 'ppp')
     * 
     * @author caohaihong <caohaihong@91waijiao.com>
     */
    public function set_ab_id($phone, $data) {
        if (!trim($data['user_id'])) {
            return FALSE;
        }
        include_once LIBPATH . 'third_party/first_cw/first_cw.php';
        $this->database->where("phone = '{$phone}' AND del = 0");

        $map = array(
            'user_id' => $data['user_id'],
            'phone' => '',
            'update_time' => time(),
            'update_way' => 1
        );
        
        isset($data['student_name']) && $data['student_name'] 
                && $map['student_name'] = trim($data['student_name']);
		isset($data['student_name']) && $data['student_name']
                && $map['initial'] = get_initial(trim($data['student_name']));
        isset($data['parent_name']) && $data['parent_name'] && $map['parent_name'] = trim($data['parent_name']);
        

        if ($this->database->update('address_book', $map)) {
            $this->set_user_active($data['user_id']);
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 设置用户最后被邀请班级状态为激活
     * 
     * @param int $user_id
     * @return bool
     * 
     * @author caohaihong <caohaihong@91waijiao.com>
     */
    public function set_user_active($user_id) {
        $ab = $this->database->select('id')
                        ->from('address_book')
                        ->where("user_id = {$user_id} AND del = 0")
                        ->get()->row_array();
        if (!$ab) {
            return false;
        }
        $this->database->where("ab_id = {$ab['id']} AND del = 0");
        $this->database->order_by('id', 'DESC');
        $this->database->limit(1);
        return $this->database->update('ab_relation', array('active' => 1, 'update_time' => time(), 'update_way' => 1));
    }

    /**
     * 根据用户手机号获取通讯录家长姓名和学生姓名是否已经存在
     * 
     * @param string $phone
     * @return boolean
     * 
     * @author caohaihong <caohaihong@91waijiao.com>
     */
    public function g_abname_phone($phone) {
        $address_book = $this->database->from('address_book')
                        ->where("phone = {$phone} AND del = 0")
                        ->select('student_name, parent_name')
                        ->get()->row_array();

        return $address_book;
    }

}
