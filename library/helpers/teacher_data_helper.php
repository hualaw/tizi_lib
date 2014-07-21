<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('splitId')) {
   function splitId($str, $sep='|', $option=0){
		$arr = explode($sep, $str);
		$arr = delEmptyElement($arr);
		$id_list = array();
		foreach($arr as $id_str1){
			$id_arr = explode('-', $id_str1);
			$id_arr = delEmptyElement($id_arr);
			if ($option == 0){
				$id_list[intval(end($id_arr))] = 0;
			} else if($option == 1) {
				$id_list[intval(end($id_arr))] = 1;
			}
			
		}
		return $id_list;
	}
}

if ( ! function_exists('prase_file_size')) {
	function prase_file_size($file_size)
    {
        $mod = 1024;
        $units = explode(' ','B KB MB');
        for ($i = 0; $file_size > $mod; $i++) 
        {
            $file_size /= $mod;
        }
        return round($file_size, 2) . ' ' . $units[$i];
    }
}

if ( ! function_exists('delEmptyElement')) {
   function delEmptyElement(&$arr){
		$new_arr = array();
		foreach($arr as $v){
			if(!empty($v) and $v != 0){
				$new_arr[] = $v;
			}
		}
		return $new_arr;
	}
}

if ( ! function_exists('range_options')) {
   function range_options($last){
		$option_list = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		if (empty($last)){return false;}
		$last_pos = strpos($option_list, $last);
		if ($last_pos == -1) {
			return false;
		}
		return substr($option_list, 0, $last_pos+1);
	}
}

if (!function_exists('count_exam_question')) {
	function count_exam_question($question_ids)
	{
		$count=0;
		if(!empty($question_ids))
		{
			$qids=explode(',',$question_ids);
			$count=count($qids);
		}
		return $count;
	}
}

if (!function_exists('witness_format')){
	function witness_format($title){
		if (preg_match('/(.*)（(.*)）/isU', $title, $matches)){
			return $matches;
		} else {
			$data = array(1 => $title, 2 => "");
			return $data;
		}
	}
}
