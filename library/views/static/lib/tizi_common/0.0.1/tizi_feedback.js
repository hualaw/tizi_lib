define(function(require,exports){
    exports.feedback = function(){
    	// require('tiziDialog');
        // $.tiziDialog({
        //     id:'feedbackFormID',
        //     title:'用户反馈',
        //     // 内容html
        //     content:$('.includeFeedback').html().replace('feedbackForm1','feedbackForm'),
        //     //隐藏icon
        //     icon:null,
        //     ok:false,
        //     width: 680,
        //     height: 390,
        //     init:function(){
        //         require.async('http://wpa.b.qq.com/cgi/wpa.php?'+(new Date).valueOf(),function(){
        //             function BizQQFeedback(){
        //                 if(window.BizQQWPA!=undefined){
        //                     BizQQWPA.add({ 
        //                         aty: '0', //接入到指定工号 
        //                         type: '10', //使用按钮类型WPA
        //                         fsty:'1',
        //                         fposX:'2',
        //                         fposY:'2',
        //                         ws: 'www.tizi.com',
        //                         nameAccount: '800068391', //营销QQ号码 
        //                         parent: 'qq2' //将WPA放置在ID为testAdd的元素里 
        //                     });
        //                 }
        //             }
        //             BizQQFeedback();
        //         });
        //     },
        //     // 按钮
        //     button: [
        //         {
        //             // 按钮名字
        //             name: '确定',
        //             className:'aui_state_highlight',
        //             focus:false,
        //             // 按下后回调
        //             callback: function(){
        //                 $(".feedbackForm").submit();
        //                 return false;
        //             }         
        //         },
        //     {
        //         name: '取消'
        //     }],
        //     close:function(){
        //         //设置关于我们页面屏蔽已有的QQ
        //         var qq_tip_about = $('.qq_tip_about')
        //         qq_tip_about.length>-1?qq_tip_about.show():"";
        //     }
        // });
        //设置关于我们页面屏蔽已有的QQ
        // var qq_tip_about = $('.qq_tip_about')
        // qq_tip_about.length>-1?qq_tip_about.hide():"";

        // 页面打开加载验证码
        require('tizi_validform').changeCaptcha('feedbackBox');
        require('tizi_validform').bindChangeVerify('feedbackBox');

        //调用检测用户反馈输入
        require('tizi_valid').FeedbackCheck();
    };
    // 点击弹出反馈
    // $('.cBtnFeedback').click(function(){
    //     exports.feedback();
    // })
});