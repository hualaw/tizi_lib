<?php 
if(!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('encrypt_password')) {
    function encrypt_password($password,$salt) {
		$encrypt_password='';
    	if($salt) $encrypt_password=$salt.'$'.sha1($salt.$password);		
		//else $encrypt_password=$password;
		return $encrypt_password;
    }   
}

if (!function_exists('encrypt_password_salt')) {
    function encrypt_password_salt($password,$salt_len=6) {
		$salt="";
		if($password&&strpos($password,'$'))
        {
            $password_tmp=explode('$',$password);
            $password_salt=$password_tmp[0];
            if(strlen($password_salt)==$salt_len) $salt=$password_salt;
            else $salt=false;
        }	
        return $salt;
    }
}

if (!function_exists('mask_phone')) {
	function mask_phone($phone) {
		$mask_phone="";
		if($phone&&strlen($phone)==11)
		{
			$phone_head=substr($phone,0,3);
			$phone_end=substr($phone,-4);
			$mask_phone=$phone_head.'****'.$phone_end;
		}	
		return $mask_phone;
	}	
}

if (!function_exists('mask_email')) {
    function mask_email($email) {
        $mask_email="";
        if($email)
        {
			$email_tmp=explode('@',$email);
            $email_head=substr($email_tmp[0],0,3);
			$email_end=$email_tmp[1];	
            $mask_email=$email_head.'****@'.$email_end;
        }
        return $mask_email;
    }
}

if (!function_exists('encrypt_encode')) {
    function encrypt_encode($data) {
	    $ci = get_instance();
        $ci->load->library('encrypt');
        $data = $ci->encrypt->encode($data);
        $data = str_replace("/","-",str_replace("+","_",$data));
        return $data;
    }
}

if (!function_exists('encrypt_decode')) {

    function encrypt_decode($data) {
	    $ci = &get_instance();
        $ci->load->library('encrypt');
        $data = str_replace("-","/",str_replace("_","+",$data));
        $data = $ci->encrypt->decode($data);
        return $data;
    }
}


if (! function_exists ( 'alpha_id_num' )) {
    function alpha_id_num($in, $to_num = false, $pad_up = 6, $passkey = '', $index = '0123456789') {
        return alpha_id($in, $to_num, $pad_up, $passkey, $index);
    }
}

if (! function_exists ( 'alpha_id' )) {
    function alpha_id($in, $to_num = false, $pad_up = 6, $passkey = '', $index = '') {
        $ci = &get_instance();
        if(!$passkey) $passkey = $ci->config->item('encryption_key');
        if(!$index) $index = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        if ($passkey !== null) {
            for($n = 0; $n < strlen ( $index ); $n ++) {
                $i [] = substr ( $index, $n, 1 );
            }
            
            $passhash = hash ( 'sha256', $passkey );
            $passhash = (strlen ( $passhash ) < strlen ( $index )) ? hash ( 'sha512', $passkey ) : $passhash;
            
            for($n = 0; $n < strlen ( $index ); $n ++) {
                $p [] = substr ( $passhash, $n, 1 );
            }
            
            array_multisort ( $p, SORT_DESC, $i );
            $index = implode ( $i );
        }
        
        $base = strlen ( $index );
        
        if ($to_num) {
            $in = strrev ( $in );
            $out = 0;
            $len = strlen ( $in ) - 1;
            for($t = 0; $t <= $len; $t ++) {
                $bcpow = bcpow ( $base, $len - $t );
                $out = $out + strpos ( $index, substr ( $in, $t, 1 ) ) * $bcpow;
            }
            
            if (is_numeric ( $pad_up )) {
                $pad_up --;
                if ($pad_up > 0) {
                    $out -= pow ( $base, $pad_up );
                }
            }
            $out = sprintf ( '%F', $out );
            $out = substr ( $out, 0, strpos ( $out, '.' ) );
        } else {
            if (is_numeric ( $pad_up )) {
                $pad_up --;
                if ($pad_up > 0) {
                    $in += pow ( $base, $pad_up );
                }
            }
            
            $out = "";
            for($t = floor ( log ( $in, $base ) ); $t >= 0; $t --) {
                $bcp = bcpow ( $base, $t );
                $a = floor ( $in / $bcp ) % $base;
                $out = $out . substr ( $index, $a, 1 );
                $in = $in - ($a * $bcp);
            }
            $out = strrev ( $out );
        }
        if ($to_num === true){
            return $out <= PHP_INT_MAX ? intval($out) : (double) $out;
        } else {
            return $out;
        }
    }
}

if (! function_exists ( 'rand6pwd' )) {    
    /**
     * 根据一个唯一的数字，生成一个6位数字的固定密码
     */
    function rand6pwd($integer){
        $pi = M_PI * $integer * M_PI;
        return substr(log10($pi), -6);
    }
}

