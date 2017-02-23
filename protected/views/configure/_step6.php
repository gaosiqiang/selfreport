<ul class="nav nav-tabs"></ul>
<div class="page-header header-container" style="padding: 15px 0 0">
    <div class="row">
        <?php $form=$this->beginWidget('CActiveForm', array(
            'action'=>Yii::app()->createUrl($this->route,array('step'=>6)),
            'htmlOptions'=>array('id'=>'form')
        )); ?>
        <?php 
            echo CHtml::hiddenField('report_id', $report_id);
        ?>
        <div class="row">
            <div class="col-md-12">
                <div class="col-md-2">图表配置</div>
                <div class="col-md-10">
                    <div class="pull-right">
                        <i class="glyphicon glyphicon-plus" style="color:#00CD00"></i>&nbsp;<?php echo CHtml::link('添加图表标签项','javascript:void(0);',array('id'=>'add-tab-item'));?>&nbsp;&nbsp;
                    </div>
                </div>
            </div>
            <div id="select-items" class="col-md-12" style="margin: 5px 0 0 0">
                <ul class="list-group">
                    <?php foreach ($chart_arr as $key => $chart) {?>
                        <li class="list-tab-item">
                            <div class="col-md-12">
                                <!-- <label>图表项名称及定义：</label> -->
                                <div class="col-md-12" style="margin-bottom: 10px">
                                    <label>图表标题：</label>
                                    <?php echo CHtml::textField('chart_title_'.$key,array_key_exists('title', $chart_arr[$key]) ? $chart_arr[$key]['title'] :"" ,array('class'=>'form-control','style'=>'display:inline-block;width:150px','id'=>'chart_title_'.$key)); ?>&nbsp;&nbsp;
                                
                                    <span style="margin: 8px -23px 8px 10px" class="pull-right"><i style="color:red" class="glyphicon glyphicon-minus"></i>&nbsp;<a href="javascript:;" class="del-tab-item" data="<?php echo $key;?>">删除图表标签项</a></span>
                                </div>
                                
                                <div  class="col-md-12 class_chart_type" style="margin-bottom: 10px">
                                    <label>图表类型：</label>
                                    <?php echo CHtml::dropDownList('chart_type_'.$key,array_key_exists('chart', $chart_arr[$key]) ? $chart_arr[$key]['chart'] :"",$chart_types,array('class'=>'form-control chart_type','style'=>'display:inline-block;width:150px','id'=>'chart_type_'.$key)); ?>&nbsp;&nbsp;
                                </div>
                                <div  class="step6-area" style="display:block">
                                    
                                </div>
                            </div>
                            <div class="col-md-12" style="margin: 20px 0 20px 0">

                                <?php 
                                    echo CHtml::hiddenField('xAxisHidden_'.$key,array_key_exists('xAxis', $chart_arr[$key]['config']) ? $chart_arr[$key]['config']['xAxis'] : '', array('id'=>'xAxisHidden_'.$key));
                                    echo CHtml::hiddenField('yAxisHidden_'.$key,array_key_exists('yAxis', $chart_arr[$key]['config']) ? (is_array($chart_arr[$key]['config']['yAxis']) ? implode(",",$chart_arr[$key]['config']['yAxis']):$chart_arr[$key]['config']['yAxis']) : '', array('id'=>'yAxisHidden_'.$key));
                                    echo CHtml::hiddenField('zAxisHidden_'.$key,array_key_exists('zAxis', $chart_arr[$key]['config']) ? $chart_arr[$key]['config']['zAxis'] : '', array('id'=>'zAxisHidden_'.$key));
                                    echo CHtml::hiddenField('yAxisGroupHidden_'.$key,array_key_exists('yAxisGroup', $chart_arr[$key]['config']) ? $chart_arr[$key]['config']['yAxisGroup'] : '', array('id'=>'yAxisGroupHidden_'.$key));
                                    
                                    
                                ?>
                            </div>
                        </li>
                    <?php }?>
                </ul>
            </div>
            <div style="margin: 20px 0 20px 0" class="col-md-12">
                <?php 
                    echo CHtml::hiddenField('li_cnt',count($chart_arr), array('id'=>'li_cnt'));
                    $action = strtolower($this->getAction()->getId());
                    if ($action == 'create') {
                        echo CHtml::submitButton('下一步>>', array('class'=>'btn btn-info pull-right'));
                    } elseif ($action == 'update') {
                        echo CHtml::submitButton('保存', array('class'=>'btn btn-info pull-right'));
                    }
                ?>
            </div>
            <?php $this->endWidget(); ?>
        </div>
    </div>
</div>
<ul class="nav nav-tabs"></ul>

<script type="text/javascript">
$(document).ready(function(){
<?php if (!empty($condition_json) && !empty($filters_json) && $condition_json!=='{}' && $filters_json!=='{}') { //修改时展示query builder?>
    $('#builder').queryBuilder({sortable: true,filters: <?php echo $filters_json;?>});
    $('#builder').queryBuilder('setRules', <?php echo $condition_json;?>);
<?php } else {?>
    chart_type=$("#chart_type_1").val();
    new_add=1;
    if(chart_type == 'line')
    {
    	
    }
    

<?php }?>
<?php foreach ($chart_arr as $key => $chart) { ?>
    $("#chart_type_<?php echo $key ?>").change();
<?php } ?>
});
$(document).on("change",".chart_type",function(e){
    var $self = $(this);
    chart_type = $(this).val();
    //console.log($(this));#原来未被注释
    var parent_id = $(this)[0].id;
    var cnt_list = parent_id.split('_');
    var cnt = cnt_list[cnt_list.length-1]
    //alert(parent_id);
    //alert($(this).parents('.class_chart_type').next());
    //console.log($(this).parents('.class_chart_type').next());
    //alert(chart_type);
    report_id = $("#report_id").val();

    if (chart_type=="line") 
    {
        var xAxis = $("#xAxisHidden_"+cnt).val();
        var yAxis = $("#yAxisHidden_"+cnt).val();
        var zAxis = $("#zAxisHidden_"+cnt).val();
        var yAxisGroup = $("#yAxisGroupHidden_"+cnt).val();
        //alert(xAxis);alert(yAxis);
        //var yAxis = config['yAxis'] ;
        $.ajax({
            type: "POST",
            url: "/configure/ajaxcolumnschart",
            data: {chart_type:chart_type,report_id:report_id},
            dataType: "json", 
            success: function(response){
                var step6_select = '';
                if (response) {
                    var step6_select_x = "<div class='col-md-12' style='margin-bottom: 10px'><label class='step6_label'>横轴坐标字段：</label>";
                    var step6_select_y = "<div class='col-md-12' style='margin-bottom: 10px'><label class='step6_label'>纵轴坐标字段：</label>";
                    var step6_group_y = "<div class='col-md-12' style='margin-bottom: 10px'><label class='step6_label'>纵轴分组字段：</label>";
                    step6_select_x += "<select id='xAxis_"+cnt+"' name='xAxis_"+cnt+"'  class='form-control'  style='display:inline-block;width:270px'>";
                    step6_group_y += "<select id='yAxisGroup_"+cnt+"' name='yAxisGroup_"+cnt+"'  class='form-control'  style='display:inline-block;width:270px'>";
                    //step6_select_y += "<select id='yAxis' name='yAxis'  class='form-control'  style='display:inline-block;width:150px'>";
                    //step6_select_z += "<select id='zAxis' name='zAxis'  class='form-control'  style='display:inline-block;width:150px'>";
                    //alert(yAxis);
                    var yAxisArr = yAxis.split(",");//alert(yAxisArr[1]);
                    step6_select_y += "<ul class='step6_ul'>";
                    step6_group_y += "<option value=''>请选择</option>";
                    
                    for (var i in response) {
                        var xSelect = '';
                        var ySelect = '';
                        var zSelect = '';
                        var ySelectGroup = '';

                        if (i == xAxis) {
                            xSelect = 'selected="selected"';
                        };
                        for(var j in yAxisArr) {
                            if (i==yAxisArr[j]) {
                                ySelect = 'checked="checked"';
                            };
                            
                        };
                        if (i == yAxisGroup) {
                            ySelectGroup = 'selected="selected"';
                        };

                        step6_select_x += "<option value='"+i+"' "+xSelect+">"+i+"("+response[i]+")</option>";
                        //step6_select_y += "<option value='"+response[i]+"'>"+response[i]+"</option>";
                        //step6_select_z += "<option value='"+response[i]+"'>"+response[i]+"</option>";
                        step6_select_y += "<li><input type='checkbox' name='yAxis_"+cnt+"[]' value='"+i+"'  "+ySelect+">"+i+"("+response[i]+")</li>";
                        step6_group_y += "<option value='"+i+"' "+ySelectGroup+">"+i+"("+response[i]+")</option>";
                    };
                    step6_select_x += '</select></div>';
                    //step6_select_y += '</select></div>';
                    //step6_select_z += '</select></div>';
                    step6_select_y += '</ul></div>';
                    step6_group_y += '</select></div>';
                    step6_select = step6_select_x +""+ step6_select_y + "" +step6_group_y;//+""+ step6_select_z;
                };
                //alert(step6_select);
                //$(this).parents('.class_chart_type').next().empty();
                $self.parents('.class_chart_type').next().html(step6_select);
                //console.log($self.parents('.class_chart_type').next());#原来未被注释
            }
        });
    }else if (chart_type=="bar") 
    {
        var xAxis = $("#xAxisHidden_"+cnt).val();
        var yAxis = $("#yAxisHidden_"+cnt).val();
        var zAxis = $("#zAxisHidden_"+cnt).val();
        var yAxisGroup = $("#yAxisGroupHidden_"+cnt).val();
        //alert(xAxis);alert(yAxis);
        //var yAxis = config['yAxis'] ;
        $.ajax({
            type: "POST",
            url: "/configure/ajaxcolumnschart",
            data: {chart_type:chart_type,report_id:report_id},
            dataType: "json", 
            success: function(response){
                var step6_select = '';
                if (response) {
                    var step6_select_x = "<div class='col-md-12' style='margin-bottom: 10px'><label class='step6_label'>横轴坐标字段：</label>";
                    var step6_select_y = "<div class='col-md-12' style='margin-bottom: 10px'><label class='step6_label'>纵轴坐标字段：</label>";
                    var step6_select_z = "<div class='col-md-12' style='margin-bottom: 10px'><label class='step6_label'>展示字段：</label>";
                    step6_select_x += "<select id='xAxis_"+cnt+"' name='xAxis_"+cnt+"'  class='form-control'  style='display:inline-block;width:270px'>";
                    //step6_select_y += "<select id='yAxis' name='yAxis'  class='form-control'  style='display:inline-block;width:150px'>";
                    //step6_select_z += "<select id='zAxis' name='zAxis'  class='form-control'  style='display:inline-block;width:150px'>";
                    //alert(yAxis);
                    var yAxisArr = yAxis.split(",");//alert(yAxisArr[1]);
                    step6_select_y += "<ul class='step6_ul'>";
                    
                    for (var i in response) {
                        var xSelect = '';
                        var ySelect = '';
                        var zSelect = '';

                        if (i == xAxis) {
                            xSelect = 'selected="selected"';
                        };
                        for(var j in yAxisArr) {
                            if (i==yAxisArr[j]) {
                                ySelect = 'checked="checked"';
                            };
                            
                        };
                        step6_select_x += "<option value='"+i+"' "+xSelect+">"+i+"("+response[i]+")</option>";
                        //step6_select_y += "<option value='"+response[i]+"'>"+response[i]+"</option>";
                        //step6_select_z += "<option value='"+response[i]+"'>"+response[i]+"</option>";
                        step6_select_y += "<li><input type='checkbox' name='yAxis_"+cnt+"[]' value='"+i+"'  "+ySelect+">"+i+"("+response[i]+")</li>";
                    
                    };
                    step6_select_x += '</select></div>';
                    //step6_select_y += '</select></div>';
                    //step6_select_z += '</select></div>';
                    step6_select_y += '</ul>';
                    step6_select = step6_select_x +""+ step6_select_y ;//+""+ step6_select_z;
                };
                //alert(step6_select);
                //$(this).parents('.class_chart_type').next().empty();
                $self.parents('.class_chart_type').next().html(step6_select);
                //console.log($self);#原来未被注释
            }
        });
    }
    else if (chart_type=="pie") 
    {
        var xAxis = $("#xAxisHidden_"+cnt).val();
        var yAxis = $("#yAxisHidden_"+cnt).val();
        var zAxis = $("#zAxisHidden_"+cnt).val();
        var yAxisGroup = $("#yAxisGroupHidden_"+cnt).val();
        //alert(xAxis);alert(yAxis);
        //var yAxis = config['yAxis'] ;
        $.ajax({
            type: "POST",
            url: "/configure/ajaxcolumnschart",
            data: {chart_type:chart_type,report_id:report_id},
            dataType: "json", 
            success: function(response){
                var step6_select = '';
                if (response) {
                    var step6_select_x = "<div class='col-md-12' style='margin-bottom: 10px'><label class='step6_label'>横轴坐标字段：</label>";
                    var step6_select_y = "<div class='col-md-12' style='margin-bottom: 10px'><label class='step6_label'>纵轴坐标字段：</label>";
                    var step6_group_y = "<div class='col-md-12' style='margin-bottom: 10px'><label class='step6_label'>纵轴分组字段：</label>";
                    step6_select_x += "<select id='xAxis_"+cnt+"' name='xAxis_"+cnt+"'  class='form-control'  style='display:inline-block;width:270px'>";
                    step6_group_y += "<select id='yAxisGroup_"+cnt+"' name='yAxisGroup_"+cnt+"'  class='form-control'  style='display:inline-block;width:270px'>";
                    //step6_select_y += "<select id='yAxis' name='yAxis'  class='form-control'  style='display:inline-block;width:150px'>";
                    //step6_select_z += "<select id='zAxis' name='zAxis'  class='form-control'  style='display:inline-block;width:150px'>";
                    //alert(yAxis);
                    var yAxisArr = yAxis.split(",");//alert(yAxisArr[1]);
                    step6_select_y += "<ul class='step6_ul'>";
                    step6_group_y += "<option value=''>请选择</option>";
                    
                    for (var i in response) {
                        var xSelect = '';
                        var ySelect = '';
                        var zSelect = '';
                        var ySelectGroup = '';

                        if (i == xAxis) {
                            xSelect = 'selected="selected"';
                        };
                        for(var j in yAxisArr) {
                            if (i==yAxisArr[j]) {
                                ySelect = 'checked="checked"';
                            };
                            
                        };
                        if (i == yAxisGroup) {
                            ySelectGroup = 'selected="selected"';
                        };

                        step6_select_x += "<option value='"+i+"' "+xSelect+">"+i+"("+response[i]+")</option>";
                        //step6_select_y += "<option value='"+response[i]+"'>"+response[i]+"</option>";
                        //step6_select_z += "<option value='"+response[i]+"'>"+response[i]+"</option>";
                        step6_select_y += "<li><input type='checkbox' name='yAxis_"+cnt+"[]' value='"+i+"'  "+ySelect+">"+i+"("+response[i]+")</li>";
                        step6_group_y += "<option value='"+i+"' "+ySelectGroup+">"+i+"("+response[i]+")</option>";
                    };
                    step6_select_x += '</select></div>';
                    //step6_select_y += '</select></div>';
                    //step6_select_z += '</select></div>';
                    step6_select_y += '</ul>';
                    step6_group_y += '</select></div>';
                    step6_select = step6_select_x +""+ step6_select_y + "" +step6_group_y;//+""+ step6_select_z;
                };
                //alert(step6_select);
                //$(this).parents('.class_chart_type').next().empty();
                $self.parents('.class_chart_type').next().html(step6_select);
                //console.log($self.parents('.class_chart_type').next());#原来未被注释
            }
        });
    };


});



//var li_cnt = 1;
$("#add-tab-item").click(function(){
    report_id = $("#report_id").val();
    li_cnt = parseInt($("#li_cnt").val()) + 1;
    var del_tab_item_list = $(".del-tab-item");

    if(del_tab_item_list.length >=3)
    {
        alert("标签不能超过3个");
    }
    else
    {
        type = "item";
        //$("#item_count").val(item_count);
        $.post("/configure/ajaxaddtabitem",{report_id:report_id,type:type,li_cnt:li_cnt},function(response,status){
            if (status=="success")
            {
                $item = $(response);
                $("#select-items .list-group").append($item);
                var xAxis = $("#xAxisHidden_"+li_cnt).val();
                var yAxis = $("#yAxisHidden_"+li_cnt).val();
                var zAxis = $("#zAxisHidden_"+li_cnt).val();
                var yAxisGroup = $("#yAxisGroupHidden_"+li_cnt).val();

                $.ajax({
                type: "POST",
                url: "/configure/ajaxcolumnschart",
                data: {chart_type:chart_type,report_id:report_id},
                dataType: "json", 
                success: function(response){
                    var step6_select = '';
                    if (response) {
                        var step6_select_x = "<div class='col-md-12' style='margin-bottom: 10px'><label class='step6_label'>横轴坐标字段：</label>";
                        var step6_select_y = "<div class='col-md-12' style='margin-bottom: 10px'><label class='step6_label'>纵轴坐标字段：</label>";
                        var step6_group_y = "<div class='col-md-12' style='margin-bottom: 10px'><label class='step6_label'>纵轴分组字段：</label>";
                        step6_select_x += "<select id='xAxis_"+li_cnt+"' name='xAxis_"+li_cnt+"'  class='form-control'  style='display:inline-block;width:270px'>";
                        step6_group_y += "<select id='yAxisGroup_"+li_cnt+"' name='yAxisGroup_"+li_cnt+"'  class='form-control'  style='display:inline-block;width:270px'>";
                        //step6_select_y += "<select id='yAxis' name='yAxis'  class='form-control'  style='display:inline-block;width:150px'>";
                        //step6_select_z += "<select id='zAxis' name='zAxis'  class='form-control'  style='display:inline-block;width:150px'>";
                        //alert(yAxis);
                        var yAxisArr = yAxis.split(",");//alert(yAxisArr[1]);
                        step6_select_y += "<ul class='step6_ul'>";
                        step6_group_y += "<option value=''>请选择</option>";
                        
                        for (var i in response) {
                            var xSelect = '';
                            var ySelect = '';
                            var zSelect = '';
                            var ySelectGroup = '';

                            if (i == xAxis) {
                                xSelect = 'selected="selected"';
                            };
                            for(var j in yAxisArr) {
                                if (i==yAxisArr[j]) {
                                    ySelect = 'checked="checked"';
                                };
                                
                            };
                            if (i == yAxisGroup) {
                                ySelectGroup = 'selected="selected"';
                            };

                            step6_select_x += "<option value='"+i+"' "+xSelect+">"+i+"("+response[i]+")</option>";
                            //step6_select_y += "<option value='"+response[i]+"'>"+response[i]+"</option>";
                            //step6_select_z += "<option value='"+response[i]+"'>"+response[i]+"</option>";
                            step6_select_y += "<li><input type='checkbox' name='yAxis_"+li_cnt+"[]' value='"+i+"'  "+ySelect+">"+i+"("+response[i]+")</li>";
                            step6_group_y += "<option value='"+i+"' "+ySelectGroup+">"+i+"("+response[i]+")</option>";
                    };
                    step6_select_x += '</select></div>';
                    //step6_select_y += '</select></div>';
                    //step6_select_z += '</select></div>';
                    step6_select_y += '</ul>';
                    step6_group_y += '</select></div>';
                    step6_select = step6_select_x +""+ step6_select_y + "" +step6_group_y;//+""+ step6_select_z;
                    };
                    //alert(step6_select);
                    //$(this).parents('.class_chart_type').next().empty();
                    $(".list-tab-item:last").find(".step6-area").html(step6_select);
                    $("#li_cnt").val(li_cnt);
                    //console.log($self.parents('.class_chart_type').next()); #原来未被注释
                }
                });
            }
        });
    }
        
});

$(document).on("click",".del-tab-item",function(e){
    var del_tab_item_list = $(".del-tab-item");

    if(del_tab_item_list.length ==1)
    {
        alert("必须保留1个图表标签项");
        return false;
    }
    $(this).parents('.list-tab-item').remove();

});
</script>