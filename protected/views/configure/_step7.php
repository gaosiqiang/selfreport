 <ul class="nav nav-tabs"></ul>
<div class="page-header header-container" style="padding: 15px 0 0">
    <div class="row">
        <div class="col-md-12">
            <label style="color: red">检查脚本：</label><br>
            <!-- <pre><?php //echo $sql;?></pre> -->
            <?php echo CHtml::textArea('sql',$sql,array('class'=>'form-control','style'=>'display:inline-block;width:1005px;height:125px;','readonly'=>'readonly'));?>
        </div>
        <?php 
            // 图表数据
            if (in_array('3', $show_parts)) {
        ?>
                <div class="col-md-12" style="margin: 20px 0 0 0;">
                    <label>图表预览：</label>
                    <?php
                    // 图表数据
                        $this->widget('Charts',array(
                            'id'=>$report_id, //自助报表配置表id
                            //'conditions' => $conditions //搜索条件数组
                        ));
                    ?>
                </div>
        <?php } ?>    
            
        <div class="col-md-12" style="margin: 20px 0 0 0;">
            <label>结果预览：</label>
            <span style="font-size:0.85em;color:red;">( ps.仅包含固定条件，不含筛选及权限条件 )</span>
            <div id="widget-preview" style="width:100%;overflow:auto">
            
            <?php 
                $columns = array();
                if (!empty($reports)) {
                    foreach ($reports as $report) {
                        foreach ($report as $col => $value) {
                            if (array_key_exists($col, $columns_info)) {
                                $header = !empty($columns_info[$col]['show_name']) ? $columns_info[$col]['show_name'] : $col;
                            } elseif (array_key_exists($col, $columns_info_citydiv)) {
                                $header = !empty($columns_info_citydiv[$col]['show_name']) ? $columns_info_citydiv[$col]['show_name'] : $col;
                            } else {
                                $header = $col;
                            }
                            $columns[] = array(
                                    'header'=>$header,
                                    'value'=>'$data["'.$col.'"]',
                            );
                        }
                        break;
                    }
                }
    
                $this->widget('zii.widgets.grid.CGridView', array(
                    'id'=>'preview-grid',
                    'itemsCssClass'=>'table table-striped table-bordered',
                    'dataProvider'=>$dataProvider,
                    'htmlOptions'=>array(),
                    'showTableOnEmpty'=>true,
                    'emptyText'=>'<div class="content"><div class="alert alert-info">对不起，暂无数据</div></div>',
                    'template'=>'{summary}{items}{pager}',
                    'ajaxUpdate'=>true,
                    'pager'=>array(
                            'class'=>'LinkPager',
                    ),
                    'rowCssClass'=>'',
                    'pagerCssClass'=>'text-center',
                    'columns'=>$columns,
                    ));
            ?>
            </div>
        </div>
        <div class="col-md-12" style="margin: 20px 0 20px 0">
            <?php echo CHtml::link('完成', Yii::app()->createUrl('/configure/index'), array('class'=>'btn btn-success pull-right'));?>
        </div>
    </div>
</div>
<ul class="nav nav-tabs"></ul>
