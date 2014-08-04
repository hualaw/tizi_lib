<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require LIBPATH."third_party/phpqrcode/phpqrcode.php";//引入PHP QR库文件

class Tizi_Qrcode extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        //$this->load->library('qrcode');
        $this->load->model('login/session_model');
    }
    
    public function generate()
    {
        $qrcode_value = $this->input->get('qrcode_value');
        $qrcode_prefix = $this->input->get('qrcode_prefix');
        $qrcode_size = $this->input->get('qrcode_size',true,true,3);
        if(!$qrcode_value)
        {
            $qrvalue=$this->session_model->generate_qrtoken();
        }
        else
        {
            $qrvalue=$qrcode_value;
        }
        if($qrcode_prefix)
        {
            $qrvalue = $qrcode_prefix.$qrvalue;
        }
        
        if($this->input->get('qrcode_type',true,true,'') == 'base64')
        {
            ob_start();
            //$image_obj = $this->qrcode->generateQrcode($qrvalue);
            QRcode::jpg($qrvalue, false, 'L', $qrcode_size);
            $this->output->set_content_type('jpeg');
            $image = ob_get_clean();
            $qrtoken_img['image']='data:image/jpeg;base64,'.base64_encode($image);
        }
        else
        {
            $qrtoken_img['image']=site_url('qrtoken_img')."?&ver=".time()
                .($qrcode_value?"&qrcode_value=".$qrcode_value:'')
                .($qrcode_size?"&qrcode_size=".$qrcode_size:'');
        }

        $qrtoken_img['errorcode']=true;

        echo json_token($qrtoken_img);
        exit();
    }

    public function generate_img()
    {
        $qrcode_value = $this->input->get('qrcode_value');
        $qrcode_prefix = $this->input->get('qrcode_prefix');
        $qrcode_size = $this->input->get('qrcode_size',true,true,3);
        if(!$qrcode_value)
        {
            $qrvalue=$this->session_model->generate_qrtoken();
        }
        else
        {
            $qrvalue=$qrcode_value;
        }
        if($qrcode_prefix)
        {
            $qrvalue = $qrcode_prefix.$qrvalue;
        }
        //$image_obj = $this->captcha->generateQrcode($qrtoken);
        QRcode::jpg($qrvalue, false, 'L', $qrcode_size);
        $this->output->set_content_type('jpeg');
        exit();
    }
}
