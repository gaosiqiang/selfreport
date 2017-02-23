<?php $this->widget('ConfigureNav'); ?>
<div class="table-container">
    <div class="form-container-site" style="background:transparent;padding-top:0">
        <h2>添加数据源</h2>
        <?php $form=$this->beginWidget('CActiveForm',array(
            'htmlOptions'=>array('class'=>'well form-horizontal')
        )); ?>
        <fieldset>
            <div class="form-group">
                <?php echo $form->labelEx($model,'name',array('class'=>'col-sm-3 control-label')); ?>
                <div class="col-sm-6">
                    <?php echo $form->textField($model,'name',array('class'=>'form-control','placeholder'=>'数据源名称(必填)')); ?>&nbsp;&nbsp;
                </div>
                <div class="col-sm-3">
                    <span class="help-inline"><?php if($model->hasErrors('name')) echo $model->getError('name');?></span>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'server_ip',array('class'=>'col-sm-3 control-label')); ?>
                <div class="col-sm-6">
                    <?php echo CHtml::textField('DataSource[server_ip]',long2ip($model->server_ip),array('id'=>'DataSource_server_ip','class'=>'form-control','placeholder'=>'服务器IP(必填)'));?>
                    <?php //echo $form->textField($model,'server_ip',array('class'=>'form-control')); ?>&nbsp;&nbsp;
                </div>
                <div class="col-sm-3">
                    <span class="help-inline"><?php if($model->hasErrors('server_ip')) echo $model->getError('server_ip');?></span>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'database',array('class'=>'col-sm-3 control-label')); ?>
                <div class="col-sm-6">
                    <?php echo $form->textField($model,'database',array('class'=>'form-control','placeholder'=>'数据库(必填)')); ?>&nbsp;&nbsp;
                </div>
                <div class="col-sm-3">
                    <span class="help-inline"><?php if($model->hasErrors('database')) echo $model->getError('database');?></span>
                </div>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model,'port',array('class'=>'col-sm-3 control-label')); ?>
                <div class="col-sm-6">
                    <?php echo $form->textField($model,'port',array('class'=>'form-control','placeholder'=>'端口号(必填)默认3306')); ?>&nbsp;&nbsp;
                </div>
                <div class="col-sm-3">
                    <span class="help-inline"><?php if($model->hasErrors('port')) echo $model->getError('port');?></span>
                </div>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model,'charset',array('class'=>'col-sm-3 control-label')); ?>
                <div class="col-sm-6">
                    <?php echo $form->dropDownList($model,'charset',array('utf8'=>'utf8'),array('class'=>'form-control')); ?>&nbsp;&nbsp;
                </div>
                <div class="col-sm-3">
                    <span class="help-inline"><?php if($model->hasErrors('charset')) echo $model->getError('charset');?></span>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'username',array('class'=>'col-sm-3 control-label')); ?>
                <div class="col-sm-6">
                    <?php echo $form->textField($model,'username',array('class'=>'form-control','placeholder'=>'用户名(必填)')); ?>&nbsp;&nbsp;
                </div>
                <div class="col-sm-3">
                    <span class="help-inline"><?php if($model->hasErrors('username')) echo $model->getError('username');?></span>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'password',array('class'=>'col-sm-3 control-label')); ?>
                <div class="col-sm-6">
                    <?php echo CHtml::passwordField('DataSource[password]','',array('id'=>'DataSource_password','class'=>'form-control','placeholder'=>'密码(必填)'));?>
                    <?php //echo $form->passwordField($model,'password',array('class'=>'form-control')); ?>&nbsp;&nbsp;
                </div>
                <div class="col-sm-3">
                    <span class="help-inline"><?php if($model->hasErrors('password')) echo $model->getError('password');?></span>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-8 pull-right">
                    <?php echo CHtml::submitButton('提交',array('class'=>'btn btn-success','style'=>'width:125px;')); ?>
                </div>
            </div>
        </fieldset>
        <?php $this->endWidget(); ?>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function(){
    var ip = $("#DataSource_server_ip").val();
    if(ip == '0.0.0.0') {
        $("#DataSource_server_ip").val('');
    }
});
</script>