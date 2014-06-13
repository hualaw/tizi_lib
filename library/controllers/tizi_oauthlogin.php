<?php  if(!defined('BASEPATH'))exit('No direct script access allowed');
require_once(__DIR__.DIRECTORY_SEPARATOR."tizi_controller.php");

class Tizi_Oauthlogin extends Tizi_Controller {

    function __construct()
    {

        parent::__construct();
        $this->load->model("login/login_model");
        $this->load->model("login/session_model");
        $this->load->model('oauth/oauth_model');

    }

    public function oauth()
    {
        /*platform*/
        $platform = 'qq';
        isset($_GET['type']) && $platform = $_GET['type'];

        $oauth_redirect=$this->input->get('redirect',true);
        if($oauth_redirect) $this->session->set_userdata('oauth_redirect',$oauth_redirect);

        $this->load->library('Oauth');
        try{
            $this->oauth->init($platform);
            $this->oauth->login();
        }catch(OauthException $e){
            //exit($e->getMessage());
            show_error($e->getMessage());
        }
    }

    public function callback($platform)
    {
        try{

            $platform_code = Constant::oauth_platform($platform);

            if($platform_code == 1 || $platform_code == 2){

                $this->load->library('Oauth');
                $this->oauth->init($platform);
                $data = $this->oauth->callback();//data = array('open_id'=>'','access_token'=>'');

            }elseif($platform_code == 3){

                $this->load->library('Oauth/wxConnect/wx_auth');
                $auth_data = $this->wx_auth->auth_data();
                $data = $this->wx_auth->user_detail($auth_data);//获取详细资料

            }

            $db_data=array(
                'open_id'=>$data['open_id'],
                'platform'=>$platform_code,
                'access_token'=>$data['access_token']
            );

            $oauth_redirect='';
            //log
            if($platform_code == 3){
                if(!isset($db_data['open_id']) || !$db_data['open_id']){
                    print_r($db_data);
                    echo "open_id is null";
                    exit();
                }
            }
            //log end
            if($db_data['open_id']){
                $user_auth_data = $this->oauth_model->save($db_data);

                $oauth_redirect=$this->session->userdata('oauth_redirect');
                if(empty($user_auth_data['user_id'])){//未绑定用户
                    if($platform_code == 3){
                        echo "unbind";
                    }
                    $this->session->set_userdata("oauth_id", $user_auth_data["oauth_id"]);
    				$this->session->set_userdata("oauth_nickname", $data["nickname"]);
    				$this->session->set_userdata("oauth_platform", $platform_code);

                    if(stripos($oauth_redirect,'http://')!==false)
                    {
                        $this->session->set_userdata('perfect_redirect',$oauth_redirect);
                    }

                    $oauth_redirect=login_url("oauth/firstlogin?platform={$platform_code}");
                }else{//绑定用户
                    if($platform_code == 3){
                        echo "bind";
                    }
                    $session=$this->session_model->generate_session($user_auth_data["user_id"]);
                    $this->session_model->generate_cookie($db_data['open_id'],$user_auth_data["user_id"]);
    				$this->session_model->clear_mscookie();
                    //redirect(redirect_url($session['user_data']['user_type'],'login'));
                    //if(!$oauth_redirect) $oauth_redirect=redirect_url($session['user_data']['user_type'],'login');
                    $oauth_redirect=$this->get_redirect($session['user_data']['user_type'],$session['user_data'],'login',$oauth_redirect);
                }
                if($platform_code == 3){
                    echo $oauth_redirect;exit;
                }
            }else{
                if($platform_code == 3){
                    print_r($data);
                    print_r($db_data);exit;
                }
            }
            if($this->tizi_mobile)
            {
                redirect($oauth_redirect);
            }
            else
            {
                $this->smarty->assign('oauth_redirect',$oauth_redirect);
                $this->smarty->display('file:[lib]header/tizi_oauth.html');
            }
        }catch(OauthException $e){
            //exit($e->getMessage());
            show_error($e->getMessage());
        }

    }

    //weixin
    public function wx_callback(){
        
        $this->load->library('Oauth/wxConnect/wx_auth');
        $auth_data = $this->wx_auth->auth_data();
        print_r($auth_data);
        $user_detail = $this->wx_auth->user_detail($auth_data);//获取详细资料
        print_r($user_detail);
        exit;

    }




}
/*Endoffilelogin.php*/
/*Location:./application/controllers/login/login.php*/
