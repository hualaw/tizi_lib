define(function(require,exports){
	//反馈提交表单结果
	exports.feedbacksubmit = function(data){
		if(data.errorcode){
			$.tiziDialog.list['feedbackFormID'].close();
			// art.dialog.list['feedbackFormID'].close();
			$.tiziDialog({
				content:data.error,
				time:3,
				lock:true,
				icon:'succeed'
			})
		}else{
			$.tiziDialog({
				content:data.error,
				time:3,
				lock:true,
				icon:'error'
			})
		}	
	}
});