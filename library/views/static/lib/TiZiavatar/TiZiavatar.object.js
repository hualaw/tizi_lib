swfobject.addDomLoadEvent(function () {
    var webcamAvailable = false;
    var currentTab = 'upload';
    var callback = function (json) {
        switch (json.code) {
            case 1: //alert("页面成功加载了组件！");
                    break;
            case 2: //alert("已成功加载默认指定的图片到编辑面板。");
                //如果加载原图成功，说明进入了编辑面板，显示保存和取消按钮，隐藏拍照按钮
                if (json.type == 0) {
                    if(this.id == "TiZiAvatar")
                    {
                        $('#webcamPanelButton').hide();
                        $('#editorPanelButtons').show();
                    }
                }
                //否则会转到上传面板
                else {
                    //隐藏所有按钮
                    if(this.id == "TiZiAvatar")$('#editorPanelButtons,#webcamPanelButton').hide();
                }
                break;
            case 3:
                //如果摄像头已准备就绪且用户已允许使用，显示拍照按钮。
                if (json.type == 0) {
                    //alert("摄像头已准备就绪且用户已允许使用。");
                    if(this.id == "TiZiAvatar")
                    {
                        $('.button_shutter').removeClass('Disabled');
                        $('#webcamPanelButton').show();
                        webcamAvailable = true;
                    }
                }
                else {
                    if(this.id == "TiZiAvatar")
                    {
                        webcamAvailable = false;
                        $('#webcamPanelButton').hide();
                    }
                    //如果摄像头已准备就绪但用户已拒绝使用。
                    if (json.type == 1) {
                        //alert('用户拒绝使用摄像头!');
                    }
                    //如果摄像头已准备就绪但摄像头被占用。
                    else {
                        //alert('摄像头被占用!');
                    }
                }
                break;
            case 4:
                //alert("请选择小于2MB的图片文件（" + json.content + "）。");
                break;
            case 5:
                //如果上传成功
                if (json.type == 0) {
                    if(json.content.sourceUrl)
                    {
                        //alert("头像已成功保存至服务器，url为：\n" +　json.content.sourceUrl);
                    }
                    //alert("头像已成功保存至服务器，url为：\n" + json.content.avatarUrls.join("\n"));
                    //$('.button_cancel').click();
                    $('.memberInfo').find('img').removeAttr('src');
                    $('.memberInfo').find('img').attr('src',json.content.avatarUrls[0]+'?v='+(new Date).valueOf());
                    cancelClick();
                }else if (json.type == 1) {
                    //$.tiziDialog({content:json.content.msg});
                }else {
                    //$.tiziDialog({content:json.content});
                }
                break;
        }
    };
    var TiZiAvatar = new TiZiavatar('TiZiAvatar', 335, {
        id: 'TiZiAvatar',
        upload_url: baseUrlName + 'upload/avatar'
    }, callback);
    //选项卡点击事件
    $('dt').click(function () {
        if (currentTab != this.id) {
            currentTab = this.id;
            $(this).addClass('current');
            $(this).siblings().removeClass('current');
            //如果是点击“相册选取”
            if (this.id === 'albums') {
                //隐藏flash
                hideSWF();
                showAlbums();
            }
            else {
                hideAlbums();
                showSWF();
                if (this.id === 'webcam') {
                    $('#editorPanelButtons').hide();
                    if (webcamAvailable) {
                        $('.button_shutter').removeClass('Disabled');
                        $('#webcamPanelButton').show();
                    }
                }
                else {
                    //隐藏所有按钮
                    $('#editorPanelButtons,#webcamPanelButton').hide();
                }
            }
            TiZiAvatar.call('changepanel', this.id);
        }
    });
    //复选框事件
    $('#src_upload').change(function () {
        TiZiAvatar.call('srcUpload', this.checked);
    });
    //点击上传按钮的事件
    $('.button_upload').click(function () {
        TiZiAvatar.call('upload');
    });
    //点击取消按钮的事件
    $('.button_cancel').click(function () {
        cancelClick();
    });
    function cancelClick(){
        var activedTab = $('dt.current')[0].id;
        if (activedTab === 'albums') {
            hideSWF();
            showAlbums();
        }
        else {
            TiZiAvatar.call('changepanel', activedTab);
            if (activedTab === 'webcam') {
                $('#editorPanelButtons').hide();
                if (webcamAvailable) {
                    $('.button_shutter').removeClass('Disabled');
                    $('#webcamPanelButton').show();
                }
            }
            else {
                //隐藏所有按钮
                $('#editorPanelButtons,#webcamPanelButton').hide();
            }
        }
    }
    //点击拍照按钮的事件
    $('.button_shutter').click(function () {
        if (!$(this).hasClass('Disabled')) {
            $(this).addClass('Disabled');
            TiZiAvatar.call('pressShutter');
        }
    });
    //从相册中选取
    $('#userAlbums a').click(function () {
        var sourcePic = this.href;
        TiZiAvatar.call('loadPic', sourcePic);
        //隐藏相册
        hideAlbums();
        //显示flash
        showSWF();
        return false;
    });
    //隐藏flash的函数
    function hideSWF() {
        //将宽高设置为0的方式来隐藏flash，而不能使用将其display样式设置为none的方式来隐藏，否则flash将不会被加载，隐藏时储存其宽高，以便后期恢复
        $('#avatar').data({
            w: $('#avatar').width(),
            h: $('#avatar').height()
        })
    .css({
        width: '0px',
        height: '0px',
        overflow: 'hidden'
    });
        //隐藏所有按钮
        $('#editorPanelButtons,#webcamPanelButton').hide();
    }
    function showSWF() {
        $('#avatar').css({
            width: $('#avatar').data('w'),
            height: $('#avatar').data('h')
        });
    }
    //显示相册的函数
    function showAlbums() {
        $('#userAlbums').show();
    }
    //隐藏相册的函数
    function hideAlbums() {
        $('#userAlbums').hide();
    }
});