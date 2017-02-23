<ul id="tab" class="nav nav-tabs">
    <li id="tab-tables" class="active"><?php echo CHtml::link('表','#step2-tables',array('data-toggle'=>'tab'))?></li>
    <li id="tab-columns"><?php echo CHtml::link('结果字段','#step2-columns',array('data-toggle'=>'tab'))?></li>
    <li id="tab-conditions"><?php echo CHtml::link('查询条件','#step2-conditions',array('data-toggle'=>'tab'))?></li>
    <li id="tab-group"><?php echo CHtml::link('分组','#step2-group',array('data-toggle'=>'tab'))?></li>
    <li id="tab-order"><?php echo CHtml::link('排序','#step2-order',array('data-toggle'=>'tab'))?></li>
</ul>
<div class="page-header header-container" style="padding: 15px 0 0">
    <div class="row">
        <?php $form=$this->beginWidget('CActiveForm', array(
            'action'=>Yii::app()->createUrl($this->route,array('step'=>2)),
            'htmlOptions'=>array('id'=>'form')
        )); ?>
        <?php 
            echo CHtml::hiddenField('report_id', $report_id);
            echo CHtml::hiddenField('condition');
            echo CHtml::hiddenField('condition_json', $condition_json);
            echo CHtml::hiddenField('is_distinct');
            echo CHtml::hiddenField('tables');
            echo CHtml::hiddenField('columns');
            echo CHtml::hiddenField('columns_choosed');
            echo CHtml::hiddenField('columns_calculation');
            echo CHtml::hiddenField('group_columns');
            echo CHtml::hiddenField('order_columns');
        ?>
        <div class="row">
            <div class="col-md-12 tab-content">
                <div id="step2-tables" class="tab-pane active">
                    <div class="col-md-12" style="margin-bottom: 10px">
                        <label>数据源：</label>
                        <?php echo CHtml::dropDownList('data_source',$data_source,$data_sources,array('class'=>'form-control','style'=>'display:inline-block;width:150px')); ?>&nbsp;&nbsp;
                    </div>
                    <div class="col-md-5">
                        <label>可用表：</label>
                        <div style="width: 100%; height: 325px; overflow: auto;">
                            <table id="table-available" class="table table-bordered table-hover" style="table-layout: fixed">
                                <tbody>
                                    <tr>
                                        <th style="width: 30%">表名</th>
                                        <th style="width: 40%">表注释</th>
                                        <th style="width: 15%">操作</th>
                                    </tr>
                                    <?php 
                                    if(!empty($tables)){
                                        foreach ($tables as $table => $comment){
                                    ?>
                                    <tr>
                                        <td style="width: 30%"><div style="width: 100%;word-wrap:break-word;text-align:left;"><?php echo $table;?></div></td>
                                        <td style="width: 40%;text-align:left;"><?php echo $comment;?></td>
                                        <td style="width: 15%">
                                            <?php echo CHtml::link('选择', 'javascript:void(0)',array('id' => "choose-table-$table"));?>
                                        </td>
                                    </tr>
                                    <?php }}?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <label>已选择的表：</label>
                        <div style="width: 100%; height: 325px; overflow: auto;">
                            <table id="tables-choosed" class="table table-bordered table-hover" style="table-layout: fixed">
                                <tbody>
                                    <tr>
                                        <th style="width: 22%">表名</th>
                                        <th style="width: 22%">SQL别名</th>
                                        <th style="width: 34%">表注释</th>
                                        <th style="width: 22%">操作</th>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="tables-modals"></div>
                </div>
                <div id="step2-columns" class="tab-pane">
                    <div class="col-md-12" style="padding-left:0; font-size: 1.05em; font-weight:bold;">字段选择</div>
                    <div class="col-md-12" style="margin-top: 10px">
                        <?php echo CHtml::checkBox('columns_distinct',false);?>&nbsp;<label>结果数据去重</label>
                    </div>
                    <div class="col-md-12">
                        <label>可用字段：</label>
                        <div style="width: 100%; height: 325px; overflow: auto;">
                            <table id="columns-optional" class="table table-bordered table-hover" style="table-layout: fixed">
                                <tbody>
                                    <tr>
                                        <th style="width: 15%">字段别名</th>
                                        <th style="width: 20%">字段SQL别名</th>
                                        <th style="width: 15%">表名</th>
                                        <th style="width: 15%">表SQL别名</th>
                                        <th style="width: 25%">字段注释</th>
                                        <th style="width: 10%">操作</th>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-12" style="margin-top: 20px">
                        <label>已选择的字段：</label>
                        <div class="pull-right"><?php echo CHtml::checkBox('all_columns',false);?>&nbsp;<label>字段全选</label></div>
                        <div style="width: 100%; height: 325px; overflow: auto;">
                            <table id="columns-choosed" class="table table-bordered table-hover" style="table-layout: fixed">
                                <tbody>
                                    <tr>
                                        <th style="width: 15%">字段别名</th>
                                        <th style="width: 20%">字段SQL别名</th>
                                        <th style="width: 15%">表名</th>
                                        <th style="width: 15%">表SQL别名</th>
                                        <th style="width: 25%">字段注释</th>
                                        <th style="width: 10%">操作</th>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-12" style="margin-top: 10px; padding-left:0; font-size: 1.05em; font-weight:bold;">字段计算</div>
                    <div class="col-md-12 well" style="margin-top: 10px;padding-top: 15px;padding-bottom: 10px;">
                        <div class="col-md-11" style="padding-left:0;">
                            <div class="col-md-12" style="padding-left:0;">
                                <div class="col-md-1" style="padding-left:0;"><?php echo CHtml::radioButton('columns_calculation_type',true,array('id'=>'columns-calculation-normal'))?>常规</div>
                                <div class="col-md-5" style="padding-left:0;">
                                    <label style="margin-bottom: 0">字段：</label>
                                    <?php echo chtml::dropDownList('columns_calculation_col', '', array(''=>'请选择'), array('style'=>'width: 83%;'));?>
                                </div>
                                <div class="col-md-2" style="padding-left:0;">
                                    <label style="margin-bottom: 0">函数：</label>
                                    <?php echo chtml::dropDownList('columns_calculation_function', '', ReportConfiguration::$calculation_functions, array('style'=>'width: 75px;'));?>
                                </div>
                                <div class="col-md-2" style="padding-left:0;">
                                    <?php echo CHtml::checkBox('columns_calculation_distinct',false);?>&nbsp;<label>去重后统计</label>
                                </div>
                                <div class="col-md-2" style="padding-left:0; display:none;" id="columns-calculation-count">
                                    <?php echo CHtml::radioButtonList('columns_calculation_count', 'all', ReportConfiguration::$calculation_count, array('separator'=>'&nbsp;&nbsp;','disabled'=>'disabled'));?>
                                </div>
                            </div>
                            <div class="col-md-12" style="padding-left:0;margin-top: 5px;">
                                <div class="col-md-1" style="padding-left:0;"><?php echo CHtml::radioButton('columns_calculation_type',false,array('id'=>'columns-calculation-custom'))?>自定义</div>
                                <div class="col-md-11" style="padding-left:0;">
                                    <?php echo CHtml::textArea('columns_calculation_custom','',array('style'=>'display:inline-block;width:75%;','disabled'=>true));?>
                                    <span style="font-size:0.85em;color:red;">eg. column1+column2 as alias1</span>
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
                    <div class="col-md-12" style="margin-top: 10px; padding-left:0; font-size: 1.05em; font-weight:bold;">字段排序</div>
                    <div class="col-md-12" style="margin-top: 10px">
                        <div class="col-md-12" style="padding-left:0;"><?php echo CHtml::link('获取结果字段', 'javascript:columns_gather();', array('class'=>'btn btn-success btn-xs'));?></div>
                        <div class="col-md-3" style="padding-left:0;margin-top: 10px"><?php echo CHtml::listBox('columns_select','',array(),array('style'=>'display:inline-block;width:230px;height:250px;'))?></div>
                        <div class="col-md-1" style="padding: 81px 0 0 0">
                            <div style="margin-bottom: 30px"><a id="up" class="btn btn-lg" href="javascript:void(0)"><i class="glyphicon glyphicon-arrow-up"></i></a></div>
                            <div style="margin-top: 30px"><a id="down" class="btn btn-lg" href="javascript:void(0)"><i class="glyphicon glyphicon-arrow-down"></i></a></div>
                        </div>
                    </div>
                </div>
                <div id="step2-conditions" class="tab-pane">
                    <div class="col-md-12" style="margin: 20px 0 0 0">
                        <span>固定查询条件：</span>
                    </div>
                    <div id="query-builder" class="col-md-12" style="margin: 20px 0 0 0">
                        <div id="builder"></div>
                        <div class="btn-group">
                            <?php echo CHtml::button('重置',array('class'=>'btn btn-warning reset'));?>
                        </div>
                        <div class="btn-group">
                            <?php echo CHtml::button('SQL预览',array('class'=>'btn btn-primary parse-sql','data-stmt'=>'false'));?>
                        </div>
                        <div id="result" class="hide">
                            <h3>Output</h3>
                            <pre></pre>
                        </div>
                    </div>
                </div>
                <div id="step2-group" class="tab-pane">
                    <div class="col-md-12" style="margin-top: 10px; padding-left:0; font-size: 1.05em; font-weight:bold;">分组字段</div>
                    <div class="col-md-12" style="margin-top: 10px">
                        <?php echo CHtml::checkBox('group_enable',false);?>&nbsp;<label>启用分组</label>
                        <span style="font-size:0.85em;color:red;">( ps.当“结果字段”配置了“字段计算”时，需要启用分组 )</span>
                    </div>
                    <div class="col-md-12">
                        <div style="width: 100%; height: 325px; overflow: auto;">
                            <table id="group-columns" class="table table-bordered table-hover" style="table-layout: fixed">
                                <tbody>
                                    <tr>
                                        <th style="width: 30%">字段SQL别名</th>
                                        <th style="width: 55%">字段注释</th>
                                        <th style="width: 15%">操作</th>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div id="step2-order" class="tab-pane">
                    <div class="col-md-12">
                        <?php echo CHtml::checkBox('order_normal',true);?>&nbsp;<label>自然排序</label>
                    </div>
                    <div class="col-md-12">
                        <label>选择排序字段与方式：</label>
                        <?php echo CHtml::dropDownList('columns_for_order', '', array(), array('style' => 'margin-right: 10px;'));?>
                        <?php echo CHtml::radioButtonList('order_type', 'asc', ReportConfiguration::$order, array('separator'=>'&nbsp;&nbsp;'));?>
                        <?php echo CHtml::button('添加', array('class'=>'btn btn-success btn-sm', 'id'=>'add-order-column', 'style' => 'margin-left: 10px;'));?>
                    </div>
                    <div class="col-md-12">
                        <div class="col-md-9" style="margin-top: 10px; padding: 0; height: 150px; overflow: auto;">
                            <table id="order-columns" class="table table-bordered table-hover" style="table-layout: fixed">
                                <tbody>
                                    <tr>
                                        <th style="width: 60%">字段SQL别名</th>
                                        <th style="width: 15%">排序方式</th>
                                        <th style="width: 25%">操作</th>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12" style="margin: 20px 0 20px 0">
                <?php //echo CHtml::link('测试','javascript:test()',array('class'=>'btn btn-warning'));?>
                <?php 
                    $action = strtolower($this->getAction()->getId());
                    if ($action == 'create') {
                        echo CHtml::submitButton('下一步>>', array('class'=>'btn btn-info pull-right'));
                    } elseif ($action == 'update') {
                        echo CHtml::submitButton('保存', array('class'=>'btn btn-info pull-right'));
                    }
                ?>
                <div class="col-md-2 pull-right" id="goto-next">
                    <?php echo CHtml::link('前往“结果字段”>>','javascript:void(0);',array('class'=>'btn btn-success pull-right', 'id'=>'goto-columns'));?>
                    <?php echo CHtml::link('前往“查询条件”>>','javascript:void(0);',array('class'=>'btn btn-success pull-right', 'id'=>'goto-conditions', 'style'=>'display:none;'));?>
                    <?php echo CHtml::link('前往“分组”>>','javascript:void(0);',array('class'=>'btn btn-success pull-right', 'id'=>'goto-group', 'style'=>'display:none;'));?>
                    <?php echo CHtml::link('前往“排序”>>','javascript:void(0);',array('class'=>'btn btn-success pull-right', 'id'=>'goto-order', 'style'=>'display:none;'));?>
                </div>
            </div>
        </div>
        <?php $this->endWidget(); ?>
    </div>
</div>
<ul class="nav nav-tabs"></ul>

<style>
<!--
.table > tbody > tr > td {
    padding: 6px;
    vertical-align: middle;
}
input[type="radio"], input[type="checkbox"] {
    margin-top: 0;
}
label {
    vertical-align: middle;
}
-->
</style>
<script type="text/javascript">
$(document).ready(function(){
<?php if (!empty($condition_json) && !empty($filters_json) && $condition_json!=='{}' && $filters_json!=='{}') { //修改时展示query builder?>
    $('#builder').queryBuilder({sortable: true,filters: <?php echo $filters_json;?>});
    $('#builder').queryBuilder('setRules', <?php echo $condition_json;?>);
<?php } else {?>
    data_source=$("#data_source").val();
    new_add=1;
    //展示query builder
    $.ajax({
        type: "POST",
        url: "/configure/ajaxqueryfilter",
        data: {data_source:data_source,new_add:new_add},//,table:table
        async: false,
        dataType: "json",//关键地方，设置响应数据类型为json
        success: function(response){
            $('#builder').queryBuilder('destroy');        //如果非第一次加载，由于前面的filter配置已生效，筛选列表已变更，所有要先将之前生成的query builder销毁
            $('#builder').queryBuilder({sortable: true,filters: response});  //再配置新的filter
        }
    });
<?php }?>

<?php if (!empty($tables_choosed)) {?>
    <?php 
        $loop = 0;
        $del_params = '';
        foreach ($tables_choosed as $table) {
            ++$loop;
            if ($loop > 1) {
                $edit = CHtml::Link('连接配置', 'javascript:tables_edit_join("'.$table['table_alias'].'")',array('id' => 'edit-join-'.$table['table_alias']));
                $del_params = '"'.$table['table_name'].'","'.$table['table_alias'].'"'.',0';
            } else {
                $edit = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                $del_params = '"'.$table['table_name'].'","'.$table['table_alias'].'"'.',1';
            }
    ?>
        var table_name = "<?php echo $table['table_name'];?>";
        var table_alias = "<?php echo $table['table_alias'];?>";
        var table_comment = "<?php echo array_key_exists($table['table_name'], $tables) ? $tables[$table['table_name']] : '';?>"
        
        data='<tr id="tables-'+table_alias+'"><td style="width: 22%"><div style="width: 100%;word-wrap:break-word;text-align:left;">'+table_name+
            '</div></td><td style="width: 22%"><div style="width: 100%;word-wrap:break-word;text-align:left;">'+table_alias+
            '</div></td><td style="width: 34%;text-align:left;">'+table_comment+
            '</td><td style="width: 22%">'+
            '<span style="margin:5px"><?php echo $edit;?></span>'+
            '<span style="margin:5px"><?php echo CHtml::Link('删除', 'javascript:tables_inverse_table('.$del_params.')',array('id' => 'inverse-table-'.$table['table_alias']));?></span></td></tr>';
        
        $("#tables-choosed").append(data);
    <?php }?>
    report_id = $("#report_id").val();
    data_source=$("#data_source").val();
    $.post("/configure/ajaxinittablerelations",{report_id:report_id,data_source:data_source},function(response,status){
        if (status=="success")
        {
            $("#tables-modals").empty();
            $("#tables-modals").append(response);
        }
    });
<?php }?>

<?php if (!empty($columns)) {?>
    <?php foreach ($columns as $column) {?>
        data = "<option value=\"<?php echo $column;?>\"><?php echo $column;?></option>";
        $('#columns_select').append(data);
    <?php }?>
<?php }?>

<?php if (!empty($columns_choosed)) {?>
    <?php foreach ($columns_choosed as $alias => $col) {
        $expression = explode(' as ', $col['expression']);
        $comment = Common::getCommentByTableColumn($expression[0]);
        $column = explode('.', $expression[0]);
        $column_name = isset($column[1]) ? $column[1] : '';
    ?>
        var alias = "<?php echo $alias;?>";
        var expression = "<?php echo $col['expression'];?>";
        var table_name = "<?php echo $col['table_name'];?>";
        var table_alias = "<?php echo $col['table_alias'];?>";
        var comment = "<?php echo $comment;?>";
        
        data='<tr id="columns-'+alias+'">'+
            '<td style="width: 15%"><div style="width: 100%;word-wrap:break-word;text-align:left;">'+alias+'</div></td>'+
            '<td style="width: 20%"><div style="width: 100%;word-wrap:break-word;text-align:left;">'+expression+'</div></td>'+
            '<td style="width: 15%"><div style="width: 100%;word-wrap:break-word;">'+table_name+'</div></td>'+
            '<td style="width: 15%"><div style="width: 100%;word-wrap:break-word;">'+table_alias+'</div></td>'+
            '<td style="width: 25%"><div style="width: 100%;word-wrap:break-word;text-align:left;">'+comment+'</div></td>'+
            '<td style="width: 10%"><?php echo CHtml::Link('删除', 'javascript:columns_inverse_column("'.$column_name.'","'.$alias.'")',array('id' => "inverse-column-$alias"));?></td>'+
            '</tr>';
        
        $("#columns-choosed").append(data);
    <?php }?>
<?php }?>

<?php if (!empty($distinct)) {?>
    $("#columns_distinct").prop('checked',true);
<?php }?>

<?php if (!empty($calculation)) {?>
    <?php foreach ($calculation as $alias => $cal) {?>
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

<?php if (!empty($group)) {?>
    $("#group_enable").prop('checked',true);
    
    <?php foreach ($group as $g) {
        $column = $g;
        $comment = Common::getCommentByTableColumn($column);
    ?>
        var column="<?php echo $column;?>";
        var comment="<?php echo $comment;?>";
        var data = "<tr>"+
            "<td style=\"width: 30%\"><div style=\"width: 100%;word-wrap:break-word;text-align:left;\">"+column+"</div></td>"+
            "<td style=\"width: 55%\"><div style=\"width: 100%;word-wrap:break-word;text-align:left;\">"+comment+"</div></td>"+
            "<td style=\"width: 15%\">"+
            "<i class=\"glyphicon glyphicon-arrow-up\"></i><span style=\"margin-right:10px\"><a href=\"javascript:void(0);\" onclick=\"move_up(this)\">上移</a></span>"+
            "<i class=\"glyphicon glyphicon-arrow-down\"></i><span style=\"margin-right:10px\"><a href=\"javascript:void(0);\" onclick=\"move_down(this)\">下移</a></span></td></tr>";
        
        $("#group-columns").append(data);
    <?php }?>
<?php }?>

<?php if (!empty($order)) {?>
    $("#order_normal").attr('checked',false);   //此处只有attr可以而prop无效，下面代码还有一处prop有效而attr无效的，同一个checkbox，见tables_inverse_table()
    <?php foreach ($order as $column => $or) {
        $column_id = str_replace('.', '-', $column);
    ?>
        column="<?php echo $column;?>";
        column_id="<?php echo $column_id;?>";
        
        order="<?php echo $or;?>";
        order_name="";
        if(order=="asc") order_name="升序";
        else if(order=="desc") order_name="降序";
        
        data="<tr id=\"order-column-"+column_id+"\"><td style=\"width: 60%\"><div style=\"width: 100%;word-wrap:break-word;text-align:left;\">"+column+"</div></td><td style=\"width: 15%\">"+order_name+
            "</td><td style=\"width: 25%\"><i class=\"glyphicon glyphicon-arrow-up\"></i><span style=\"margin-right:10px\"><a href=\"javascript:void(0);\" onclick=\"move_up(this)\">上移</a></span>"+
            "<i class=\"glyphicon glyphicon-arrow-down\"></i><span style=\"margin-right:10px\"><a href=\"javascript:void(0);\" onclick=\"move_down(this)\">下移</a></span>"+
            "<i class=\"glyphicon glyphicon-trash\"></i><span><a href=\"javascript:order_del_column(&quot;"+column_id+"&quot;);\">删除</a></span></td></tr>";
        
        $("#order-columns").append(data);
    <?php }?>
<?php }?>

    if($("#order_normal").attr('checked') == 'checked') {
        $("#columns_for_order").attr('disabled', true);
        $("#order_type :radio").attr('disabled', true);
        $("#add-order-column").attr('disabled', true);
        $("#order-columns tr").each(function(){
            id=$(this).attr('id');
            if(typeof(id)!='undefined' && id.indexOf('order-column-')==0) {
                $(this).remove();
            }
        });
    }
});
////////////////////////////////表格/列表行记录上下移动////////////////////////////////
//表格行记录上下移动，并高亮当前移动项
function move_up(obj){
    var current=$(obj).parent().parent().parent();
    var prev=current.prev();
    if(current.index()>1){
        current.insertBefore(prev);
    }
    
    var table = current.parent();
    table.children().each(function(){
        if($(this).children().first().get(0).tagName == 'TD'){
            $(this).css('background', '');
        }
    });
    current.css('background', 'none repeat scroll 0 0 #FFFF00');
}

function move_down(obj)
{
    var current=$(obj).parent().parent().parent();
    var next=current.next();
    if(next){
        current.insertAfter(next);
    }
    
    var table = current.parent();
    table.children().each(function(){
        if($(this).children().first().get(0).tagName == 'TD'){
            $(this).css('background', '');
        }
    });
    current.css('background', 'none repeat scroll 0 0 #FFFF00');
}
//END

//字段列表值上下移动
function listbox_order(action) {
    var size = $('#columns_select').find("option").size();
    var selsize = $('#columns_select').find("option:selected").size();
    if (size > 0 && selsize > 0) {
        $('#columns_select').find("option:selected").each(function(index, item) {
            if (action == "up") {
                $(item).prev().insertAfter($(item));
                return false;
            } else if (action == "down") {    //down时选中多个连靠则操作没效果
                $(item).next().insertBefore($(item));
                return false;
            }
        })
    }
    return false;
}

$('#up').click(function() {
    $('#columns_select').find("option:selected").each(function(index, item) {
        listbox_order("up");
    });
    return false;
});

$('#down').click(function() {
    $('#columns_select').find("option:selected").each(function(index, item) {
        listbox_order("down");
    });
    return false;
});
//END

////////////////////////////////////////////////////////////////////////////////////////////////

//删除选择的表
function tables_inverse_table(table,alias,is_main){
    if(is_main == 1){
        $.ajax({
            type: "POST",
            url: "/configure/ajaxinversetable",
            data: {table:table,alias:alias,is_main:1},
            async: false,
            success: function(response){
                //清除选择的表
                $("#tables-choosed tr").each(function(){
                    if($(this).children().get(0).tagName == 'TD'){
                        $(this).remove();
                    }
                });
                //清除表关联配置
                $("[id^=tables-join-modal-]").remove();
                
                //还原为不排重
                $("#columns_distinct").prop('checked',false);
                //清除选择的字段
                $("#columns-choosed tr").each(function(){
                    if($(this).children().get(0).tagName == 'TD'){
                        $(this).remove();
                    }
                });
                //清除计算字段
                $("#columns-calculation-list tr").each(function(){
                    if($(this).children().get(0).tagName == 'TD'){
                        $(this).remove();
                    }
                });
                //清除排序字段
                $("#columns_select").empty();
                
                //重置查询条件
                $('#builder').queryBuilder('destroy');
                $('#result').empty().addClass('hide');
                
                //禁用分组
                $("#group_enable").prop('checked',false);
                //清除分组字段
                $("#group-columns tr").each(function(){
                    if($(this).children().get(0).tagName == 'TD'){
                        $(this).remove();
                    }
                });
                
                //启用自然排序
                $("#order_normal").prop('checked',true);    //此处只有prop可以而attr无效，prop与attr的区别？据说jquery-1.6+的attr可以改变状态，但此处无效。
                $("#columns_for_order").attr('disabled', true);
                $("#order_type :radio").attr('disabled', true);
                $("#add-order-column").attr('disabled', true);
                $("#order-columns tr").each(function(){
                    if($(this).children().get(0).tagName == 'TD'){
                        $(this).remove();
                    }
                });
            }
        });
    } else {
        $.ajax({
            type: "POST",
            url: "/configure/ajaxinversetable",
            data: {table:table,alias:alias,is_main:0},
            async: false,
            success: function(response){
                $("#tables-join-modal-"+alias).remove();
                $("#tables-"+alias).remove();
            }
        });
    }
}

//编辑表关联
function tables_edit_join(alias){
    data_source=$("#data_source").val();
    $.ajax({
        type: "POST",
        url: "/configure/ajaxeditjoin",
        data: {data_source:data_source,alias:alias},
        async: false,
        success: function(response){
            if(response != '') {
                $("#tables-join-modal-"+alias).remove();
                $("#tables-modals").append(response);
            }
        }
    });
    
    $('#tables-join-modal-'+alias).modal();
}

//删除表关联
function tables_del_join_relation(alias,related_table_col){
    //console.log($("#join-relation-"+alias+"-"+related_table_col));
    $("#join-relation-"+alias+"-"+related_table_col).remove();
}

//删除选择的字段
function columns_inverse_column(column,alias){
    $.ajax({
        type: "POST",
        url: "/configure/ajaxinversecolumn",
        data: {column:column,alias:alias},
        async: false,
        success: function(response){
            $("#columns-"+alias).remove();
        }
    });
    
    if($("#all_columns").prop('checked') == true){
        $("#all_columns").prop('checked',false);
    }
}

//删除计算字段
function columns_calculation_del(alias){
    $.ajax({
        type: "POST",
        url: "/configure/ajaxinversecalculationcolumn",
        data: {alias:alias},
        async: false,
        success: function(response){
            $("#col-cal-list-"+alias).remove();
        }
    });
}

//获取结果字段
function columns_gather(){
    /* 
    在each代码块内不能使用break和continue,要实现break和continue的功能的话，要使用其它的方式 
    break----用return false;
    continue --用return ture;
     */
    var columns = new Array();
    $("#columns-choosed").find("tr").each(function(){
        var idx1 = 0;
        $(this).children().each(function(){
            ++idx1;
            if($(this).get(0).tagName == 'TD' && idx1 == 1){
                columns.push($(this).text());
                return false;
            }
        });
    });
    $("#columns-calculation-list").find("tr").each(function(){
        var idx2 = 0;
        $(this).children().each(function(){
            ++idx2;
            if($(this).get(0).tagName == 'TD' && idx2 == 1){
                columns.push($(this).text());
                return false;
            }
        });
    });
    //console.log(columns);
    
    $("#columns_select").empty();
    for(c in columns){
        var data = "<option value=\""+columns[c]+"\">"+columns[c]+"</option>";
        $("#columns_select").append(data);
    }
}

//删除排序字段
function order_del_column(column){
    $("#order-column-"+column).remove();
}

/////////////////////////////////////////////事件响应/////////////////////////////////////////////
//选择“表”
$("#tab-tables,#goto-tables").click(function(){
    if(!$("#tab-tables").hasClass("active")) {
        //切换标签页
        $("#tab-tables").addClass("active").siblings().removeClass("active");
        $("#step2-tables").show().siblings().hide();
        $("#goto-columns").show().siblings().hide();
        
        //location.hash = 'top';  //返回页顶，瞬间跳转，Chrome不支持
        $("body,html").animate({scrollTop:0},0); //返回页顶，平滑滚动
    }
});

//选择“结果字段”
$("#tab-columns,#goto-columns").click(function(){
    if(!$("#tab-columns").hasClass("active")) {
        data_source=$("#data_source").val();
        
        $("#columns-optional tr").each(function(){
            //除id外的另一种判断非标题行的方式
            if($(this).children().get(0).tagName == 'TD'){
                $(this).remove();
            }
        });
        
        $.ajax({
            type: "POST",
            url: "/configure/ajaxcolumns",
            data: {data_source:data_source},
            async: false,
            success: function(response){
                $("#columns-optional").append(response);
            }
        });
        
        $("#columns_calculation_col").load("/configure/ajaxcolumnsname",{data_source:data_source},function(response,status){
            if (status=="success")
            {
                $("#columns_calculation_col").empty();
                $("#columns_calculation_col").append(response);
            }
        });
        
        //切换标签页
        $("#tab-columns").addClass("active").siblings().removeClass("active");
        $("#step2-columns").show().siblings().hide();
        $("#goto-conditions").show().siblings().hide();
        
        //location.hash = 'top';  //返回页顶，瞬间跳转，Chrome不支持
        $("body,html").animate({scrollTop:0},0); //返回页顶，平滑滚动
        
        //如果编辑时已选择字段的数量与全部字段数量一致，则勾选“全部字段”
        var columns_count = 0;
        var columns_choosed_count = 0;
        $("#columns-optional tr").each(function(){
            if($(this).children().get(0).tagName == 'TD'){
                ++columns_count;
            }
        });
        $("#columns-choosed tr").each(function(){
            if($(this).children().get(0).tagName == 'TD'){
                ++columns_choosed_count;
            }
        });
        if(columns_count == columns_choosed_count && columns_count>0){
            $("#all_columns").prop('checked',true);
        }
    }
});

//选择“查询条件”
$("#tab-conditions,#goto-conditions").click(function(){
    if(!$("#tab-conditions").hasClass("active")) {
        data_source=$("#data_source").val();
        //展示query builder
        $.ajax({
            type: "POST",
            url: "/configure/ajaxqueryfilter",
            data: {data_source:data_source},
            async: false,
            dataType: "json",//关键地方，设置响应数据类型为json
            success: function(response){
                $('#builder').queryBuilder('destroy');        //如果非第一次加载，由于前面的filter配置已生效，筛选列表已变更，所有要先将之前生成的query builder销毁
                $('#builder').queryBuilder({sortable: true,filters: response});  //再配置新的filter
            }
        });
        
        //切换标签页
        $("#tab-conditions").addClass("active").siblings().removeClass("active");
        $("#step2-conditions").show().siblings().hide();
        $("#goto-group").show().siblings().hide();

        //location.hash = 'top';  //返回页顶，瞬间跳转，Chrome不支持
        $("body,html").animate({scrollTop:0},0); //返回页顶，平滑滚动
    }
});

//选择“分组”
$("#tab-group,#goto-group").click(function(){
    if(!$("#tab-group").hasClass("active")) {
        //判断是否配置了“计算字段”，没有配置则“启用分组”无效化
        var columns = new Array();
        $("#columns-calculation-list").find("tr").each(function(){
            var idx = 0;
            $(this).children().each(function(){
                ++idx;
                if($(this).get(0).tagName == 'TD' && idx == 1){
                    columns.push($(this).text());
                    return false;
                }
            });
        });
        
        if(columns.length > 0){
            $("#group_enable").attr("disabled",false);
        } else {
            $("#group-columns tr").each(function(){
                if($(this).children().get(0).tagName == 'TD'){
                    $(this).remove();
                }
            });
            $("#group_enable").prop("checked",false);
            $("#group_enable").attr("disabled",true);
        }
        //切换标签页
        $("#tab-group").addClass("active").siblings().removeClass("active");
        $("#step2-group").show().siblings().hide();
        $("#goto-order").show().siblings().hide();
        
        //location.hash = 'top';  //返回页顶，瞬间跳转，Chrome不支持
        $("body,html").animate({scrollTop:0},0); //返回页顶，平滑滚动
    }
});

//选择“排序”
$("#tab-order,#goto-order").click(function(){
    if(!$("#tab-order").hasClass("active")) {
        var columns = new Array();
        $("#columns-calculation-list").find("tr").each(function(){
            var idx = 0;
            $(this).children().each(function(){
                ++idx;
                if($(this).get(0).tagName == 'TD' && idx == 1){
                    columns.push($(this).text());
                    return false;
                }
            });
        });
        
        data_source=$("#data_source").val();
        $("#columns_for_order").load("/configure/ajaxcolumnsname",{data_source:data_source},function(response,status){
            if (status=="success")
            {
                $("#columns_for_order").empty();
                $("#columns_for_order").append(response);
                if(columns.length > 0){
                    for(c in columns){
                        var data = "<option value=\""+columns[c]+"\">"+columns[c]+"</option>";
                        $("#columns_for_order").append(data);
                    }
                }
            }
        });
        
        //切换标签页
        $("#tab-order").addClass("active").siblings().removeClass("active");
        $("#step2-order").show().siblings().hide();
        $("#goto-order").hide().siblings().hide();
        
        //location.hash = 'top';  //返回页顶，瞬间跳转，Chrome不支持
        $("body,html").animate({scrollTop:0},0); //返回页顶，平滑滚动
    }
});

//取数据源的数据表
$("#data_source").change(function(){
    $("#tables-choosed tr").each(function(){
        //除id外的另一种判断非标题行的方式
        if($(this).children().get(0).tagName == 'TD'){
            $(this).remove();
        }
    });
    
    $("#tables-modals").empty();
    
    $('#builder').queryBuilder('destroy');
    $('#result').addClass('hide');
    
    data_source=$("#data_source").val();
    $("#table-available").load("/configure/ajaxtables",{data_source:data_source},function(response,status){
        if (status=="success")
        {
            $("#table-available").empty();
            $("#table-available").append(response);
        }
    });
});

//选择表
$("#table-available").on('click','[id^=choose-table-]',function(){
    var id = $(this).attr('id');    //获取当前对象id属性 
    var arr = id.split("-");
    var table = arr[2];
    var comment = $(this).parent().prev().text();
    
    $(this).parent().parent().css('background', 'none repeat scroll 0 0 #FFFF00');
    
    $.ajax({
        type: "POST",
        url: "/configure/ajaxchoosetable",
        data: {table:table,comment:comment},
        async: false,
        success: function(response){
            $("#tables-choosed").append(response);
        }
    });
});

//选择字段
$("#columns-optional").on('click','[id^=choose-column-]',function(){
    var id = $(this).attr('id');    //获取当前对象id属性 
    var arr = id.split("-");
    var alias = arr[2];
    var column = arr[3];
    
    $(this).parent().parent().css('background', 'none repeat scroll 0 0 #FFFF00');
    
    $.ajax({
        type: "POST",
        url: "/configure/ajaxchoosecolumn",
        data: {alias:alias,column:column},
        async: false,
        success: function(response){
            if(response == "exist") {
                $("body,html").animate({scrollTop:0},0); //返回页顶，平滑滚动
                var message = '<div class="alert alert-danger"><a class="close" data-dismiss="alert">×</a>字段别名已被计算字段占用，请修改计算字段</div>';
                //$("#main-body").prepend(message);
                $("#alert-area").empty();
                $("#alert-area").append(message);
            } else {
                $("#columns-choosed").append(response);
            }
        }
    });
});

//点击“全部字段”
$("#all_columns").click(function(){
    all_columns=$("input[name=all_columns]:checked").val();
    data_source=$("#data_source").val();
    $("#columns-choosed tr").each(function(){
        //除id外的另一种判断非标题行的方式
        if($(this).children().get(0).tagName == 'TD'){
            $(this).remove();
        }
    });
    
    if(all_columns==1) {
        $.ajax({
            type: "POST",
            url: "/configure/ajaxchooseallcolumns",
            data: {data_source:data_source},
            async: false,
            success: function(response){
                if(response == "exist") {
                    $("body,html").animate({scrollTop:0},0); //返回页顶，平滑滚动
                    var message = '<div class="alert alert-danger"><a class="close" data-dismiss="alert">×</a>字段别名已被计算字段占用，请修改计算字段</div>';
                    //$("#main-body").prepend(message);
                    $("#alert-area").empty();
                    $("#alert-area").append(message);
                } else {
                    $("#columns-choosed").append(response);
                }
            }
        });
    } else {
        $.ajax({
            type: "POST",
            url: "/configure/ajaxinverseallcolumns",
            async: false,
            success: function(response){
                //清除计算字段
                $("#columns-calculation-list tr").each(function(){
                    if($(this).children().get(0).tagName == 'TD'){
                        $(this).remove();
                    }
                });
                //清除排序字段
                $("#columns_select").empty();
                
                $("#columns-optional tr").each(function(){
                    if($(this).children().get(0).tagName == 'TD'){
                        $(this).css('background', '');
                    }
                });
            }
        });
    }
});

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
    if($("#columns-calculation-normal").attr('checked') == 'checked'){
        var col = $("#columns_calculation_col").val();
        var func = $("#columns_calculation_function").val();
        var distinct = $("input[name=columns_calculation_distinct]:checked").val();
        if(func == "count") {
            var count = $("#columns_calculation_count :checked").val();
        }
        
        if(typeof(count)!='undefined' && count=="all"){
            var alias = "count_all";<?php //COUNT_ALL?>
            var expression = "count(*) as "+alias;
        } else {
            var expression = func+"(";
            var alias = func; <?php //.toUpperCase()?>
            if(typeof(distinct)!='undefined' && distinct==1){
                alias = alias+"_dst";
                expression = expression+"distinct ";
            }
            alias = alias+"_"+col.replace(".","_");
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
        
        $.ajax({
            type: "POST",
            url: "/configure/ajaxaddcalculationcolumn",
            data: {alias:alias},
            async: false,
            success: function(response){
                if(response == 'not exist'){
                    data="<tr id=\"col-cal-list-"+alias+"\"><td style=\"width: 25%\"><div style=\"width: 100%;word-wrap:break-word;text-align:left;\">"+alias+
                        "</div></td><td style=\"width: 45%\"><div style=\"width: 100%;word-wrap:break-word;text-align:left;\">"+expression+
                        "</div></td><td style=\"width: 15%\">"+func_name+
                        "</td><td style=\"width: 15%\"><span><a href=\"javascript:columns_calculation_del(&quot;"+alias+"&quot;);\">删除</a></span></td></tr>";
                } else {
                    $("body,html").animate({scrollTop:0},0); //返回页顶，平滑滚动
                    var message = '<div class="alert alert-danger"><a class="close" data-dismiss="alert">×</a>字段别名已被使用，请更换</div>';
                    //$("#main-body").prepend(message);
                    $("#alert-area").empty();
                    $("#alert-area").append(message);
                }
            }
        });
        
    } else if($('#columns-calculation-custom').attr('checked') == 'checked'){
        var custom = $("#columns_calculation_custom").val();//.toLowerCase()不可以统一转换为小写，因为某些函数参数区分大小写，如，DATE_FORMAT(FROM_UNIXTIME(report_date),'%Y-%m-%d')
        if(custom.indexOf(';')>=0 || custom.indexOf('#')>=0 || custom.indexOf('-- ')>=0 || custom.indexOf('/*')>=0){
            $("body,html").animate({scrollTop:0},0); //返回页顶，平滑滚动
            var message = '<div class="alert alert-danger"><a class="close" data-dismiss="alert">×</a>查询sql的语句有误，包含结束或注释字符。</div>';
            //$("#main-body").prepend(message);
            $("#alert-area").empty();
            $("#alert-area").append(message);
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
                    
                    $.ajax({
                        type: "POST",
                        url: "/configure/ajaxaddcalculationcolumn",
                        data: {alias:alias},
                        async: false,
                        success: function(response){
                            if(response == 'not exist'){
                                data="<tr id=\"col-cal-list-"+alias+"\"><td style=\"width: 25%\"><div style=\"width: 100%;word-wrap:break-word;text-align:left;\">"+alias+
                                    "</div></td><td style=\"width: 45%\"><div style=\"width: 100%;word-wrap:break-word;text-align:left;\">"+expression+
                                    "</div></td><td style=\"width: 15%\">--</td><td style=\"width: 15%\"><span><a href=\"javascript:columns_calculation_del(&quot;"+alias+"&quot;);\">删除</a></span></td></tr>";
                                $("#columns_calculation_custom").val('');
                            } else {
                                $("body,html").animate({scrollTop:0},0); //返回页顶，平滑滚动
                                var message = '<div class="alert alert-danger"><a class="close" data-dismiss="alert">×</a>字段别名已被使用，请更换</div>';
                                //$("#main-body").prepend(message);
                                $("#alert-area").empty();
                                $("#alert-area").append(message);
                            }
                        }
                    });
                } else {
                    $("body,html").animate({scrollTop:0},0); //返回页顶，平滑滚动
                    var message = '<div class="alert alert-danger"><a class="close" data-dismiss="alert">×</a>自定义计算字段缺少“ as ”或“ AS ”，注意大小写</div>';
                    //$("#main-body").prepend(message);
                    $("#alert-area").empty();
                    $("#alert-area").append(message);
                }
            } else {
                $("body,html").animate({scrollTop:0},0); //返回页顶，平滑滚动
                var message = '<div class="alert alert-danger"><a class="close" data-dismiss="alert">×</a>自定义计算字段为空</div>';
                //$("#main-body").prepend(message);
                $("#alert-area").empty();
                $("#alert-area").append(message);
            }
        }
    }
    
    if(data.length > 0){
        $("#columns-calculation-list").append(data);
    }
});

//添加排序字段
$("#add-order-column").click(function(){
    column=$("#columns_for_order").val();
    
    if(column != null) {
        column_id=column.replace(".","-");
        
        order=$("input[name=order_type]:checked").val();
        order_name="";
        if(order=="asc") order_name="升序";
        else if(order=="desc") order_name="降序";
        
        data="<tr id=\"order-column-"+column_id+"\"><td style=\"width: 60%\"><div style=\"width: 100%;word-wrap:break-word;text-align:left;\">"+column+"</div></td><td style=\"width: 15%\">"+order_name+
            "</td><td style=\"width: 25%\"><i class=\"glyphicon glyphicon-arrow-up\"></i><span style=\"margin-right:10px\"><a href=\"javascript:void(0);\" onclick=\"move_up(this)\">上移</a></span>"+
            "<i class=\"glyphicon glyphicon-arrow-down\"></i><span style=\"margin-right:10px\"><a href=\"javascript:void(0);\" onclick=\"move_down(this)\">下移</a></span>"+
            "<i class=\"glyphicon glyphicon-trash\"></i><span><a href=\"javascript:order_del_column(&quot;"+column_id+"&quot;);\">删除</a></span></td></tr>";
        
        $("#order-columns").append(data);
    }
});

//点击“启用分组”
$("#group_enable").click(function(){
    group_enable=$("input[name=group_enable]:checked").val();
    if(group_enable==1) {
        $("#columns-choosed").find("tr").each(function(){
            var columns = new Array();
            var idx = 0;
            $(this).children().each(function(){
                ++idx;
                if($(this).get(0).tagName == 'TD' && idx < 6){
                    columns.push($(this).text());
                }
            });
            
            if(columns.length>0){
                //console.log(columns);
                var arr = columns[1].split(" as ");
                var col = arr[0];
                var data = "<tr>"+
                    "<td style=\"width: 30%\"><div style=\"width: 100%;word-wrap:break-word;text-align:left;\">"+col+"</div></td>"+
                    "<td style=\"width: 55%\"><div style=\"width: 100%;word-wrap:break-word;text-align:left;\">"+columns[4]+"</div></td>"+
                    "<td style=\"width: 15%\">"+
                    "<i class=\"glyphicon glyphicon-arrow-up\"></i><span style=\"margin-right:10px\"><a href=\"javascript:void(0);\" onclick=\"move_up(this)\">上移</a></span>"+
                    "<i class=\"glyphicon glyphicon-arrow-down\"></i><span style=\"margin-right:10px\"><a href=\"javascript:void(0);\" onclick=\"move_down(this)\">下移</a></span></td></tr>";
                
                $("#group-columns").append(data);
            }
        });
    } else {
        $("#group-columns tr").each(function(){
            if($(this).children().get(0).tagName == 'TD'){
                $(this).remove();
            }
        });
    }
});

//点击“自然排序”
$("#order_normal").click(function(){
    order_normal=$("input[name=order_normal]:checked").val();
    if(order_normal==1) {
        $("#columns_for_order").attr('disabled', true);
        $("#order_type :radio").attr('disabled', true);
        $("#add-order-column").attr('disabled', true);
        $("#order-columns tr").each(function(){
            id=$(this).attr('id');
            if(typeof(id)!='undefined' && id.indexOf('order-column-')==0) {
                $(this).remove();
            }
        });
    } else {
        $("#order_normal").prop('checked',false);
        $("#columns_for_order").attr('disabled', false);
        $("#order_type :radio").attr('disabled', false);
        $("#add-order-column").attr('disabled', false);
    }
});

//query builder
//reset builder
$('.reset').on('click', function() {
    $('#builder').queryBuilder('reset');
    $('#result').empty().addClass('hide');
});

//get rules
$('.parse-json').on('click', function() {
    var res = $('#builder').queryBuilder('getRules');
    $('#result').removeClass('hide')
        .find('pre').html(
            JSON.stringify(res, null, 2)
    );
});

$('.parse-sql').on('click', function() {
    var res = $('#builder').queryBuilder('getSQL', $(this).data('stmt'));
        $('#result').removeClass('hide')
            .find('pre').html(
                res.sql + (res.params ? '\n\n' + JSON.stringify(res.params, null, 2) : '')
    );
});
//END

//提交前对部分参数赋值
$('#form').submit(function(){
    //select赋值需放在queryBuilder之前否则可能赋值失败。由于当没有配置查询条件时，queryBuilder构造失败会跳过后续代码直接提交表单（现象如此，具体原因尚未查明）
    
    //表选择
    var tables_error = false;
    var tables = new Array();
    $("#tables-choosed").find("tr").each(function(index,item){
        if(index > 1){
            var table_col = new Array();
            var idx1 = 0;
            $(this).children().each(function(){
                ++idx1;
                if($(this).get(0).tagName == 'TD' && idx1 < 3){
                    table_col.push($(this).text());
                }
            });
            
            if(table_col.length > 0){
                var table_name = table_col[0];
                var table_alias = table_col[1];
                var join_type = $("#join-type-"+table_alias).val();
                var related_table = $("#related-table-"+table_alias).val();
                var related_condition = new Array();
                
                $("#join-relation-"+table_alias).find("tr").each(function(){
                    var related_col = new Array();
                    var idx2 = 0;
                    $(this).children().each(function(){
                        ++idx2;
                        if($(this).get(0).tagName == 'TD' && idx2 < 4){
                            related_col.push($(this).text());
                        }
                    });
                    if(related_col.length > 0){
                        var col = {
                                'self_column':related_col[0],
                                'operator':related_col[1],
                                'related_column':related_col[2],
                        };
                        related_condition.push(col);
                    }
                });
                
                //检查连接类型、关联表、关联字段是否配置
                if(typeof(join_type)=="undefined" || typeof(related_table)=="undefined" || related_condition.length==0){
                    tables_error = true;
                    return false;
                }
                var table_info = {
                        'join_type':join_type,
                        'table_name':table_name,
                        'table_alias':table_alias,
                        'related_table_alias':related_table,
                        'related_condition':related_condition
                };
                tables.push(table_info);
            }
        } else {
            var column = new Array();
            var idx = 0;
            $(this).children().each(function(){
                ++idx;
                if($(this).get(0).tagName == 'TD' && idx < 3){
                    column.push($(this).text());
                }
            });
            if(column.length > 0){
                var col = {
                        'table_name':column[0],
                        'table_alias':column[1],
                };
                tables.push(col);
            }
        }
    });
    
    if(tables.length > 0){
        var json = JSON.stringify(tables);  //JSON.stringify(order_columns, null, 2)对json结构进行了美化
        $("#tables").val(json);
    } else {
        $("body,html").animate({scrollTop:0},0); //返回页顶，平滑滚动
        var message = '<div class="alert alert-danger"><a class="close" data-dismiss="alert">×</a>没有配置数据表！</div>';
        //$("#main-body").prepend(message);
        $("#alert-area").empty();
        $("#alert-area").append(message);
        return false;
    }
    
    if(tables_error == true){
        $("body,html").animate({scrollTop:0},0); //返回页顶，平滑滚动
        var message = '<div class="alert alert-danger"><a class="close" data-dismiss="alert">×</a>存在未配置关联信息的表，请先完成配置！</div>';
        //$("#main-body").prepend(message);
        $("#alert-area").empty();
        $("#alert-area").append(message);
        return false;
    }
    
    //结果字段 - 剔除重复结果
    var is_distinct=$("input[name=columns_distinct]:checked").val();
    if(is_distinct==1) {
        $("#is_distinct").val(1);
    } else {
        $("#is_distinct").val(0);
    }
    
    //结果字段 - 字段选择
    var columns_choosed = new Array();
    $("#columns-choosed").find("tr").each(function(){
        var column = new Array();
        var idx = 0;
        $(this).children().each(function(){
            ++idx;
            if($(this).get(0).tagName == 'TD' && idx < 5){
                column.push($(this).text());
            }
        });
        if(column.length > 0){
            var col = {
                    'col_alias':column[0],
                    'expression':column[1],
                    'table_name':column[2],
                    'table_alias':column[3],
            };
            columns_choosed.push(col);
        }
    });
    if(columns_choosed.length > 0){
        var json = JSON.stringify(columns_choosed);  //JSON.stringify(order_columns, null, 2)对json结构进行了美化
        $("#columns_choosed").val(json);
    }
    
    //结果字段 - 字段计算
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
    
    //结果字段 - 字段排序
    var columns = new Array();
    $('#columns_select').find("option").each(function(index, item) {
        columns.push(item.value);
    });
    if(columns.length > 0){
        var json = JSON.stringify(columns);
        $('#columns').val(json);    //查询字段参数 
    } else {
        $("body,html").animate({scrollTop:0},0); //返回页顶，平滑滚动
        var message = '<div class="alert alert-danger"><a class="close" data-dismiss="alert">×</a>“结果字段”为必须配置项，请前往配置！</div>';
        //$("#main-body").prepend(message);
        $("#alert-area").empty();
        $("#alert-area").append(message);
        return false;
    }
    
    //分组字段
    var group_enable=$("input[name=group_enable]:checked").val();
    if(group_enable==1) {
        var group_columns = new Array();
        $("#group-columns").find("tr").each(function(){
            var idx = 0;
            $(this).children().each(function(){
                ++idx;
                if($(this).get(0).tagName == 'TD' && idx == 1){
                    group_columns.push($(this).text());
                    return false;
                }
            });
        });
        if(group_columns.length > 0){
            var json = JSON.stringify(group_columns);
            $("#group_columns").val(json);
        }
    }
    
    //排序字段
    var order_normal=$("input[name=order_normal]:checked").val();
    if(typeof(order_normal)=='undefined') {
        var order_columns = new Array();
        $("#order-columns").find("tr").each(function(){
            var column = new Array();
            var idx = 0;
            $(this).children().each(function(){
                ++idx;
                if($(this).get(0).tagName == 'TD' && idx < 3){
                    column.push($(this).text());
                }
            });
            if(column.length > 0){
                var order = "";
                if(column[1] == "升序"){
                    order = "asc";
                }else if(column[1] == "降序"){
                    order = "desc";
                }
                var order_column = {
                        'column':column[0],
                        'order':order
                };
                order_columns.push(order_column);
            }
        });
        if(order_columns.length > 0){
            var json = JSON.stringify(order_columns);  //JSON.stringify(order_columns, null, 2)对json结构进行了美化
            $("#order_columns").val(json);
        }
    }
    
    //查询条件
    var res = $('#builder').queryBuilder('getSQL', false);    //return false;改变位置测试是否禁止提交
    //var sql = res.sql + (res.params ? '\n\n' + JSON.stringify(res.params, null, 2) : '');
    var sql = res.sql + (res.params ? JSON.stringify(res.params) : '');
    //console.log(sql);
    $('#condition').val(sql);    //查询条件参数
    
    var res_json = $('#builder').queryBuilder('getRules');
    var json = JSON.stringify(res_json);
    $('#condition_json').val(json);    //查询条件参数JSON格式，用于修改时展示query builder
    
    return true;
});

//配合“测试”按钮使用
function test(){

}

</script>
