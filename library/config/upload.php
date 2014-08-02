<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
	用于阿里云oss的常量数据
 */
$config['oss_access_id'] = 'rtFNKZ1oDRMFzTBo';
$config['oss_access_key'] = 'Bu64WbqJ1k4a4LDrqHQMSdGUJCl4Wc';
$config['oss_domain'] = 'oss-internal.aliyuncs.com';		//如果web服务器在阿里云上请改为oss-internal.aliyuncs.com

//aq
$config['bucket_aq'] = 'tizi-dayi-thumb';		//答疑存放图片的bucket
$config['domain_aq'] = 'http://tizi-dayi-thumb.oss.aliyuncs.com/';	//答疑图片的访问地址
$config['exts_aq'] = 'jpg,gif,bmp,png,jpeg';				//答疑图片允许上传的类型
$config['size_aq'] = 1024 * 1024 * 2;					//单个文件最大的文件大小
$config['max_width_aq'] = 770;						//单个文件最大的宽度
$config['max_height_aq'] = 770;					//单个文件最大的高度
$config['oauth_firewall'] = array('max_visit' => 1800, 'atime' => 600);
$config['ip_firewall'] = array('max_visit' => 100, 'atime' => 86400);

//feedback
$config['bucket_feedback'] = 'tizi';       //暂时存在答疑存放图片的bucket
$config['domain_feedback'] = 'http://tizi.oss.aliyuncs.com/';  //暂时用答疑图片的访问地址
$config['exts_feedback'] = 'jpg,gif,bmp,png,jpeg';                //图片允许上传的类型
$config['size_feedback'] = 1024 * 1024 * 2;                   //单个文件最大的文件大小
$config['max_width_feedback'] = 770;                      //单个文件最大的宽度
$config['max_height_feedback'] = 770;                 //单个文件最大的高度

//doc
$config['bucket_document'] = 'tizi-zujuan-thumb';
$config['domain_document'] = 'tizi-zujuan-thumb.oss.aliyuncs.com/';	//
$config['exts_files']='doc,docx,ppt,pptx,xls,xlsx,wps,et,dps,xls,pdf,xlsx,txt';
$config['size_files'] = 1024*1024*20;
$config['oauth_firewall'] = array('max_visit' => 1800, 'atime' => 600);
$config['ip_firewall'] = array('max_visit' => 100, 'atime' => 86400);

//cloud 
$config['cloud_one_file_size'] =  209715200;//200*1024*1024;//单个文件的大小不能超过200M

//avatar
$config['bucket_avatar'] = 'tizi';
$config['bucket_space_avatar'] = 'tizi';
$config['domain_avatar'] = 'http://tizi.oss.aliyuncs.com/';
$config['exts_avatar'] = 'jpg,gif,png,jpeg';//图片允许上传的类型
$config['exts_space_avatar'] = 'jpg,gif,png,jpeg';//图片允许上传的类型
$config['size_avatar'] = 1024 * 1024 * 2;//单个文件最大的文件大小
$config['size_space_avatar'] = 1024 * 1024 * 2;//单个文件最大的文件大小
$config['max_width_avatar'] = 180;//单个文件最大的宽度
$config['max_height_avatar'] = 180;//单个文件最大的高度
$config['folder_avatar'] = 'avatar/';
$config['prefix_avatar'] = 'tizi__';
$config['num_pf_avatar'] = 10000;
//avatar-----space
$config['max_width_space_avatar'] = 180;//单个文件最大的宽度
$config['max_height_space_avatar'] = 180;//单个文件最大的高度
$config['space_folder_avatar'] = 'space_avatar/';
$config['space_prefix_avatar'] = 'tizi__';
$config['space_num_pf_avatar'] = 10000;

//edu
$config['bucket_edu'] = 'tizi';
$config['domain_edu'] = 'http://tizi.oss.aliyuncs.com/';
$config['exts_edu'] = 'jpg,gif,png,jpeg';//图片允许上传的类型
$config['size_edu'] = 1024 * 1024 * 2;//单个文件最大的文件大小
$config['max_width_edu'] = 2000;//单个文件最大的宽度
$config['max_height_edu'] = 2000;//单个文件最大的高度
$config['prefix_edu'] = 'tizi__';
$config['num_pf_edu'] = 10000;

/* End of file cms_ques.php */
/* Location: ./application/config/cms_ques.php */
