<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<style type="text/css">
html, body, h1, h2, h3, h4, h5, h6, div, ol, ul, li, dl, dt, dd, p, textarea, input, select, option, form, tr, th, td, img, em, i, var, strong {
margin: 0;padding: 0;}
body{font-size:12px;font-family:"微软雅黑",Tahoma,Arial,sans-serif;}
body,html{height:100%}
h1,h2,h3,h4,h5,h6{ font-size:100%;}
em {font-style:normal}
li {list-style:none}
img {border:0;vertical-align:middle}
table {border-collapse:collapse;border-spacing:0}
p {word-wrap:break-word}
.undis {display:none}
.dis {display:block}
a,textarea,input{outline:none}
textarea {overflow:auto;resize:none;}
img {border:none;display: block;}
em,i{ font-style:normal;}
a {text-decoration:none;color:#333;}
a:hover {text-decoration:none;color:#f90;}
.layout{width:1000px;margin:0 auto;}
.layout:after,.hd:after,.bd:after,.ft:after,.cf:after,.header:after,.wrap:after,.footer:after,.fn-clear:after{content:"";display:table;clear:both}
.layout,.hd,.bd,.ft,.cf,.header,.wrap,.footer,.fn-clear{*zoom:1}
.bold{font-weight:bold;}
.org{color:#f60;}
.fl{ float:left; display:inline;}
.fr{ float:right; display:inline;}
.yh{ font-family:"微软雅黑"; font-weight:normal;}
.posr{ position:relative; *zoom:1;}
.bgf{ background:#fff;}
.mt20{ margin-top:20px}
.border1{border:1px solid lightgrey;}
.border1 input{border:0px!important;}
input::-ms-clear{display:none;}
.colorGreen{color:#2A8D6A}
.bgGreen{background:#298D6A;}
.orange{color:#fc0;}
.l50{ line-height:50px;}
.cInput{border:1px solid #999;height:38px;line-height:38px;padding:0px 6px;color:#666;font-size: 14px;}
.csInput{border:1px solid #999;height:26px;line-height: 26px;padding:0 6px;font-size:14px;color: #666;}
/*head**/
/*非三端头部开始*/
.topNav{background:#f6f6f6;height:41px;border-bottom:1px solid #ececec;line-height: 41px;font-size:14px;}
.topNav p.fl a{margin-right:10px;}
.topNav .info a{margin-left:10px;}
.topNav .info span.l{margin-left:10px;}
.topNav .info .newMassage{background:url(<?php echo $site_url; ?>application/views/static/<?php echo $static_version; ?>image/common/button/ico_newMassage.gif) no-repeat left;padding-left:20px;color:#fe9d25;}
.topNav .info .normalMassage{background:url(<?php echo $site_url; ?>application/views/static/<?php echo $static_version; ?>image/common/button/ico_newMassage.gif) no-repeat left;padding-left:20px;}
.topNav .miniBox{margin:0px 10px;}
.header .bd{padding:13px 0px;}
.header .bd h1 a{background:url(<?php echo $site_url; ?>application/views/static/<?php echo $static_version; ?>image/common/w_logo.gif) no-repeat;width:162px;height:67px;display:block; text-indent: -999em; overflow: hidden;}
.header .bd h2{border-left:1px solid #ccc;padding-left:20px;margin-left:20px;font-size:24px;color:#ff9600; font-weight: normal;}
.header .bd h2 span.channel{height:25px;}
.header .bd h2 span{display: block;font-size: 16px;color:#009b7d;margin-top:8px;}
.header .bd p{margin-top:38px;margin-left:20px;}
.header .bd p a{background: #09aa83;padding:3px 10px;color:#fff;border:1px solid #00836f;}
.header .bd .secLogo{margin-top:3px;}
.header .bd .allNumber{margin-top:45px; font-size: 14px;}
.header .bd .allNumber em{color:#ff9600;}
.header .bd .rightTips{margin-top:30px;}
.header .bd .rightTips a{background:url(<?php echo $site_url; ?>application/views/static/<?php echo $static_version; ?>image/common/button/header_button.gif) no-repeat;display: block;width: 102px;height:35px; float: left;margin-left:10px;line-height:35px; text-align: center;color:#fff;font-size:14px;}
.header .bd .rightTips a.stuChannel{background-position: 0 0}
.header .bd .rightTips a.parChannel{background-position:-102px 0}
.headerLineGreen{height:5px;overflow: hidden;background:#0f9271;}
.headerLineOrg{height:5px;overflow: hidden;background:#e54912;}
/*非三端头部结束*/
/*content**/
/*404页面样式开始*/
.noFind{margin:35px auto 35px auto;background: #f6f6f6;padding:90px 0 200px 0;}
.noFind h2{background:url(<?php echo $site_url; ?>application/views/static/<?php echo $static_version; ?>image/common/nofind_bg.gif) no-repeat left top; padding-left:150px;height: 140px;line-height: 120px;font-size: 24px;width:140px;margin:0 auto; font-weight: normal}
.noFind .bd{font-size:18px;line-height:32px; text-align: center;}
/*404页面样式结束*/
/*footer**/
/*底部开始*/
/*非三端底部样式开始*/
.footer{border-top:1px solid #d7d7d7;margin-top:20px;padding-top:20px;line-height:28px; text-align: center;color:#898989;}
.footer .hd{font-size:14px;}
.footer a{color:#898989;}
/*非三端底部样式结束*/
</style>
<title>页面不存在－梯子网</title>
</head>

<body>
<!--头部start-->
<div class="topNav">
    <div class="layout">
        <p class="info fr">
        <?php if($uname): ?>
            <a href="<?php echo $login_url; ?>logout">退出</a>
        <?php else: ?>
            <a href="<?php echo $login_url; ?>">登录</a>
        <?php endif; ?>
        </p>
        <p class="fl">
            <a href="<?php echo $tizi_url; ?>">梯子网首页</a>
            <!--
            <a class="cBtnFeedback" href="javascript:void(0);">用户反馈</a>
            -->
        </p>
    </div>
</div>
<div class="header">
    <div class="bd layout">
        <h1 class="fl">
            <a href="<?php echo $tizi_url; ?>">梯子网</a>
        </h1>
        <div class="secLogo fl">
           <h2 class="fl">
               <span class="channel"><!--教师频道--></span>
               <span class="info">中小学优质教育资源共享平台</span>
           </h2>
        </div>
        <!--
        <div class="rightTips fr">
            <a href="#" target="_blank" class="stuChannel">学生频道</a>
            <a href="#" target="_blank" class="parChannel">家长频道</a>
        </div>
        -->
    </div>
</div>
<div class="headerLineGreen"></div>
<!--头部end-->


<!--内容start-->
<div class="noFind layout">
  <h2>出错啦</h2>
  <div class="bd">
      <p>很抱歉，您访问的页面不存在，请检查您访问的网址是否正确。</p>
      <?php if($settimeout): ?>
      <p>系统将在 <span class="oTime" id="oTime">5</span> 秒后为您跳转到个人主页。</p>
      <?php else: ?>
      <p><a href="<?php echo $redirect; ?>">返回您的个人主页</a></p>
      <?php endif; ?>
  </div>
</div>
<!--内容end-->


<!--尾部start-->
<div class="footer layout">
  <div class="hd">
    <a target="_blank" href="<?php echo $tizi_url; ?>about/us">关于我们</a> | <a target="_blank" href="<?php echo $tizi_url; ?>about/school">团体帐户申请</a> | <a target="_blank" href="<?php echo $tizi_url; ?>about/contact">合作</a> | <a target="_blank" href="<?php echo $tizi_url; ?>about/join">加入我们</a>
  </div>
  <div class="bd">
  <span>&copy;2014 tizi</span>
  <span>京ICP备12050551号</span>
  <span>京公网安备11010802012731号</span>
  </div>
</div>
<!--尾部end-->

<?php if($settimeout): ?>
<script>
//倒计时跳转页面
var start = 5;
var step = -1;
function count(){
	var oTime = document.getElementById("oTime");
	oTime.innerHTML = start;
	start += step;
	if(start <1 ){
		window.location.href = '<?php echo $redirect; ?>';
	}
	setTimeout("count()",1000);
}
window.onload = count;
</script>
<?php endif; ?>
</body>
</html>
