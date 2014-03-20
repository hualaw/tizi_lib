
function checkJRE() {
    try {
        if (document.getElementById('wordImageApplet').isActive()) {
            return 1;
        }
    } catch (error) {
        return 0;
    }
}
function WordImageUploader(s_url,appletUrl)
{
	if(typeof(appletUrl) === "undefined"){
        //console.log('undefined applet url')
		return;
	}
    var _this = this; 
    var sUrl = baseUrlName + 'upload/uques';
    var init = function()
    {
		// 构造函数代码 
        sUrl = s_url;
    };

    var printRequiredHtml11 = function() {
        var xx = '<div id=\"word_image_container_temp\" style=\"display:none;\"></div>';
        //var yy1 = '<div id=\"wordImageAppletWrapper\" style=\"height: 22px;background-color: #f2f1f1;border-top: 1px solid gray;position:fixed; bottom:0;left:0; width:100%; overflow: hidden;z-index:1000;\" > ';
        var yy2 = '<applet style=\"visible:hidden;\" id=\"wordImageApplet\" name=\"wordImageApplet\" code=\"com.tizi.applet.imageuploader.UploaderApplet\" archive=\"'
		+ appletUrl +'js/tools/java_applet/uploader.jar\" width=\"0\" height=\"0\"></applet>';
        var yy3 = '</div>';
		
        document.write(xx);
        //document.write(yy1);
        document.write(yy2);
        document.write(yy3);
    }
    init();
    printRequiredHtml11();

    var yy4 = '<span style="color:blue;font-size:12px;">&nbsp;&nbsp;未安装JAVA环境或JAVA运行不正常，“Word图片自动上传插件”不能运行，<a href="http://java.com/zh_CN/" target="_blank">点此下载JDK</a>。</span>';
    var cjj = checkJRE();
    if (cjj != 1) {
		$('.ckEdTip').show();
    }

    _this.uploadWordImagesFromCKEditor = function(editorInstance) {
        var cj = checkJRE();
        if (cj != 1) {
            return 0;
        }
        var ed = editorInstance;
        var txt = ed.getData();
        var txt0 = txt;
        $('#word_image_container_temp').html(txt);
        $('.ckEdTip').hide();
        var i = 0;
        $('#word_image_container_temp img').each(function() {
            var src = $(this).attr('src');
            if (src.indexOf("file:///") != -1) {
                var srct = src.replace('file:///', '');
                var serverPath = _this.uploadLocalFile(srct);
                if (serverPath != 'error') {
                    txt = txt.replace(src, serverPath);
                }else{
					i++;
				}
            }
        });
        if (txt0 != txt) {
            ed.setData(txt);
			if(i>0){
				var tipHtml = '<span style="color:#E94729;">提示：您有'+i+'张图片上传失败，请重新尝试！</span>';
				$('.ckEdTip').html(tipHtml);
				$('.ckEdTip').show().css({"padding":"5px"});
			}
        }
    }

    _this.uploadLocalFile = function(filename) {
		var token=basePageToken;
		var page_name=basePageName;
        var appletObj = document.getElementById("wordImageApplet");
        var result = appletObj.upload(sUrl,filename,page_name,token,$.cookies.get(baseSessID));
        return result;
    }

}


