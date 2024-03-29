<?php

class cloud_model extends MY_Model{
    private $_file_table = 'cloud_user_file';
    private $_dir_table = 'cloud_user_directory';
    private $_share_table = 'cloud_share';
    private $_love_table = 'cloud_love_log';
    private $_down_table = 'cloud_download_log';
    private $_redis=false;
    private $_old_cloud_dir_where = 'cat_id=null ';
    function __construct(){
        parent::__construct();
        $this->load->model("redis/redis_model");
    }

    //创建新的文件夹
    function insert_user_directory($param){
        return $this->db->insert($this->_dir_table,$param);
    }

    //将文件分享到班级
    function share_to_classes($param){
        $this->load->model('class/classes_student');
        $this->load->model("exercise_plan/student_task_model");
        foreach($param as $val){
            $this->db->insert($this->_share_table,$val);
            $insert_id = $this->db->insert_id();
            if($insert_id){
                $stu_ids = array();
                $stu_ids = $this->classes_student->get_user_ids($val['class_id'],'user_id');
                if($stu_ids){
                    foreach($stu_ids as $stus=>$s){
                        $this->student_task_model->pushTaskOnShare($s['user_id'],$insert_id,time()); 
                    }
                }
            }
        }
        
        $this->load->library("credit");
        if (isset($param[0]["user_id"])){
			$this->credit->exec($param[0]["user_id"], "cloud_share");
		}
        return $insert_id;
    }

    //[某老师]对某班级的分享的资料
    function get_share_files_by_class($uid,$class_id,$page=1,$pagesize=10){
        if($page<1)$page=1;
        $start = ($page-1)*$pagesize;
        $limit_sql = " limit $start , $pagesize";
        if($uid){
            $uid_sql = " and s.user_id=$uid ";
        }else{
            $uid_sql = '';
        }
        $sql = "select *,s.id as share_id from $this->_share_table s left join $this->_file_table f on f.id=s.file_id where 1=1 $uid_sql and s.class_id=$class_id and s.is_del=0  order by s.create_time desc ".$limit_sql;
        /*2014-07-08 删除条件：and f.is_del=0   删除文件本身不影响分享到班级的文件 */
        return $this->db->query($sql)->result_array();//echo $this->db->last_query();die;
    }

    //通过分享id来获取相关信息
    function get_file_by_share_id($share_id){
        $sql = "select f.*,s.*,s.id as share_id from $this->_share_table as s left join $this->_file_table as f on s.file_id = f.id where s.id=$share_id and s.is_del=0";
        /*2014-07-08 删除条件：and f.is_del=0   删除文件本身不影响分享到班级的文件 */
        return $this->db->query($sql)->result_array();
    }

    //上传文件后写相关数据
    function insert_upload_file($param){
        $user_cloud_storage = $this->get_user_cloud_storage($param['user_id']);
        //echo $user_cloud_storage;
        $this->load->library('credit');
        $privilege = $this->credit->userlevel_privilege($param['user_id']);
        $my_cloud_size = $privilege['privilege']['cloud_sizem']['value']; //单位是M
        $my_cloud_size *= 1024*1024; //单位是byte

        $user_cloud_storage += $param['file_size']; 
        if($user_cloud_storage > $my_cloud_size){
            return -1;
        }
        $this->db->insert($this->_file_table,$param);
        $id = $this->db->insert_id();
        if($this->redis_model->connect('cloud_statistics')){
            $this->_redis=true;
        }
        if($this->_redis){
            $key = 'user_cloud_storage_'.$param['user_id'];
            $expire=0;
            $this->cache->save($key, $user_cloud_storage, $expire);//所用空间统计
            $key = 'user_cloud_file_total_'.$param['user_id'];  // 上传文件的总数
            $value = $this->cache->get($key);
            if($value === false){
                //redis中没有相应数据就执行sql
                $sql = "select count(*) as num from $this->_file_table where user_id = ? and is_del = 0";
                $arr = array($param['user_id']);
                $value = $this->db->query($sql,$arr)->row(0)->num;
                $this->cache->save($key, $value, 0);
            }else{
                $this->cache->save($key, $value+1, 0);
            }
        }
        if ($id > 0 && isset($param["user_id"])){
			// $this->load->library("credit");
			// $this->credit->exec($param["user_id"], "cloud_first_uploaded");
			
			//任务系统_用户在教学网盘中任意上传一个文件
			$this->load->library("task");
			$this->task->exec($param["user_id"], "use_educloud");
		}
        return $id;
    }

    //redis 操作用户当前网盘存储量
    function get_user_cloud_storage($user_id,$is_percentage = false){
        if($this->redis_model->connect('cloud_statistics'))   
        {
            $this->_redis=true;
        }
        if($this->_redis){
            $key = 'user_cloud_storage_'.$user_id;
            $value = $this->cache->get($key);
            if($value === false){
                $this->db->select("sum(`file_size`) as total_size");
                $value = $this->db->get_where($this->_file_table,array('user_id'=>$user_id,'is_del'=>0))->row()->total_size;
                 $this->cache->save($key, $value, 0);
            }
        }else{
            $this->db->select("sum(`file_size`) as total_size");
            $value = $this->db->get_where($this->_file_table,array('user_id'=>$user_id,'is_del'=>0))->row()->total_size;
        }

        if($is_percentage){
            $this->load->library('credit');
            $privilege = $this->credit->userlevel_privilege($user_id);
            $my_cloud_size = $privilege['privilege']['cloud_sizem']['value']; //单位是M
            $my_cloud_size *= 1024*1024; //单位是byte
            // $my_cloud_size = Constant::CLOUD_DISK_SIZE;//get from previliege
            $value = empty($value)?0:$value;
            $this->load->helper('number');
            if($value > 0){
                $percentage = $value/$my_cloud_size * 100;
                $percentage = round($percentage,1)<=2.0 ? 2 :round($percentage,1);
                $percentage_arr['percentage'] = $percentage > 100 ?'100%':round($percentage,1).'%';
                $percentage_arr['use_storage'] = byte_format($value,0);
                $percentage_arr['total_storage'] = byte_format($my_cloud_size,0);
            }else{
                $percentage_arr['percentage'] = '0%';
                $percentage_arr['use_storage'] = byte_format(0,0);
                $percentage_arr['total_storage'] = byte_format($my_cloud_size,0);
            }
            return $percentage_arr;
        }else{
            return $value;
        }
    }

    //获取某人的某类型的文件集合
    function get_file_by_type($user_id,$type,$is_cloud=true,$page_num=1,$total=false){
        if(in_array($type, array_keys(Constant::cloud_filetype(0,true)))){
            if($total){
                $this->db->select("COUNT(`id`) AS filenum");
                $w = array('user_id'=>$user_id,'file_type'=>$type,'is_del'=>0);
                if($is_cloud){
                    $w['show_place'] = 0 ; //网盘
                }
                return $this->db->get_where($this->_file_table,$w)->row()->filenum;
            }else{

                $limit=Constant::CLOUD_FILE_PER_PAGE_NUM;
                if($page_num<=0) $page_num=1;
                $offset=($page_num-1)*$limit;
                if($is_cloud){
                    $this->db->where('show_place',0);
                }
                $this->db->where(array('user_id'=>$user_id,'file_type'=>$type,'is_del'=>0));
                $this->db->order_by('upload_time','desc');
                $this->db->limit($limit,$offset);
                return $this->db->get($this->_file_table)->result_array();
            }
        }
        return null;
    }

    function get_dir_info($dir_id,$field='*'){
        $dir_id = intval($dir_id);
        $sql = "select $field from $this->_dir_table where dir_id=$dir_id and is_del=0 limit 1";
        $return = $this->db->query($sql)->result_array();
        if(isset($return[0])){
            return $return[0];
        }
        return null;
    }

    //获取某人名下的某个文件夹下的所有文件和文件夹
    function get_dir_child_by_p_id($user_id,$dir_id=0,$page_num=1,$file_offset=0,$total=false){
        //tizi4.0 ， 分离网盘和资源库（备课）
        if($dir_id ==0 ){//因为从备课上传的文件，dir_id也会是0，所以不能认为dir_id为0的就是网盘
            $is_cloud = array('dir_id'=>0,'show_place'=>0);
        }
        if($total){
            $this->db->select("COUNT(`id`) AS filenum");
            $w = array('user_id'=>$user_id,'dir_id'=>$dir_id,'is_del'=>0);
            if($dir_id == 0){
                $w = array_merge($w,$is_cloud);
            }
            $files_total = $this->db->get_where($this->_file_table,$w)->row()->filenum;
            $this->db->select("COUNT(`dir_id`) AS dirnum");
            $w = array('user_id'=>$user_id,'p_id'=>$dir_id,'is_del'=>0);
            $dir_total = $this->db->get_where($this->_dir_table,$w)->row()->dirnum;
            $all_total = $dir_total+$files_total;
            if($files_total>0){
                return array('all_total'=>$all_total,'total_page'=>$all_total,'dir_total'=>$dir_total,'file_total'=>$files_total);
            }
            return array('all_total'=>$all_total,'total_page'=>$all_total,'dir_total'=>$dir_total,'file_total'=>$files_total);
        }else{
            //dir list
            $limit=Constant::CLOUD_FILE_PER_PAGE_NUM;
            if($page_num<=0) $page_num=1;
            $offset=($page_num-1)*$limit;
            $w = array('user_id'=>$user_id,'is_del'=>0,'p_id'=>$dir_id,'cat_id'=>null);
            $this->db->where($w);
            $this->db->limit($limit,$offset);
            $this->db->order_by('create_time','desc');
            $dir_query=$this->db->get($this->_dir_table);
            $return = array('dir'=> $dir_query->result_array(),'file'=>null);
            $count_dir = count($return['dir']);
            //files list
            $count_file=Constant::CLOUD_FILE_PER_PAGE_NUM-$count_dir;
            if(!$count_file){
                return $return;
            }
            
            $show_place = $dir_id==0?' and show_place=0 ':'';
            if(!$file_offset){
                $file_offset = $count_file;
                $sql = "select * from $this->_file_table where dir_id=$dir_id $show_place and user_id=$user_id and is_del=0 order by upload_time desc limit 0,$file_offset";
            }else{
                $sql = "select * from $this->_file_table where dir_id=$dir_id $show_place and user_id=$user_id and is_del=0 order by upload_time desc limit $file_offset,".Constant::CLOUD_FILE_PER_PAGE_NUM;
            }
            $return['file'] = $this->db->query($sql)->result_array();            
            return $return;
        }
    }

    //获取文件夹下的文件
    function get_files_in_a_dir($uid,$dir_id,$filetype=0,$sub_cat_id=null){
        $is_cloud = $dir_id==0?' and show_place=0 ':'';
        if(in_array($filetype,array_keys(Constant::cloud_filetype(0,true)))){
            $f_sql = " and file_type=$filetype";
        }else{
            $f_sql = "";
        }
        $sub_cat_sql = $sub_cat_id?" and sub_cat_id = $sub_cat_id ":'';
        $sql = "select * from $this->_file_table where dir_id=$dir_id  $is_cloud  and user_id=$uid $sub_cat_sql and is_del=0 $f_sql order by id desc ";
        return $this->db->query($sql)->result_array();
    }

    //完整的目录结构     班级分享，从网盘上传 的box的左侧
    function get_dir_tree($uid,$from_dir=0){
        $sql = "select dir_id,dir_name,depth,p_id from $this->_dir_table where user_id=$uid and is_del=0 and dir_id>=$from_dir and cat_id is null order by dir_id desc";
        $res = $this->db->query($sql)->result_array();
        if(!isset($res[0])){
            $html="<ul>
            <!-- 第一级 -->
            <li class=''><div class='tree-title' dir-id='0'><a href='javascript:void(0)' class='icon'></a><a href='javascript:void(0)' class='shareItem  unfold'>其他文件（原网盘）</a></div>";
            $html.="</li></ul>";
            return $html;
        }
        $this->load->helper('array');
        //获取depth区间
        $max_depth = 0;
        foreach($res as $dps => $d){
            if($d['depth']>$max_depth){
                $max_depth = $d['depth'];
            }
        }
        for($i=$max_depth;$i>=0;$i--){ //处理成树形，子集存在child里,有没有自己看有没有child字段
            foreach($res as $key=>$val){
                if($val['depth']==$i){
                    foreach($res as $k=>$v){
                        if($val['p_id']==$v['dir_id']){
                            $v['child'][] = $val;
                            $res[$k] = $v;
                            unset($res[$key]);
                        }
                    }
                }
            }
        }
        $res = array_merge($res);//让下标从0开始按顺序排，不然加html的时候会出错；
        $return =  $this->build_dir_tree_with_html($res);
        $html="<ul>
            <!-- 第一级 -->
            <li class=''><div class='tree-title' dir-id='0'><a href='javascript:void(0)' class='icon icon-add'></a><a href='javascript:void(0)' class='shareItem fold unfold'>其他文件（原网盘）</a></div>";
        $html.=$return."</li></ul>";
        return $html;
    }

    function build_dir_tree_with_html($res,$html='',$collaps_times=1){
        $collaps = 15;//前端写的是m_l_15, m_l_30 这样的 , 15为一个单位，跟前端商定就好
        $indent = $collaps_times*$collaps;
        $indent_style = "style='margin-left:{$indent}px;'";
        $has_sibling = 0;
        $ul_begin = "<ul class='undis folderList'>";
        $ul_end = "</ul>";
        $li_begin = "<li><div class='tree-title' dir-id='%d' sub_cat_id='%d'><a href='javascript:void(0)' class='icon icon-width %s' %s></a><a href='javascript:void(0)' class='shareItem fold'>%s</a></div>";//%s处是dir_name
        // $li_begin = "<li>%s";//%s处是dir_name
        $li_end = "</li>";
        $count_li_end = $count_ul_end = 0;
        foreach($res as $key=>$val){
            if(0==$key){
                $html.=$ul_begin;
            }
            if(isset($val['child'])){
                $icon_add = 'icon-add';
            }else{
                $icon_add = '';
            }
            if(!isset($val['sub_cat_id'])){
                $val['sub_cat_id'] = 0;
            }
            $html.=sprintf($li_begin,$val['dir_id'],$val['sub_cat_id'],$icon_add,$indent_style,$val['dir_name']);
            $count_li_end++;
            if(isset($val['child'])){
                $html = $this->build_dir_tree_with_html($val['child'],$html,$collaps_times+2);
            }
            $html.=$li_end;
            if(!isset($res[$key+1])){
                $html.=$ul_end;
            }
        }
        return $html ;
    }

    //获取文件的信息    当only_file_info为true，只获取file表中的内容
    function file_info($file_id,$field='*',$class_id=0,$only_file_info=false){
        $file_id = intval($file_id);
        if(!$file_id){
            return null;
        }
        $class_sql = '';
        if($class_id){
            $class_sql = " and s.class_id=$class_id";
        }
        $sql = "select f.$field , s.*,s.id as share_id , f.user_id as user_id from $this->_file_table f left join $this->_share_table s on s.file_id=f.id where f.id=$file_id  $class_sql limit 1";
        /*2014-07-08 删除条件：and f.is_del=0   删除文件本身不影响分享到班级的文件 */
        if($only_file_info){
            $sql = "select * from $this->_file_table where id=$file_id and is_del=0 limit 1";
        }
        $res = $this->db->query($sql)->result_array();
        if(isset($res[0])){
            return $res[0];
        }
        return null;
    }

    //某文件夹下的文件总数
    function file_sum_by_dir_id($user_id,$dir_id){
        $user_id = intval($user_id);
        $dir_id = intval($dir_id);
        $sql = "select count(1) as num from $this->_file_table where user_id=$user_id and is_del=0 ";
        if($dir_id){
            $sql .= " and dir_id = $dir_id";
        }
        $num = $this->db->query($sql)->row(0)->num;
        return $num;
    }

    //检查文件夹or文件是否属于该用户
    function check_belonging($user_id,$dir_id,$is_file=false,$is_del=0){
        $user_id = intval($user_id); $dir_id = intval($dir_id);
        if(!$is_file){
            $sql = "select count(1) as num from $this->_dir_table where user_id=$user_id and dir_id=$dir_id";
        }else{
            $sql = "select count(1) as num from $this->_file_table where user_id=$user_id and id=$dir_id";
        }
        if($is_del == 0){
            $sql.=" and is_del=0";//班级空间里的文件下载时也要检测，但是可以排除这个条件
        }
        $num = $this->db->query($sql)->row(0)->num;
        return $num;
    }

    function rename_dir_or_file($is_file=true,$id,$newname){
        if($is_file){//是文件
            $data = array('file_name' => $newname );
            $table = $this->_file_table;
            $index = 'id';
        }else{//是目录
            $data = array('dir_name' => $newname );
            $table = $this->_dir_table;
            $index = 'dir_id';
        }
        $this->db->where($index, $id);
        $this->db->where('is_del', 0);
        return $this->db->update($table, $data); 
    }

    //移动文件夹/文件
    function move_dir_or_file($is_file,$resouce_id,$to_dir_id,$uid,$dir_cat_id=null,$sub_cat_id=null,$resource_type=null){
        if($is_file){//是文件
            $table = $this->_file_table;
            $data = array('dir_id' => $to_dir_id );
            // if($dir_cat_id and $sub_cat_id and $resource_type){
                $data['dir_cat_id'] = $dir_cat_id;
                $data['sub_cat_id'] = $sub_cat_id;
                $data['resource_type'] = $resource_type;
            // }
            $index = 'id';
        }else{//是目录
            $table = $this->_dir_table;
            $former_depth = $this->get_dir_info($resouce_id,'depth');
            if($to_dir_id==0){//移动到最上面一层，‘全部文件’层
                $new_depth = 0;
            }else{
                $new_depth = $this->get_dir_info($to_dir_id,'depth');
                if(isset($new_depth['depth']) && isset($former_depth['depth'])){
                    $new_depth = $new_depth['depth']+1;
                }else{
                    return false;
                }
            }
            //改变子文件夹的深度
            $depth_diff = $new_depth-$former_depth['depth'];//为负数也没关系
            $this->change_child_dir_depth($resouce_id,$depth_diff,$uid);

            $data = array('p_id' => $to_dir_id,'depth'=>$new_depth );
            $index = 'dir_id';
        }
        $this->db->where($index, $resouce_id);
        $this->db->where('is_del', 0);
        return  $this->db->update($table, $data); //echo $this->db->last_query();die;
    }

    //改变子文件夹的depth字段，iteration
    function change_child_dir_depth($p_dir_id,$depth_diff,$uid){
        $dirs = $this->get_dir_child_by_p_id($uid,$p_dir_id,1,Constant::CLOUD_DIR_NUM_MAX);
        $dirs = $dirs['dir'];
        if(!empty($dirs)){//如果有子文件夹
            foreach($dirs as $key=>$val){
                $this->change_child_dir_depth($val['dir_id'],$depth_diff,$uid);
            }
            $sql = "update $this->_dir_table set depth = depth+ $depth_diff where dir_id={$val['dir_id']} and is_del=0";
            $this->db->query($sql);
        }
    }

    //从action调用，删除某个文件夹or文件,如果是删除文件夹，就要连其下的文件夹&&文件统统删掉
    function del_dir_or_file($uid,$id,$is_file=true){
        if($is_file){//是文件
            $table = $this->_file_table;
            $index = 'id';
        }else{//是目录
            $table = $this->_dir_table;
            $index = 'dir_id';
        }
        if(!$is_file){//是文件夹，就要将下面的文件夹也一并删除
            $dirs = $this->get_dir_child_by_p_id($uid,$id,1,Constant::CLOUD_DIR_NUM_MAX);
            $dirs = $dirs['dir'];
            if(!empty($dirs)){//iteration
                foreach($dirs as $key=>$val){
                    $this->del_dir_or_file($uid,$val['dir_id'],false);
                }
            }
            $this->del_files_under_dir($uid,$id);
        }

        $data = array('is_del'=>1);
        $this->db->where($index, $id);
        $this->db->where('is_del', 0);
        if($is_file){
            $data['del_time'] = time();
        }
        $re_value =  $this->db->update($table, $data); 
        if($re_value){
            if($this->redis_model->connect('cloud_statistics'))   
            {
                $this->_redis=true;
            }
            if($this->_redis){
                $key = 'user_cloud_storage_'.$uid;
                $this->cache->delete($key);

                $key = 'user_cloud_file_total_'.$uid;  // 上传文件的总数
                $this->cache->delete($key); 
            }
        }
        return $re_value;
    }

    //删除一个文件夹下的所有文件
    function del_files_under_dir($uid,$dir_id){
        $time = time();
        $sql = "update $this->_file_table set is_del=1,del_time = $time where user_id=$uid and dir_id=$dir_id";
        $this->db->query($sql);
    }

    //获取下载链接
    function get_download_file_path($file_id,$file_path=''){
        $file_info = $this->file_info($file_id);
        if(!$file_info){
            return '';
        }
        $this->load->config('upload',true,true);
        $config = $this->config->item('upload');
        if(!$file_path){
            $file_path = $file_info['file_path'];
        }
        $file_type = $file_info['file_type'];
        if($file_type==Constant::CLOUD_FILETYPE_DOC){
            $base_url  = $config['domain_document']; 
            $file_path = $base_url.urldecode($file_path);
            if(strpos($file_path, 'http://')===false){
                $file_path='http://'.$file_path;
            }
        }else{
            // $this->load->library('qiniu');
            $this->load->helper('qiniu');
            $file_path = qiniu_download($file_path,$file_info['file_name'].$file_info['file_ext']);
        }
        return $file_path;
    }

    //分享文件的总数
    function share_file_total($class_id='',$uid=0){
        $class_sql = $uid_sql = '';
        if($class_id){
            $class_id = intval($class_id);
            $class_sql = " and s.class_id = $class_id ";
        }
        if($uid){
            $uid = intval($uid);
            $uid_sql = " and s.user_id = $uid ";
        }
        $sql = "select count(1) as num from $this->_share_table s left join $this->_file_table f on f.id=s.file_id where s.is_del=0  $class_sql $uid_sql";
        /*2014-07-08 删除条件：and f.is_del=0   删除文件本身不影响分享到班级的文件 */
        return $this->db->query($sql)->row(0)->num;
    }

    //tizi 3.0 老师的 上传文件 的 所有总数
    function teacher_file_total($user_id){
        if($this->redis_model->connect('cloud_statistics')){
            $this->_redis=true;
        }
        if($this->_redis){
            $key = 'user_cloud_file_total_'.$user_id;  // key
            $value = $this->cache->get($key);
            if($value === false){
                //redis中没有相应数据就执行sql
                $sql = "select count(*) as num from $this->_file_table where user_id = ? and is_del = 0";
                $arr = array($user_id);
                $value = $this->db->query($sql,$arr)->row(0)->num;
                $this->cache->save($key, $value, 0);
            }
        }else{
            //没有redis 执行sql
            $sql = "select count(*) as num from $this->_file_table where user_id = ? and is_del = 0";
            $arr = array($user_id);
            $value = $this->db->query($sql,$arr)->row(0)->num;
        }
        return $value;
    }

    //取消分享
    function del_share($share_id){
        $sql = "update $this->_share_table set is_del=1 where id=$share_id ";
        return $this->db->query($sql);
    }

    //点赞
    function add_love_share($share_id,$uid){
        if($this->check_have_loved($share_id,$uid)){//如果已经点赞，就不能再点；
            return false;
        }
        $this->db->trans_start();
        //先往cloud_love_log插入一条记录，再往cloud_share中love_count记录+1
        if($this->db->insert($this->_love_table,array('share_id'=>$share_id,'user_id'=>$uid,'op_time'=>time(),'is_del'=>0))){
            $sql = "update $this->_share_table set love_count=love_count+1 where id=$share_id ";
            $this->db->query($sql);
        }
        $this->db->trans_complete();
        if($this->db->trans_status() === FALSE){
            return false;
        }
        return true;
    }

    //查询该学生是否已赞该分享
    function check_have_loved($share_id,$uid){
        $sql = "select count(1) as num from $this->_love_table where user_id=$uid and share_id=$share_id ";
        if($this->db->query($sql)->row()->num){ 
            return true;
        }
        return false;
    }

    //学生点击进入详情后，hit_count+1
    function add_hit_count($share_id){
        $sql = "update $this->_share_table set hit_count=hit_count+1 where id=$share_id ";
        $this->db->query($sql);
    }

    //学生下载后，记录加一
    function add_download_share($share_id,$uid){
        //先往cloud_download_log插入一条记录，再往cloud_share中download_count记录+1
        if($this->db->insert($this->_down_table,array('share_id'=>$share_id,'user_id'=>$uid,'op_time'=>time(),'is_del'=>0))){
            $sql = "update $this->_share_table set download_count=download_count+1 where id=$share_id ";
            $this->db->query($sql);
        }
        /*充话费活动，记录学生下载情况*/
        $this->load->model('resource/download_share_model');
        $this->download_share_model->add($share_id,$uid);
        /*活动结束*/
        $share = $this->get_file_by_share_id($share_id);
        if (isset($share[0]["user_id"])){
            $teacher_id = $share[0]["user_id"];//老师的id
            //学生下载才给加分
            $this->load->model('login/register_model');
            $role = $this->register_model->get_user_info($uid,0,'user_type');
            if($role['errorcode']){
                if($role['user']->user_type == Constant::USER_TYPE_STUDENT){
                    $teacher_is_cert = $this->register_model->get_user_info($teacher_id,0,'certification');
                    if(isset($teacher_is_cert['user']->certification)){
                        $teacher_is_cert = $teacher_is_cert['user']->certification?1:0;
                    }else{
                        $teacher_is_cert = 0;
                    }
        			$this->load->library("credit");
        			$data = array($uid);
        			$this->credit->exec($share[0]["user_id"], "cloud_share_download", $teacher_is_cert, "", $data);
                }
            }
		}
        return true;
    }

    //计算用户的目录总数
    function dir_count($uid){
        $sql = "select count(*) as num from $this->_dir_table where user_id=$uid and is_del=0 ";
        $num = $this->db->query($sql)->row(0)->num;
        return $num;
    }

    //新建文件夹和rename文件夹的时候，如果有重名，就自动加一
    //$dir_name是用户输入或上传的文件的原始名字
    //$to_cat 为true，就是检查资源库中的文件；为false就是检查老网盘中的文件
    function check_dir_name_exist($pid=0,$dir_name,$uid,$is_file=false,$ext=0,$to_cat=false){
        if($is_file){
            $table = $this->_file_table;
            $select = 'file_name';
            $dir_index = 'dir_id';
            $ext_sql = " and file_ext='$ext' ";
            $cat = ' and (dir_cat_id is null or dir_cat_id = 0 ) ';
            if($to_cat){
                $cat = ' and dir_cat_id > 0 ';
            }
        }else{
            $table = $this->_dir_table;
            $select = 'dir_name';
            $dir_index = 'p_id';
            $ext_sql = "";
            $cat = ' and cat_id is null ';
            if($to_cat){
                $cat = ' and dir_cat_id is not null ';
            }
        }
        $sql = "select count(1) as num from $table where user_id=$uid $cat and is_del=0 and $select=? and $dir_index=$pid $ext_sql";
        $sql_arr = array($dir_name);
        $num = $this->db->query($sql,$sql_arr)->row(0)->num;
        // echo $this->db->last_query();die;
        if(!$num){//不存在就返回当前名字
            return $dir_name;
        }
        $tmp_dir_name = addslashes($dir_name);
        $sql = "select $select from $table where user_id=$uid $cat and is_del=0 and $dir_index=$pid $ext_sql and $select REGEXP '^$tmp_dir_name\\\(?[0-9]*\\\)?$' order by $select ";
        $res = $this->db->query($sql)->result_array();
        if(!$res){
            return $dir_name."(1)";
        }
        foreach($res as $key=>$val){
            $temp = explode($dir_name.'(', $val[$select]);
            if(isset($temp[1])){
                $temp = rtrim($temp[1],')');
                $new[] = $temp;
            }
        }
        if(!isset($new) || $new[0]>1){
            return $dir_name."(1)";
        }
        $count = count($new);
        for($i=0;$i<$count;$i++){
            $_tmp = $new[$i]+1;
            if(!in_array($_tmp,$new)){
                return $dir_name."($_tmp)";
            }
        }
        return $dir_name."($_tmp)";
    }

    function get_parent_dir($p_id){
        return $this->db->get_where($this->_dir_table,array('dir_id'=>$p_id))->row();
    }

    //搜出所有的子文件夹,recursively（包括子文件夹的子文件夹）
    function get_all_child_dir_id_string($uid,$p_id=0,$all_dir=''){
        $sql = "select dir_id from $this->_dir_table where p_id=$p_id and is_del=0 and user_id=$uid";
        $res = $this->db->query($sql)->result_array();
        if($res){
            foreach($res as $key=>$val){
                $all_dir .= $val['dir_id'].",";
                $all_dir=$this->get_all_child_dir_id_string($uid,$val['dir_id'],$all_dir);
            }    
        }
        return $all_dir;
    }

    public function get_single_doc_preview($doc_id,$user_id,$is_join = false,$is_del=0)
    {
        $this->db->where($this->_file_table.'.id',$doc_id);
        if($user_id) $this->db->where($this->_file_table.'.user_id',$user_id);
        if($is_del==0){
            $this->db->where($this->_file_table.'.is_del',0);
        }
        $this->db->where($this->_file_table.'.queue_status',1);
        if($is_join){
            $this->db->join('cloud_document_preview','cloud_user_file.id=cloud_document_preview.doc_id','left');
            $this->db->select($this->_file_table.'.*,cloud_document_preview.swf_folder_path,cloud_document_preview.page_count');
        }
        $this->db->limit(1);
        return $this->db->get($this->_file_table)->row();
        
    }

    function update_file_table($data,$where,$where_in=false){
        if($where_in)$this->db->where_in('id',$where);
        else $this->db->where($where);
        $res = $this->db->update($this->_file_table,$data);
        return $res;
    }

    /**
     * 我的贡献页面（审核中的文件）
     */
    public function get_upload_check($user_id,$page_num=1,$total=false)
    {
        
        $this->db->where('user_id',$user_id);
        $this->db->where_in('is_share_to_tizi',array(2,3));
        if($total){
            $this->db->select('count(id) as total');
            $query=$this->db->get('cloud_user_file');
            $count=isset($query->row()->total)?$query->row()->total:0;
            return $count;
        }else{
            $this->db->select('id,file_name,file_ext,upload_time,is_share_to_tizi');
            $limit=10;
            if($page_num<=0) $page_num=1;
            $offset=($page_num-1)*$limit;
            $this->db->order_by('upload_time','desc');
            $this->db->limit($limit,$offset);
            $query=$this->db->get('cloud_user_file');
            return $query->result();
        }
    }

}