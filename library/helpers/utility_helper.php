<?php  if(!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('tizi_404')) {
    function tizi_404($redirect = '', $settimeout = true, $status_code = 404) {
        show_404('',true,array('redirect'=>$redirect,'settimeout'=>$settimeout,'status_code'=>$status_code));
        /*
        if($redirect) $redirect = '/'.urlencode($redirect);
        else $redirect = '/0';
        if($settimeout) $settimeout = '';
        else $settimeout = '/0';
        if($status_code == 404) $status_code = '';
        else $status_code = '/'.$status_code;
        redirect('404'.$redirect.$settimeout.$status_code);
        */
    }   
}

if (!function_exists('tizi_get_contents')) {
    function tizi_get_contents($file_path,$redirect='',$ctimeout=Constant::MAX_CONNECT_TIMEOUT,$timeout=Constant::MAX_DOWNLOAD_TIMEOUT)
    {
        set_time_limit($timeout);
        $ci = &get_instance();
        $ci->load->library('curl');
        $ci->curl->option('connecttimeout',$ctimeout);
        $ci->curl->option('timeout',$timeout);
        $ci->curl->create($file_path);
        $file_get_contents = $ci->curl->execute();
        if(empty($file_get_contents))
        {
            if($redirect!==false) tizi_404($redirect);
            else return false;
        }
        else
        {
            return $file_get_contents;
        }
    }
}  

//日志统计
if (!function_exists('log_statistics')) {
    function log_statistics($data, $statistics_url)
    {
        $url_query = '';                                                         
        if(is_array($data) && !empty($data)){
            foreach($data as $key=>$val){                                            
                $url_query .= "&".$key.'='.$val;                                     
            }                                                                        
            $statistics_url .= $url_query;
        }
        $ci = &get_instance();
        $ci->load->library('curl');
        $ci->curl->option('connecttimeout',1);
        $ci->curl->option('timeout',1);
        $ci->curl->option('useragent','Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; WOW64; Trident/6.0; MALCJS)');
        $ci->curl->create($statistics_url);
        $ci->curl->execute();
        if($ci->curl->error_code) log_message('error_tizi','2100010:log statistics error: '.strval($ci->curl->error_code),array('data'=>$data));
        return;
    }
}

//过滤文件/文件夹中的特殊字符
if(!function_exists('filter_file_name')){
    function filter_file_name($name){
        $find = array("\\", "\"","/",":","*","?","<",">","|");
        $replace = "";
        return str_replace($find, $replace, $name);
    }   
}

if(!function_exists('trans_filesize')){
    function trans_filesize($filesize){
        $kb = $filesize/1024;
        if($kb<1){
            return sprintf("%.1f",$filesize)."B";
        }elseif($kb<1024){
            return sprintf("%.1f", $kb)."K";
        }
        $mb = $kb/1024;
        if($mb<1024){
            return sprintf("%.1f", $mb)."M";
        }
        $gb = $mb/1024;
        if($gb<1024){
            return sprintf("%.1f", $gb)."G";
        }
    }   
}

if(!function_exists('tizi_rand')){
    function tizi_rand($award)
    {
        if(empty($award)||!is_array($award)) return false;
        
        $base = 10000;
        $group = count($award);
        $aid = 0;
        //srand((double)microtime()*1000000);
        $rand1 = mt_rand(0,$base)%$group;
        $rand2 = round(mt_rand(0,$base),4)*100/$base;

        $num = count($award[$rand1]);
        for($j=0;$j<$num;$j++)
        {
            if($award[$rand1][$j][1]==100)
            {
                $aid = $award[$rand1][$j][0];
                break;
            }
            else
            {
                if($j==0&&$rand2<=$award[$rand1][$j][1])
                {
                    $aid = $award[$rand1][$j][0];
                    break;
                }
                else if($rand2<=$award[$rand1][$j][1]&&$rand2>$award[$rand1][$j-1][1])
                {
                    $aid = $award[$rand1][$j][0];
                    break;
                }
            }
        }
        return array('rand1'=>$rand1,'rand2'=>$rand2,'aid'=>$aid);
    }
}