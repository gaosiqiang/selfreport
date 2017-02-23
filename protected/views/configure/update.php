<?php $this->widget('ConfigureNav'); ?>
<div class="table-container">
    <?php $action = strtolower($this->getAction()->getId());?>
    <div style="margin-top: 5px;margin-bottom: 10px;line-height: 1;text-align: right;text-shadow: 1px 2px 3px #ddd;color :#428bca;font-size: 1.05em;">
        <span class="glyphicon glyphicon-leaf" aria-hidden="true" style="top: 2px"></span>
        <?php if ($action == 'create') echo '创建报表'; elseif ($action == 'update') echo '编辑报表';?>
    </div>
    <div class="row">
        <div class="col-md-1" style="width:13%;padding: 0">
            <div class="well sidebar-nav" id="layout" style="position: relative;padding:10px">
                <?php
                $this->renderPartial('stepnav', array(
                    'step'=>$step,
                    'visiable'=>isset($visiable) ? $visiable : 0,
                    'report_id'=>isset($report_id) ? $report_id : '',
                    'action' => $action,
                ));
                ?>
            </div>
        </div>
        <div class="col-md-10 pull-right" style="width:86%;padding: 0">
            <?php
            switch ($step) {
                case 1:
                    $this->renderPartial('update_step1', array(
                        'report_id' => isset($report_id) ? $report_id : '',
                        'platform' => isset($platform) ? $platform : 0,
                        'report_name' => isset($report_name) ? $report_name : '',
                        'tab_state' => isset($tab_state) ? $tab_state : 0,
                        'first_grade' => isset($first_grade) ? $first_grade : '',
                        'second_grade' => isset($second_grade) ? $second_grade : '',
                        'third_grade' => isset($third_grade) ? $third_grade : '',
                        'first_menus' => isset($first_menus) ? $first_menus : array(''=>'请选择一级菜单'),
                        'second_menus' => isset($second_menus) ? $second_menus : array(''=>'请选择二级菜单'),
                        'third_menus' => isset($third_menus) ? $third_menus : array(''=>'请选择三级菜单'),
                    ));
                    break;
                case 2:
                    $this->renderPartial('_step2', array(
                        'report_id' => !empty($report_id) ? $report_id : '',
                        'data_source' => !empty($data_source) ? $data_source : '',
                        'data_sources' => !empty($data_sources) ? $data_sources : array(),
                        'tables' => !empty($tables) ? $tables : array(),
                        'tables_choosed' => !empty($tables_choosed) ? $tables_choosed : array(),
                        'columns' => !empty($columns) ? $columns : array(),
                        'columns_choosed' => !empty($columns_choosed) ? $columns_choosed : array(),
                        'distinct' => !empty($distinct) ? $distinct : 0,
                        'calculation' => !empty($calculation) ? $calculation : array(),
                        'condition_json' => isset($condition_json) ? $condition_json : '',
                        'filters_json' => isset($filters_json) ? $filters_json : '',
                        'group' => !empty($group) ? $group : array(),
                        'order' => !empty($order) ? $order : array(),
                    ));
                    break;
                case 3:
                    $this->renderPartial('_step3', array(
                        'report_id' => !empty($report_id) ? $report_id : '',
                        'show_parts' => $show_parts,
                        'item_columns' => $item_columns,
                        'dwysum_columns' => $dwysum_columns,
                        'dwysum_calculation' => $dwysum_calculation,
                        'columns_info' => !empty($columns_info) ? $columns_info : array(),
                        'city_div_columns' => !empty($city_div_columns) ? $city_div_columns : array(),
                        'conditions' => !empty($conditions) ? $conditions : array(),
                        'item_count' => isset($item_count) ? $item_count : 0,
                        'city_item_count' => isset($city_item_count) ? $city_item_count : 0,
                    ));
                    break;
                case 4:
                    $this->renderPartial('_step4', array(
                        'report_id' => !empty($report_id) ? $report_id : '',
                        'user_group' => !empty($user_group) ? $user_group : array(),
                        'user_groups' => $user_groups,
                        'item_columns' => $item_columns,
                        'privilege_columns' => !empty($privilege_columns) ? $privilege_columns : array(),
                        'item_count' => isset($item_count) ? $item_count : 0,
                    ));
                    break;
                case 5:
                    $this->renderPartial('_step5', array(
                        'report_id' => !empty($report_id) ? $report_id : '',
                        'item_columns' => $item_columns,
                        'columns_info' => $columns_info,
                        'columns_info_citydiv' => $columns_info_citydiv,
                        'columns_info_exists' => $columns_info_exists,
                        'alias_comments' => $alias_comments,
                    ));
                    break;
                case 6:
                    $this->renderPartial('_step6', array(
                        'report_id' => !empty($report_id) ? $report_id : '',
                        'sql' => $sql,
                        'reports' => $reports,
                        'dataProvider' => $dataProvider,
                        'columns_info' => $columns_info,
                        'columns_info_citydiv' => $columns_info_citydiv,
                        'chart_types' => $chart_types,
                        'chart_arr' => $chart_arr
                    ));
                    break;
                case 7:
                    $this->renderPartial('_step7', array(
                        'report_id' => !empty($report_id) ? $report_id : '',
                        'sql' => $sql,
                        'reports' => $reports,
                        'conditions' => $conditions,
                        'dataProvider' => $dataProvider,
                        'columns_info' => $columns_info,
                        'columns_info_citydiv' => $columns_info_citydiv,
                        'show_parts' => $show_parts,
                    ));
                    break;
            }
            ?>
        </div>
    </div>
</div>