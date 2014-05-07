<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');


class Captcha {

    private $_ci;          // CI object
    private $word;         // 验证码显示内容;
    private $config;

    public function __construct(){
        $this->_ci = & get_instance();
        $this->_ci->load->library('session');
        $this->_ci->load->config('captcha', true, true);
		$this->_ci->load->helper('url');
        $this->_ci->load->helper('string');
        $this->_ci->load->helper('captcha');
        
        $this->setWord();
        $this->setImgOptions();
    }

    public function validateCaptcha($input_captcha_word,$page_name,$unset_word=true)
    {
        if(!$page_name) return false;
        if(empty($input_captcha_word)) return false;
        $input_captcha_word = $this->formatWord($input_captcha_word);
        $captcha_token = $this->_ci->session->userdata('captcha_token');
        if($captcha_token) $captcha_token=json_decode($captcha_token,true);      
        else return false;

        if(isset($captcha_token[$page_name])&&strtolower($input_captcha_word)==strtolower($captcha_token[$page_name])) 
        {
            if($unset_word)
            {
                unset($captcha_token[$page_name]);
                if(empty($captcha_token)) $this->_ci->session->unset_userdata('captcha_token');
                else $this->_ci->session->set_userdata('captcha_token', json_encode($captcha_token));
            }
            return true;
        }
        else return false;
    }
    
    public function generateCaptcha($page_name)
    {
        if(!$page_name) return false;

        $this->config['word'] = $this->generate_word($page_name);
        $captcha_arr  = create_captcha($this->config);

        return $captcha_arr;
    }

    public function generate_word($page_name)
    {
        if(!$page_name) return false;
        $captcha_token = $this->_ci->session->userdata('captcha_token');
        if($captcha_token) $captcha_token=json_decode($captcha_token,true);
        else $captcha_token=array($page_name=>'');
        $captcha_token[$page_name]=$this->word;
        $this->_ci->session->set_userdata('captcha_token', json_encode($captcha_token));
        return $this->word;
    }

    private function setWord($count=4){
        $this->word = $this->formatWord(random_string('alnum','4'));
        
    }

    public function setImgOptions(){
        $this->config = $this->_ci->config->item('captcha');
    }

    private function formatWord($word){
        if($word){
            $word = str_ireplace(array('i','1'), 'I', $word);
            $word = str_ireplace(array('0','o'), 'O', $word);
        }
        return $word;
    }

}
