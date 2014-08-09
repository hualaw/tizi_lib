<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('array_get')) {
    function array_get($array, $key, $default=null) {
        if(is_array($key)) {
            $list = array();
            foreach($key as $k) {
                $list[] = isset($array[$k]) ? $array[$k] : $default;
            }
            return $list;
        }
        else if(is_string($key)) {
            if(isset($array[$key])) {
                return $array[$key];
            } else return $default;
        }
        return $default;
    }   
}

if ( ! function_exists('explode_to_distinct_and_notempty')) {
    function explode_to_distinct_and_notempty($array,$delimiter=',') {
        $array = explode("$delimiter",$array);
        $array = array_unique($array);
        $array = array_filter($array); // 去空
        return $array;
    }   
/*
  对一个2维数组，给定一个key按照这个key进行排序
*/
function array_sort_by_key($arr,$key,$type='desc')
{ 
    if(empty($arr))return $arr;
    foreach ($arr as $k => $v) {
        if(!array_key_exists($key, $v))return $arr;
    }
   
    $keysvalue = $new_array = array();
    foreach ($arr as $k=>$v){
        $keysvalue[$k] = $v[$key];
    }
    if($type == 'asc'){
        asort($keysvalue);
    }else{
        arsort($keysvalue);
    }
    reset($keysvalue);
    foreach ($keysvalue as $k=>$v){
        $new_array[$k] = $arr[$k];
    }
        return $new_array; 
    } 

}   
