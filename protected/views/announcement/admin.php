<?php $this->widget('AdminNav'); ?>
<div class="page-header header-container">
    <div class="row">
        <?php $form=$this->beginWidget('CActiveForm', array(
            'action'=>Yii::app()->createUrl($this->route),
            'method'=>'get',
            'htmlOptions'=>array('class'=>'well')
        )); ?>
        <div class="row">
            <div class="col-md-10">
                <label>公告标题：</label>
                <?php echo $form->textField($model,'title',array('class'=>'form-control','style'=>'display:inline-block;width:150px','placeholder'=>'公告标题')); ?>
                <?php echo CHtml::submitButton('查询',array('class'=>'btn btn-success','style'=>'margin-left:10px;')); ?>
            </div>
            <div class="col-md-2">
                <?php echo CHtml::link('新建公告', array('announcement/create'),array('class'=>'btn btn-info'));?>
            </div>
        </div>
        <?php $this->endWidget(); ?>
    </div>
</div>
<?php $this->widget('zii.widgets.grid.CGridView', array(
    'id'=>'announcement-grid',
    'itemsCssClass'=>'table table-bordered table-striped',
    'dataProvider'=>$model->search(),
    'htmlOptions'=>array('class'=>'table-container'),
    'showTableOnEmpty'=>false,
    'emptyText'=>'<div class="alert alert-info">对不起，没有任何搜索结果</div>',
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
                'header'=>'公告标题',
                'type'=>'raw',
                'htmlOptions'=>array('style'=>'text-align:left;vertical-align:middle'),
                'value'=>'CHtml::link($data->title, array("announcement/view","id"=>$data->id), array("target"=>"_blank"))',
        ),
        array(
                'header'=>'开始时间',
                'htmlOptions'=>array('style'=>'vertical-align:middle'),
                'value'=>'date("Y-m-d",$data->start_time)',
        ),
        array(
                'header'=>'结束时间',
                'htmlOptions'=>array('style'=>'vertical-align:middle'),
                'value'=>'date("Y-m-d",$data->end_time)',
        ),
        array(
                'header'=>'操作',
                'htmlOptions'=>array('style'=>'text-align:center'),
                'headerHtmlOptions'=>array('style'=>'text-align:center'),
                'class'=>'CButtonColumn',
                'template'=>'{update}&nbsp;{delete}',
                'buttons'=>array(
                    'update'=>array(
                        'label'=>'<i class="glyphicon glyphicon-pencil icon-white"></i> 编辑',
                        'options'=>array('class'=>'btn btn-info','title'=>'编辑'),
                        'imageUrl'=>false,
                        'visiable'=>false,
                    ),
                    'delete'=>array(
                        'label'=>'<i class="glyphicon glyphicon-trash icon-white"></i> 删除',
                        'options'=>array('class'=>'btn btn-danger','title'=>'删除'),
                        'imageUrl'=>false,
                        'click'=>'function(){return confirm("您确认要删除吗？");}',
                    ),
                ),
        ),
    ),
)); ?>