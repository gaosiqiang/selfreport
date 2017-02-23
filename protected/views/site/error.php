<?php
$this->pageTitle=Yii::app()->name . ' - Login';
?>
<div class="container">
	<div class="content">
		<div class="form-container">
			<h1>Error <?php echo $code; ?></h1>
			<p class="alert alert-error">
				<?php echo CHtml::encode($message); ?>
			</p>
		</div>
	</div>
</div>