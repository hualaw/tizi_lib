<?php

/**
 * Description of ab_jxt_model
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
        return $this->database->update('address_book', array('user_id' => $user_id, 'phone' => ''));
    }
    
}
