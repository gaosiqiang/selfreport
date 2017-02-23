<?php $this->widget('AdminNav'); ?>
<div class="page-header header-container">
    <div class="row">
        <?php $form=$this->beginWidget('CActiveForm', array(
            'action'=>Yii::app()->createUrl($this->route),
            'method'=>'get',
            'htmlOptions'=>array('class'=>'well')
        )); ?>
        <div class="row">
            <div class="col-md-9">
                <div>
                    <label>邮箱：</label>
                    <?php echo CHtml::textField('email',$email,array('class'=>'form-control','style'=>'display:inline-block;width:150px','placeholder'=>'邮箱')); ?>
                    &nbsp;<label>用户名：</label>
                    <?php echo CHtml::textField('username',$username,array('class'=>'form-control','style'=>'display:inline-block;width:150px','placeholder'=>'用户名')); ?>
                    &nbsp;<label>手机号：</label>
                    <?php echo CHtml::textField('mobile',$mobile,array('class'=>'form-control','style'=>'display:inline-block;width:150px','placeholder'=>'手机号')); ?>
                </div>
                <div style="margin-top:7px">
<!--                    <label>城市：</label>-->
<!--                    --><?php //echo CHtml::textField('city_name',$city_name,array('class'=>'form-control typeahead','style'=>'display:inline-block;width:150px','placeholder'=>'权限城市')); ?>
<!--                    --><?php //echo CHtml::hiddenField('city_id', $city_id);?>
<!--                    &nbsp;-->
                    <label>用户组：</label>
                    <?php echo CHtml::dropDownList('user_group',$user_group_id,$user_group,array('class'=>'form-control','style'=>'display:inline-block;width:163px')); ?>
                </div>
            </div>
            <div class="col-md-1">
                <?php echo CHtml::submitButton('查询',array('class'=>'btn btn-success')); ?>
            </div>
            <div class="col-md-2">
                <?php echo CHtml::link('新建用户', array('admin/create'),array('class'=>'btn btn-info'));?>
            </div>
        </div>
        <?php $this->endWidget(); ?>
    </div>
</div>
<?php 
$params = '"email"=>"'.$email.'","username"=>"'.$username.'","mobile"=>"'.$mobile.'","city_id"=>"'.$city_id.'","city_name"=>"'.$city_name.'","user_group"=>"'.$user_group_id.'"';
$page = Common::getNumParam('Admin_page');
if (!empty($page))
    $params = $params.',"Admin_page"=>"'.$page.'"';
?>
<?php $this->widget('zii.widgets.grid.CGridView', array(
    'id'=>'admin-grid',
    'itemsCssClass'=>'table table-bordered table-striped',
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
                'header'=>'用户名',
                'htmlOptions'=>array('style'=>'vertical-align:middle;text-align:left;'),
                'value'=>'$data->username',
        ),
        array(
                'header'=>'邮箱',
                'htmlOptions'=>array('style'=>'vertical-align:middle;text-align:left;'),
                'value'=>'$data->email',
        ),
        array(
                'header'=>'上次登录',
                'htmlOptions'=>array('style'=>'vertical-align:middle'),
                'value'=>'date("Y-m-d H:i:s",$data->login_time)',
        ),
        array(
                'header'=>'距到期天数',
                'htmlOptions'=>array('style'=>'color:red;vertical-align:middle'),
                'value'=>'60-floor((time()-$data->create_time)/(3600*24))'
        ),
        array(
                'header'=>'操作',
                'htmlOptions'=>array('style'=>'text-align:center;vertical-align:middle'),
                'headerHtmlOptions'=>array('style'=>'text-align:center'),
                'class'=>'CButtonColumn',
                'template'=>'{toggle1}{toggle2}&nbsp;{prolong}&nbsp;{update}&nbsp;{authority}&nbsp;{delete}',
                'buttons'=>array(
                    'toggle1'=>array(
                            'label'=>'<i class="glyphicon glyphicon-off icon-white"></i>禁用',
                            'url'=>'Yii::app()->controller->createUrl("toggle",array("id"=>$data->id,'.$params.'))',
                            'options'=>array('class'=>'btn btn-warning','title'=>'禁用'),
                            'imageUrl'=> false,
                            'visible' => '$data->status==1',
                    ),
                    'toggle2'=>array(
                            'label'=>'<i class="glyphicon glyphicon-off icon-white"></i>启用',
                            'url'=>'Yii::app()->controller->createUrl("toggle",array("id"=>$data->id,'.$params.'))',
                            'options'=>array('class'=>'btn btn-success','title'=>'启用'),
                            'imageUrl'=> false,
                            'visible' => '$data->status==0',
                    ),
                    'prolong'=>array(
                            'label'=>'<i class="glyphicon glyphicon-repeat icon-white"></i>延期',
                            'url'=>'Yii::app()->controller->createUrl("prolong",array("id"=>$data->id,'.$params.'))',
                            'options'=>array('class'=>'btn btn-success','title'=>'延期'),
                            'imageUrl'=> false,
                    ),
                    'update'=>array(
                        'label'=>'<i class="glyphicon glyphicon-pencil icon-white"></i>编辑',
                        'options'=>array('class'=>'btn btn-info','title'=>'编辑'),
                        'imageUrl'=>false,
                        'visiable'=>false,
                    ),
                    'authority'=>array(
                        'label'=>'<i class="glyphicon glyphicon-cog icon-white"></i>权限',
                        'options'=>array('class'=>'btn btn-primary','title'=>'权限'),
                        'imageUrl'=>false,
                        'url'=>'Yii::app()->createURL("/admin/authority",array("id"=>$data->id))',
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
<script type="text/javascript">
$(document).ready(function(){
    $(".tt-input").css("vertical-align","baseline");    //将城市提示框扶正，由于该属性是由控件写在标签内，所以需要JS介入
});

$(function(){
    /////////城市、用户组与用户信息互斥 START/////////
    city_name = $("#city_name").val();
    user_group = $("#user_group").val();
    if (city_name != '' || user_group != '') {
        $("#email").attr('disabled', true);
        $('#username').attr('disabled',true)
        $('#mobile').attr('disabled',true);
    }
    
    $("#city_name").change(function(){
        city_name = $("#city_name").val();
        user_group = $("#user_group").val();
        if (city_name != '' || user_group != '') {
            $("#email").attr('disabled', true);
            $('#username').attr('disabled',true)
            $('#mobile').attr('disabled',true);
        } else {
            $("#email").attr('disabled', false);
            $('#username').attr('disabled',false)
            $('#mobile').attr('disabled',false);
        }
    });
    
    $("#user_group").change(function(){
        city_name = $("#city_name").val();
        user_group = $("#user_group").val();
        if (city_name != '' || user_group != '') {
            $("#email").attr('disabled', true);
            $('#username').attr('disabled',true)
            $('#mobile').attr('disabled',true);
        } else {
            $("#email").attr('disabled', false);
            $('#username').attr('disabled',false)
            $('#mobile').attr('disabled',false);
        }
    });
/////////城市、用户组与用户信息互斥 END/////////
});

$("#yw0").submit(function() {
    if($("#city_name").val() == ''){
        $("#city_id").val('');
    }
});
</script>
