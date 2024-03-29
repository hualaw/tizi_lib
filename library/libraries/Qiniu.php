<?php
require_once("Qiniu/io.php");
require_once("Qiniu/rs.php");
require_once("Qiniu/fop.php"); // file operation

class Qiniu {
    private $_CI;          // CI object
    private $secretKey = '';//'PzJzXNJGhIMauc-3WSWIgi5iuRweVsUjpkM9YWKe';
    private $accessKey = '';//zmFixOH8ZTtYSLPEyNX8940-92wdlxm6jR477ml7';

    protected $bucket = "";
    protected $domain = "";

    function __construct(){
        $this->_CI = & get_instance();
        $this->_CI->load->config('qiniu',false,true);
        $this->accessKey = $this->_CI->config->item('accessKey');
        $this->secretKey = $this->_CI->config->item('secretKey');
        Qiniu_SetKeys($this->accessKey, $this->secretKey);
        $this->bucket = $this->_CI->config->item('bucket');
        $this->domain = $this->_CI->config->item('domain');
    }
	
	function get_domain(){
		return $this->domain;
	}

    function change_bucket($bucket_prefix='certification_'){
        $this->_CI->load->config('qiniu',false,true);
        $this->bucket = $this->_CI->config->item($bucket_prefix.'bucket');
        $this->domain = $this->_CI->config->item($bucket_prefix.'domain');  
    }

    // protected function set_bucket($bucket){
    //     $this->bucket = $bucket;
    //     $this->domain = $this->bucket."qiniudn.com";
    // } 

    /*生成token  
        需要轉換成mp4的话：PersistentOps = 'avthumb/mp4'
        转成mp3： avthumb/mp3
    */
    function make_token($expires=3600,$PersistentOps=null)
    {
        $bucket = $this->bucket;
        $putPolicy = new Qiniu_RS_PutPolicy($bucket);
        $putPolicy->Expires = $expires;
        $putPolicy->PersistentOps = $PersistentOps;
        $upToken = $putPolicy->Token(null);
        return $upToken;
    }
 
    //获取下载链接 (私有资源)
    function qiniu_download_link($key,$name = 'unknow',$with_name=true,$ttl=3600){
        $domain = $this->domain;
        $client = new Qiniu_MacHttpClient(null);
        $getPolicy = new Qiniu_RS_GetPolicy(); // 私有资源得有token
        $getPolicy->Expires = $ttl;
        $baseUrl = Qiniu_RS_MakeBaseUrl($domain, $key);
        if($with_name){
            $baseUrl.='?download/'.$name;
        }
        $privateUrl = $getPolicy->MakeRequest($baseUrl, null); // 私有资源得有token
        return $privateUrl;
    }

    //获取共有链接
    function qiniu_public_link($key){
        $domain = $this->domain;
        $baseUrl = 'http://'.($domain.'/'.$key);
        return $baseUrl;
    }

    //即时转换成mp4的接口   访问时间默认设置成 3小时
    function qiniu_media($key,$ext='mp4',$ttl=10800){
        $domain = $this->domain;
        $client = new Qiniu_MacHttpClient(null);
        $getPolicy = new Qiniu_RS_GetPolicy(); // 私有资源得有token
        $getPolicy->Expires = $ttl;
        $baseUrl = Qiniu_RS_MakeBaseUrl($domain, $key);
        //同后缀就不用加转换参数
        $rpos = strrpos($key , '.');
        $get_ext = '';
        if($rpos !== false){
            $get_ext = substr($key,$rpos+1);
        }
        if($get_ext != $ext){
            $baseUrl .= "?avthumb/{$ext}";
        }
        $privateUrl = $getPolicy->MakeRequest($baseUrl, null); // 私有资源得有token
        $privateUrl = ($privateUrl);
        return $privateUrl;
    }

    /*视频截图*/
    function qiniu_vframe($key,$offset=1,$w=400,$h=225,$ttl=36000){
        $domain = $this->domain;
        $client = new Qiniu_MacHttpClient(null);
        $getPolicy = new Qiniu_RS_GetPolicy(); // 私有资源得有token
        $getPolicy->Expires = $ttl;
        $baseUrl = Qiniu_RS_MakeBaseUrl($domain, $key);
        $baseUrl .= "?vframe/jpg/offset/$offset/w/$w/h/$h";
        $privateUrl = $getPolicy->MakeRequest($baseUrl, null); // 私有资源得有token
        $privateUrl = ($privateUrl);
        return $privateUrl;
    }

    //访问预处理后的非mp4资源  如果没转换好，访问链接得到 not found 
    function qiniu_media_afterfop($key,$ext='mp4',$ttl=10800){
        $domain = $this->domain;
        $client = new Qiniu_MacHttpClient(null);
        $getPolicy = new Qiniu_RS_GetPolicy(); // 私有资源得有token
        $getPolicy->Expires = $ttl;
        $baseUrl = Qiniu_RS_MakeBaseUrl($domain, $key);
        //同后缀就不用加转换参数
        $rpos = strrpos($key , '.');
        $get_ext = '';
        if($rpos !== false){
            $get_ext = substr($key,$rpos+1);
        }
        if($get_ext != $ext){
            $baseUrl .= "?p/1/avthumb/{$ext}";
        }
        $privateUrl = $getPolicy->MakeRequest($baseUrl, null); // 私有资源得有token
        $privateUrl = ($privateUrl);
        return $privateUrl;
    }

    //删除七牛上的资源
    function qiniu_del($key){
        $bucket = $this->bucket;
        $domain = $this->domain;
        $client = new Qiniu_MacHttpClient(null);
        $getPolicy = new Qiniu_RS_GetPolicy();
        $baseUrl = Qiniu_RS_MakeBaseUrl($domain, $key);
        $privateUrl = $getPolicy->MakeRequest($baseUrl, null);
        $client = new Qiniu_MacHttpClient(null);
        $err = Qiniu_RS_Delete($client, $bucket, $key);
        return $this->qiniu_result($err);
    }


    //获取指定大小的图片
    function qiniu_get_image($key,$mode=1,$width=100,$height=100){
        $domain = $this->domain;
        $baseUrl = Qiniu_RS_MakeBaseUrl($domain, $key);
        $imgView = new Qiniu_ImageView;
        $imgView->Mode = $mode;
        $imgView->Width = $width;
        if($height)$imgView->Height = $height;
        $imgViewUrl = $imgView->MakeRequest($baseUrl);
        // echo $imgViewUrl;die;

        //对fopUrl 进行签名，生成privateUrl。 公有bucket 此步可以省去。
        $getPolicy = new Qiniu_RS_GetPolicy();
        $imgViewPrivateUrl = $getPolicy->MakeRequest($imgViewUrl, null);
        return $imgViewPrivateUrl;
    }

    //返回结果
    protected function qiniu_result($res,$ret=null){
        if ($res !== null) {
            return array('errorcode'=>false,'msg'=>$res);
        } else {
            return array('errorcode'=>true,'msg'=>$res,'ret'=>$ret);
        }
    }

    /* $name 是上传字段的name*/
    function qiniu_upload($name='uploadfile',$dir=""){
        if(!strlen($name)) {return false;}
        $filesize = $_FILES[$name]["size"];
        // file_put_contents('test_speed.txt', 'size:'.$filesize.',start time: '.time()."\r\n", FILE_APPEND | LOCK_EX);
        $bucket = $this->bucket;
        if (!isset($_FILES[$name]["error"]) || $_FILES[$name]["error"] != 0){
            return false;
        }
        
        $pathinfo = pathinfo($_FILES[$name]["name"]);
        $ext = isset($pathinfo["extension"])?$pathinfo["extension"]:"";
        $md5 = md5(uniqid());
        $filename = alpha_id(mt_rand(1000000, 9999999)) . "." . $ext;

        $key = $dir.date("Ymd") . "/" . substr($md5, 3, 2) . "/" . substr($md5, 7,26).$filename; //不能是slash开头，不然下载会报：bad oauth request

        $_file_content = $_FILES[$name]["tmp_name"];
        $putPolicy = new Qiniu_RS_PutPolicy($bucket);
        $upToken = $putPolicy->Token(null);
        $putExtra = new Qiniu_PutExtra();
        $putExtra->Crc32 = 1;
        list($ret, $err) = Qiniu_PutFile($upToken, $key, $_file_content, $putExtra);
        // file_put_contents('test_speed.txt', 'over time: '.time()."\r\n", FILE_APPEND | LOCK_EX);
        return $this->qiniu_result($err,$ret);
    }
    
    //按要求获取视频资源，second是切片时间长度，preset是预设集
    // function qiniu_get_video($key,$second=10,$preset="video_16x9_440k"){
    //     $domain = $this->domain;
    //     $baseUrl = Qiniu_RS_MakeBaseUrl($domain, $key);

    //     $baseUrl = "http://apitest.b1.qiniudn.com/sample.wav";
    //     $imgViewPrivateUrl = $baseUrl .= "?avthumb/m3u8/preset/{$preset}";
    //     //对fopUrl 进行签名，生成privateUrl。 公有bucket 此步可以省去。
    //     $getPolicy = new Qiniu_RS_GetPolicy();
    //     // $imgViewPrivateUrl = $getPolicy->MakeRequest($baseUrl, null);
    //     return $imgViewPrivateUrl;
    // }

    function test($key){
        $domain = $this->domain;
        $baseUrl = Qiniu_RS_MakeBaseUrl($domain, $key);
        $imgViewPrivateUrl = $baseUrl .= "-m3u8_audio";
        //对fopUrl 进行签名，生成privateUrl。 公有bucket 此步可以省去。
        $getPolicy = new Qiniu_RS_GetPolicy();
        // $imgViewPrivateUrl = $getPolicy->MakeRequest($baseUrl, null);
        return $imgViewPrivateUrl;
    }

    
    //移动文件或重命名, 暂时没用到；
    // function qiniu_move($key, $new_key){
    //     $bucket = $this->bucket;
    //     $domain = $this->domain;
    //     $client = new Qiniu_MacHttpClient(null);
    //     $getPolicy = new Qiniu_RS_GetPolicy();
    //     $baseUrl = Qiniu_RS_MakeBaseUrl($domain, $key);
    //     $privateUrl = $getPolicy->MakeRequest($baseUrl, null);
    //     $client = new Qiniu_MacHttpClient(null);
    //     $err = Qiniu_RS_Move($client, $bucket, $key, $bucket, $new_key);
    //     return $this->qiniu_result($err);
    // }

}