<?php

class Rbac_model extends LI_Model {

    const TYPE_OPERATION = 0;
    const TYPE_TASK = 1;
    const TYPE_ROLE = 2;
    
    private $_table;
    private $_childs_table;
    private $_assignments_table;
    private $_primary_key;

    function __construct()
    {
        parent::__construct();
    	$this->_initialize();
	$this->load->database();
    }
    
    private function _initialize() {
    	($this->config->item('table'))				|| $this->_table = 'auth_item';
    	($this->config->item('childs_table'))		|| $this->_primary_key = 'id';
    	($this->config->item('assignments_table'))	|| $this->_childs_table = 'auth_item_child';
    	($this->config->item('primary_key'))		|| $this->_assignments_table = 'auth_assignment';
    }
    
    function get_item_by_pk($id) 
    {
        return $this->db->get_where($this->_table, array($this->_primary_key => $id))->row();
    }
    
    function get_item_by_name($name)
    {
        return $this->db->get_where($this->_table, array('name' => $name))->row();
    }
    
    function create_item($name, $type, $description='', $biz_rule='', $data='') 
    {
        $conditions = array('name' => $name, 'type' => $type, 'description' => $description, 'bizRule' => $biz_rule, 'data' => $data);
        
        $q = $this->db->get_where($this->_table, $conditions);
        
        if ($q->num_rows() === 0)
        {
            $this->db->insert($this->_table, $conditions);
            return $this->db->insert_id();
        } 
        else
        {
            return $q->row()->id;
        }
    }
    
    function add_child($parent, $child)
    {
        if ($parent == $child)
        {
            return FALSE;
        }

        $conditions = array('parent_id' => $parent, 'child_id' => $child);

        if ($this->db->get_where($this->_childs_table, $conditions)->num_rows() === 0)
        {
            $this->db->insert($this->_childs_table, $conditions);
            return $this->db->insert_id();
        }
    }
    
    function add_childs($parent, $array_childs)
    {
        foreach ($array_childs as $child)
        {
            $this->add_child($parent, $child);
        } 
    }
    
    function item_has_child($item, $child)
    {
        $this->db->where(array('parent_id' => $item, 'child_id' => $child));
        return ($this->db->count_all_results($this->_childs_table) ? TRUE : FALSE);
    }
    
    function has_childs($item)
    {
        $this->db->where(array('parent_id' => $item));
        return ($this->db->count_all_results($this->_childs_table) ? TRUE : FALSE);
    }
    
    function get_item_childs($item)
    {
        $this->db->select("{$this->_table}.*");
        $this->db->where(array('parent_id' => $item));
        $this->db->join("{$this->_childs_table}", "{$this->_table}.id={$this->_childs_table}.child_id");
        return $this->db->get($this->_table)->result();
    }
    
    function get_inherited_roles($itemid)
    {
        $this->db->select("{$this->_table}.*");
        //$this->db->where(array("{$this->_childs_table}.parent_id" => $itemid, "{$this->_table}.type" => self::TYPE_ROLE));
		//增加task的继承纪录，疑似bug
		$this->db->where("{$this->_childs_table}.parent_id",$itemid);
		$this->db->where_in("{$this->_table}.type",array(1,2));
        $this->db->where("{$this->_childs_table}.child_id !=", $itemid );
        $this->db->join("{$this->_childs_table}", "{$this->_table}.id={$this->_childs_table}.child_id");
        return $this->db->get($this->_table)->result();
    }
    
    function check_access($item_name, $childname)
    {
        if ($item = $this->get_item_by_name($item_name) && $child = $this->get_item_by_name($childname))
        {
            return $this->check_access_by_id($item->id, $child->id);
        }

    }
    
    function check_access_by_id($itemid, $somethingid)
    {
        if ($this->item_has_child($itemid, $somethingid))
        {
            return TRUE;
        }
        
        if ($roles = $this->get_inherited_roles($itemid)) 
        {
            foreach ($roles as $role) 
            {
				//return $this->check_access_by_id($role->id, $somethingid);
				//增加找到后返回，否则一直找的逻辑，疑似bug
            	$return=$this->check_access_by_id($role->id, $somethingid);
				//var_dump($return);
				if($return) return $return;
            }
        }
        return FALSE;
    }
    
    function check_user_access($uid, $item_name) 
    {
        if ($item = $this->get_item_by_name($item_name))
        {
            return $this->check_user_access_id($uid, $item->id);
        }
        return FALSE;
    }
    
    function check_user_access_id($uid, $itemid)
    {
        if ($this->user_has_item($uid, $itemid))
        {
            return TRUE;
        }

        if ($assignments = $this->get_assignments($uid))
        {
            foreach ($assignments as $assignment)
            {
                return $this->check_access_by_id($assignment->id, $itemid);
            }
        }
        return FALSE;
    }
    
    function user_has_item($uid, $itemid)
    {
        $this->db->where(array('userid' => $uid, 'itemid' => $itemid));
        return ($this->db->count_all_results($this->_assignments_table) ? TRUE : FALSE);
    }
    
    function get_assignments($uid)
    {
        $this->db->select("{$this->_table}.*");
        $this->db->join($this->_assignments_table, "{$this->_table}.id=itemid");
        $this->db->where('userid', $uid);
        return $this->db->get($this->_table)->result();
    }
    
    function assign($item_name,$user_id, $biz_rule='',$data='')
    {
        if ($item = $this->get_item_by_name($item_name))
        {
        	return $this->assign_by_id($item->id, $user_id, $biz_rule, $data);
        }
    }
    
    function assign_by_id($itemId,$user_id, $biz_rule='',$data='')
    {
        $conditions = array('itemid' => $itemId, 'userid' => $user_id, 'bizrule' => $biz_rule, 'data' => $data);
        $q = $this->db->get_where($this->_assignments_table, $conditions);
        
        if ($q->num_rows() === 0)
        {
            $this->db->insert($this->_assignments_table, $conditions);
            return $this->db->insert_id();
        }
        return $q->row()->id;
    }
    
    function create_role($name, $description='', $biz_rule=NULL, $data=NULL) 
    {
        return $this->create_item($name, self::TYPE_ROLE, $description, $biz_rule, $data);
    }

    function create_task($name, $description='', $biz_rule=NULL, $data=NULL) 
    {
        return $this->create_item($name, self::TYPE_TASK, $description, $biz_rule, $data);
    }

    function create_operation($name, $description='', $biz_rule=NULL, $data=NULL) 
    {
        return $this->create_item($name, self::TYPE_OPERATION, $description, $biz_rule, $data);
    }
    
    function get_roles() 
    {
        return $this->db->get_where($this->_table, array('type' => self::TYPE_ROLE))->result();
    }

    function get_tasks() 
    {
		return $this->db->get_where($this->_table, array('type' => self::TYPE_TASK))->result();
    }

    function get_operations() 
    {
        return $this->db->get_where($this->_table, array('type' => self::TYPE_OPERATION))->result();
    }

	function get_item_operations($item_id, &$array)
	{
		$this->db->select("child_id, {$this->_table}.name, type");
    	$this->db->join($this->_table, "{$this->_table}.id={$this->_childs_table}.child_id");
    	$q = $this->db->get_where($this->_childs_table, array('parent_id' => $item_id));
    	if ($q->num_rows() > 0)
    	{
    		foreach($q->result() as $item)
    		{
    			if (!$this->_is_duplicated_operation($item, $array) && $this->_is_operation($item))
    			{
    				$array[] = $item->name;
    			}    			
    			$this->get_item_operations($item->child_id, $array);
    		}
    	} else {
    		if ($item = $this->db->get_where('auth_item', array('id' => $item_id))->row())
    		{
    			if (!$this->_is_duplicated_operation($item, $array) && $this->_is_operation($item))
    			{
    				$array[] = $item->name;
    			}
    			
    		}
    	}		
	}
	
	function get_user_operations($uid, &$array)
	{
		foreach($this->db->get_where('auth_assignment', array('userid' => $uid))->result() as $item) 
		{
			$this->get_item_operations($item->itemid, $array);
		}
	}
	
	private function _is_duplicated_operation($item, $array) {
		return (in_array($item->name, $array));
	}
	
	private function _is_operation($item) {
		return (!in_array($item->type, array(self::TYPE_ROLE, self::TYPE_TASK)));
	}
	
}

