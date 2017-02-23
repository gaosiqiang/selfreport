<div class="form-container">
	<div class="row">
		<div class="col-md-12"><h3>公告列表</h3></div>
	</div>
	<hr>
	<?php $this->widget('zii.widgets.CListView', array(
		'htmlOptions' => array('class'=>''),
		'dataProvider'=>$dataProvider,
		'itemView'=>'_view',
		'template'=>"{items}\n{pager}",
		'pager'=>array(
			'class'=>'LinkPager',
		),
		'pagerCssClass'=>'text-center',
	)); ?>
</div>