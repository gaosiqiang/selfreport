<ul class="nav nav-tabs"></ul>
<div class="page-header header-container" style="padding: 15px 0 0">
    <div class="row">
        <?php $form=$this->beginWidget('CActiveForm', array(
            'action'=>Yii::app()->createUrl($this->route,array('step'=>1)),
            'htmlOptions'=>array('class'=>'form-horizontal','id'=>'form')
        )); ?>
        <?php echo CHtml::hiddenField('report_id', $report_id);?>
        <div class="form-group">
            <label class="col-sm-1 control-label" style="width:12%">报表名称：</label>
            <div class="col-sm-10">
                <?php echo CHtml::textField('report_name',$report_name,array('class'=>'form-control', 'style'=>'width:200px')); ?>
            </div>
        </div>

        <!--        <div class="form-group">-->
        <!--            <label class="col-sm-1 control-label" style="width:12%">归属平台：</label>-->
        <!--            <div class="col-sm-10">-->
        <!--            --><?php //echo CHtml::dropDownList('platform',$platform,Common::addTitleToList(Menu::$platforms),array('class'=>'form-control', 'style'=>'width:200px')); ?>
        <!--            </div>-->
        <!--        </div>-->

        <div class="form-group">
            <label class="col-sm-1 control-label" style="width:12%">父级菜单：</label>
            <div class="col-sm-10">
                <?php echo CHtml::dropDownList('first_grade',$first_grade,$first_menus,array('class'=>'form-control', 'style'=>'width:200px;display:inline;')); ?>
                <?php echo CHtml::dropDownList('second_grade',$second_grade,$second_menus,array('class'=>'form-control', 'style'=>'width:200px;display:inline;')); ?>
                <?php echo CHtml::dropDownList('third_grade',$third_grade,$third_menus,array('class'=>'form-control', 'style'=>'width:200px;display:inline;')); ?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-1 control-label" style="width:12%">&nbsp;</label>
            <div class="col-sm-10">
                <div class="checkbox" style="padding-top: 0">
                    <label>
                        <?php echo CHtml::checkBox('tab_state',false,array('value'=>'2','disabled'=>true));?>展示为标签页
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-9">
                <?php
                $action = strtolower($this->getAction()->getId());
                if ($action == 'create') {
                    echo CHtml::submitButton('下一步>>', array('class'=>'btn btn-info'));
                } elseif ($action == 'update') {
                    echo CHtml::submitButton('保存', array('class'=>'btn btn-info'));
                }
                ?>
            </div>
        </div>
        <?php $this->endWidget(); ?>
    </div>
</div>
<ul class="nav nav-tabs"></ul>

<script type="text/javascript">




            $(document).ready(function(){
                tab_state=<?php echo $tab_state?>;
                second_grade=<?php echo empty($second_grade) ? 0 : $second_grade;?>;

                if(second_grade > 0) {
                    $("#tab_state").attr('disabled',false);
                }
                if(tab_state == 2) {
                    $("#tab_state").attr('checked','checked');
                }
            });

            //取平台一级菜单（模块）
            $("#platform").change(function(){
                $("#tab_state").attr('disabled',true);

                $("#first_grade").empty();
                $("#first_grade").append('<option value="">请选择一级菜单</option>');
                $("#second_grade").empty();
                $("#second_grade").append('<option value="">请选择二级菜单</option>');
                $("#third_grade").empty();
                $("#third_grade").append('<option value="">请选择三级菜单</option>');

                platform=9;
                $("#first_grade").load("/configure/ajaxmenugrade",{platform:platform,grade:1},function(response,status){
                    if (status=="success")
                    {
                        $("#first_grade").empty();
                        $("#first_grade").append(response);
                    }
                });
            });

            //取平台二级菜单
            $("#first_grade").change(function(){
                $("#tab_state").attr('disabled',true);

                $("#third_grade").empty();
                $("#third_grade").append('<option value="">请选择三级菜单</option>');

                platform=9;
                parent_id=$("#first_grade").val();
                $("#second_grade").load("/configure/ajaxmenugrade",{platform:platform,grade:2,parent_id:parent_id},function(response,status){
                    if (status=="success")
                    {
                        $("#second_grade").empty();
                        $("#second_grade").append(response);
                    }
                });
            });

            //取平台三级菜单
            $("#second_grade").change(function(){
                platform=9;
                parent_id=$("#second_grade").val();

                if(parent_id != '' && parent_id > 0) {
                    $("#tab_state").attr('disabled',false);
                } else {
                    $("#tab_state").attr('disabled',true);
                }

                $("#third_grade").load("/configure/ajaxmenugrade",{platform:platform,grade:3,parent_id:parent_id},function(response,status){
                    if (status=="success")
                    {
                        $("#third_grade").empty();
                        $("#third_grade").append(response);
                    }
                });
            });


</script>