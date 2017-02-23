<?php
$this->pageTitle=Yii::app()->name . ' - 修改密码';
?>
<div class="form-container-site" style="background:transparent">
    <h1>修改密码</h1>
    <div class="alert alert-info fade in">如果不想修改，请保持空白</div>
    <?php $form=$this->beginWidget('CActiveForm',array(
        'htmlOptions'=>array('class'=>'well form-horizontal')
    )); ?>
    <fieldset>
        <div class="form-group">
            <?php echo $form->labelEx($model,'password',array('class'=>'col-sm-3 control-label')); ?>
            <div class="col-sm-6">
                <?php echo $form->passwordField($model,'password',array('class'=>'form-control')); ?>&nbsp;&nbsp;
            </div>
            <div class="col-sm-3">
                <span class="help-inline"><?php if($model->hasErrors('password')) echo $model->getError('password');?></span>
            </div>
        </div>
        <div class="form-group">
            <?php echo $form->labelEx($model,'newPassword',array('class'=>'col-sm-3 control-label')); ?>
            <div class="col-sm-6">
                <?php echo $form->passwordField($model,'newPassword',array('class'=>'form-control')); ?>&nbsp;&nbsp;
            </div>
            <div class="col-sm-3">
                <span class="help-inline"><?php if($model->hasErrors('newPassword')) echo $model->getError('newPassword');?></span>
            </div>
        </div>
        <div class="form-group">
            <?php echo $form->labelEx($model,'verifyPassword',array('class'=>'col-sm-3 control-label')); ?>
            <div class="col-sm-6">
                <?php echo $form->passwordField($model,'verifyPassword',array('class'=>'form-control')); ?>&nbsp;&nbsp;
            </div>
            <div class="col-sm-3">
                <span class="help-inline"><?php if($model->hasErrors('verifyPassword')) echo $model->getError('verifyPassword');?></span>
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