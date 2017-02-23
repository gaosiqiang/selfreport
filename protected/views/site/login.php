<?php
$this->pageTitle=Yii::app()->name . ' - Login';
?>
<div class="form-container-site" style="background:transparent">
	<h1>登录</h1>
	<?php $form=$this->beginWidget('CActiveForm',array(
			'htmlOptions'=>array('class'=>'well form-horizontal')
	)); ?>
	<fieldset>
		<div class="form-group">
			<?php echo $form->labelEx($model,'username',array('class'=>'col-sm-3 control-label')); ?>
			<div class="col-sm-5">
				<?php echo $form->textField($model,'username',array('class'=>'form-control','style'=>'width:200px;')); ?>
			</div>
			<div class="col-sm-3">
				<span class="help-inline"><?php if($model->hasErrors('username')) echo $model->getError('username');?></span>
			</div>
		</div>
		<div class="form-group">
			<?php echo $form->labelEx($model,'password',array('class'=>'col-sm-3 control-label')); ?>
			<div class="col-sm-5">
				<?php echo $form->passwordField($model,'password',array('class'=>'form-control', 'autocomplete'=>'off','style'=>'width:200px;')); ?>
			</div>
			<div class="col-sm-3">
				<span class="help-inline"><?php if($model->hasErrors('password')) echo $model->getError('password');?></span>
			</div>
		</div>

		<div class="form-group">
				<?php echo $form->labelEx($model,'verifyCode',array('class'=>'col-sm-3 control-label')); ?>
			<div class="col-sm-5">
				<?php echo $form->textField($model,'verifyCode',array('autocomplete'=>'off', 'size'=>'7', 'class'=>'form-control','style'=>'width:200px;'));?>
				<span class="help-inline"><?php echo $model->getError('verifyCode');?></span>
			</div>
			<div class="col-sm-3">
				<?php $this->widget('CCaptcha',array('showRefreshButton'=>false,'clickableImage'=>true,'imageOptions'=>array('alt'=>'点击换图','title'=>'点击换图','style'=>'cursor:pointer'))); ?>
			</div>
		</div>

		<div class="form-group">
			<div class="col-sm-8 pull-right">
				<?php echo CHtml::submitButton('登录',array('id'=>'login_btn','class'=>'btn btn-success','style'=>'width:120px;')); ?>
			</div>
		</div>
	</fieldset>
	<?php $this->endWidget(); ?>
</div>
<script type="text/javascript">
	$(function () {
		$.cookie('time_limit', null, {domain:'table.com',path:'/'});
		$("form:first").submit(function(){
			$("#login_btn").attr("disabled", true);
		});
	});
</script>

