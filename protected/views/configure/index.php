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
                <label>报表名称：</label>
                <?php echo CHtml::textField('report_name',$report_name,array('class'=>'form-control','placeholder'=>'报表名称')); ?>
                <?php echo CHtml::submitButton('查询',array('class'=>'btn btn-success')); ?>
            </div>
            <div class="col-md-2">
                <?php echo CHtml::link('新建报表', array('configure/create'),array('class'=>'btn btn-info'));?>
            </div>
        </div>
        <?php $this->endWidget(); ?>
    </div>
</div>
<?php 
$params = '"report_name"=>"'.$report_name.'"';
$page = Common::getNumParam('ReportConfiguration_page');
if (!empty($page))
    $params = $params.',"ReportConfiguration_page"=>"'.$page.'"';
?>
<?php $this->widget('zii.widgets.grid.CGridView', array(
    'id'=>'configure-grid',
    'itemsCssClass'=>'table table-bordered table-striped table-hover',
    'dataProvider'=>$dataProvider,
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
        /* array(
                'header'=>'ID',
                'htmlOptions'=>array('style'=>'vertical-align:middle'),
                'value'=>'$data->id',
        ), */
        array(
                'header'=>'报表名称',
                'htmlOptions'=>array('style'=>'vertical-align:middle;text-align:left;'),
                'value'=>'$data->report_name',
        ),
        array(
                'header'=>'数据源服务器IP',
                'htmlOptions'=>array('style'=>'vertical-align:middle'),
                'value'=>'isset($data->datasource)&&isset($data->datasource->server_ip)?long2ip($data->datasource->server_ip):""',
        ),
        array(
                'header'=>'数据源库',
                'htmlOptions'=>array('style'=>'vertical-align:middle'),
                'value'=>'isset($data->datasource)&&isset($data->datasource->database)?$data->datasource->database:""',
        ),
        /* array(
                'header'=>'数据生成时间',
                'htmlOptions'=>array('style'=>'vertical-align:middle'),
                'value'=>'$data->is_timed==1?$data->crontab:"即时查询"',
        ), */
        array(
                'header'=>'创建时间',
                'htmlOptions'=>array('style'=>'vertical-align:middle'),
                'value'=>'date("Y-m-d H:i:s",$data->create_time)',
        ),
        array(
                'header'=>'更新时间',
                'htmlOptions'=>array('style'=>'vertical-align:middle'),
                'value'=>'isset($data->update_time)?date("Y-m-d H:i:s",$data->update_time):""',
        ),
        array(
            'header'=>'操作',
            'htmlOptions'=>array('style'=>'text-align:center'),
            'headerHtmlOptions'=>array('style'=>'text-align:center'),
            'class'=>'CButtonColumn',
            'template'=>'{toggle1}{toggle2}&nbsp;{update}&nbsp;{delete}',
            'buttons'=>array(
                'toggle1'=>array(
                        'label'=>'<i class="glyphicon glyphicon-eye-close icon-white"></i> 隐藏',
                        'url'=>'Yii::app()->controller->createUrl("toggle",array("report_id"=>$data->id,'.$params.'))',
                        'options'=>array('class'=>'btn btn-warning','title'=>'隐藏'),
                        'imageUrl'=> false,
                        'visible' => '$data->menu->status==1',
                ),
                'toggle2'=>array(
                        'label'=>'<i class="glyphicon glyphicon-eye-open icon-white"></i> 显示',
                        'url'=>'Yii::app()->controller->createUrl("toggle",array("report_id"=>$data->id,'.$params.'))',
                        'options'=>array('class'=>'btn btn-success','title'=>'显示'),
                        'imageUrl'=> false,
                        'visible' => '$data->menu->status==0',
                ),
                'update'=>array(
                    'label'=>'<i class="glyphicon glyphicon-pencil icon-white"></i> 编辑',
                    'options'=>array('class'=>'btn btn-primary','title'=>'编辑'),
                    'imageUrl'=>false,
                    'url'=>'Yii::app()->createURL("/configure/update",array("report_id"=>$data->id))',
                ),
                'delete'=>array(
                    'label'=>'<i class="glyphicon glyphicon-trash icon-white"></i> 删除',
                    'options'=>array('class'=>'btn btn-danger','title'=>'删除'),
                    'imageUrl'=>false,
                    'click'=>'function(){return confirm("删除是不可恢复的，您确认要删除吗？");}',
                    'url'=>'Yii::app()->createURL("/configure/delete",array("report_id"=>$data->id))',
                ),
            ),
        ),
    ),
)); ?>