<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rbac_auth{

    public function __construct(){
        $this->CI=&get_instance();
        $this->CI->load->model("authman/authitem_model");
    }

    public function createRole($name, $description='', $bizRule=null, $data=null) {
        return $this->CI->authitem_model->createRole($name, $description, $bizRule, $data);
    }
    public function createTask($name, $description='', $bizRule=null, $data=null){
        return $this->CI->authitem_model->createTask($name, $description, $bizRule, $data);
    }
    public function createOperation($name, $description='', $bizRule=null, $data=null){
        return $this->CI->authitem_model->createOperation($name, $description, $bizRule, $data);
    }
    public function getRoles() {
        return $this->CI->authitem_model->getRoles();
    }

    public function getTasks() {
        return $this->CI->authitem_model->getTasks();
    }

    public function getOperations() {
        return $this->CI->authitem_model->getOperations();
    }
    
    public function addChild($parent, $child){
        return $this->CI->authitem_model->addChild($parent, $child);
    }
    public function addChilds($parent, $arrChilds){
        return $this->CI->authitem_model->addChilds($parent, $arrChilds);
    }
    public function getAuthItemByID($id){
       return $this->CI->authitem_model->getAuthItemByID($id);
        
    }
    public function getAuthItemByName($name){
        return $this->CI->authitem_model->getAuthItemByName($name);
    }
    public function assign($itemName,$userId, $bizRule='',$data=''){
        return $this->CI->authitem_model->assign($itemName, $userId, $bizRule, $data);
    }
    
    public function assignByID($itemId,$userId, $bizRule='',$data=''){
        return $this->CI->authitem_model->assignByID($itemId, $userId, $bizRule, $data);
    }
    public function checkAccess($itemname, $childname){
        return $this->CI->authitem_model->checkAccess($itemname, $childname);
    }
    public function checkAccessByID($itemid, $somethingid){
        return $this->CI->authitem_model->checkAccessByID($itemid, $somethingid);
    }
    
    public function checkAccessForUser($uid, $itemname){
        return $this->CI->authitem_model->checkAccessForUser($uid, $itemname);
    }
    public function checkAccessForUserByID($uid, $itemid){
        return $this->CI->authitem_model->checkAccessForUserByID($uid, $itemid);
    }
    public function getAuthAssignments($uid){
        return $this->CI->authitem_model->getAuthAssignments($uid);
    }

    
}

