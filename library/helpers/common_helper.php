<?php 
if(!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('get_remote_ip')) {
	function get_remote_ip(){
		if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) $ip = getenv("HTTP_CLIENT_IP"); 
		else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) $ip = getenv("HTTP_X_FORWARDED_FOR"); 
		else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) $ip = getenv("REMOTE_ADDR"); 
		else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) $ip = $_SERVER['REMOTE_ADDR']; 
		else $ip = "unknown"; 
		return $ip;
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