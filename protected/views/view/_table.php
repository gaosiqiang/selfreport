<div class="row page-header header-container">
    <div class="col-md-10">
        <h4>详细数据</h4>
    </div>

    <div class="col-md-2">
        <?php 
            $url = array('view/export');
            $url = $url + $params;
            if ($page > 1) {
        ?>
            <!-- 导出弹出浮层  start-->
            <!-- Button trigger modal -->
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#exportModal">导出</button>
            <!-- Modal -->
            <div id="exportModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="exportModalLabel">选择导出部分</h4>
                  </div>
                  <div class="modal-body">
                    <?php 
                        for ($i=1; $i<=$page; $i++) {
                            $url['page'] = $i;
                            echo CHtml::link('Part'.$i, $url, array('class'=>"btn btn-info",'style'=>'margin:3px 3px 3px 3px'));
                        }
                    ?>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                  </div>
                </div>
              </div>
            </div>
            <!-- 导出弹出浮层  end-->
        <?php } else {
                echo CHtml::link('导出', $url,array('class'=>'btn btn-success'));
            }
        ?>
    </div>
</div>

<div id="widget-report" class="table-container">
<?php 
    $week_cols = array();
    if (!empty($conditions) && array_key_exists('date', $conditions) && !empty($conditions['date'])) {
        foreach ($conditions['date'] as $date_conf) {
            if ($date_conf['type'] == 'week' || ($date_conf['type'] == 'dwysum' && $period == 'week')) {
                $week_cols[] = $date_conf['column'];
            }
        }
    }
    $city_div_relations = Common::getCityDivisions();
    $grid_columns = array();
    if (!empty($title_columns)) {
        foreach ($title_columns as $col => $name) {
            if (in_array($col, $show_columns)) {
                if (array_key_exists($col, $city_divisions)) {
                    $c = $city_divisions[$col];
                    if (in_array($c, $show_columns)) {
                        $grid_columns[] = array(
                                'header'=>$name,
                                'value'=>function($data,$row) use ($city_div_relations,$col,$c){
                                    return Utility::getDivisionByCity($city_div_relations, $data[$c], $col);
                                },
                        );
                    } else {
                        $grid_columns[] = array(
                                'header'=>$name,
                                'value'=>'',
                        );
                    }
                } elseif (in_array($col, $week_cols)) {
                    $grid_columns[] = array(
                        'header'=>$name,
                        'value'=>'Utility::turnWeek($data["'.$col.'"])',
                    );
                } else {
                    $grid_columns[] = array(
                            'header'=>$name,
                            'value'=>'Utility::turnNull($data["'.$col.'"])',
                    );
                }
            }
        }
    }
    
    $this->widget('zii.widgets.grid.CGridView', array(
        'id'=>'report-grid',
        'itemsCssClass'=>'table table-striped table-bordered',
        'dataProvider'=>$dataProvider,
        'htmlOptions'=>array('style'=>'padding:0;width:100%;overflow:auto;'),
        'showTableOnEmpty'=>true,
        'emptyText'=>'<div class="content"><div class="alert alert-info">对不起，暂无数据</div></div>',
        'template'=>'{summary}{items}{pager}',
        'ajaxUpdate'=>true,
        'pager'=>array(
                'class'=>'LinkPager',
        ),
        'rowCssClass'=>'',
        'pagerCssClass'=>'text-center',
        'summaryCssClass'=>'summary-left',
        'columns'=>$grid_columns,
        ));
?>
</div>
<?php  ?>
