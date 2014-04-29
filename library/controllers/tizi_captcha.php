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
        ob_start();
        $image_obj = $this->captcha->generateCaptcha($captcha_name);
        $this->output->set_content_type('jpeg');
        ImageJPEG($image_obj['im']);
        ImageDestroy($image_obj['im']);
        $image = ob_get_clean();
        $image_obj['image']='data:image/jpeg;base64,'.base64_encode($image);
        if($need_check) $image_obj['word']='';
        unset($image_obj['im']);
        $image_obj['errorcode']=true;
        echo json_token($image_obj);
        exit();
    }

    public function validate() 
    {
        $input_captcha = $this->input->get('check_code');
        $keep_captcha = $this->input->get('keep_code');
        $captcha_name = $this->input->get('captcha_name');
		
        if($keep_captcha) $unset_word=false;
        else $unset_word=true;
        $response = $this->captcha->validateCaptcha($input_captcha,$captcha_name,$unset_word);
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

        echo json_token($data); 
        exit; 
    }

    protected function captcha_rule($captcha_name)
    {
        $need_check=true;
        return $need_check;
    }
}
