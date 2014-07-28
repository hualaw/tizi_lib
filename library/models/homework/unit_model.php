<?php

class Unit_Model extends MY_Model {
    protected $tab_s_unit = "common_unit";

    function __construct(){
        parent::__construct();
    }

    function get_units_by_banben_stage($banben_id,$stage_id){
        $banben_id = intval($banben_id);
        $stage_id = intval($stage_id);
        if(!$banben_id or !$stage_id){return null;}

        $select = "*";
        $sql = "select $select from {$this->tab_s_unit} where stage_id=$stage_id and edition_id=$banben_id and status=1";
        $order = ' order by unit_number ';
        $res = $this->db->query($sql.$order)->result_array();
        return $res;
    }   

    //通过unit id串 搜出 对应的版本和年级 信息
    function get_banben_stage_by_unit($unit_ids){
        if(!$unit_ids)return null;
        $sql = "select cu.*, ce.name as banben_name, map.zy_stage_id , cs.name,cs.semester from common_unit cu 
                left join common_edition ce on ce.id=cu.edition_id 
                left join common_stage cs on cs.id=cu.stage_id
                left join zuoye_stage_map map on map.standard_stage_id=cs.id
                where cu.id in ( {$unit_ids} ) ";
        $res = $this->db->query($sql)->result_array();
        $bb = array();
        if($res){
            foreach($res as $k=>$val){
                $shangxia = $val['semester']==1?'上':'下';
                $tmp = $val['banben_name'].$val['name'].$shangxia;
                if(!in_array($tmp,$bb)){
                    $bb[] = $tmp;
                }
            }
        }
        return array('units'=>$res,'banbens'=>$bb);
    }
}

