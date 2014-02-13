<?php
//  CI 2.0 Compatibility
if(!class_exists('CI_Model')) { class CI_Model extends CI_Model {  } }

class Authitem_model extends CI_Model {
    const TYPE_OPERATION=0;
    const TYPE_TASK=1;
    const TYPE_ROLE=2;

    function __construct() {
        parent::__construct();
        $this->_table = "AuthItem";
        $this->primary_key='id';
        $this->_childstable = "AuthItemChild";
        $this->_assignmentstable = "AuthAssignment";
        
    }
    
    function get_all(){
        return $this->db->get($this->_table)->result();
    }
    
    function getAuthItemByID($id){
        //return $this->get($id);
        $d=array();
        $d['id']=$id;
        $this->db->where($d);
        
        return $this->db->get($this->_table)->row();
        
    }
    function getAuthItemByName($name){
        $d=array();
        $d['name']=$name;
        $this->db->where($d);
        
        return $this->db->get($this->_table)->row();
        
    }
    
    function createAuthItem($name, $type, $description='', $bizRule='', $data='') {
        $d=array();
        $d['name'] = $name;
        $d['type'] = $type;
        $d['description'] = $description;
        $d['bizRule'] = $bizRule;
        $d['data'] = $data;
        
        /*check exists*/
        $this->db->where($d);
        $q=$this->db->get($this->_table);
        if(! $q->num_rows()){
            $this->db->insert($this->_table, $d);
            echo $this->db->last_query();
            return $this->db->insert_id();
        }else{
            return $q->row()->id;
        }
    }
    function addChild($parent, $child){
        if ($parent==$child){
            return FALSE;
        }
        $d=array();
        $d['parent_id']=$parent;
        $d['child_id']=$child;
        /*check if it's there...*/
        $this->db->where($d);
        $q=$this->db->get($this->_childstable);
        if(!($q->num_rows())){
            $this->db->insert($this->_childstable, $d); 
            return $this->db->insert_id();
        }      
    }
    function addChilds($r, $arrChilds){
        foreach($arrChilds as $child){
            $this->addChild($r, $child);
        } 
    }
    function itemHasChild($item, $child){
        $d=array();
        $d['parent_id']=$item;
        $d['child_id']=$child;
        /*check if it's there...*/
        $this->db->where($d);
        //$q=$this->db->get($this->_table);
        return ($this->db->count_all_results($this->_childstable) ?TRUE:FALSE);
    }
    function hasChilds($item){
        $d=array();
        $d['parent_id']=$item;
        $this->db->where($d);
        return ($this->db->count_all_results($this->_childstable) ?TRUE:FALSE);
        /*
        $q=$this->db->get($this->_childstable);
        if($q->num_rows()){
            return TRUE;
        }
        return FALSE;
        * */
    }
    function getChildsOfItem($item){
        $d=array();
        $d['parent_id']=$item;
        $this->db->select("AuthItem.*");
        $this->db->where($d);
        $this->db->join("AuthItemChild", "AuthItem.id=AuthItemChild.child_id");
        $q=$this->db->get($this->_table);
        //echo $this->db->last_query();
        if($q->num_rows()){
            return $q->result();
        }
        return FALSE;
    }
    
    function getInheritedRoles($itemid){
        $d=array();
        $d['parent_id']=$itemid;
        $d['type']=self::TYPE_ROLE;
        $this->db->select("AuthItem.*");
        $this->db->where($d);
        $this->db->where("AuthItemChild.child_id !=", $itemid );
        $this->db->join("AuthItemChild", "AuthItem.id=AuthItemChild.child_id");
        $q=$this->db->get($this->_table);
        //echo $this->db->last_query();
        if($q->num_rows()){
            return $q->result();
        }
        return FALSE;
    }
    function checkAccess($itemname, $childname){
        $item=$this->getAuthItemByName($itemname);
        $child=$this->getAuthItemByName($childname);

        if($item && $child){
            return $this->checkAccessByID($item->id, $child->id);
        }

        
    }
    function checkAccessByID($itemid, $somethingid){

        if($this->itemHasChild($itemid, $somethingid)){
            return TRUE;
        }
        //check for the inherited roles.
        $roles=$this->getInheritedRoles($itemid);
        if($roles){
            foreach($roles as $r){
                if($this->checkAccessByID($r->id, $somethingid)){
                    return TRUE;
                }
            }
            
        }

        return FALSE;
        
    }
    function checkAccessForUser($uid, $itemname){
        $item=$this->getAuthItemByName($itemname);
        if($item){
            $itemid=$item->id;
            return $this->checkAccessForUserByID($uid, $itemid);
        }
        return FALSE;
        
    }
    function checkAccessForUserByID($uid, $itemid){
        if ($this->userHasItem($uid, $itemid)){
            return TRUE;
        }
        //IF NOT; CHECK IF ANY OF ITS ASSIGNED ITEMS HAS ACCESS
        $assignments=$this->getAuthAssignments($uid);
        if($assignments){
            foreach($assignments as $assignment){
                $id=$assignment->id;
                if($this->checkAccessByID($assignment->id, $itemid)){
                    return TRUE;
                }
            }
        }
        return FALSE;
    }
    function userHasItem($uid, $itemid){
        $this->db->where('userid', $uid);
        $this->db->where('itemid', $itemid);
        
        return ($this->db->count_all_results($this->_assignmentstable) ?TRUE:FALSE);
        
    }
    function getAuthAssignments($uid){

        $this->db->select("AuthItem.*");
        $this->db->join($this->_assignmentstable, "AuthItem.id=itemid");
        $this->db->where('userid', $uid);
        $q=$this->db->get($this->_table);
        if($q->num_rows()>0){
            return $q->result();
        }
        return FALSE;
    }
    function assign($itemName,$userId, $bizRule='',$data=''){
        $item=$this->getAuthItemByName($itemName);
        if($item){
            return $this->assignByID($item->id, $userId, $bizRule, $data);
        }
    }
    
    function assignByID($itemId,$userId, $bizRule='',$data=''){
        $d=array();
        $d['itemid']=$itemId;
        $d['userid']=$userId;
        $d['bizrule']=$bizRule;
        $d['data']=$data;
        
        $this->db->where($d);
        $q=$this->db->get($this->_assignmentstable);

        if(!$q->num_rows()){
            $this->db->insert($this->_assignmentstable, $d);
            return $this->db->insert_id();
        }
        $r=$q->row();
        return $r->id; 

    }
    function createRole($name, $description='', $bizRule=null, $data=null) {
        
        $t = self::TYPE_ROLE;
        return  $this->createAuthItem($name, $t, $bizRule, $data);
    }

    function createTask($name, $description='', $bizRule=null, $data=null) {
        $t = self::TYPE_TASK;
        return $this->createAuthItem($name, $t, $bizRule, $data);
    }

    function createOperation($name, $description='', $bizRule=null, $data=null) {
        $t = self::TYPE_OPERATION;
        return $this->createAuthItem($name, $t, $bizRule, $data);
    }

    function getRoles() {
        $this->db->where("type", self::TYPE_ROLE);
        return $this->get_all();
    }

    function getTasks() {
        $this->db->where("type", self::TYPE_TASK);
        return $this->get_all();
    }

    function getOperations() {
        $this->db->where("type", self::TYPE_OPERATION);
        return $this->get_all();
    }

}

