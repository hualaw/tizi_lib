<?php
//新作业，基础模块;
//这里的方法一般不由controller直接调用
//controller直接调用teacher_exercise_plan_model里的方法
class T_Ex_Plan_Infrastructure_Model extends MY_Model{
    private $_table = 'exercise_plan_assign';
    public function __construct(){
        parent::__construct();
        $this->load->model('question/question_course_model');
    }

    //科目id (not subject type id) ，获取第二级的目录, 即教材版本
    //$data['category_second_root_id'] 
    function get_cat_ver_by_subject_id($subject_id){
        $subject_id = intval($subject_id);
        if(!$subject_id) return null;
        //get category depth 1
        $data = array();
        $data['category_root_id']=$this->question_course_model->get_root_id($subject_id);
        $data['category_select']=$category_root_select=$data['category_root_id'][0]->id;
        foreach($data['category_root_id'] as $key=>$c_r_id)
        {
            $data['category_root_id'][$key]->name=mb_substr($c_r_id->name,4);
            if($category_root_select==$c_r_id->id)
            {
                $data['category_root_name']=$c_r_id->name;
                //unset($data['category_root_id'][$key]);
                $check_second_root=true;
            }
        }   
        if(!isset($data['category_root_name']))
        {
            $data['category_root_name']=$data['category_root_id'][0]->name;
            unset($data['category_root_id'][0]);
        }

        //get category depth 2 
        $data['category_second_root_id']=array();
        $data['category_second_root_name']='';
        if($check_second_root)
        {
            $data['category_second_root_id']=$this->question_course_model->get_subtree_node($category_root_select);
            if(!empty($data['category_second_root_id']))
            {
                if($data['category_select']==$category_root_select) $data['category_select']=$data['category_second_root_id'][0]->id;

                foreach($data['category_second_root_id'] as $key=>$c_s_r_id)
                {
                    // $data['category_second_root_id'][$key]->name=$c_s_r_id->name;
                    $data['category_second_root_id'][$key]->name=$c_s_r_id->name;
                    if($data['category_select']==$c_s_r_id->id)
                    {
                        $data['category_second_root_name']=$c_s_r_id->name;
                        //unset($data['category_second_root_id'][$key]);
                    }
                }
                if(!isset($data['category_second_root_name']))
                {
                    $data['category_second_root_name']=$data['category_second_root_id'][0]->name;
                    unset($data['category_second_root_id'][0]);
                }
            }
        }
        return $data; //只需要这个字段
    }

    //获取第三个or第四个节点的单元
    public function get_course_category_depth3($category_node_select)
    {
        $category_node_select=intval($category_node_select);
        $category_node_list = array();
        if($category_node_select<=0)
        {
            $category_node_list['errorcode']=false;
            $category_node_list['error']=$this->lang->line("error_get_category");
        }
        else
        {
            $category_list=$this->question_course_model->get_subtree_node($category_node_select);
            $category_node_list=array();
            $i=0;

            foreach($category_list as $c_l)
            {
                $category_node_list['category'][$i]['id']=$c_l->id;
                $category_node_list['category'][$i]['depth']=$c_l->depth;
                if('course') $category_node_list['category'][$i]['depth']--;
                $category_node_list['category'][$i]['name']=$c_l->name;
                if($c_l->lft==$c_l->rgt-1) $category_node_list['category'][$i]['is_leaf']=1;
                else $category_node_list['category'][$i]['is_leaf']=0;
                $i++;

            }
            $category_node_list['errorcode']=true;
        }   
        return ($category_node_list);
    }

    //通过subject id 获取最上面depth 1 的所有版本id ，人教、苏教 等;
    //和depth2 ， 八年级下 这种
    function get_subject_course_root_id($s_id){
        $data = null;
        $data=$this->question_course_model->get_root_id($s_id);
        if($data){
            foreach($data as $k=>$v){
                $second = $this->get_course_category_depth3($v->id);
                if(isset($second['category'])){
                    $v->second = $second['category'];
                }else{
                    $v->second = null;
                }
            }
        }
        // print_r($data);die;
        return $data;
    }

    

}