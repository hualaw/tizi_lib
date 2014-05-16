<?php

class Question_Category_Model extends MY_Model {
    
	protected $_table="category";

    public function __construct()
    {
        parent::__construct();
    }

    /*get root id*/
    public function get_root_id($subject_id)
    {
		$this->db->select("id,name,lft,rgt,type,category_type");
		$this->db->where('subject_id',$subject_id);
		$this->db->where('depth',1);
		$this->db->order_by('list_order', 'desc');
		$query=$this->db->get($this->_table);
		
		return $query->result();
    }

	public function get_parent_id($node_id)
	{
		$query = $this->db->query(
            "select parent.id from $this->_table as node,$this->_table as parent
             where (node.lft between parent.lft and parent.rgt) and
                (node.depth = parent.depth + 1) and node.id = $node_id"
        );
        $parent_id=$query->row();			
		if(isset($parent_id->id)) return $parent_id->id;
		else return false;
	}

    /*get sub node id under a node*/
    public function get_subtree_node($node_id)
    {
        $query = $this->db->query(
            "select node.* from $this->_table as node,$this->_table as parent
             where (node.lft between parent.lft and parent.rgt) and 
             	(node.depth = parent.depth + 1) and parent.id = $node_id 
			order by node.list_order desc, node.lft"
        );
        return $query->result();
    }

    /*is leaf*/
    public function is_leaf_node($node_id)
    {
		$is_leaf=false;
        $this->db->select('lft,rgt');
        $this->db->where('id',$node_id);
        $node=$this->db->get($this->_table)->row();
		if($node->rgt==$node->lft+1) $is_leaf=true;
        return $is_leaf;
    }

    /*get sub tree under a node*/
    public function get_node_tree($node_id)
    {
        $query=$this->db->query(
        	"select node.id,node.name,node.lft,node.rgt,node.depth from $this->_table as node,$this->_table as parent 
             where (node.lft between parent.lft and parent.rgt) and parent.id = $node_id 
             order by node.lft"
        );
        return $query->result();
    }

    /*get single path from root to a node*/
    public function get_single_path($node_id,$select='name')
    {
        $query=$this->db->query(
			"select parent.{$select} from $this->_table as node,$this->_table as parent
             where (node.lft between parent.lft and parent.rgt) and node.id = $node_id
             order by parent.lft"
        );
        //print_r($this->db);die;
        return $query->result();
    }
	
	/*get subject_id by a node*/
	public function get_subject_id($node_id)
	{
		$query = $this->db->query(
            "select parent.subject_id from $this->_table as node,$this->_table as parent
            where (node.lft between parent.lft and parent.rgt) and
            (parent.depth = 1) and node.id = $node_id"
        );
        $result=$query->row();			
		if(isset($result->subject_id)) return $result->subject_id;
		else return false;
	}

    /*get sub node id under a node*/
    public function get_node($node_id)
    {
        $this->db->where('id',$node_id);
        $query = $this->db->get($this->_table);
        return $query->row();
    }
}

/*end of question_category_model.php*/
