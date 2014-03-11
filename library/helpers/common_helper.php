<?php 
if(!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('preg_phone')) {
    function preg_phone($phone) {
        return preg_match("/^1(3|4|5|8)\d{9}$/",$phone);
    }   
}

if (!function_exists('preg_email')) {
    function preg_email($email) {
        return preg_match("/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/",$email);
    }   
}

if (!function_exists('preg_stuid')) {
    function preg_stuid($stuid) {
        return preg_match("/^\d{8,10}$/",$stuid);
    }   
}

if (!function_exists('preg_uname')) {
    function preg_uname($uname) {
        return preg_match("/^[a-zA-Z]{1}\w{5,17}$/",$uname);
    }
}

if (!function_exists('preg_qq')) {
    function preg_qq($qq) {
        return preg_match("/^\d{5,12}$/",$qq);
    }   
}

if (!function_exists('preg_domain')){
	function preg_domain($domain_name){
		return preg_match("/^[a-zA-Z]{1}[a-zA-Z_0-9]{3,17}$/", $domain_name);
	}
}

if (!function_exists('preg_utype')) {
    function preg_utype($username) {
        if(preg_phone($username)) $login_type=Constant::LOGIN_TYPE_PHONE;
        else if(preg_email($username)) $login_type=Constant::LOGIN_TYPE_EMAIL;
        else if(preg_stuid($username)) $login_type=Constant::LOGIN_TYPE_STUID;
        else if(preg_uname($username)) $login_type=Constant::LOGIN_TYPE_UNAME;
        else $login_type=Constant::LOGIN_TYPE_ERROR;
        return $login_type;
    }
}

if (!function_exists('get_remote_ip')) {
	function get_remote_ip() {
		if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) $ip = getenv("HTTP_CLIENT_IP"); 
		else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) $ip = getenv("HTTP_X_FORWARDED_FOR"); 
		else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) $ip = getenv("REMOTE_ADDR"); 
		else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) $ip = $_SERVER['REMOTE_ADDR']; 
		else $ip = "unknown"; 
		return $ip;
	}
}	

if(!function_exists('user_agent')) {
    function user_agent() {
        $ci = &get_instance();
        $agent = 'Agent:Unidentified User Agent';
        if ($ci->agent->is_browser())
        {
            $agent = 'Browser: '.$ci->agent->browser().' | '.$ci->agent->version().' | '.$ci->agent->platform();
        }
        if ($ci->agent->is_robot())
        {
            $agent = 'Robot: '.$ci->agent->robot().' | '.$ci->agent->version().' | '.$ci->agent->platform();
        }
        if ($ci->agent->is_mobile())
        {
            $agent = 'Mobile: '.$ci->agent->mobile().' | '.$ci->agent->version().' | '.$ci->agent->platform();
        }
        return $agent;
    }
}

if (!function_exists('strap')) {
    function strap($content)
    {
        $content = trim($content);
        $content = strip_tags($content);
        $content = preg_replace("/(&[a-zA-Z]{0,4};)/u", ' ', $content);
        $content = preg_replace("/([^a-zA-Z0-9\x{4e00}-\x{9fa5}]+)/u", ' ', $content);
        $content = trim($content);
        return $content;
    }
}

if (!function_exists('sub_str')) {
    function sub_str($str,$start=0,$length=18,$left="...",$charset="utf-8")
    {
        if($charset=="utf-8"){
            $index=0; 
            $index1=0; 
            $result="";
            $haslen=0; 
            for($i=0;$i<$length;$i++)
            {
                $index1=$index;
                $len=0;

                $len1=0;
                $first_b=substr($str,$index,1);
                if(ord($first_b)>224){
                    if($i>=$start){
                        $len=3;

                        $len1=3;
                    }
                    $index+=$len;
                    $haslen+=$len;
                }elseif(ord($first_b)>192){
                    if($i>=$start) {
                        $len=2;

                        $len1=2;
                    }
                    $index+=$len;
                    $haslen+=$len;
                }else{
                    if($i>=$start) {
                        $len=1;

                        $len1=1.5;
                    }
                    $index+=$len;
                    $haslen+=$len1;
                }
                if($haslen<=$length){
                    $result.=substr($str,$index1,$len);
                }else{
                    break;
                }
            }
        }else{
            $result=mb_substr($str,$start,$length,$charset);
        }
        if($result!=$str){
            $result.=$left;
        }
        return $result;
    }
}    