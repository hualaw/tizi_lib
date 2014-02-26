<?php

/**
 * 家校通通讯录公共库model
 *
 * @author caohaihong <caohaihong@91waijiao.com>
 */
class Ab_Jxt_Model extends JXT_Model{
    
    /**
     * 根据手机号设置通讯录用户id
     * 
     * @param string $phone
     * @param type $user_id
     * 
     * @author caohaihong <caohaihong@91waijiao.com>
     */
    public function set_ab_id($phone,$user_id){
        $this->database->where("phone = '{$phone}' AND del = 0");
        if($this->database->update('address_book', array('user_id' => $user_id, 'phone' => ''))){
            $this->set_user_active($user_id);
            return TRUE;
        }else{
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
    public function set_user_active($user_id){
        $ab = $this->database->select('id')
                ->from('address_book')
                ->where("user_id = {$user_id} AND del = 0")
                ->get()->row_array();
        
        $this->database->where("ab_id = {$ab['id']} AND del = 0");
        $this->database->order_by('id', 'DESC');
        $this->database->limit(1);
        return $this->database->update('ab_relation', array('active' => 1));
    }
    
}
