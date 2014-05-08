<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tizi_Captcha extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('captcha');
        $this->load->model('redis/redis_model');
    }
    
    public function generate()
    {
        $captcha_name = $this->input->get('captcha_name');
        if(!$captcha_name)
        {
            echo json_token(array('errorcode'=>false,'error'=>$this->lang->line('default_error')));
            exit();
        }

        $need_check=$this->captcha_rule($captcha_name);
        if($need_check)
        {
            if($this->input->get('captcha_type',true,true,'') == 'base64')
            {
                ob_start();
                $image_obj = $this->captcha->generateCaptcha($captcha_name);
                $this->output->set_content_type('jpeg');
                ImageJPEG($image_obj['im']);
                ImageDestroy($image_obj['im']);
                $image = ob_get_clean();
                $captcha_img['image']='data:image/jpeg;base64,'.base64_encode($image);
            }
            else
            {
                $captcha_img['image']=site_url('captcha_img')."?captcha_name=".$captcha_name."&ver=".time();
            }
            
            $captcha_img['word']='';
        }
        else
        {
            $captcha_img['image']='';
            $captcha_img['word']=$this->captcha->generate_word($captcha_name);
        }
        $captcha_img['errorcode']=true;

        echo json_token($captcha_img);
        exit();
    }

    public function generate_img()
    {
        $captcha_name = $this->input->get('captcha_name');
        $image_obj = $this->captcha->generateCaptcha($captcha_name);
        $this->output->set_content_type('jpeg');
        ImageJPEG($image_obj['im']);
        ImageDestroy($image_obj['im']);
        unset($image_obj['im']);
        exit();
    }

    public function validate() 
    {
        $captcha_word = $this->input->get('check_code');
        $keep_captcha = $this->input->get('keep_code');
        $captcha_name = $this->input->get('captcha_name');
		
        $need_check=$this->captcha_rule($captcha_name);
        if($need_check)
        {
            if($keep_captcha) $unset_word=false;
            else $unset_word=true;
            $response = $this->captcha->validateCaptcha($captcha_word,$captcha_name,$unset_word);
            $data = array();
            if($response) {
                $data['error'] = 'Ok';
                $data['errorcode'] = true;
                $data['status'] = '0';
            } else {
                $data['error'] = $this->lang->line('error_captcha_code');
                $data['errorcode'] = false;
                $data['status'] = '1';
            }
        }
        else
        {
            $this->captcha->generate_word($captcha_name,$captcha_word);
            $data['error'] = 'Ok';
            $data['errorcode'] = true;
            $data['status'] = '0';
        }

        echo json_token($data); 
        exit; 
    }

    protected function captcha_rule($captcha_name)
    {
        $need_check=true;
        return $need_check;
    }
}
