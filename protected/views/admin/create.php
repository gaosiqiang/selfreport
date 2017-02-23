<?php $this->widget('AdminNav'); ?>
<div class="page-header header-container">
    <div style="margin-top: 5px;margin-bottom: 10px;line-height: 1;text-align: left;text-shadow: 1px 2px 3px #ddd;color :#428bca;font-size: 1.05em;">
        <span class="glyphicon glyphicon-leaf" aria-hidden="true" style="top: 2px"></span>添加用户
    </div>
    <div class="alert alert-info fade in">带 * 号的为必填项</div>
    <?php $form=$this->beginWidget('CActiveForm',array(
        'htmlOptions'=>array('class'=>'well form-horizontal')
    )); ?>
    <fieldset>
        <div class="col-md-6">
            <div class="form-group">
                <?php echo $form->labelEx($model,'username',array('class'=>'col-sm-3 control-label')); ?>
                <div class="col-sm-6">
                    <?php echo $form->textField($model,'username',array('class'=>'form-control','placeholder'=>'用户名(必填)')); ?>
                </div>
                <div class="col-sm-3">
                    <span class="help-inline"><?php if($model->hasErrors('username')) echo $model->getError('username');?></span>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'email',array('class'=>'col-sm-3 control-label')); ?>
                <div class="col-sm-6">
                    <?php echo $form->textField($model,'email',array('class'=>'form-control','placeholder'=>'邮箱(必填)')); ?>
                </div>
                <div class="col-sm-3">
                    <span class="help-inline"><?php if($model->hasErrors('email')) echo $model->getError('email');?></span>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'mobile',array('class'=>'col-sm-3 control-label')); ?>
                <div class="col-sm-6">
                    <?php echo $form->textField($model,'mobile',array('class'=>'form-control','placeholder'=>'手机号码(必填)')); ?>
                </div>
                <div class="col-sm-3">
                    <span class="help-inline"><?php if($model->hasErrors('mobile')) echo $model->getError('mobile');?></span>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'password',array('class'=>'col-sm-3 control-label')); ?>
                <div class="col-sm-6">
                    <?php echo $form->textField($model,'password',array('class'=>'form-control','placeholder'=>'密码(必填)')); ?>
                </div>
                <div class="col-sm-3">
                    <span class="help-inline"><?php if($model->hasErrors('password')) echo $model->getError('password');?></span>
                </div>
            </div>
            <div class=form-group>
                <?php echo CHtml::label('权限类型', false, array('class'=>'col-sm-3 control-label'));?>
                <div class="col-sm-6" style="padding-top:7px">
                    <?php echo CHtml::radioButtonList('privtype','0',$privtypes,array('separator'=>'&nbsp;&nbsp;')); ?>
                </div>
            </div>
            <div class="form-group" style="display:none;" id="usergroups">
                <?php echo CHtml::label('用户组', false, array('class'=>'col-sm-3 control-label'));?>
                <div class="col-sm-6">
                    <?php echo CHtml::listBox('usergroup','',$usergroups,array('style'=>'width:163px;height:100px;','multiple'=>'multiple')); ?>
                </div>
            </div>
            <div class="form-group" style="display:none;" id="privlevels">
                <?php echo CHtml::label('权限级别', false, array('class'=>'col-sm-3 control-label'));?>
                <div class="col-sm-6">
                    <?php echo CHtml::dropDownList('privlevel','',$privlevels,array('class'=>'form-control')); ?>
                </div>
            </div>
            <!--      start注释媒体      -->
<!--            <div class="form-group" id="media">-->
<!--                --><?php //echo CHtml::label('媒体', false, array('class'=>'col-sm-3 control-label'));?>
<!--                <div class="col-sm-6">-->
                     <!-- 媒体权限选择弹出浮层  start-->
                     <!-- Button trigger modal -->
<!--                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#mediaModal">请选择</button>-->

                    <!-- Modal -->
<!--                    <div id="mediaModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="mediaModalLabel" aria-hidden="true">-->
<!--                        <div class="modal-dialog">-->
<!--                            <div class="modal-content">-->
<!--                                <div class="modal-header">-->
<!--                                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>-->
<!--                                    <h4 class="modal-title" id="mediaModalLabel">选择权限媒体</h4>-->
<!--                                </div>-->
<!--                                <div class="modal-body" style="max-height:400px;overflow-y:auto;">-->
<!--                                    <ul class="nav" id="media-menu-tree-sub"></ul>-->
<!--                                </div>-->
<!--                                <div class="modal-footer">-->
<!--                                    <button type="button" class="btn btn-default" data-dismiss="modal" aria-hidden="true" id="close_media">关闭</button>-->
<!--                                    <button class="btn btn-primary" data-dismiss="modal" aria-hidden="true" id="save_media">保存</button>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->
                    <!-- 媒体权限选择弹出浮层  end-->
<!--                </div>-->
<!--            </div>-->
            <!--       end注释媒体     -->

<!--            <div class="form-group">-->
<!--                --><?php //echo CHtml::label('品类', false, array('class'=>'col-sm-3 control-label'));?>
<!--                <div class="col-sm-9">-->
<!--                    <div class="alert alert-warning" style="margin-bottom:0">-->
<!--                        --><?php //echo CHtml::checkBoxList('category', '', $first_cates, array('checkAll'=>'全选','separator'=>'&nbsp;&nbsp;'));?>
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->

        </div>



        <div class="col-md-12">
            <div class="form-group">
                <div class="col-sm-7 pull-right">
                    <?php echo CHtml::submitButton('提交',array('class'=>'btn btn-success','style'=>'width:125px;')); ?>
                </div>
            </div>
        </div>
    </fieldset>
    <?php $this->endWidget(); ?>
</div>
<style type="text/css">
#category input[type="checkbox"],#priv_wdt_only input[type="checkbox"] {
    margin-top: 0;
}
#category label,#priv_wdt_only label {
    vertical-align: middle;
}
</style>
<script type="text/javascript">
$(document).ready(function(){
    
    mediaMenuReload();
    
    /////////报表权限 START/////////
    privtype = $('input[name=privtype]:checked').val();
    if (privtype == '<?php echo UserGroup::SUPER_GROUP;?>') {
        $('#usergroups').css({"display":"none"});
        $('#usergroup').attr('disabled',true);
        $('#privlevels').css({"display":""});
        $('#privlevel').attr('disabled',false);
    } else {
        $('#usergroups').css({"display":""});
        $('#usergroup').attr('disabled',false);
        $('#privlevels').css({"display":"none"});
        $('#privlevel').attr('disabled',true);
    }
    
    /////////报表权限 END/////////
    
    /////////大区与城市权限互斥 START/////////
    if($('#select_area').attr('checked') == 'checked'){
        $("#region").attr('disabled', true);
        
        $('#allcity').attr('disabled',true)
        $('#allsite').attr('disabled',true);
        $('#quanguo').attr('disabled',true);
        $('#cities').attr('disabled',true);
        $('#add-city').attr('disabled',true);
        $("#selected-ids").val('');
        $('#selected-ids').attr('disabled',true);
        $('#selected-city').empty();
        $("#priv_wdt_only :checkbox").attr('disabled', true);
    }
    if($('#select_region').attr('checked') == 'checked'){
        $("#area").attr('disabled', true);
        
        $('#allcity').attr('disabled',true);
        $('#allsite').attr('disabled',true);
        $('#quanguo').attr('disabled',true);
        $('#cities').attr('disabled',true);
        $('#add-city').attr('disabled',true);
        $("#selected-ids").val('');
        $('#selected-ids').attr('disabled',true);
        $('#selected-city').empty();
        $("#priv_wdt_only :checkbox").attr('disabled', true);
    }
    if($('#select_city').attr('checked') == 'checked'){
        $("#area").attr('disabled', true);
        $("#region").attr('disabled', true);
    }
    /////////大区与城市权限互斥 END/////////
    
    /////////事业部权限与品类、大区、城市权限互斥 START/////////
    $("#category_all").attr('value',9999);
    /////////事业部权限与品类、大区、城市权限互斥 END/////////
});

////////////////////////////////////////////////////////////////////////////////////////////////
//删除选择城市
function del_city(id)
{
    $('#city-id-'+id).remove();
    var result_ids = $('#selected-ids').val();
    var new_arr = [];

    var ids_arr = result_ids.split(',');
    for(var i in ids_arr)
    {
        if(ids_arr[i] != id){
            new_arr.push(ids_arr[i]);
        }
    }
    $('#selected-ids').val(new_arr.join(','));
}

function media_menu_click(){
    var id = $(this).attr("id");
    //var check =  $(this).attr("checked");
    var check =  $(this).prop("checked");
    
    //下级菜单
    $(this).parent("li").find("input[type='checkbox']").each(function(){
        //if($(this).attr("id")!=id){
            if(check){
                //$(this).attr("checked",true);
                $(this).prop("checked",true);
            }else{
                //$(this).attr("checked",false);
                $(this).prop("checked",false);
            }
        //}

        // 例如:当子级 第三方运营选中的时候,其兄弟节点(京东,淘宝,苏宁)的也要选中...
        // note 2014/04/13    
        //if(($(this).attr("id") == $(this).parent().parent().siblings(":input").attr("id")) && ($(this).attr("checked") == 'checked')){
        //    $(this).parent().siblings().find("input:checkbox").attr("checked",true);    
        //}
        if(($(this).attr("id") == $(this).parent().parent().siblings(":input").attr("id")) && ($(this).prop("checked") == 'checked')){
            $(this).parent().siblings().find("input:checkbox").prop("checked",true);    
        }

    });

    //上级菜单
    $(this).parents("li").each(function(){
        var first = $(this).children("input");
        if(check){
            //first.attr("checked",true);
            first.prop("checked",true);
        }
    });

    var check_state = false;
    
    $(this).parent().parent().find("input[type='checkbox']").each(function(){
        //if($(this).attr("id")!= id && $(this).attr("checked")){
        if($(this).attr("id")!= id && $(this).prop("checked")){
            check_state = true;
            return;
        }
    });

    if(!check && !check_state){
        $(this).parents("li").each(function(){
            var first = $(this).children("input");
            if(first.attr("id") != id){
                //first.attr("checked",false);
                first.prop("checked",false);
            }
        });
    }
}

function mediaMenuReload(){
    var handle = "authority";
    var username = "<?php echo $model->username;?>";
    $("#media-menu-tree-sub").load("/ajax/showmediamenutree",{handle:handle, username:username},function(response,status){
        if (status=="success")
        {
            $("#media-menu-tree-sub").html(response);

            //对li进行遍历
            $("#media-menu-tree-sub > div > li").each(function(){
                //如果有子节点的input有选中的,则将该父节点的input也置于选择状态
                if($(this).find("input:not(:first):checked").length > 0){
                    //$(this).find("input:first").attr('checked',true);
                    $(this).find("input:first").prop('checked',true);
                }
            })
            
            $("#media-menu-tree-sub :checkbox").click(media_menu_click);
            $(".media-menu-tree-collapse").parent().addClass("active");
            
            $('#all_media').click(function(){
                //var checked = $(this).attr('checked');
                //$('#media-menu-tree-sub :checkbox').each(function(){$(this).attr('checked',checked?true:false);});
                var checked = $(this).prop('checked');
                $('#media-menu-tree-sub :checkbox').each(function(){$(this).prop('checked',checked?true:false);});
            });
            
            
        }
    });
}

////////////////////////////////////////////////////////////////////////////////////////////////

$('#close_media').click(function(){
    $('#mediaModal').modal('hide')
    mediaMenuReload();
})

/////////报表权限 START/////////
$("#privtype").change(function(){
    privtype = $('input[name=privtype]:checked').val();
    if (privtype == '<?php echo UserGroup::SUPER_GROUP;?>') {
        $('#usergroups').css({"display":"none"});
        $('#usergroup').attr('disabled',true);
        $('#privlevels').css({"display":""});
        $('#privlevel').attr('disabled',false);
        $('select[name="medias[]"]').val("all");
    } else {
        $('#usergroups').css({"display":""});
        $('#usergroup').attr('disabled',false);
        $('#privlevels').css({"display":"none"});
        $('#privlevel').attr('disabled',true);
    }
});
/////////报表权限 END/////////

/////////大区与城市权限互斥 START/////////
$('#select_area').click(function(){
    $("#area").attr('disabled', false);
    
    $("#region").attr('disabled', true);
    
    $('#allcity').prop('checked',false);
    $('#allcity').attr('disabled',true);
    $('#allsite').prop('checked',false);
    $('#allsite').attr('disabled',true);
    $('#quanguo').prop('checked',false);
    $('#quanguo').attr('disabled',true);
    
    $('#cities').attr('disabled',true);
    $('#add-city').attr('disabled',true);
    $("#selected-ids").val('');
    $('#selected-ids').attr('disabled',true);
    $('#selected-city').empty();
    
    $("#priv_wdt_only :checkbox").attr('disabled', true);
});

$('#select_region').click(function(){
    $("#area").attr('disabled', true);
    
    $("#region").attr('disabled', false);

    $('#allcity').prop('checked',false);
    $('#allcity').attr('disabled',true);
    $('#allsite').prop('checked',false);
    $('#allsite').attr('disabled',true);
    $('#quanguo').prop('checked',false);
    $('#quanguo').attr('disabled',true);
    
    $('#cities').attr('disabled',true);
    $('#add-city').attr('disabled',true);
    $("#selected-ids").val('');
    $('#selected-ids').attr('disabled',true);
    $('#selected-city').empty();
    
    $("#priv_wdt_only :checkbox").attr('disabled', true);
});

$('#select_city').click(function(){
    $("#area").attr('disabled', true);
    
    $("#region").attr('disabled', true);
    
    $('#allcity').attr('disabled',false);
    $('#allsite').attr('disabled',false);
    $('#quanguo').attr('disabled',false);
    $('#cities').attr('disabled',false);
    $('#add-city').attr('disabled',false);
    $('#selected-ids').attr('disabled',false);
    $("#priv_wdt_only :checkbox").attr('disabled', false);
});
/////////大区与城市权限互斥 END/////////

$('#allcity').click(function(){
    if($('#allcity').prop('checked') == true)
    {
        $('#allsite').prop('checked',true);
        $('#quanguo').prop('checked',true);
        $('#cities').attr('disabled',true);
        $('#add-city').attr('disabled',true);
        $("#selected-ids").val('');
        $('#selected-ids').attr('disabled',true);
        $('#selected-city').empty();
    }
    else
    {
        $('#allsite').attr('disabled',false);
        $('#quanguo').attr('disabled',false);
        $('#cities').attr('disabled',false);
        $('#add-city').attr('disabled',false);
        $('#selected-ids').attr('disabled',false);
    }
});

/////////城市提示框/////////
var city_initials = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('initials'),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    prefetch: {
        url: '/ajax/ajaxcitiesautocomplete',
        cache: false
    }
});

var city_names = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    prefetch: {
        url: '/ajax/ajaxcitiesautocomplete',
        cache: false
    }
});

$('#cities').typeahead({
        highlight: true
    }, 
    {
        name: 'city-initials',
        display: 'name',
        source: city_initials,
        templates: {
            //header: '<h3 class="league-name">缩写查询</h3>',
            suggestion: Handlebars.compile('<div>"{{name}}"[{{initials}}]</div>')
        },
    },
    {
        name: 'city-names',
        display: 'name',
        source: city_names,
        templates: {
            //header: '<h3 class="league-name">名称查询</h3>',
            suggestion: Handlebars.compile('<div>"{{name}}"[{{initials}}]</div>')
        }
    }
).bind('typeahead:select', function(ev, suggestion) {
    $('#tmp-city').attr('data-id',suggestion.id);
    $('#tmp-city').attr('data-name',suggestion.name);
});

//点击添加城市
$('#add-city').click(function (){
    var id = $('#tmp-city').attr('data-id');
    var name = $('#tmp-city').attr('data-name');
    var partition = $("#selected-ids").val().split(',');

    if($('#cities').val() == '' || id == '')
    {
        alert('请输入城市');
        return false;
    }

    if($.inArray(id,partition)==-1)
    {
        $("#selected-ids").val($("#selected-ids").val()+','+id);
        $("#selected-city").html($("#selected-city").html()+"<span class='selected-city' id='city-id-"+id+"' onclick=del_city("+id+")><code>"+name+"</code></span>");
    }
    $('#cities').val(null);
});

document.onkeydown = function(e){
    var ev = document.all ? window.event : e; 
    if(ev.keyCode==13) {
        var id = $('#tmp-city').attr('data-id');
        var name = $('#tmp-city').attr('data-name');
        var current_value = $("#selected-ids").val();
        var partition = current_value.split(',');
        
        if($('#cities').val() == '')
        {
            return true;
        }
        if($.inArray(id,partition)==-1)
        {
            $("#selected-ids").val($("#selected-ids").val()+','+id);
            $("#selected-city").html($("#selected-city").html()+"<span id='city-id-"+id+"'>"+name+" <a href='javascript:;' onclick=del_city("+id+")>X</a> </span>");
        }
        $('#cities').val(null);
        return false;
    }
}

$("#yw0").submit(function(){
    flag = false;
    privtype = $('input[name=privtype]:checked').val();
    usergroup = $('#usergroup option:selected').length;
    privlevel = $('select[name=privlevel]').val();
    
    if(privtype==0){
        if(usergroup>0){
            flag = true;
        }else{
            alert("请选择用户组");
            return false;
        }
    }
    if(privtype==<?php echo UserGroup::SUPER_GROUP;?>){
        if(privlevel>0){
            flag = true;
        }else{
            alert("请选择权限级别");
            return false;
        }
    }
    /*if(usergroup.match(/^\d+$/g)!=null)
        flag = true;
    else {
        alert("请选择用户组");
        return false;
    }*/
    
//    var category="";
//    $("#category :checked").each(function(){
//        category+=$(this).val()+",";
//    })
//    if(category!=""){
//        flag = true;
//    } else {
//        alert("未选择品类，无法提交");
//        return false;
//    }
//
//    return flag;
});
</script>