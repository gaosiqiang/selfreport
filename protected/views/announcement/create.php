<?php $this->widget('AdminNav'); ?>
<div class="page-header header-container">
    <div style="margin-top: 5px;margin-bottom: 10px;line-height: 1;text-align: left;text-shadow: 1px 2px 3px #ddd;color :#428bca;font-size: 1.05em;">
        <span class="glyphicon glyphicon-leaf" aria-hidden="true" style="top: 2px"></span>添加公告
    </div>
    <div class="alert alert-info fade in">带 * 号的为必填项，公告开始时间 和 结束时间均为当天0点</div>
    <?php $form=$this->beginWidget('CActiveForm',array(
        'htmlOptions'=>array('class'=>'well form-horizontal')
    )); ?>
    <fieldset>
        <div class="form-group">
            <?php echo $form->labelEx($model,'title',array('class'=>'col-sm-2 control-label')); ?>
            <div class="col-sm-8">
                <?php echo $form->textField($model,'title',array('class'=>'form-control','placeholder'=>"公告标题")); ?>
            </div>
            <div class="col-sm-2">
                <span class="help-inline"><?php if($model->hasErrors('title')) echo $model->getError('title');?></span>
            </div>
        </div>
        <div class="form-group">
            <?php echo $form->labelEx($model,'content',array('class'=>'col-sm-2 control-label')); ?>
            <div class="col-sm-8">
                <?php echo $form->textArea($model,'content',array('class'=>'form-control', 'style'=>"height: 100px", 'placeholder'=>"公告内容",'id'=>'textarea')); ?>
            </div>
            <div class="col-sm-2">
                <span class="help-inline"><?php if($model->hasErrors('content')) echo $model->getError('content');?></span>
            </div>
        </div>
        
        <div class="form-group">
            <label class="col-sm-2 control-label">公告时间 *</label>
            <div class="col-sm-8">
                <span class="input-append date" data-date-format="yyyy-mm-dd">
                    <?php echo $form->textField($model,'start_time',array('readonly'=>'true', 'class'=>'input-small', 'id'=>'dp1', 'placeholder'=>'开始时间')); ?>
                </span> -
                <span class="input-append date" data-date-format="yyyy-mm-dd">
                    <?php echo $form->textField($model,'end_time',array('readonly'=>'true', 'class'=>'input-small', 'id'=>'dp2', 'placeholder'=>'结束时间')); ?>
                </span>
            </div>
            <div class="col-sm-2">
                <span class="help-inline"><?php if($model->hasErrors('end_time')) echo $model->getError('end_time');?></span>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-4 pull-right">
                <?php echo CHtml::submitButton('提交',array('class'=>'btn btn-success','style'=>'width:175px;')); ?>
            </div>
        </div>
    </fieldset>
    <?php $this->endWidget(); ?>
</div>