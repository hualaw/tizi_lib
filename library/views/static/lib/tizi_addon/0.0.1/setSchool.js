define(function(require, exports) {
    //设置学校
    $('#resetSchool').live('click',function(){
        var _this = $(this);
        $.ajax({
            'url' : baseUrlName + 'class/area?id=1',
            'type' : 'GET',
            'dataType' : 'json',
            success : function(json, status){
                var listr = '';
                for (var i = 0; i < json.length; ++i){
                    listr += '<li data-id="'+json[i].id+'" ismunicipality="'+json[i].ismunicipality+'">'+json[i].name+'</li>';
                };
                $('.province').html(listr);
                $('.province').fadeIn();
            }
        });
        $.tiziDialog({
            id:"setSchollID",
            title:'选择学校',
            top:100,
            content:$('#resetSchoolPop').html().replace('resetSchoolPopCon_beta', 'resetSchoolPopCon'),
            icon:null,
            width:800,
            ok:function(){
                if($('.school li.active').length < 1){
                    return;
                };
                $(".theGenusScholl_n").addClass("undis");
                $(".theGenusScholl_y").removeClass("undis");
                var class_id = $('#class_id').val();
                var school_id = $('.aui_content .school li.active').attr('data-id');
                var fullname = '';
                var province = $(".schoolProvice li.active").html();
                var city = $(".schoolCity li.active").html();
                var county = $(".schoolCounty li.active").html();
                var schoolname = $(".schoolName li.active").html();
                if (typeof city == 'undefined'){city = '';}
                fullname = province + city + county + schoolname;
                // 判断是否是重设学校
                if(_this.hasClass('resetSchool')){
                     $(".theGenusScholl_n").add("undis");                        
                     $("#school").html(fullname);
                      $('#resetSchool').text('重设学校');
                    $("#schoolVal").val(school_id);
                }else{
                    $("#schoolVal").val(school_id);
                    $("#school").html(fullname);
                    $('#resetSchool').text('重设学校');
                    if($("#schoolVal").val() > 0){
                        $('.schoolBox').find('.ValidformInfo,.Validform_checktip').hide();
                    }
                }
            },
            cancel:true,
            close:function(){
                $('.city,.county,.sctype,.schoolInfo').hide();
            }
        });
        // 显示学校地区、学校等
        exports.showSchool();
        // 验证表单
        exports.seacherSchoolValid();
        // 搜索结果方法
        exports.seacheSchoolResult();
        // 加载没有我的学校方法
        exports.noMyScholl();
    });
    // 搜索结果方法
    exports.seacheSchoolResult = function(){
        // 点击reset清空输入框内容和恢复学校内容
        $('span.reset').click(function(){
            $('.schoolNames').val('');
            $('.seacherResult').hide();
            $('.school').fadeIn();
            $('span.reset').addClass('undis');
        })
    }
    // 选择学校搜索学校名称验证
    exports.seacherSchoolValid = function(){
        var _Form = $(".seacherSchoolForm").Validform({
            // 自定义tips在输入框上面显示
            tiptype: function(msg, o, cssctl) {
                if (!o.obj.is("form")) {
                    var objtip = o.obj.next().find(".Validform_checktip");
                    objtip.text(msg);
                    o.obj.next().show();
                    var objtip = o.obj.next().find(".Validform_checktip");
                    objtip.text(msg);
                    var infoObj = o.obj.next(".ValidformTips");
                    // 判断验证成功
                    if (o.type == 2) {
                        infoObj.show();
                        o.obj.next().hide();
                    }
                }
            },
            showAllError: false,
            ajaxPost: true,
            callback: function(data){    
                $('.school').hide();
                $('.seacherResult').fadeIn();
                $('span.reset').removeClass('undis');
            }
        });
        _Form.addRule([{
            ele:".schoolNames",
            datatype:"*",
            nullmsg:"请输入学校名称",
            errormsg:"学校名称输入错误"
            }
        ]);
    };
    //取出学校第一个字母
    exports.buildSchool = function(query) {
        $('.schoolInfo').fadeIn();
        var listr = "";
        $.each(school_array, function(k, v) {
            var total = 0, asort = "<dl class='cf'><dt class='fl'>" + k.toUpperCase() + "</dt><dd class='fr'><ul>";
            $.each(v, function(k2, v2) {
                if (typeof query != "undefined" && $.trim(query) != "") {
                    if (v2.schoolname.indexOf(query) != -1 || v2.py.indexOf(query) != -1 || v2.first_py.indexOf(query) != -1) listr += '<li data-id="' + v2.id + '" title="' + v2.schoolname + '">' + v2.schoolname + "</li>";
                } else asort += '<li data-id="' + v2.id + '" title="' + v2.schoolname + '">' + v2.schoolname + "</li>", total++;
            }), asort += '</ul></dd></dl>', total > 0 && (listr += asort);
        }), typeof query != "undefined" && $.trim(query) != "" ? $(".school").html("<dl>" + listr + "</dl>") : $(".school").html(listr), $(".school").css("display", "block");
    };
    // 显示学校地区、学校等
    exports.showSchool = function(){
        //点击省份
        $('.province li').live('click', function(){
            $('.sctype').hide();
            var _cityName = 
                // 北京
                $(this).attr('data-id') == 2 || 
                // 上海
                $(this).attr('data-id') == 25 || 
                // 天津
                $(this).attr('data-id') == 27 || 
                // 重庆
                $(this).attr('data-id') == 32 || 
                // 香港
                $(this).attr('data-id') == 33 || 
                // 澳门
                $(this).attr('data-id') == 34 || 
                // 台湾
                $(this).attr('data-id') == 35;
            if(_cityName){
                $('.city,.sctype,.schoolInfo').hide();
            }else{
                $('.county,.schoolInfo').hide();
            };
            if($(this).attr('class') !== 'active'){
                var id = $(this).attr('data-id');
                var ismunicipality = $(this).attr('ismunicipality');
                $.ajax({
                    'url' : baseUrlName + 'class/area?id='+id,
                    'type' : 'GET',
                    'dataType' : 'json',
                    success : function(json, status){
                        var listr = '';
                        for (var i = 0; i < json.length; ++i){
                            listr += '<li data-id="'+json[i].id+'">'+json[i].name+'</li>';
                        }
                        if (ismunicipality == 1){
                            $('.county').html(listr);
                            $('.county').fadeIn();
                        } else {
                            $('.city').html(listr);
                            $('.city').fadeIn();
                        }
                    }
                });
            }
        });
        //点击城市
        $('.city li').live('click', function(){
            $('.sctype,.schoolInfo').hide();
            if($(this).attr('class') !== 'active'){
                var id = $(this).attr('data-id');
                $.ajax({
                    'url' : baseUrlName + 'class/area?id='+id,
                    'type' : 'GET',
                    'dataType' : 'json',
                    success : function(json, status){
                        var listr = '';
                        for (var i = 0; i < json.length; ++i){
                            listr += '<li data-id="'+json[i].id+'">'+json[i].name+'</li>';
                        }
                        $('.county').html(listr);
                        $('.county').css('display', 'block');
                    }
                });
            }
        });
        //点击城镇
        $('.county li').live('click', function(){
            $('.sctype,.schoolInfo').hide();
            $.ajax({
                'url' : baseUrlName + 'class/area/sctype',
                'type' : 'GET',
                'dataType' : 'json',
                success : function(json, status){
                    var listr = '';
                    for (var i = 0; i < json.length; ++i){
                        listr += '<li data-id="'+json[i].id+'">'+json[i].name+'</li>';
                    }
                    $('.sctype').html(listr);
                    $('.sctype').fadeIn();
                }
            });
        });
        //点击学校
        $('.sctype li').live('click', function(){
            $('.schoolInfo .hd').show();
            $('.schoolInfo .bd').hide();
            if($(this).attr('class') !== 'active'){
                var sctype = $(this).attr('data-id');
                var county_id = $('.aui_content .county li.active').attr('data-id');
                $.tizi_ajax({
                    'url' : baseUrlName+'class/schools/county_sch',
                    'type' : 'POST',
                    'dataType' : 'json',
                    'data' : {
                        'id' : county_id,
                        'sctype' : sctype
                    },
                    success : function(json, status){
                        school_array = json.data;
                        exports.buildSchool();
                    }
                });
            };
        });
        //设置学校点击地区效果
        $('.schooLocation li').live('click', function(){
            $(this).addClass('active').siblings().removeClass('active');
        }); 
        //设置学校点击学校效果
        $('.school li').live('click', function(){
            $('.school li').removeClass('active');
            $(this).addClass('active');
        });
        //点击搜索结果的li
        $('.seacherResult li').live('click', function(){
            $('.seacherResult li').removeClass('active');
            $(this).addClass('active');
        });  
    }
    // 点击没有我的学校
    exports.noMyScholl = function(){
        // 点击没有我的学校
        $(".noMySchollBtn").live("click",function(){
            $('.schoolInfo .hd,.schoolInfo .seacherResult').hide();
            $('.school').hide();
            $('.schoolInfo .bd').fadeIn();
        });
        // 点击返回选择学校
        $(".returnSetSchool").live("click",function(){
            $('.schoolInfo .bd').hide();
            $('.schoolInfo .hd').fadeIn();
            $('.school').fadeIn();
        });
    }
});