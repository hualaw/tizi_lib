<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//default redis for auto login and verify with ci
$config['redis_db']['timer'] 		= 1;//学生作业(没有redis不能进入写作业页面)
$config['redis_db']['auto_login'] 	= 2;//自动登录
$config['redis_db']['notice']		= 3;//消息通知
$config['redis_db']['session']		= 4;//SESSION
$config['redis_db']['verify'] 		= 5;//短信，邮件验证码
$config['redis_db']['download'] 	= 6;//下载计数器
$config['redis_db']['q_count'] 		= 7;//所有题目缓存，所有教材的总数缓存
$config['redis_db']['apps_oauth']	= 8;//存放手机APP的oauth验证机制内容
$config['redis_db']['intelligent'] 	= 9;//智能选题
$config['redis_db']['doc_count'] 	= 10;//文档总数缓存
$config['redis_db']['teacher_data_group'] 	= 11;//老师上传数据分组统计
$config['redis_db']['practice'] 	= 12;//做练习(没有redis不能进入练习页面)
$config['redis_db']['practice_statistics'] 	= 13;//练习统计
$config['redis_db']['tips'] 		= 14;//页面提示
$config['redis_db']['pq_count'] 	= 15;//所有题目组卷次数
$config['redis_db']['statistics'] 	= 16;//小型数据统计，hash结构，首页用户统计
$config['redis_db']['seo'] 			= 17;//SEO
$config['redis_db']['medal'] 		= 18;//勋章medal
$config['redis_db']['cloud_statistics'] = 19;//网盘统计
$config['redis_db']['qiniu_file'] = 20 ; //七牛上的文件的地址
$config['redis_db']['study_statistics'] = 21;//梯子学堂统计

// Default connection group
$config['redis_default']['host'] = '192.168.11.12';	// IP address or host
$config['redis_default']['port'] = '6379';	// Default Redis port is 6379
$config['redis_default']['password'] = 't3)TKle[q8vk\|&JsM1%!yj{(2:G0-HN';	// Can be left empty when the server does not require AUTH
$config['redis_default']['timeout'] = 0.25;

$config['redis_slave']['host'] = '192.168.11.12';
$config['redis_slave']['port'] = '6380';
$config['redis_slave']['password'] = ']X(=zB~1&B$v)rJQ<3KiZ@SB,F|k6*;0';
$config['redis_slave']['timeout'] = 0.25;

$config['redis_backup']['host'] = '192.168.11.12';
$config['redis_backup']['port'] = '6381';
$config['redis_backup']['password'] = ']X(=zB~1&B$v)rJQ<3KiZ@SB,F|k6*;0';
$config['redis_backup']['timeout'] = 0.25;

/* End of file redis.php */
/* Location: ./application/config/redis.php */
