<?php 
if(!defined('BASEPATH')) exit('No direct script access allowed');


/*
    从七牛上获取图片   mode，long，short都是七牛接口的参数
*/
if (!function_exists('qiniu_img')) {
    function qiniu_img($key,$mode=0,$short=0,$long=600,$ttl=3600) {
        $ci =& get_instance();
        $ci->load->model('redis/redis_model');
        $redis_key = $key.'_'.$mode.'_'.$short.'_'.$long;
        if($ci->redis_model->connect('qiniu_file')){ //连得上redis，取的到值就直接返回值
            $path = $ci->cache->redis->get($redis_key);
            if($path !== false){ //取的到值就直接返回值
                return $path ;
            }
        }
        // var_dump($key);die;
        //连不上redis或者redis中没有相应的值,就去七牛上获取，然后存入redis
        $ci->load->library('qiniu');
        $path = $ci->qiniu->qiniu_get_image($key,$mode,$short,$long);
        if($path){
            $ci->cache->redis->save($redis_key,$path,$ttl);
            return $path;
        }
        return false;
    }
}

/*七牛下载链接*/
if (!function_exists('qiniu_download')) {
    function qiniu_download($key,$name='unknown',$ttl=3600) {
        $ci =& get_instance();
        $ci->load->model('redis/redis_model');
        if($ci->redis_model->connect('qiniu_file')){ //连得上redis，取的到值就直接返回值
            $path = $ci->cache->redis->get($key);
            if($path !== false){ //取的到值就直接返回值
                return $path ;
            }
        }
        //连不上redis或者redis中没有相应的值,就去七牛上获取，然后存入redis
        $ci->load->library('qiniu');
        $path = $ci->qiniu->qiniu_download_link($key,$name);
        if($path){
            $ci->cache->redis->save($key,$path,$ttl);
            return $path;
        }
        return false;
    }
}