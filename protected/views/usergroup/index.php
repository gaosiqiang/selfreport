<?php $this->widget('ConfigureNav'); ?>
<div class="page-header header-container">
    <div class="row">
        <?php $form=$this->beginWidget('CActiveForm', array(
            'action'=>Yii::app()->createUrl($this->route),
            'method'=>'post',
            'htmlOptions'=>array('class'=>'well form-inline')
        )); ?>
        <div class="row">
            <div class="col-md-10">
                <label>用户组名称：</label>
                <?php echo $form->textField($model,'group_name',array('class'=>'form-control','placeholder'=>'用户组名称')); ?>
                <?php echo CHtml::submitButton('查询',array('class'=>'btn btn-success')); ?>
            </div>
            <div class="col-md-2">
                <?php echo CHtml::link('新建用户组', array('usergroup/create'),array('class'=>'btn btn-info'));?>
            </div>
        </div>
        <?php $this->endWidget(); ?>
    </div>
</div>
<?php $this->widget('zii.widgets.grid.CGridView', array(
    'id'=>'user-group-grid',
    'itemsCssClass'=>'table table-bordered table-striped table-hover',
    'dataProvider'=>$model->search(),
    'htmlOptions'=>array('class'=>'table-container'),
    'showTableOnEmpty'=>true,
    'emptyText'=>'<div class="alert alert-info">对不起，没有任何查询结果</div>',
    'template'=>'{items}{pager}',
    'ajaxUpdate'=>false,
    'pager'=>array(
        'class'=>'LinkPager',
    ),
    'rowCssClass'=>'',
    'pagerCssClass'=>'text-center',
    'columns'=>array(
        /* 
        array(
                'header'=>'ID',
                'htmlOptions'=>array('style'=>'vertical-align:middle'),
                'value'=>'$data->id',
        ),
         */
        array(
                'header'=>'序号',
                'htmlOptions'=>array('style'=>'vertical-align:middle'),
                'value'=>'$this->grid->dataProvider->pagination->currentPage * $this->grid->dataProvider->pagination->pageSize + ($row+1)',
        ),
        array(
                'header'=>'用户组名称',
                'htmlOptions'=>array('style'=>'vertical-align:middle;text-align:left;'),
                'value'=>'$data->group_name',
        ),
        array(
                'header'=>'操作',
                'htmlOptions'=>array('style'=>'text-align:center'),
                'headerHtmlOptions'=>array('style'=>'text-align:center'),
                'class'=>'CButtonColumn',
                'template'=>'{update}&nbsp;{delete}',
                'buttons'=>array(
                    'update'=>array(
                        'label'=>'<i class="glyphicon glyphicon-pencil icon-white"></i>编辑',
                        'options'=>array('class'=>'btn btn-primary','title'=>'编辑'),
                        'imageUrl'=>false,
                    ),
                    'delete'=>array(
                        'label'=>'<i class="glyphicon glyphicon-trash icon-white"></i>删除',
                        'options'=>array('class'=>'btn btn-danger','title'=>'删除'),
                        'imageUrl'=>false,
                        'click'=>'function(){return confirm("删除是不可恢复的，您确认要删除吗？");}',
                    ),
                ),
        ),
    ),
));
?>
