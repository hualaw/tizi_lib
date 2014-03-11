define(function(require, exports) {
	//错误信息弹框
	exports.errormsg = function(){
		var errormsg = $('#errormsg').html();
		if(errormsg){
			require('tiziDialog');
			$.tiziDialog({content:errormsg})
		}	
	}
});