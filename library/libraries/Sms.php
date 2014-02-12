<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CodeIgniter Sms Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		Zujuan Team
 */
class Sms {

    private $_ci;        // CI 对象
    private $phone_nums; // 电话号码
    private $_content;   // 短信内容
    private $signature;  // 请求接口签名
    private $api_uri;    // 短信服务API地址
    private $server_ip;  // 调用服务器IP

    private $version ; // 短信程序的版本

    //Note: Construct don't allow passing params for filtering phone_nums and content withi set method
    public function __construct(/*$phone_nums='', $content=''*/){
       $this->_ci = & get_instance();
       $this->_ci->load->library('curl'); 
       $this->_ci->load->config('sms');
       $this->api_uri = $this->_ci->config->item('api_uri');
       $this->server_ip =  $this->_ci->config->item('server_ip');
       //$phone_nums = $this->filterPhoneNums($phone_nums);
       //$this->phone_nums = $phone_nums;
       //$this->_content = $content;
       $this->signature = $this->buildSignature();

       $this->version = $this->_ci->config->item('version'); // 获取配置文件中的版本号
    }

    //根据version选择相应的发送短信的方法
    public function send(){
        switch($this->version){
            case 1: 
                  $ret=$this->send_1();break;
            case 2:
                  $ret=$this->send_2();break;   
        }
		return $ret;
    }

    //新的发送短信程序
    public function send_2(){
        $flag = 0; 
        //账号SDK-BBX-010-18316  密码463ce3D-
        $sn = $this->_ci->config->item('sn');
        $pwd = $this->_ci->config->item('secret');
        $mobiles = $this->phone_nums;
        $conts = $this->_content;
        $conts = iconv( "UTF-8", "gb2312//IGNORE" ,$conts); // 要先转换成gb
        //要post的数据 
        $argv = array( 
              'sn'=>$sn,
             'pwd'=>strtoupper(md5($sn.$pwd)), //此处密码需要加密 加密方式为 md5(sn+password) 32位大写
             'mobile'=>$mobiles,//手机号 多个用英文的逗号隔开 post理论没有长度限制.推荐群发一次小于等于10000个手机号
             'content'=>$conts,//短信内容
             'ext'=>'',   
             'stime'=>'',//定时时间 格式为2011-6-29 11:09:21
             'rrid'=>''
          ); 
        //构造要post的字符串 
        $params = null;
        foreach ($argv as $key=>$value) { 
            if ($flag!=0) { 
                 $params .= "&"; 
                 $flag = 1; 
            }
            $params.= $key."="; $params.= urlencode($value); 
            $flag = 1; 
         } 
          $length = strlen($params); 
            //创建socket连接 
            $fp = fsockopen("sdk2.entinfo.cn",80,$errno,$errstr,10) or exit($errstr."--->".$errno); 
             //构造post请求的头 
             $header = "POST /webservice.asmx/mt HTTP/1.1\r\n"; 
             $header .= "Host:sdk2.entinfo.cn\r\n"; 
             $header .= "Content-Type: application/x-www-form-urlencoded\r\n"; 
             $header .= "Content-Length: ".$length."\r\n"; 
             $header .= "Connection: Close\r\n\r\n"; 
             //添加post的字符串 
             $header .= $params."\r\n"; 
             //发送post的数据 
             fputs($fp,$header); 
             $inheader = 1; 
              while (!feof($fp)) { 
                             $line = fgets($fp,1024); //去除请求包的头只显示页面的返回数据 
                             if ($inheader && ($line == "\n" || $line == "\r\n")) { 
                                     $inheader = 0; 
                              } 
                              if ($inheader == 0) { 
                                    // echo $line; 
                              } 
              } 
          //<string xmlns="http://tempuri.org/">-5</string>
             $line=str_replace("<string xmlns=\"http://tempuri.org/\">","",$line);
             $line=str_replace("</string>","",$line);
            $result=explode("-",$line);
            if(count($result)>1){
                $msg = 'Send text error code:'.$line.'. ('.$this->get_error_code($line).')';
                $res= array('error'=>$msg,'status'=>'');
                // 发送失败，写日志
                log_message('error_tizi', $msg, array('phone'=>$mobiles));
            }else{
                $res= array('error'=>'Ok','status'=>'');    
                // 测试成功时写日志
                // log_message('info_tizi', $msg, $env_variable);
            }
            return $res;
    }

    //老版 发送短信
    public function send_1(){
       $this->_ci->curl->create($this->api_uri);
       $params = array('phone_nums'=>$this->phone_nums, 'content'=>$this->_content, 'signature'=>$this->signature);
       $this->_ci->curl->post($params);
       $this->_ci->curl->execute();
       $curl_obj = & $this->_ci->curl;
       if(property_exists($curl_obj, 'last_response')){
           $response = $curl_obj->last_response;
       } else {
           //Note: get curl error
           $response = json_encode(array('error'=>$curl_obj->error_string, 'status' => strval($curl_obj->error_code)));
           // 发送失败，写日志
          log_message('error_tizi', $curl_obj->error_string, $params);
       }

       return json_decode($response, true);
       //$this->_ci->curl->debug();
       //$this->_ci->curl->debug_request();
    }

    private function filterPhoneNums($phone_nums=''){
        $phone_list = explode(',', $phone_nums);
        if(!empty($phone_list)) {
            foreach($phone_list as $k => $v){
                $phone_list[$k] = trim($v);
            }
        }
        $phone_nums = implode(',', $phone_list);
        return $phone_nums;
    }

    public function setPhoneNums($phone_nums=''){
        $phone_nums = $this->filterPhoneNums($phone_nums);
        $this->phone_nums = $phone_nums;
    }

    public function setContent($content=''){
        //Ask substring 100 char ?
        $this->_content = $content;
    }

    private function buildSignature(){
        $server_ip = $this->_ci->config->item('server_ip');
        $secret = $this->_ci->config->item('secret');
        return md5($server_ip.$secret);
    }

    // send_2( ) 对应的返回状态码，对应不同的出错情况
    private function get_error_code($code){
        switch($code){
            case '-1': $desc = "重复注册";break;
            case '-2': $desc = "帐号/密码不正确";break;
            case '-4': $desc = "余额不足支持本次发送";break;
            case '-5': $desc = "数据格式错误";break;
            case '-6': $desc = "调用方法的参数有误";break;
            case '-7': $desc = "权限受限,请查看该序列号是否已经开通了调用该方法的权限";break;
            case '-8': $desc = "流量控制错误";break;
            case '-9': $desc = "扩展码权限错误";break;
            case '-10': $desc = "短信内容过长";break;
            case '-11': $desc = "内部数据库错误";break;
            case '-12': $desc = "序列号状态错误";break;
            case '-13': $desc = "没有提交增值内容";break;
            case '-14': $desc = "服务器写文件失败";break;
            case '-15': $desc = "文件内容base64编码错误";break;
            case '-16': $desc = "返回报告库参数错误";break;
            case '-17': $desc = "没有权限";break;
            case '-18': $desc = "上次提交没有等待返回不能继续提交";break;
            case '-19': $desc = "禁止同时使用多个接口地址";break;
            case '-20': $desc = "相同手机号，相同内容重复提交";break;
            case '-22': $desc = "Ip鉴权失败";break;
            default: $desc = 'unkown error';break;
        }
        return $desc;
    }

}
