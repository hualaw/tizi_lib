<?php

require_once __DIR__ . '/Qiniu.php';

/**
 * Description of qiniu_jxt
 *
 * @author caohaihong <caohaihong@91waijiao.com>
 */
class Qiniu_Jxt extends Qiniu {

    /**
     * 上传字符串
     * 
     * @param string $file_name
     * @param string $content
     * @return mixed
     * 
     * @author caohaihong <caohaihong@91waijiao.com>
     */
    public function qiniu_upload_str($file_name, $content) {
        if (!$file_name || !$content) {
            return FALSE;
        }
        $bucket = $this->bucket;

        $pathinfo = pathinfo($file_name);
        $ext = isset($pathinfo["extension"]) ? $pathinfo["extension"] : "";
        $md5 = md5(uniqid());
        $filename = alpha_id(mt_rand(1000000, 9999999)) . "." . $ext;

        $key = date("Ymd") . "/" . substr($md5, 3, 2) . "/" . substr($md5, 7, 26) . $filename;

        $putPolicy = new Qiniu_RS_PutPolicy($bucket);
        $upToken = $putPolicy->Token(null);
        list($ret, $err) = Qiniu_Put($upToken, $key, $content, null);
        return $this->qiniu_result($err, $ret);
    }

    /**
     * 图片缩放[->裁切[->缩放]]
     * 
     * @param string $key
     * @param mixed $data 说明array(
     * 'width_percent' => 100, 
     * ['crop' => true, 'crop_width' => 100, 'crop_height' => 100, 'crop_left' => 100, 'crop_top' => 100, 
     * ['crop_thumb' => true, 'crop_thumb_wp' => 100]])
     * @return string
     * 
     * @author caohaihong <caohaihong@91waijiao.com>
     */
    public function qiniu_get_thumb_crop($key, $data) {
        $domain = $this->domain;
        $baseUrl = Qiniu_RS_MakeBaseUrl($domain, $key);
        $img_thumb = new Qiniu_ImageThumbCrop();

        foreach ($data as $k => $v) {
            $img_thumb->$k = $v;
        }

        $img_thumb_url = $img_thumb->MakeRequest($baseUrl);

        //对fopUrl 进行签名，生成privateUrl。 公有bucket 此步可以省去。
        $getPolicy = new Qiniu_RS_GetPolicy();
        $imgViewPrivateUrl = $getPolicy->MakeRequest($img_thumb_url, null);
        return $imgViewPrivateUrl;
    }

    //图片限定宽高非等比缩放
    public function qiniu_get_thumbn($key, $width, $height) {
        $domain = $this->domain;
        $baseUrl = Qiniu_RS_MakeBaseUrl($domain, $key);
        $img_thumbn = new Qiniu_ImageThumb();

        $img_thumbn->width = $width;
        $img_thumbn->height = $height;

        $img_url = $img_thumbn->MakeRequest($baseUrl);

        //对fopUrl 进行签名，生成privateUrl。 公有bucket 此步可以省去。
        $getPolicy = new Qiniu_RS_GetPolicy();
        $imgViewPrivateUrl = $getPolicy->MakeRequest($img_url, null);
        return $imgViewPrivateUrl;
    }

    /**
     * 获取图片信息
     * 
     * @param type $key
     * @return type
     * 
     * @author caohaihong <caohaihong@91waijiao.com>
     */
    public function qiniu_get_img_size($key) {
        $domain = $this->domain;
        $baseUrl = Qiniu_RS_MakeBaseUrl($domain, $key);
        $info_url = $baseUrl . '?imageInfo';

        //对fopUrl 进行签名，生成privateUrl。 公有bucket 此步可以省去。
        $getPolicy = new Qiniu_RS_GetPolicy();
        $info_url = $getPolicy->MakeRequest($info_url, null);

        $img_info = file_get_contents($info_url);

        return json_decode($img_info, true);
    }

    public function qiniu_img_thumbr($key, $width, $height){
        $domain = $this->domain;
        $baseUrl = Qiniu_RS_MakeBaseUrl($domain, $key);
        
        $img_url = $baseUrl . "?imageMogr2/thumbnail/!{$width}x{$height}r";
        //对fopUrl 进行签名，生成privateUrl。 公有bucket 此步可以省去。
        $getPolicy = new Qiniu_RS_GetPolicy();
        $img_url = $getPolicy->MakeRequest($img_url, null);
        return $img_url;
    }
    
    public function show_file($key){
        $domain = $this->domain;
        $client = new Qiniu_MacHttpClient(null);
        $getPolicy = new Qiniu_RS_GetPolicy(); // 私有资源得有token
        $baseUrl = Qiniu_RS_MakeBaseUrl($domain, $key);
        $privateUrl = $getPolicy->MakeRequest($baseUrl, null); // 私有资源得有token
        return $privateUrl;
    }
}
