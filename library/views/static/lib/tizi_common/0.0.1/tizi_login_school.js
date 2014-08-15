define(function(require,exports){
	exports.init = function(){
		// 选择省份
		exports.selectProvince();
	};
	// 选择省份
	exports.selectProvince = function(){
		$('#cmbProvince').change(function(){
			// 调用默认值
			$('#cmbCity').html('<option value="">请选择市</option>');
			$('#schoolNameSelect').html('<option value="">请选择学校</option>');
			// 获取当前选择的省份
			var _proValue = $(this).val();
			$.ajax({
				url:baseUrlName + 'class/agents_school/get_city?province_id=' + _proValue,
				'type' : 'GET',
				'dataType' : 'json',
				success : function(json, status){
					// 循环json数据结果
					var listr = '<option value="">请选择市</option>';
					for (var i = 0; i < json.length; i++){
	                    listr += '<option value="'+json[i].city_id+'">'+json[i].city_name+'</option>';
	                };
	                // 插入到select
	                $('#cmbCity').html(listr);
	                // 调用选择市
	                exports.selectCity();
				}
			})
		});
	}
	// 选择市
	exports.selectCity = function(){
		$('#cmbCity').change(function(){
			// 默认值
			$('#schoolNameSelect').html('<option value="">请选择学校</option>');
			// 获取当前选择的城市
			var _cityValue = $(this).val();
			$.ajax({
				url:baseUrlName + 'class/agents_school/get_school?city_id=' + _cityValue,
				'type' : 'GET',
				'dataType' : 'json',
				success : function(json, status){
					// 循环json数据结果
					var listr = '<option value="">请选择学校</option>';
					for (var i = 0; i < json.length; i++){
	                    listr += '<option value="'+json[i].school_id+'">'+json[i].schoolname+'</option>';
	                };
	                console.log(listr);
	                // 插入到select
	                $('#schoolNameSelect').html(listr);
				}
			})
		})
	}
});