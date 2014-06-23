define(function(require,exports){
	// 公共发送手机验证码--电话号码验证码发送
	var sms = {
		init:function(phone,code_type,element){
			var _this = this;
			$('.aSendCaptap').removeAttr("disabled");
			$('.aSendCaptap').live("click",function(){
				if($(".forPhoneHidden").length!=0){
            		$(".forPhoneHidden").show();
				}
				if(element) phone = $(element).val();
				require.async("tizi_validform",function(ex){
                    ex.sendPhoneCode(phone,code_type,function(data){
							//调用倒计时
							_this.telWaitTime(phone,code_type);
							$(".sendTelCaptap .Validform_checktip").removeClass('Validform_wrong').addClass('Validform_right').html(data.error);
							$('.aSendCaptap').addClass('aSendCaptapOk').attr("disabled",true);
						},function(data){
							$(".sendTelCaptap .Validform_checktip").removeClass('Validform_right').addClass('Validform_wrong').html(data.error);
						});
						
					});
                });	
		},
		// 倒计时90秒
		telWaitTime:function(phone,code_type){
			var wait=90;
			var timer = setInterval(function(){
				wait--;
				if(wait<0){
					clearInterval(timer);
					$('.aSendCaptap').removeClass('aSendCaptapOk').removeAttr("disabled").val('发送验证码');
					$('.aSendCaptap').next('span').fadeOut();
				}else{
					$('.aSendCaptap').val(wait + '秒后重新发送');	
				}
			}, 1000);
		}
	};
	exports.sms = sms;
	// 信箱找回验证码发送
	var email = {
		init:function(email,code_type,element){
			var _this = this;
			$('.aSendEmail').removeAttr("disabled");
			$('.aSendEmail').live("click", function(){
				// $(this).parent().parent().parent().siblings('dd').removeClass('activeDiv');
				if(element) email = $(element).val();
				require.async("tizi_validform",function(ex){
                    ex.sendEmailCode(email,code_type,function(){
					_this.emailWaitTime(email,code_type);
					$('.aSendEmail').addClass('aSendCaptapOk').attr("disabled",true);
				},function(data){
					$(".aSendEmailTip").html(data.error).addClass("error").removeClass("undis");
				});
                });	
				// Common.comValidform.sendEmailCode(email,code_type,function(){
				// 	_this.emailWaitTime(email,code_type);
				// 	$(this).addClass('aSendCaptapOk');
				// 	$('.aSendEmail').attr("disabled",true);
				// },function(data){
				// 	$(".aSendEmailTip").html(data.error).addClass("error").removeClass("undis");
				// });


			})
		},
		// 倒计时90秒
		emailWaitTime:function(email,code_type){
			var wait=90;
			var timer = setInterval(function(){
				wait--;
				if(wait<0){
					clearInterval(timer);
					$('.aSendEmail').removeClass('aSendEmailOk').removeAttr("disabled").val('重新发送');
					$('.aSendEmail').next('span').fadeOut();
				}else{
					$('.aSendEmail').val(wait + '秒后重新发送');	
				}
			}, 1000);	
		}
	};
	exports.email = email;
})