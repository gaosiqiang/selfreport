<?php
$this->pageTitle=Yii::app()->name . ' - Verify';
?>
<div class="form-container-site" style="background:transparent">
	<h1>验证</h1>
	<?php $form=$this->beginWidget('CActiveForm',array(
		'action'=>Yii::app()->createUrl('site/verify'),
		'method'=>'post',
		'htmlOptions'=>array('class'=>'well form-horizontal')
	));
	$user = Admin::getUserInformation(Yii::app()->user->name);
	?>
	<fieldset>
		<div class="control-group" style="padding-left: 30px;">短信已发送至<?php preg_match('/^([0-9]{3})[0-9]{5}([0-9]{3})$/', $user->mobile, $matches); echo $matches[1].'*****'.$matches[2];?>，如果1分钟内没收到，请<input type="button" id='resend'/></div>
		<div class="control-group" style="padding-left: 75px;">
			<label>短信验证码：<?php echo CHtml::textField('token','',array('style'=>'width:145px; margin-left:20px;')); ?></label>
		</div>
		<div class="control-group">
			<div style="margin-left: 200px;">
				<?php echo CHtml::submitButton('确定',array('id'=>'confirm_btn','class'=>'btn btn-success','style'=>'width:100px;')); ?>
			</div>
		</div>
	</fieldset>
	<br><br>
	<div id="response"></div>
	<hr>
	<div>收不到短信验证码？</div>
	<div>&gt; 由于通信原因，可能会有延迟，如果1分钟内没有收到，请重新获取</div>
	<div>&gt; 手机停机后，无法接收短信验证码，请及时充值</div>
	<div>&gt; 更换手机号后，请及时通过OA提交更换手机号码申请，以保证您的正常使用</div>
	<?php $this->endWidget(); ?>
</div>
<script type="text/javascript">
$(function () {
	var count = 60;
	if ($.cookie('time_limit') != null)
		var count = $.cookie('time_limit');
	var countdown = setInterval(CountDown, 1000);
	
	function CountDown() {
		if (count >= 0) {
			$("#resend").attr("disabled", true);
			$("#resend").val(count + "秒后重新获取");
			$.cookie('time_limit', count, {expires:0.007,domain:'.wowotuan.com',path:'/'});
			if (count == 0) {
				//$.cookie('time_limit', null, {domain:'.wowotuan.com',path:'/'});
				$("#resend").val("重新获取").removeAttr("disabled");
				clearInterval(countdown);
			}
			count--;
		}
	}

	$("#resend").click(function(){
		$("#resend").attr("disabled", true);
		$(".alert").alert('close');	//关闭上方通知栏
		$("#response").load("/resend",'',function(response,status){
			if (status=="success")
			{
				if (response == ""){
					window.location.href = "<?php echo Yii::app()->createUrl('site/failed/type/1');?>";
				} else {
					$("#response").empty();
					$("#response").append(response);
					
					var count = 60;
					var countdown = setInterval(function(){
						if (count >= 0) {
							$("#resend").attr("disabled", true);
							$("#resend").val(count + "秒后重新获取");
							$.cookie('time_limit', count, {expires:0.007,domain:'.wowotuan.com',path:'/'});
							if (count == 0) {
								//$.cookie('time_limit', null, {domain:'.wowotuan.com',path:'/'});
								$("#resend").val("重新获取").removeAttr("disabled");
								clearInterval(countdown);
							}
							count--;
						}
					}, 1000);
				}
			}
		});
	});

	 $("form:first").submit(function(){
		$("#confirm_btn").attr("disabled", true);
	});
});
</script>
