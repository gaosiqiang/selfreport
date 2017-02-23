<?php $this->widget('AdminNav'); ?>
<div class="page-header header-container">
    <div class="row">
        <?php $form=$this->beginWidget('CActiveForm', array(
            'action'=>Yii::app()->createUrl($this->route),
            'method'=>'get',
            'htmlOptions'=>array('class'=>'well form-inline')
        )); ?>
        <div class="row">
            <div class="col-md-12">
                <label>用户名：</label>
                <?php echo $form->textField($model,'username',array('class'=>'form-control','placeholder'=>'用户名')); ?>
                &nbsp;<label>URL：</label>
                <?php echo $form->textField($model,'url',array('class'=>'form-control','placeholder'=>'URL')); ?>
                &nbsp;<label>菜单：</label>
                <?php echo $form->dropDownList($model,'menu',$menus,array('prompt'=>'--请选择菜单--','class'=>'form-control','style'=>'width:210px'));?>
                <?php echo CHtml::submitButton('查询',array('class'=>'btn btn-success','style'=>'margin-left:10px;')); ?>
            </div>
        </div>
        <?php $this->endWidget(); ?>
    </div>
</div>
<?php $this->widget('zii.widgets.grid.CGridView', array(
    'id'=>'user-grid',
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
        array(
            'header'=>'用户名',
            'value'=>'$data->username',
            'htmlOptions'=>array('style'=>'width:10%;text-align:left;')
        ),
        array(
            'header'=>'菜单',
            'value'=>'$data->menu_name',
            'htmlOptions'=>array('style'=>'width:20%;text-align:left;')
        ),
        array(
            'header'=>'访问IP',
            'value'=>'long2ip($data->ip)',
            'htmlOptions'=>array('style'=>'width:20%')
        ),
        array(
            'header'=>'访问时间',
            'value'=>'date("Y-m-d H:i:s",$data->time)',
            'htmlOptions'=>array('style'=>'width:20%')
        ),
        array(
            'header'=>'完整URL',
            'value'=>'$data->url',
            'htmlOptions'=>array('style'=>'width:30%;text-align:left;')
        ),
    )
)); ?>
<style>
<!--
.row label{
    display: inline;
}
-->
</style>