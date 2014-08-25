define(function(require,exports){

	require('tizi_ajax');

	exports.init = function(){
		// 选择省份
		exports.selectProvince();
	};
	// 选择省份
	exports.selectProvince = function(){
		$('#cmbProvince li').each(function(){
			$(this).click(function(){
				$('#cmbProvince').removeClass('error');
				// 恢复学校为默认值
				$('#cmbCity ul').html('<li class="grey">请稍候...</li>');
				$('#schoolNameSelect ul').html('<li></li>');
				// 为当前li添加active
				$(this).addClass('active').siblings().removeClass('active');
				// 获取当前选择的省份
				var _proValue = $(this).val();
				$.tizi_ajax({
					url:loginUrlName + 'class/agents_school/get_city?province_id=' + _proValue,
					'type' : 'GET',
					'dataType' : 'jsonp',
					success : function(json, status){
						json = json.data;
						// 循环json数据结果
						var listr = '';
						for (var i = 0; i < json.length; i++){
		                    listr += '<li value="'+json[i].city_id+'">'+json[i].city_name+'</li>';
		                };
		                // 插入到select
		                $('#cmbCity ul').html(listr);
		                exports.selectCity();
					}
				});
				$('#validError').html('').removeClass('Validform_wrong');
			})
		});
	};
	// 选择市
	exports.selectCity = function(){
		$('#cmbCity li').each(function(){
			$(this).click(function(){
				$('#cmbCity').removeClass('error');
				$('#schoolNameSelect ul').html('<li class="grey">请稍候...</li>');
				$(this).addClass('active').siblings().removeClass('active');
				// 获取当前选择的城市
				var _cityValue = $(this).val();
				$.tizi_ajax({
					url:loginUrlName + 'class/agents_school/get_school?city_id=' + _cityValue,
					'type' : 'GET',
					'dataType' : 'jsonp',
					success : function(json, status){
						json = json.data;
						// 循环json数据结果
						var listr = '';
						for (var i = 0; i < json.length; i++){
		                    listr += '<li value="'+json[i].school_id+'">'+json[i].schoolname+'</li>';
		                };
		                // console.log(listr);
		                // 插入到select
		                $('#schoolNameSelect ul').html(listr);
		                exports.selectSchool();
					}
				});
				$('#validError').html('').removeClass('Validform_wrong');
			});
		});
	};
	// 选择学校
	exports.selectSchool = function(){
		$('#schoolNameSelect li').each(function(){
			$(this).click(function(){
				$('#schoolNameSelect').removeClass('error');
				$(this).addClass('active').siblings().removeClass('active');
				$('#validError').html('').removeClass('Validform_wrong');
			});

		});
	}
});