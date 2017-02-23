<ul class="nav nav-tabs"></ul>
<div class="page-header header-container" style="padding: 15px 0 0">
    <div class="row">
        <?php $form=$this->beginWidget('CActiveForm', array(
            'action'=>Yii::app()->createUrl($this->route,array('step'=>3)),
            'htmlOptions'=>array('id'=>'form')
        )); ?>
        <?php 
            echo CHtml::hiddenField('report_id', $report_id);
            echo chtml::hiddenField('item_count', $item_count);
            echo chtml::hiddenField('city_item_count', $city_item_count);
            echo CHtml::hiddenField('columns_calculation');
        ?>
        <div class="row">
            <div class="col-md-12">
                <span>
                    <label>展示区域：</label>
                    <?php echo CHtml::checkBoxList('show_parts', $show_parts, ReportConfiguration::$show_parts, array('separator'=>'&nbsp;&nbsp;','checkAll'=>'全选'));?>
                </span>
            </div>
            <div class="col-md-12" style="margin: 20px 0 0 0;padding-left:0">
                <div class="col-md-2">筛选项配置：</div>
                <div class="col-md-10">
                    <div class="pull-right">
                        <i class="glyphicon glyphicon-plus" style="color:#00CD00"></i>&nbsp;<?php echo CHtml::link('添加筛选项','javascript:void(0);',array('id'=>'add-select-item'));?>&nbsp;&nbsp;
                        <i class="glyphicon glyphicon-plus-sign" style="color:#00CD00"></i>&nbsp;<?php echo CHtml::link('添加联动列表','javascript:void(0);',array('id'=>'add-linked-list'));?>
                    </div>
                </div>
            </div>
            <div id="select-items" class="col-md-12" style="margin: 5px 0 0 0">
                <div id="dwysum-modal-content">
                    <div class="modal fade bs-example-modal-lg" id="dwysum-modal" tabindex="-1" role="dialog" aria-labelledby="dwysum-modal-label" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="dwysum-modal-label">日周月累计-指定时间累计-汇总字段配置</h4></div>
                                <div class="modal-body">
                                    <div class="container-fluid">
                                        <div class="col-md-12 well" style="padding-top: 15px;padding-bottom: 10px;">
                                            <div class="col-md-11" style="padding-left:0;">
                                                <div class="col-md-12" style="padding-left:0;">
                                                    <div class="col-md-1" style="padding-left:0;padding-right:0;">
                                                        <?php echo CHtml::radioButton('columns_calculation_type',true,array('id'=>'columns-calculation-normal'));?>常规
                                                    </div>
                                                    <div class="col-md-11" style="padding-left:0;">
                                                        <div class="col-md-10" style="padding-left:0;">
                                                            <label style="margin-bottom: 0">字段：</label>
                                                            <?php echo CHtml::dropDownList('columns_calculation_col', '', $dwysum_columns, array('style'=>'width: 83%;'));?>
                                                        </div>
                                                        <div class="col-md-12" style="padding-left:0;margin-top:5px;">
                                                            <div class="col-md-3" style="padding-left:0;">
                                                                <label style="margin-bottom: 0">函数：</label>
                                                                <?php echo CHtml::dropDownList('columns_calculation_function', '', ReportConfiguration::$calculation_functions, array('style'=>'width: 75px;'));?>
                                                            </div>
                                                            <div class="col-md-2" style="padding-left:0;">
                                                                <?php echo CHtml::checkBox('columns_calculation_distinct',false);?>&nbsp;<label>去重后统计</label>
                                                            </div>
                                                            <div class="col-md-3" style="padding-left:0; display:none;" id="columns-calculation-count">
                                                                <?php echo CHtml::radioButtonList('columns_calculation_count', 'all', ReportConfiguration::$calculation_count, array('separator'=>'&nbsp;&nbsp;','disabled'=>'disabled'));?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12" style="padding-left:0;margin-top: 5px;">
                                                    <div class="col-md-1" style="padding-left:0;padding-right:0;">
                                                        <?php echo CHtml::radioButton('columns_calculation_type',false,array('id'=>'columns-calculation-custom'));?>自定义
                                                    </div>
                                                    <div class="col-md-11" style="padding-left:0;">
                                                        <?php echo CHtml::textArea('columns_calculation_custom','',array('style'=>'display:inline-block;width:75%;','disabled'=>true));?>
                                                        <div class="col-md-12" style="padding-left:0;font-size:0.85em;color:red;">eg. sum(column1)*sum(column2) as alias1</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <?php echo CHtml::link('添加', 'javascript:void(0)', array('id'=>"columns-calculation-add", 'class'=>'btn btn-success'));?>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div style="width: 100%; height: 131px; overflow: auto;">
                                                <table id="columns-calculation-list" class="table table-bordered table-hover" style="table-layout: fixed">
                                                    <tbody>
                                                        <tr>
                                                            <th style="width: 25%">字段别名</th>
                                                            <th style="width: 45%">字段SQL别名</th>
                                                            <th style="width: 15%">函数</th>
                                                            <th style="width: 15%">操作</th>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">关闭</button></div>
                            </div>
                        </div>
                    </div>
                </div>
                <ul class="list-group">
                    <li class="list-group-item">
                        <label>筛选字段：</label>
                        <?php echo CHtml::dropDownList('column_0','',$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')); ?>&nbsp;&nbsp;
                        <label>控件类型：</label>
                        <?php echo CHtml::dropDownList('select_item_0','',ReportConfiguration::$select_items,array('class'=>'form-control','style'=>'display:inline-block;width:120px')); ?>&nbsp;&nbsp;
                        <label>控件配置：</label>
                        <?php echo CHtml::dropDownList('item_attr_0','',array(''=>'请选择特性'),array('class'=>'form-control','style'=>'display:inline-block;width:150px')); ?>&nbsp;&nbsp;
                        <span id="list-value-type-0" class="hide">
                            <label>列表值类型：</label>
                            <?php echo CHtml::radioButtonList('list_value_type_0', '0', ReportConfiguration::$list_value_type,array('separator'=>'&nbsp;&nbsp;','disabled'=>'true'));?>
                        </span>
                        <?php echo CHtml::Link('汇总字段配置', 'javascript:awake_dwysum_config()',array('id'=>"dwysum-config-0", 'class'=>'hide'));?>
                        <span class="pull-right" style="margin: 8px 20px 8px 10px">
                            <i class="glyphicon glyphicon-minus" style="color:red"></i>&nbsp;<?php echo CHtml::link('删除','javascript:void(0);',array('id'=>'del-select-item-0'));?>
                        </span>
                    </li>
                </ul>
            </div>

<!--            <div class="col-md-12" style="margin: 20px 0 0 0;padding-left:0">-->
<!--                <div class="col-md-9">城市归属展示：<span style="color: red">( ps. 用于展示城市所属的大区、区域、战区等信息。目标城市字段值须为城市名称 )</span></div>-->
<!--                <div class="col-md-3">-->
<!--                    <div class="pull-right">-->
<!--                        <i class="glyphicon glyphicon-plus" style="color:#00CD00"></i>&nbsp;--><?php //echo CHtml::link('添加目标城市字段','javascript:void(0);',array('id'=>'add-city-column'));?><!--&nbsp;&nbsp;-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
<!--            <div id="city-items" class="col-md-12" style="margin: 5px 0 0 0">-->
<!--                <ul class="list-group">-->
<!--                    <li class="list-group-item">-->
<!--                        <label>城市字段：</label>-->
<!--                        --><?php //echo CHtml::dropDownList('city_column_0','',$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')); ?><!--&nbsp;&nbsp;-->
<!--                        <label>归属类型：</label>-->
<!--                        --><?php //echo CHtml::checkBoxList('city_divisions_0', '', ReportConfiguration::$city_divisions, array('separator'=>'&nbsp;&nbsp;'));?>
<!--                        <span class="pull-right" style="margin: 8px 20px 8px 10px">-->
<!--                            <i class="glyphicon glyphicon-minus" style="color:red"></i>&nbsp;--><?php //echo CHtml::link('删除','javascript:void(0);',array('id'=>'del-city-column-0'));?>
<!--                        </span>-->
<!--                    </li>-->
<!--                </ul>-->
<!--            </div>-->

            <div class="col-md-12" style="margin: 20px 0 20px 0">
                <?php 
                    $action = strtolower($this->getAction()->getId());
                    if ($action == 'create') {
                        echo CHtml::submitButton('下一步>>', array('class'=>'btn btn-info pull-right'));
                    } elseif ($action == 'update') {
                        echo CHtml::submitButton('保存', array('class'=>'btn btn-info pull-right'));
                    }
                ?>
            </div>
        </div>
        <?php $this->endWidget(); ?>
    </div>
</div>
<ul class="nav nav-tabs"></ul>

<style type="text/css">
input[type="radio"], input[type="checkbox"] {
    margin-top: 0;
}
label {
    vertical-align: middle;
}
</style>

<script type="text/javascript">
<?php if (!empty($conditions) || !empty($city_div_columns)) { //修改时展示筛选项配置和城市归属 ?>
$(document).ready(function(){
    report_id = $("#report_id").val();
    <?php if (!empty($conditions)) { ?>
    item_count = $("#item_count").val();
    $.post("/configure/ajaxinitconditions",{report_id:report_id,item_count:item_count},function(response,status){
        if (status=="success")
        {
            var data = response.split("#####");
            $item = $(data[0]);
            $("#item_count").val(data[1]);
            $("#select-items .list-group").empty();
            $("#select-items .list-group").append($item);
        }
    });
    <?php } ?>
    <?php if (!empty($dwysum_calculation)) { ?>
        <?php foreach ($dwysum_calculation as $alias => $cal) {?>
            var alias = "<?php echo $alias;?>";
            var expression = "<?php echo $cal['expression'];?>";
            var func = "<?php echo $cal['function'];?>";
            var func_name = "--";
            switch(func){
                case 'sum':
                    func_name = "总计";
                    break;
                case 'count':
                    func_name = "数量";
                    break;
                case 'avg':
                    func_name = "平均";
                    break;
                case 'max':
                    func_name = "最大";
                    break;
                case 'min':
                    func_name = "最小";
                    break;
            }
            
            data="<tr id=\"col-cal-list-"+alias+"\"><td style=\"width: 25%\"><div style=\"width: 100%;word-wrap:break-word;text-align:left;\">"+alias+
                "</div></td><td style=\"width: 45%\"><div style=\"width: 100%;word-wrap:break-word;text-align:left;\">"+expression+
                "</div></td><td style=\"width: 15%\">"+func_name+
                "</td><td style=\"width: 15%\"><span><a href=\"javascript:columns_calculation_del(&quot;"+alias+"&quot;);\">删除</a></span></td></tr>";
            
            $("#columns-calculation-list").append(data);
        <?php }?>
    <?php }?>
    <?php if (!empty($city_div_columns)) { ?>
    city_item_count = $("#city_item_count").val();
    $.post("/configure/ajaxinitcitydivision",{report_id:report_id,city_item_count:city_item_count},function(response,status){
        if (status=="success")
        {
            var data = response.split("#####");
            $item = $(data[0]);
            $("#city_item_count").val(data[1]);
            $("#city-items .list-group").empty();
            $("#city-items .list-group").append($item);
        }
    });
    <?php } ?>
});
<?php }?>

//////////////////////////////筛选项配置//////////////////////////////

//对id以select_item_开头的对象绑定change事件处理方法，live使后生成的对象也适用
//从 jQuery 1.7 开始，不再建议使用 .live() 方法。请使用 .on() 来添加事件处理。使用旧版本的用户，应该优先使用 .delegate() 来替代 .live()。 
//具体使用，在父级元素ID上绑定事件，用于实际起作用元素
$("#select-items").on('change','[id^=select_item_]',function(){
    var id = $(this).attr('id');    //获取当前对象id属性 
    var arr = id.split("_");
    select_item=$(this).val();
    if(select_item == "list" || select_item == "linked") {
        $('#list-value-type-'+arr[2]).removeClass('hide');
        $('#list-value-type-'+arr[2]+' :radio').attr('disabled',false);
    } else {
        if(!$('#list-value-type-'+arr[2]).hasClass('hide')) {
            $('#list-value-type-'+arr[2]).addClass('hide');
        }
        $('#list-value-type-'+arr[2]+' :radio').attr('disabled',true);
    }
    
    $("#item_attr_"+arr[2]).load("/configure/ajaxselectitemattr",{select_item:select_item},function(response,status){
        if (status=="success")
        {
            $("#item_attr_"+arr[2]).empty();
            $("#item_attr_"+arr[2]).append(response);
        }
    });
    
    //日周月累计 - 汇总字段配置
    if(select_item == "dwysum") {
        $('#dwysum-config-'+arr[2]).removeClass('hide');
        $("#dwysum-modal").modal();
    } else {
        $('#dwysum-config-'+arr[2]).addClass('hide');
        $("#columns-calculation-list tr").each(function(){
            if($(this).children().get(0).tagName == 'TD'){
                $(this).remove();
            }
        });
    }
});

////////////////////// 日周月累计 - 汇总字段 //////////////////////
//打开日周月累计汇总字段配置
function awake_dwysum_config(){
    $("#dwysum-modal").modal();
}

//删除计算字段
function columns_calculation_del(alias){
    $("#col-cal-list-"+alias).remove();
}

//字段计算方式选择
$("#columns-calculation-normal").click(function(){
    $("#columns-calculation-normal").attr('checked',true);
    $('#columns_calculation_col').attr('disabled',false);
    $('#columns_calculation_function').attr('disabled',false);
    $('#columns_calculation_distinct').attr('disabled',false);
    $('#columns_calculation_count').attr('disabled',false);

    $("#columns-calculation-custom").attr('checked', false);
    $("#columns_calculation_custom").attr('disabled', true);
});

$("#columns-calculation-custom").click(function(){
    $("#columns-calculation-custom").attr('checked',true);
    $("#columns_calculation_custom").attr('disabled',false);

    $("#columns-calculation-normal").attr('checked',false);
    $('#columns_calculation_col').attr('disabled',true);
    $('#columns_calculation_function').attr('disabled',true);
    $('#columns_calculation_distinct').attr('disabled',true);
    $('#columns_calculation_count').attr('disabled',true);
});

//字段计算，选择函数count时展示总数和字段选项
$("#columns_calculation_function").change(function(){
    var func = $("#columns_calculation_function").val();
    if(func == "count") {
        $('#columns-calculation-count').css({"display":"inline"});
        $("[id^=columns_calculation_count_]").attr('disabled',false);
    } else {
        $('#columns-calculation-count').css({"display":"none"});
        $("[id^=columns_calculation_count_]").attr('disabled',true);
    }
});

//添加计算字段
$("#columns-calculation-add").click(function(){
    var data = "";
    var columns = new Array();
    var idx = 0;
    
    $("#columns-calculation-list tr").each(function(){
        if($(this).children().get(0).tagName == 'TD'){
            columns.push($(this).children().get(0).textContent);
            return true;            // false时相当于break, 如果return true 就相当于continure。
        }
    });
    
    if($("#columns-calculation-normal").attr('checked') == 'checked'){
        var col = $("#columns_calculation_col").val();
        var func = $("#columns_calculation_function").val();
        var distinct = $("input[name=columns_calculation_distinct]:checked").val();
        if(func == "count") {
            var count = $("#columns_calculation_count :checked").val();
        }
        
        if(typeof(count)!='undefined' && count=="all"){
            var alias = "count_all";<?php //COUNT_ALL?>
            var expression = "count(*) as "+alias;      //目前暂无适用场景，如果出现则无法匹配字段名称
        } else {
            var expression = func+"(";
            //var alias = func; //.toUpperCase()
            if(typeof(distinct)!='undefined' && distinct==1){
                //alias = alias+"_dst";
                expression = expression+"distinct ";
            }
            //alias = alias+"_"+col.replace(".","_");
            var arr = col.split(".");
            var alias = arr[1];                         //别名使用原始字段名，即可适用原始数据项定义
            expression = expression+col+") as "+alias;
        }
        
        var func_name = "";
        switch(func){
            case 'sum':
                func_name = "总计";
                break;
            case 'count':
                func_name = "数量";
                break;
            case 'avg':
                func_name = "平均";
                break;
            case 'max':
                func_name = "最大";
                break;
            case 'min':
                func_name = "最小";
                break;
        }
        
        if($.inArray(alias, columns)>=0) {
            $("body,html").animate({scrollTop:0},0); //返回页顶，平滑滚动
            var message = '<div class="alert alert-danger"><a class="close" data-dismiss="alert">×</a>字段别名已被使用，请更换</div>';
            //$("#main-body").prepend(message);
            $("#alert-area").empty();
            $("#alert-area").append(message);
            $("#dwysum-modal").modal('hide');
        } else {
            data="<tr id=\"col-cal-list-"+alias+"\"><td style=\"width: 25%\"><div style=\"width: 100%;word-wrap:break-word;text-align:left;\">"+alias+
                "</div></td><td style=\"width: 45%\"><div style=\"width: 100%;word-wrap:break-word;text-align:left;\">"+expression+
                "</div></td><td style=\"width: 15%\">"+func_name+
                "</td><td style=\"width: 15%\"><span><a href=\"javascript:columns_calculation_del(&quot;"+alias+"&quot;);\">删除</a></span></td></tr>";
            $("#alert-area").empty();
        }
        
    } else if($('#columns-calculation-custom').attr('checked') == 'checked'){
        var custom = $("#columns_calculation_custom").val();//.toLowerCase()不可以统一转换为小写，因为某些函数参数区分大小写，如，DATE_FORMAT(FROM_UNIXTIME(report_date),'%Y-%m-%d')
        if(custom.indexOf(';')>=0 || custom.indexOf('#')>=0 || custom.indexOf('-- ')>=0 || custom.indexOf('/*')>=0){
            $("body,html").animate({scrollTop:0},0); //返回页顶，平滑滚动
            var message = '<div class="alert alert-danger"><a class="close" data-dismiss="alert">×</a>查询sql的语句有误，包含结束或注释字符。</div>';
            //$("#main-body").prepend(message);
            $("#alert-area").empty();
            $("#alert-area").append(message);
            $("#dwysum-modal").modal('hide');
        } else {
            if(custom.length > 0) {
                if(custom.indexOf(' as ')>0 || custom.indexOf(' AS ')>0){   //因为不再转换小写，所以需要添加 as 大写支持
                    if(custom.indexOf(' as ')>0) {
                        var arr = custom.split(" as ");
                    } else {
                        var arr = custom.split(" AS ");
                    }
                    var alias = arr[1].toLowerCase();   //将自定义字段别名转换为小写
                    var expression = arr[0]+" as "+alias;   //custom;

                    if($.inArray(alias, columns)>=0) {
                        $("body,html").animate({scrollTop:0},0); //返回页顶，平滑滚动
                        var message = '<div class="alert alert-danger"><a class="close" data-dismiss="alert">×</a>字段别名已被使用，请更换</div>';
                        //$("#main-body").prepend(message);
                        $("#alert-area").empty();
                        $("#alert-area").append(message);
                        $("#dwysum-modal").modal('hide');
                    } else {
                        data="<tr id=\"col-cal-list-"+alias+"\"><td style=\"width: 25%\"><div style=\"width: 100%;word-wrap:break-word;text-align:left;\">"+alias+
                            "</div></td><td style=\"width: 45%\"><div style=\"width: 100%;word-wrap:break-word;text-align:left;\">"+expression+
                            "</div></td><td style=\"width: 15%\">--</td><td style=\"width: 15%\"><span><a href=\"javascript:columns_calculation_del(&quot;"+alias+"&quot;);\">删除</a></span></td></tr>";
                        $("#columns_calculation_custom").val('');
                        $("#alert-area").empty();
                    }
                
                } else {
                    $("body,html").animate({scrollTop:0},0); //返回页顶，平滑滚动
                    var message = '<div class="alert alert-danger"><a class="close" data-dismiss="alert">×</a>自定义计算字段缺少“ as ”或“ AS ”，注意大小写</div>';
                    //$("#main-body").prepend(message);
                    $("#alert-area").empty();
                    $("#alert-area").append(message);
                    $("#dwysum-modal").modal('hide');
                }
            } else {
                $("body,html").animate({scrollTop:0},0); //返回页顶，平滑滚动
                var message = '<div class="alert alert-danger"><a class="close" data-dismiss="alert">×</a>自定义计算字段为空</div>';
                //$("#main-body").prepend(message);
                $("#alert-area").empty();
                $("#alert-area").append(message);
                $("#dwysum-modal").modal('hide');
            }
        }
    }
    
    if(data.length > 0){
        $("#columns-calculation-list").append(data);
    }
});

//////////////////////日周月累计 - 汇总字段 END //////////////////////

$("#select-items").on('change','[id^=dict_]',function(){
    report_id = $("#report_id").val();
    var id = $(this).attr('id');    //获取当前对象id属性 
    var arr = id.split("_");
    item_attr=$(this).val();
    
    $(this).parent().siblings().remove();
    var parent = $(this).parent().parent();
    $.post("/configure/ajaxexchangelinkitem",{report_id:report_id,dict:item_attr,item_count:arr[1]},function(response,status){
        if (status=="success")
        {
           $item = $(response);
           parent.append($item);
        }
    });
//     if($.inArray(item_attr, window.sum_detail_list)>=0) {   //window.sum_detail_list在common.js中定义
//     } else {
//     }
});

//对id以del-select-item-开头的对象绑定click事件处理方法，live使后生成的对象也适用。另，由于联动列表在删除按钮外包了一层div，所以需要再上一层进行删除
//从 jQuery 1.7 开始，不再建议使用 .live() 方法。请使用 .on() 来添加事件处理。使用旧版本的用户，应该优先使用 .delegate() 来替代 .live()。 
//具体使用，在父级元素ID上绑定事件，用于实际起作用元素
$("#select-items").on('click','[id^=del-select-item-]',function(){
    var tag = $(this).parent().parent();
    if(tag[0].tagName=="LI") {    //取对象标签名
        tag.remove();
    } else {
        tag.parent().remove();
    }
});

$("#add-select-item").click(function(){
    report_id = $("#report_id").val();
    item_count = parseInt($("#item_count").val()) + 1;
    type = "item";
    $("#item_count").val(item_count);
    $.post("/configure/ajaxaddselectitem",{report_id:report_id,item_count:item_count,type:type},function(response,status){
        if (status=="success")
        {
            $item = $(response);
            $("#select-items .list-group").append($item);
        }
    });
});

$("#add-linked-list").click(function(){
    report_id = $("#report_id").val();
    item_count = parseInt($("#item_count").val()) + 1;
    type = "linked";
    $("#item_count").val(item_count);
    $.post("/configure/ajaxaddselectitem",{report_id:report_id,item_count:item_count,type:type},function(response,status){
        if (status=="success")
        {
            $item = $(response);
            $("#select-items .list-group").append($item);
        }
    });
});

//////////////////////////////城市归属展示//////////////////////////////

//对id以del-select-item-开头的对象绑定click事件处理方法，live使后生成的对象也适用
//从 jQuery 1.7 开始，不再建议使用 .live() 方法。请使用 .on() 来添加事件处理。使用旧版本的用户，应该优先使用 .delegate() 来替代 .live()。 
//具体使用，在父级元素ID上绑定事件，用于实际起作用元素
$("#city-items").on('click','[id^=del-city-column-]',function(){
    $(this).parent().parent().remove();
});

$("#add-city-column").click(function(){
    report_id = $("#report_id").val();
    city_item_count = parseInt($("#city_item_count").val()) + 1;
    $("#city_item_count").val(city_item_count);
    $.post("/configure/ajaxaddcitydivision",{report_id:report_id,city_item_count:city_item_count},function(response,status){
        if (status=="success")
        {
            $item = $(response);
            $("#city-items .list-group").append($item);
        }
    });
});

//提交前对部分参数赋值
$('#form').submit(function(){
    //日周月累计-汇总字段
    var columns_calculation = new Array();
    $("#columns-calculation-list").find("tr").each(function(){
        var column = new Array();
        var idx = 0;
        $(this).children().each(function(){
            ++idx;
            if($(this).get(0).tagName == 'TD' && idx < 4){
                column.push($(this).text());
            }
        });
        if(column.length > 0){
            var func = "";
            switch(column[2]){
                case '总计':
                    func = "sum";
                    break;
                case '数量':
                    func = "count";
                    break;
                case '平均':
                    func = "avg";
                    break;
                case '最大':
                    func = "max";
                    break;
                case '最小':
                    func = "min";
                    break;
            }
            var col = {
                    'col_alias':column[0],
                    'expression':column[1],
                    'function':func,
            };
            columns_calculation.push(col);
        }
    });
    if(columns_calculation.length > 0){
        var json = JSON.stringify(columns_calculation);  //JSON.stringify(order_columns, null, 2)对json结构进行了美化
        $("#columns_calculation").val(json);
    }
    return true;
});
</script>
