!function ($) {
    $(function(){
        ///////////////////////////////////////日期相关方法///////////////////////////////////////
        // 格式化日期
        // 对Date的扩展，将 Date 转化为指定格式的String
        // 月(M)、日(d)、小时(h)、分(m)、秒(s)、季度(q) 可以用 1-2 个占位符，
        // 年(y)可以用 1-4 个占位符，毫秒(S)只能用 1 个占位符(是 1-3 位的数字)
        // 例子：
        // new Date().format("yyyy-MM-dd hh:mm:ss.S") ==> 2006-07-02 08:09:04.423
        // new Date().format("yyyy-M-d h:m:s.S")      ==> 2006-7-2 8:9:4.18
        // author: meizz
        // endDate已经被注释掉了,endDate是限制时间控件的显示时间
        Date.prototype.format = function(format){
            var o = { 
                    "M+" : this.getMonth()+1, //month 
                    "d+" : this.getDate(),    //day 
                    "h+" : this.getHours(),   //hour 
                    "m+" : this.getMinutes(), //minute 
                    "s+" : this.getSeconds(), //second 
                    "q+" : Math.floor((this.getMonth()+3)/3),  //quarter 
                    "S" : this.getMilliseconds() //millisecond 
            } 
            if(/(y+)/.test(format)) format=format.replace(RegExp.$1, 
                    (this.getFullYear()+"").substr(4 - RegExp.$1.length)); 
            for(var k in o)if(new RegExp("("+ k +")").test(format)) 
                format = format.replace(RegExp.$1, 
                        RegExp.$1.length==1 ? o[k] : 
                            ("00"+ o[k]).substr((""+ o[k]).length)); 
            return format; 
        }
        
        //var now = new Date(); now.addDays(1); alert(now.Format("yyyy-MM-dd"));
        Date.prototype.addDays = function(d)
        {
            this.setDate(this.getDate() + d);
        };
        
        Date.prototype.addWeeks = function(w)
        {
            this.addDays(w * 7);
        };
        
        Date.prototype.addMonths= function(m)
        {
            var d = this.getDate();
            this.setMonth(this.getMonth() + m);
            if (this.getDate() < d)
                this.setDate(0);
        };
        
        Date.prototype.addYears = function(y)
        {
            var m = this.getMonth();
            this.setFullYear(this.getFullYear() + y);
            if (m < this.getMonth())
             {
                this.setDate(0);
             }
        };
        ///////////////////////////////////////日期相关方法 END///////////////////////////////////////
        
        // 格式化数字
        $.extend({
            format : function(str, step, splitor) {
                step = 3;
                splitor = ',';
                str = str.toString();
                var len = str.length;
                if(len > step) {
                     var l1 = len%step, 
                         l2 = parseInt(len/step),
                         arr = [],
                         first = str.substr(0, l1);
                     if(first != '') {
                         arr.push(first);
                     };
                     for(var i=0; i<l2 ; i++) {
                         arr.push(str.substr(l1 + i*step, step));
                     };
                     str = arr.join(splitor);
                 };
                 return str;
            }
        });
        
        // 鼠标移动到帮助信息上显示浮层
        $('.overview').tooltip({
            selector: "a[rel=tooltip]"
        })
        $('.detail').tooltip({
            selector: "span[rel=tooltip]"
        })
        
        //日期计算
        function GetDateStr(AddDayCount)
        {
            var dd = new Date();
            dd.setDate(dd.getDate()+AddDayCount);//获取AddDayCount天后的日期
            var y = dd.getFullYear();
            var m = dd.getMonth()+1;//获取当前月份的日期
            var d = dd.getDate();
            return y+"-"+m+"-"+d;
        }
        var dateLimit = GetDateStr(0);//-1，日期选择限制为当天，因为某些报表当天也会出数据
        var nowTemp = new Date();
        var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);
        var weekLimit = GetDateStr(0);//GetDateStr(-(nowTemp.getDay()));周数选择限制为当周，因为某些报表当周也会出数据
        var monthLimitTmp = GetDateStr(0).split("-");//GetDateStr(-(nowTemp.getDate())).split("-");月份选择限制为当月，因为某些报表当月也会出数据
        var monthLimit = monthLimitTmp[0]+"-"+monthLimitTmp[1];
        
        //切换日期
        var date_picker = $('[id^=date_day_]').datepicker({
            format: "yyyy-mm-dd",
            autoclose: true,
            //endDate: dateLimit
        }).on('changeDate', function(ev) {
            date_picker.hide();
        }).data('datepicker');
        
        //切换周数
        var week_picker = $('[id^=calendar_week_]').datepicker({
            language: "zh-CN",
            calendarWeeks: true,
            autoclose: true,
            //endDate: weekLimit
        }).on('changeDate', function(ev) {
            var today = new Date();
            var time = ev.date.valueOf();
            if (today.valueOf() > time+86400000){
                var viewDate = UTCDate(ev.date.getFullYear(), ev.date.getMonth(), ev.date.getDate()),
                    // Start of select week: based on weekstart/select date
                    weekstart = 1,
                    ws = new Date(+viewDate + (weekstart - viewDate.getUTCDay() - 7) % 7 * 864e5),
                    // Thursday of this week
                    th = new Date(+ws + (7 + 4 - ws.getUTCDay()) % 7 * 864e5),
                    // First Thursday of year, year from thursday
                    yth = new Date(+(yth = UTCDate(th.getUTCFullYear(), 0, 1)) + (7 + 4 - yth.getUTCDay())%7*864e5),
                    // Calendar week: ms between thursdays, div ms per day, div 7 days
                    calWeek =  (th - yth) / 864e5 / 7 + 1;
                var year = yth.getFullYear();
                
                if(calWeek < 10) {
                    var param_value = year+"0"+calWeek;
                } else {
                    var param_value = year+""+calWeek;
                }
                
                var thfmt = th.format("yyyy-MM-dd");
                var mon = new Date(thfmt);
                var sun = new Date(thfmt);
                mon.addDays(-3);
                sun.addDays(3);
                var value = mon.format("yyyyMMdd")+"~"+sun.format("yyyyMMdd");
                
                var id = $(this).attr('id');    //获取当前对象id属性 
                var arr = id.split("_");
                if(arr.length == 3) {
                    $("#date_week_"+arr[2]).val(param_value);
                }
                if(arr.length == 4) {
                    $("#date_week_"+arr[2]+"_"+arr[3]).val(param_value);
                }
                $(this).html(value+" <b class=\"caret\"></b>");
            }
        });
        
        //默认周起始日期为周一，选中当周所有日期
        $('[id^=calendar_week_]').click(function(){
            $(".day.active").siblings(".day").addClass("active");
        });
        
        // 切换月份
        var month_picker = $('[id^=date_month_]').datepicker({
            format: "yyyy-mm",
            minViewMode: 1,
            autoclose: true,
            //endDate: monthLimit
        }).on('changeDate', function(ev) {
            month_picker.hide();
        }).data('datepicker');
        
        //公告中的日期
        var dp1 = $('#dp1').datepicker({
            format: "yyyy-mm-dd",
            autoclose: true,
            //endDate: dateLimit
        }).on('changeDate', function(ev) {
            dp1.hide();
        }).data('datepicker');
        
        var dp2 = $('#dp2').datepicker({
            format: "yyyy-mm-dd",
            autoclose: true,
            //endDate: dateLimit
        }).on('changeDate', function(ev) {
            dp2.hide();
        }).data('datepicker');
        
        //折叠菜单
        $('.sidebar-nav-header').click(function(){
            if($(this).parent().hasClass('active')){
                $(this).parent().removeClass('active');
                $('.sidebar-nav .nav>.nav').css({"display":"none"});
            } else {
                $('.sidebar-nav .nav>li').removeClass('active');
                $(this).parent().addClass('active');
                $('.sidebar-nav .nav>.nav').css({"display":"inline"});
            }
        });
        $('.sidebar-nav .nav .active').parent().parent().addClass('active');
        
        /////////城市提示框/////////
        var city_initials = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('initials'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            prefetch: {
                url: '/ajax/ajaxcitiesautocomplete',
                cache: false
            }
        });

        var city_names = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            prefetch: {
                url: '/ajax/ajaxcitiesautocomplete',
                cache: false
            }
        });
        
        //报表查看 - 城市自动补全
        $("[id^=text_city_]").typeahead({
                highlight: true
            }, 
            {
                name: 'city-initials',
                display: 'name',
                source: city_initials,
                templates: {
                    //header: '<h3 class="league-name">缩写查询</h3>',
                    suggestion: Handlebars.compile('<div>"{{name}}"[{{initials}}]</div>')
                },
            },
            {
                name: 'city-names',
                display: 'name',
                source: city_names,
                templates: {
                    //header: '<h3 class="league-name">名称查询</h3>',
                    suggestion: Handlebars.compile('<div>"{{name}}"[{{initials}}]</div>')
                }
            }
        );
        
        //用户管理 - 城市自动补全
        $("#city_name").typeahead({
                highlight: true
            }, 
            {
                name: 'city-initials',
                display: 'name',
                source: city_initials,
                templates: {
                    //header: '<h3 class="league-name">缩写查询</h3>',
                    suggestion: Handlebars.compile('<div>"{{name}}"[{{initials}}]</div>')
                },
            },
            {
                name: 'city-names',
                display: 'name',
                source: city_names,
                templates: {
                    //header: '<h3 class="league-name">名称查询</h3>',
                    suggestion: Handlebars.compile('<div>"{{name}}"[{{initials}}]</div>')
                }
            }
        ).bind('typeahead:select', function(ev, suggestion) {
            $('#tmp-city').attr('data-id',suggestion.id);
            $('#city_name').val(suggestion.name);
            $('#city_id').val(suggestion.id);
            $('#tmp-city').attr('data-name',suggestion.name);
        });
        
        //jquery浮动层，浮动侧栏
        $(document).ready(function(){
            $(window).scroll(function (){
                var offsetTop = $(window).scrollTop();    // + 75 +"px"
                $("#layout").animate({top : offsetTop },{ duration:250 , queue:false });    //duration为跟随时间间隔
            });
        });
        
        //页面右下侧，提供按钮，“返回顶部”和“前往底部”
        $('#go-top').click(function(){$('html,body').animate({scrollTop: '0px'}, 500);return false;});
        $('#go-bottom').click(function(){$('html,body').animate({scrollTop: document.body.scrollHeight},500);return false;});
        
        //菜单权限列表折叠
        $(".menu-tree-collapse").click(function(){
            if($(this).siblings("ul").hasClass("tab")==true) {
                menu=$(this).html();
                if($(this).parent().hasClass("active")){
                    $(this).parent().removeClass("active");
                    $(this).siblings("ul").css({"display":"none"});
                    if(menu.substr(0,3)==" - ")
                        $(this).html(" + "+menu.substr(3));
                    else
                        $(this).html(" + "+menu);
                } else {
                    $(this).parent().addClass("active");
                    $(this).siblings("ul").css({"display":"block"});
                    if(menu.substr(0,3)==" + ")
                        $(this).html(" - "+menu.substr(3));
                    else
                        $(this).html(" - "+menu);
                }
            }
        });
        $(".menu-tree-collapse").parent().addClass("active");
        
        //明细汇总整合联动列表
        window.sum_detail_list = ["area-city-sum-detail","area-branch-sum-detail"];
        
        
        
        
        
        
        ////////////////////////////////////////////////////////////////////////////////////////////////
        
        // 切换日期
        $('#change_date').datepicker({
            format: "yyyy-mm-dd",
            autoclose: true,
            //endDate: dateLimit
        }).on('changeDate', function(ev){
                var today = new Date();
                var time = ev.date.valueOf();
                if (today.valueOf() > time+86400000){
                    var date = new Date(time).format("yyyy-MM-dd");
                    var current_url = window.location.href;
                    var reg = /date=\d{4}(\-|\/|\.)\d{1,2}\1\d{1,2}/;
                    var matches = reg.exec(current_url);
                    if(!matches)
                    {
                        jump_url = current_url+'\?date='+date;
                    }
                    else
                    {
                        jump_url = current_url.replace(reg,'date='+date);
                    }
                    window.location.href=jump_url;
                }
        });
        // 切换周
        function UTCDate(){
            return new Date(Date.UTC.apply(Date, arguments));
        }
        
        $('#change_week').datepicker({
            language: "zh-CN",
            format: "yyyy-mm-dd",
            //weekStart: 1,
            calendarWeeks: true,
            autoclose: true,
            //endDate: weekLimit
        }).on('changeDate', function(ev){
                var today = new Date();
                var time = ev.date.valueOf();
                if (today.valueOf() > time+86400000){
                    var viewDate = UTCDate(ev.date.getFullYear(), ev.date.getMonth(), ev.date.getDate()),
                        // Start of select week: based on weekstart/select date
                        weekstart = 1,
                        ws = new Date(+viewDate + (weekstart - viewDate.getUTCDay() - 7) % 7 * 864e5),
                        // Thursday of this week
                        th = new Date(+ws + (7 + 4 - ws.getUTCDay()) % 7 * 864e5),
                        // First Thursday of year, year from thursday
                        yth = new Date(+(yth = UTCDate(th.getUTCFullYear(), 0, 1)) + (7 + 4 - yth.getUTCDay())%7*864e5),
                        // Calendar week: ms between thursdays, div ms per day, div 7 days
                        calWeek =  (th - yth) / 864e5 / 7 + 1,
                        // End of this week
                        we = new Date(+ws + (7 + 7 - ws.getUTCDay()) % 7 * 864e5);
                    
                    var date_start = ws.format("yyyyMMdd");
                    var date_end = we.format("yyyyMMdd");
                    
                    //var date = new Date(time).format("yyyy-MM-dd");
                    var current_url = window.location.href;
                    var reg = /week_end=\d{8}-\d{8}/;
                    var matches = reg.exec(current_url);
                    if(!matches)
                    {
                        jump_url = current_url+'\?week_end='+date_start+'-'+date_end;
                    }
                    else
                    {
                        jump_url = current_url.replace(reg,'week_end='+date_start+'-'+date_end);
                    }
                    window.location.href=jump_url;
                }
        });
        //默认周起始日期为周一，选中当周所有日期
        $("#change_week").click(function(){
            $(".day.active").siblings(".day").addClass("active");;
        });
        // 切换月份
        $('#change_month').datepicker({
            format: "yyyy-mm",
            minViewMode: 1,
            autoclose: true,
            //endDate: monthLimit
        }).on('changeDate', function(ev){
                var today = new Date();
                var time = ev.date.valueOf();
                if (today.valueOf() > time+86400000){
                    var date = new Date(time).format("yyyy-MM");
                    var current_url = window.location.href;
                    var reg = /date=\d{4}(\-|\/|\.)\d{1,2}/;
                    var matches = reg.exec(current_url);
                    if(!matches)
                    {
                        jump_url = current_url+'\?date='+date;
                    }
                    else
                    {
                        jump_url = current_url.replace(reg,'date='+date);
                    }
                    window.location.href=jump_url;
                }
        });
        
        var start_picker = $('#date_start').datepicker({
            format: "yyyy-mm-dd",
            autoclose: true,
            //endDate: dateLimit
        }).on('changeDate', function(ev) {
            start_picker.hide();
        }).data('datepicker');
        
        var end_picker = $('#date_end').datepicker({
            format: "yyyy-mm-dd",
            autoclose: true,
            //endDate: dateLimit
        }).on('changeDate', function(ev) {
            end_picker.hide();
        }).data('datepicker');
        
        // 页面导航
        var $win = $(window)
            , $nav = $('.subnav')
            , navTop = 40
            , isFixed = 0
        $win.on('scroll', processScroll)

        function processScroll()
        {
            var i, scrollTop = $win.scrollTop()
            if (scrollTop >= navTop && !isFixed) {
                isFixed = 1
                $nav.addClass('subnav-fixed')
                $nav.css('display','')
            } else if (scrollTop <= navTop && isFixed) {
                isFixed = 0
                $nav.removeClass('subnav-fixed')
                $nav.css('display','none')
            }
        }
        
        //周区间选择
        $('#week_start_calendar').datepicker({
            language: "zh-CN",
            format: "yyyy-mm-dd",
            //weekStart: 1,
            calendarWeeks: true,
            autoclose: true,
            //endDate: weekLimit
        }).on('changeDate', function(ev) {
            $("#handle").val("next");
            var today = new Date();
            var time = ev.date.valueOf();
            if (today.valueOf() > time+86400000){
                var viewDate = UTCDate(ev.date.getFullYear(), ev.date.getMonth(), ev.date.getDate()),
                    // Start of select week: based on weekstart/select date
                    weekstart = 1,
                    ws = new Date(+viewDate + (weekstart - viewDate.getUTCDay() - 7) % 7 * 864e5),
                    // End of this week
                    we = new Date(+ws + (7 + 7 - ws.getUTCDay()) % 7 * 864e5);
                
                var date_start = ws.format("yyyyMMdd");
                var date_end = we.format("yyyyMMdd");
                
                $("#week_start").val(date_start+"-"+date_end);
                $('#week_start_calendar').attr('data-date',ev.date.format("yyyy-MM-dd"));
                $('#week_start_calendar').html(date_start+"-"+date_end+" <b class=\"caret\"></b>");
            }
        });
        //默认周起始日期为周一，选中当周所有日期
        $("#week_start_calendar").click(function(){
            $(".day.active").siblings(".day").addClass("active");
        });
        $('#week_end_calendar').datepicker({
            language: "zh-CN",
            format: "yyyy-mm-dd",
            //weekStart: 1,
            calendarWeeks: true,
            autoclose: true,
            //endDate: weekLimit
        }).on('changeDate', function(ev) {
            $("#handle").val("prev");
            var today = new Date();
            var time = ev.date.valueOf();
            if (today.valueOf() > time+86400000){
                var viewDate = UTCDate(ev.date.getFullYear(), ev.date.getMonth(), ev.date.getDate()),
                    // Start of select week: based on weekstart/select date
                    weekstart = 1,
                    ws = new Date(+viewDate + (weekstart - viewDate.getUTCDay() - 7) % 7 * 864e5),
                    // End of this week
                    we = new Date(+ws + (7 + 7 - ws.getUTCDay()) % 7 * 864e5);
                
                var date_start = ws.format("yyyyMMdd");
                var date_end = we.format("yyyyMMdd");
                
                $("#week_end").val(date_start+"-"+date_end);
                $('#week_end_calendar').attr('data-date',ev.date.format("yyyy-MM-dd"));
                $('#week_end_calendar').html(date_start+"-"+date_end+" <b class=\"caret\"></b>");
            }
        });
        $("#week_end_calendar").click(function(){
            $(".day.active").siblings(".day").addClass("active");;
        });
        
        // 切换月份
        $('#month').datepicker({
            format: "yyyy-mm",
            minViewMode: 1,
            autoclose: true,
            //endDate: monthLimit
        });
        $('#start_month').datepicker({
            format: "yyyy-mm",
            minViewMode: 1,
            autoclose: true,
            //endDate: monthLimit
        });
        $('#end_month').datepicker({
            format: "yyyy-mm",
            minViewMode: 1,
            autoclose: true,
            //endDate: monthLimit
        });
        
        //////////////////周报导出，选择城市(部分公用js)//////////////////
        $('#city-nav li a').click(function(){
            $('#city-nav li').each(function(){$(this).removeClass('active');});
            $(this).parent().addClass('active');
            $('#export_cities span').each(function(){$(this).hide();});
            var initial = $(this).html();
            $('#init'+initial).show();
        });
        $('#allcity').click(function(){
            var checked = $(this).attr('checked');
            $('.ex_city').each(function(){$(this).attr('checked',checked?true:false);});
        });
        $('.ex_city').click(function(){
            var checked = true;
            $('.ex_city').each(function(){if(!$(this).attr('checked')) checked=false;});
            $('#allcity').attr('checked',checked);
        });
        $('#export_btn').click(function(){
            var checked = false;
            $('.ex_city').each(function(){if($(this).attr('checked')) checked=true;});
            if(!checked)
            {
                alert('请选择城市');
                return false;
            }
        });
        
        ////////////////////城市盈亏 - 详细数据趋势页 - filter//////////////////////
        $("#range").change(function(){
            range=$("#range").val();
            lossucc=$("#lossucc").val();
            if(lossucc==null) lossucc=0;
            
            if(lossucc == 0) {
                if(range != "city") {
                    $("#single_city").attr('disabled',true);
                    $('#single-city-span').css({"display":"none"});
                    $("#zone").attr('disabled',false);
                    $('#zone-span').css({"display":"inline-block"});
                    $("#city").attr('disabled',false);
                    $('#city-span').css({"display":"inline-block"});
                    $('#ok').css({"display":"inline-block"});
                    
                    $("#city").empty();
                    $("#city").append("<option value=''>全部</option>");
                    
                    $("#zone").load("selectrange",{range:range},function(response,status){
                        if (status=="success")
                        {
                            $("#zone").empty();
                            $("#zone").append(response);
                        }
                    });
                } else {
                    $("#single_city").attr('disabled',false);
                    $('#single-city-span').css({"display":"inline-block"});
                    $("#zone").attr('disabled',true);
                    $('#zone-span').css({"display":"none"});
                    $("#city").attr('disabled',true);
                    $('#city-span').css({"display":"none"});
                    haspost=$("#haspost").val();
                    if(haspost==null){
                        $('#ok').css({"display":"none"});
                    }
                }
            } else {
                $("#zone").attr('disabled',false);
                $('#zone-span').css({"display":"inline-block"});
                $("#city").attr('disabled',false);
                $('#city-span').css({"display":"inline-block"});
                $('#ok').css({"display":"inline-block"});
                
                $("#city").empty();
                $("#city").append("<option value=''>全部</option>");
                
                $("#zone").load("selectrange",{range:range,lossucc:lossucc},function(response,status){
                    if (status=="success")
                    {
                        $("#zone").empty();
                        $("#zone").append(response);
                    }
                });
            }
        });

        $("#zone").change(function(){
            range=$("#range").val();
            zone=$("#zone").val();
            extra=$("#extra").val();
            lossucc=$("#lossucc").val();
            if(extra==null) extra=0;
            if(lossucc==null) lossucc=0;
            $("#city").load("selectcity",{range:range,zone:zone,extra:extra,lossucc:lossucc},function(response,status){
                if (status=="success")
                {
                    $("#city").empty();
                    $("#city").append(response);
                }
            });
        });
        
        
        /////////////////////////////////////////////////
    })
}(window.jQuery)
