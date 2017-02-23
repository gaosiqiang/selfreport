<?php $this->widget('AdminNav'); ?>
<div class="page-header header-container">
    <div style="margin-top: 5px;margin-bottom: 10px;line-height: 1;text-align: left;text-shadow: 1px 2px 3px #ddd;color :#428bca;font-size: 1.05em;">
        <span class="glyphicon glyphicon-leaf" aria-hidden="true" style="top: 2px"></span>编辑用户
    </div>
    <?php $form=$this->beginWidget('CActiveForm',array(
        'htmlOptions'=>array('class'=>'well form-horizontal')
    )); ?>
    <fieldset>
        <div class="form-group">
            <?php echo $form->labelEx($model,'username',array('class'=>'col-sm-4 control-label')); ?>
            <div class="col-sm-5">
                <?php echo $form->textField($model,'username',array('readonly'=>true,'class'=>'form-control')); ?>
            </div>
        </div>
        <div class="form-group">
            <?php echo $form->labelEx($model,'email',array('class'=>'col-sm-4 control-label')); ?>
            <div class="col-sm-5">
                <?php echo $form->textField($model,'email',array('class'=>'form-control')); ?>
            </div>
        </div>
        <div class="form-group">
            <?php echo $form->labelEx($model,'mobile',array('class'=>'col-sm-4 control-label')); ?>
            <div class="col-sm-5">
                <?php echo $form->textField($model,'mobile',array('class'=>'form-control')); ?>
            </div>
            <div class="col-sm-3">
                <span class="help-inline"><?php if($model->hasErrors('mobile')) echo $model->getError('mobile');?></span>
            </div>
        </div>
        <div class="form-group">
            <?php echo $form->labelEx($model,'password',array('class'=>'col-sm-4 control-label')); ?>
            <div class="col-sm-5">
                <?php echo CHtml::passwordField('password','',array('class'=>'form-control', 'autocomplete'=>'off'))?>
            </div>
            <div class="col-sm-3">
                <span class="help-inline"><?php if($model->hasErrors('password')) echo $model->getError('password');?></span>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-5 pull-right">
                <?php echo CHtml::submitButton('提交',array('class'=>'btn btn-success','style'=>'width:175px;')); ?>
            </div>
        </div>
    </fieldset>
    <?php $this->endWidget(); ?>
</div>