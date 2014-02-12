<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rbac {

	private $_CI;

    public function __construct()
    {
        $this->_CI = &get_instance();
        $this->_CI->load->model("login/rbac_model");
        $this->_CI->load->config('rbac');
    }

    public function create_role($name, $description='', $biz_rule=NULL, $data=NULL) 
    {
        return $this->_CI->rbac_model->create_role($name, $description, $biz_rule, $data);
    }
    
    public function create_task($name, $description='', $biz_rule=NULL, $data=NULL)
    {
        return $this->_CI->rbac_model->create_task($name, $description, $biz_rule, $data);
    }
    
    public function create_operation($name, $description='', $biz_rule=NULL, $data=NULL)
    {
        return $this->_CI->rbac_model->create_operation($name, $description, $biz_rule, $data);
    }
    
    public function get_roles()
    {
        return $this->_CI->rbac_model->get_roles();
    }

    public function get_tasks()
    {
        return $this->_CI->rbac_model->get_tasks();
    }

    public function get_operations()
    {
        return $this->_CI->rbac_model->get_operations();
    }
    
    public function add_child($parent, $child)
    {
        return $this->_CI->rbac_model->add_child($parent, $child);
    }
    
    public function add_childs($parent, $childs_array)
    {
        return $this->_CI->rbac_model->add_childs($parent, $childs_array);
    }
    
    public function get_item_by_pk($id)
    {
       return $this->_CI->rbac_model->get_item_by_pk($id);
    }
    
    public function get_item_by_name($name)
    {
        return $this->_CI->rbac_model->get_item_by_name($name);
    }
    
    public function assign($item_name,$user_id, $biz_rule='',$data='')
    {
        return $this->_CI->rbac_model->assign($item_name, $user_id, $biz_rule, $data);
    }
    
    public function assign_by_id($item_id,$user_id, $biz_rule='',$data='')
    {
        return $this->_CI->rbac_model->assign($item_id, $user_id, $biz_rule, $data);
    }
    
    public function check_access($item_name, $childname)
    {
        return $this->_CI->rbac_model->check_access($item_name, $childname);
    }
    
    public function check_access_by_id($item_id, $something_id)
    {
        return $this->_CI->rbac_model->check_access_by_id($item_id, $something_id);
    }
    
    public function check_user_access($uid, $item_name)
    {
        return $this->_CI->rbac_model->check_user_access($uid, $item_name);
    }
    public function check_user_access_id($uid, $item_id)
    {
        return $this->_CI->rbac_model->check_user_access_id($uid, $item_id);
    }
    
    public function get_assignments($uid)
    {
        return $this->_CI->rbac_model->get_assignments($uid);
    }
    
    public function get_item_operations($item_id, &$array)
    {
    	return $this->_CI->rbac_model->get_item_operations($item_id, $array);	
    }
    
    public function get_user_operations($uid, &$array) 
    {
    	return $this->_CI->rbac_model->get_user_operations($uid, $array);
    }
}

