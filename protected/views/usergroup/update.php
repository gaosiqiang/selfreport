<?php $this->widget('ConfigureNav'); ?>
<div class="table-container">
    <div class="form-container-site" style="background:transparent;width:88%;padding-top:0">
        <h2>编辑用户组</h2>
        <?php $form=$this->beginWidget('CActiveForm',array(
            'htmlOptions'=>array('class'=>'well')
        )); ?>
        <fieldset>
            <div class="col-md-12">
                <div class="col-md-2"><?php echo $form->labelEx($model,'group_name',array('class'=>'control-label pull-right')); ?></div>
                <div class="col-md-10">
                    <?php echo $form->textField($model,'group_name',array('class'=>'input-medium')); ?>&nbsp;&nbsp;
                    <span class="help-inline"><?php if($model->hasErrors('group_name')) echo $model->getError('group_name');?></span>
                </div>
            </div>
            <div class="col-md-12">
                <div class="col-md-2"><?php echo CHtml::label('报表权限', false, array('class'=>'control-label pull-right'));?></div>
                <div class="col-md-10 alert alert-warning" style="margin-top:10px;margin-bottom:10px;">
                    <div style="margin-bottom:10px;">自助报表平台：</div>
                    <ul id="menu-tree-sub" class="nav">
                        <div class="col-md-12" style="padding-left:0;margin-bottom:20px;">
                        <?php 
                            $loop = 0;
                            foreach ($menu_map as $first_id => $second_menu_arr) {
                                if (++$loop == 4) {
                                    $loop = 1;
                                    echo "</div><div class=\"col-md-12\" style=\"margin-bottom:20px;\">";
                                }
                        ?>
                            <li id="<?php echo $first_id;?>" style="float:left;">
                            <input id="<?php echo $first_id;?>" type="checkbox" name="menu[]" value="<?php echo $first_id;?>" style="width:20px;float:left;">
                            <label class="menu-tree-collapse" style="display:inline"> - <?php echo array_key_exists($first_id, $all_menu) ? $all_menu[$first_id]['menu_name'] : '';?></label>
                            <?php 
                                if (!empty($second_menu_arr)) { ?>
                                <ul class="tab nav" style="display: block;">
                                    <?php foreach ($second_menu_arr as $second_id => $third_menu_arr) {?>
                                    <li id="<?php echo $second_id;?>">
                                    <input id="<?php echo $second_id;?>" type="checkbox" name="menu[]" value="<?php echo $second_id;?>" style="width:20px;float:left;">
                                    <label class="menu-tree-collapse" style="display:inline"> - <?php echo array_key_exists($second_id, $all_menu) ? $all_menu[$second_id]['menu_name'] : '';?></label>
                                    <?php 
                                        if (!empty($third_menu_arr)) { ?>
                                        <ul class="tab nav" style="display: block;">
                                            <?php foreach ($third_menu_arr as $third_id => $fourth_menu_arr) {?>
                                            <li id="<?php echo $third_id;?>">
                                            <input id="<?php echo $third_id;?>" type="checkbox" name="menu[]" value="<?php echo $third_id;?>" style="width:20px;float:left;">
                                            <label class="menu-tree-collapse" style="display:inline"> - <?php echo array_key_exists($third_id, $all_menu) ? $all_menu[$third_id]['menu_name'] : '';?></label>
                                            <?php 
                                                if (!empty($fourth_menu_arr)) { ?>
                                                <ul class="tab nav" style="display: block;">
                                                    <?php foreach ($fourth_menu_arr as $fourth_id => $fourth_arr) {?>
                                                    <li id="<?php echo $fourth_id;?>">
                                                    <input id="<?php echo $fourth_id;?>" type="checkbox" name="menu[]" value="<?php echo $fourth_id;?>" style="width:20px;float:left;">
                                                    <label style="display:inline"> - <?php echo array_key_exists($fourth_id, $all_menu) ? $all_menu[$fourth_id]['menu_name'] : '';?></label>
                                                    </li>
                                                    <?php }?>
                                                </ul>
                                                <?php }?>
                                            </li>
                                        <?php }?>
                                        </ul>
                                    <?php }?>
                                    </li>
                                <?php }?>
                            </ul>
                            <?php }?>
                            </li>
                        <?php }?>
                        </div>
                    </ul>
                    <!--     注释数据平台显示数据代码               -->
<!--                    <div style="margin-bottom:10px;">数据平台：</div>-->
<!--                    <ul id="menu-tree-sub-dcplatform" class="nav">-->
<!--                        <div class="col-md-12" style="padding-left:0;margin-bottom:20px;">-->
<!--                        --><?php //
//                            $loop = 0;
//                            foreach ($menu_map_dcplatform as $first_id => $second_menu_arr) {
//                                if (++$loop == 4) {
//                                    $loop = 1;
//                                    echo "</div><div class=\"col-md-12\" style=\"margin-bottom:20px;\">";
//                                }
//                        ?>
<!--                            <li id="--><?php //echo $first_id.'-dcplatform';?><!--" style="float:left;">-->
<!--                            <input id="--><?php //echo $first_id.'-dcplatform';?><!--" type="checkbox" name="menu_dcplatform[]" value="--><?php //echo $first_id.'-dcplatform';?><!--" style="width:20px;float:left;">-->
<!--                            <label class="menu-tree-collapse" style="display:inline"> - --><?php //echo array_key_exists($first_id, $all_menu_dcplatform) ? $all_menu_dcplatform[$first_id]['menu_name'] : '';?><!--</label>-->
<!--                            --><?php //if (!empty($second_menu_arr) || array_key_exists($first_id, $reports_dcplatform)) { ?>
<!--                                <ul class="tab nav" style="display: block;">-->
<!--                                    --><?php //foreach ($second_menu_arr as $second_id => $third_menu_arr) {?>
<!--                                    <li id="--><?php //echo $second_id.'-dcplatform';?><!--">-->
<!--                                        <input id="--><?php //echo $second_id.'-dcplatform';?><!--" type="checkbox" name="menu_dcplatform[]" value="--><?php //echo $second_id.'-dcplatform';?><!--" style="width:20px;float:left;">-->
<!--                                        <label class="menu-tree-collapse" style="display:inline"> - --><?php //echo array_key_exists($second_id, $all_menu_dcplatform) ? $all_menu_dcplatform[$second_id]['menu_name'] : '';?><!--</label>-->
<!--                                    --><?php //if (!empty($third_menu_arr) || array_key_exists($second_id, $reports_dcplatform)) { ?>
<!--                                        <ul class="tab nav" style="display: block;">-->
<!--                                            --><?php //foreach ($third_menu_arr as $third_id => $fourth_menu_arr) {?>
<!--                                            <li id="--><?php //echo $third_id.'-dcplatform';?><!--">-->
<!--                                            <input id="--><?php //echo $third_id.'-dcplatform';?><!--" type="checkbox" name="menu_dcplatform[]" value="--><?php //echo $third_id.'-dcplatform';?><!--" style="width:20px;float:left;">-->
<!--                                            <label class="menu-tree-collapse" style="display:inline"> - --><?php //echo array_key_exists($third_id, $all_menu_dcplatform) ? $all_menu_dcplatform[$third_id]['menu_name'] : '';?><!--</label>-->
<!--                                            --><?php //if (array_key_exists($third_id, $reports_dcplatform)) { ?>
<!--                                                <ul class="tab nav" style="display: block;">-->
<!--                                                    --><?php //foreach ($reports_dcplatform[$third_id] as $menu_id => $menu) { ?>
<!--                                                    <li id="--><?php //echo $menu_id;?><!--">-->
<!--                                                        <input id="--><?php //echo $menu_id;?><!--" type="checkbox" name="menu[]" value="--><?php //echo $menu_id;?><!--" style="width:20px;float:left;">-->
<!--                                                        <label class="menu-tree-collapse" style="display:inline"> - --><?php //echo !empty($menu['menu_name']) ? $menu['menu_name'] : '';?><!--</label>-->
<!--                                                    </li>-->
<!--                                                    --><?php //}?>
<!--                                                </ul>-->
<!--                                                --><?php //}?>
<!--                                            </li>-->
<!--                                        --><?php //}?>
<!--                                        --><?php //
//                                            if (array_key_exists($second_id, $reports_dcplatform)) {
//                                                foreach ($reports_dcplatform[$second_id] as $menu_id => $menu) {
//                                        ?>
<!--                                            <li id="--><?php //echo $menu_id;?><!--">-->
<!--                                                <input id="--><?php //echo $menu_id;?><!--" type="checkbox" name="menu[]" value="--><?php //echo $menu_id;?><!--" style="width:20px;float:left;">-->
<!--                                                <label class="menu-tree-collapse" style="display:inline"> - --><?php //echo !empty($menu['menu_name']) ? $menu['menu_name'] : '';?><!--</label>-->
<!--                                            </li>-->
<!--                                            --><?php //}?>
<!--                                        --><?php //}?>
<!--                                        </ul>-->
<!--                                    --><?php //}?>
<!--                                    </li>-->
<!--                                --><?php //}?>
<!--                                --><?php //
//                                    if (array_key_exists($first_id, $reports_dcplatform)) {
//                                        foreach ($reports_dcplatform[$first_id] as $menu_id => $menu) {
//                                ?>
<!--                                    <li id="--><?php //echo $menu_id;?><!--">-->
<!--                                        <input id="--><?php //echo $menu_id;?><!--" type="checkbox" name="menu[]" value="--><?php //echo $menu_id;?><!--" style="width:20px;float:left;">-->
<!--                                        <label class="menu-tree-collapse" style="display:inline"> - --><?php //echo !empty($menu['menu_name']) ? $menu['menu_name'] : '';?><!--</label>-->
<!--                                    </li>-->
<!--                                    --><?php //}?>
<!--                                --><?php //}?>
<!--                            </ul>-->
<!--                            --><?php //}?>
<!--                            </li>-->
<!--                        --><?php //}?>
<!--                        </div>-->
<!--                    </ul>-->
                    <!--          end          -->
                </div>
            </div>
            <div class="col-md-11">
                <?php echo CHtml::submitButton('提交',array('class'=>'btn btn-success pull-right')); ?>
            </div>
        </fieldset>
        <?php $this->endWidget(); ?>
    </div>
</div>
<style type="text/css">
[class*="span"] {
    margin-left: 0;
}
</style>
<script type="text/javascript">
function menu_click(){
    var id = $(this).attr("id");
    //var check =  $(this).attr("checked");
    var check =  $(this).prop("checked");
    
    //下级菜单
    $(this).parent("li").find("input[type='checkbox']").each(function(){
        if($(this).attr("id")!=id){
            if(check){
                //$(this).attr("checked",true);
                $(this).prop("checked",true);
            }else{
                //$(this).attr("checked",false);
                $(this).prop("checked",false);
            }
        }
    });

    //上级菜单
    $(this).parents("li").each(function(){
        var first = $(this).children("input");
        if(first.attr("id") != id && check){
            //first.attr("checked",true);
            first.prop("checked",true);
        }
    });

    if(!check){
        $(this).parents("li").each(function(){
            var first = $(this).children("input");
            var lid = $(this).attr("id");
            var check_state = false;
            $(this).find("input[type='checkbox']").each(function(){
                //if($(this).attr("id")!= lid && $(this).attr("checked")){
                if($(this).attr("id")!= lid && $(this).prop("checked")){
                    check_state = true;
                }
            });
            if(!check_state){
                if($(this).attr("id")!= id){
                    //first.attr("checked",false);
                    first.prop("checked",false);
                }
            }
        });
    }
}

$(document).ready(function(){
    $("input[type='checkbox']").click(menu_click);
    <?php
        foreach ($menu_ids as $menu_id) {
    ?>
    //$("input[id='<?php echo $menu_id;?>'][type='checkbox']").attr("checked",true);
    $("input[id='<?php echo $menu_id;?>'][type='checkbox']").prop("checked",true);
    <?php }?>
});
</script>