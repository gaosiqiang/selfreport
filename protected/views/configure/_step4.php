<ul class="nav nav-tabs"></ul>
<div class="page-header header-container" style="padding: 15px 0 0">
    <div class="row">
        <?php $form=$this->beginWidget('CActiveForm', array(
            'action'=>Yii::app()->createUrl($this->route,array('step'=>4)),
            'htmlOptions'=>array('id'=>'form')
        )); ?>
        <?php 
            echo CHtml::hiddenField('report_id', $report_id);
            echo chtml::hiddenField('item_count', $item_count);
        ?>
        <div class="row">
            <div class="col-md-12">
                    <div class="col-md-2"><label>权限归属：</label></div>
                    <div class="col-md-10">
                        <?php echo CHtml::listBox('user_group', $user_group, $user_groups, array('class'=>'form-control','style'=>'display:inline-block;width:250px;height:180px;','multiple'=>'multiple'));?>
                    </div>
            </div>

<!--            <div class="col-md-12" style="margin: 20px 0 0 0;padding-left:0">-->
<!--                <div class="col-md-9">行级权限配置：<span style="color: red">( ps. 权限控制字段值类型要求：大区、区域、分类为ID；城市、媒体为名称。 )</span></div>-->
<!--                <div class="col-md-3">-->
<!--                    <div class="pull-right">-->
<!--                        <i class="glyphicon glyphicon-plus" style="color:#00CD00"></i>&nbsp;--><?php //echo CHtml::link('添加行级权限控制字段','javascript:void(0);',array('id'=>'add-privilege-column'));?><!--&nbsp;&nbsp;-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
<!--            <div id="select-items" class="col-md-12" style="margin: 5px 0 0 0">-->
<!--                <ul class="list-group">-->
<!--                    <li class="list-group-item">-->
<!--                        <label>权限类型：</label>-->
<!--                        --><?php //echo CHtml::dropDownList('privilege_type_0','',ReportConfiguration::$privileges_types,array('class'=>'form-control','style'=>'display:inline-block;width:150px')); ?><!--&nbsp;&nbsp;-->
<!--                        <label>控制字段：</label>-->
<!--                        --><?php //echo CHtml::dropDownList('column_0','',$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:500px')); ?><!--&nbsp;&nbsp;-->
<!--                        <span class="pull-right" style="margin: 8px 40px 8px 10px">-->
<!--                            <i class="glyphicon glyphicon-minus" style="color:red"></i>&nbsp;--><?php //echo CHtml::link('删除','javascript:void(0);',array('id'=>'del-privilege-column-0'));?>
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

<script type="text/javascript">
<?php if (!empty($privilege_columns)) { //修改时展示筛选项配置 ?>
$(document).ready(function(){
    report_id = $("#report_id").val();
    item_count = $("#item_count").val();
    $.post("/configure/ajaxinitprivcolumns",{report_id:report_id,item_count:item_count},function(response,status){
        if (status=="success")
        {
            var data = response.split("#####");
            $item = $(data[0]);
            $("#item_count").val(data[1]);
            $(".list-group").empty();
            $(".list-group").append($item);
        }
    });
});
<?php }?>

//对id以del-select-item-开头的对象绑定click事件处理方法，live使后生成的对象也适用
//从 jQuery 1.7 开始，不再建议使用 .live() 方法。请使用 .on() 来添加事件处理。使用旧版本的用户，应该优先使用 .delegate() 来替代 .live()。 
//具体使用，在父级元素ID上绑定事件，用于实际起作用元素
$("#select-items").on('click','[id^=del-privilege-column-]',function(){
    $(this).parent().parent().remove();
});

$("#add-privilege-column").click(function(){
    report_id = $("#report_id").val();
    item_count = parseInt($("#item_count").val()) + 1;
    $("#item_count").val(item_count);
    $.post("/configure/ajaxaddprivilegecolumn",{report_id:report_id,item_count:item_count},function(response,status){
        if (status=="success")
        {
            $item = $(response);
            $(".list-group").append($item);
        }
    });
});
</script>
