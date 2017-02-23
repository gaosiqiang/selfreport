<ul class="nav nav-tabs"></ul>
<div class="page-header header-container" style="padding: 15px 0 0">
    <div class="row">
        <?php $form=$this->beginWidget('CActiveForm', array(
            'action'=>Yii::app()->createUrl($this->route,array('step'=>5)),
            'htmlOptions'=>array('id'=>'form')
        )); ?>
        <?php 
            echo CHtml::hiddenField('report_id', $report_id);
        ?>
        <div class="row">
            <div class="col-md-12">
                <!-- <label>数据项名称及定义：</label> -->
                <table class="table table-striped table-bordered detail">
                    <tbody>
                        <tr>
                            <th width="30%">字段</th>
                            <th width="20%">数据项名称</th>
                            <th width="50%">定义</th>
                        </tr>
                        <?php if(!empty($item_columns)){
                            foreach ($item_columns as $key => $column){
                                if (!empty($key)) {
                                    $column_name = '';
                                    $column_define = '';
                                    if ($columns_info_exists) {
                                        if (array_key_exists($column, $columns_info)) {
                                            $column_name = array_key_exists($column, $columns_info) ? $columns_info[$column]['show_name'] : '';
                                            $column_define = array_key_exists($column, $columns_info) ? $columns_info[$column]['define'] : '';
                                        } elseif (array_key_exists($column, $columns_info_citydiv)) {
                                            $column_name = array_key_exists($column, $columns_info_citydiv) ? $columns_info_citydiv[$column]['show_name'] : '';
                                            $column_define = array_key_exists($column, $columns_info_citydiv) ? $columns_info_citydiv[$column]['define'] : '';
                                        }
                                    } else {
                                        if (array_key_exists($column, $alias_comments)) {
                                            $comment = array_key_exists($column, $alias_comments) ? $alias_comments[$column] : '';
                                            if (!empty($comment)) {
                                                $array = explode(Yii::app()->params['column_define_separator'], $comment);
                                                $column_name = empty($column_name) && isset($array[0]) && !empty($array[0]) ? $array[0] : '';
                                                $column_define = empty($column_define) && isset($array[1]) && !empty($array[1]) ? $array[1] : '';
                                            }
                                        }
                                    }
                        ?>
                        <tr>
                            <td width="30%" style="text-align: left"><?php echo $column;?></td>
                            <td width="20%"><?php echo CHtml::textField('col_'.$column.'_name', $column_name);?></td>
                            <td width="50%"><?php echo CHtml::textField('col_'.$column.'_define',$column_define,array('style'=>'width:420px'));?></td>
                        </tr>
                        <?php }}}?>
                    </tbody>
                </table>
            </div>
            <div class="col-md-12" style="margin: 20px 0 20px 0">
                <?php 
                    $action = strtolower($this->getAction()->getId());
                    if ($action == 'create') {
                        echo CHtml::submitButton('下一步>>', array('class'=>'btn btn-info pull-right'));
                    } elseif ($action == 'update') {
                        echo CHtml::submitButton('保存', array('class'=>'btn btn-info pull-right'));
                    }
                ?>
            </div>
        </div>
        <?php $this->endWidget(); ?>
    </div>
</div>
<ul class="nav nav-tabs"></ul>
