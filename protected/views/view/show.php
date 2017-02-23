<?php
$this->renderPartial('nav');

if (in_array('1', $show_parts)) {
    // 搜索过滤
    $this->renderPartial('_filter', array(
        'platform' => $platform,
        'module' => $module,
        'mp' => $mp,
        'report_id' => $report_id,
        'period' => $period,
        'conditions' => $conditions,
        'columns_info' => $columns_info,
        'title_columns' => $title_columns,
        'show_columns' => $show_columns,
        'condition_params' => $condition_params,
        'base_params' => $base_params,
    ));
}
// 主体数据
/* 
$this->renderPartial('main/' . $kind . '_report', array(
    'city' => $city,
    'report' => $report,
    'comparison_month' => $comparison_month,
    'comparison_year' => $comparison_year,
));
 */


?>

<?php 
// 图表数据
if (in_array('3', $show_parts)) {
?>
<style>
<!--
body {
    overflow: -moz-scrollbars-vertical;
}
-->
</style>
<div class="row page-header header-container">
<?php 
    // 图表数据
    $this->widget('Charts',array(
        'id'=>$report_id, //自助报表配置表id
        //'conditions' => $condition_params, //搜索条件数组，在Charts内容调用方法生成
    ));
?>
</div>
<?php } ?>

<?php
if (in_array('4', $show_parts)) {
    // 详细数据
    $this->renderPartial('_table', array(
        'dataProvider' => $dataProvider,
        'title_columns' => $title_columns,
        'show_columns' => $show_columns,
        'city_divisions' => $city_divisions,
        'period' => $period,
        'conditions' => $conditions,
        'params' => $params,
        'page' => $page,
    ));
}

if (in_array('5', $show_parts)) {
    // 数据项定义
    $this->renderPartial('_define', array(
        'columns_info' => $columns_info,
        'columns_info_citydiv' => $columns_info_citydiv,
        'city_div_cols' => $city_div_cols,
    ));
?>
<script type="text/javascript">
    $("#widget-report").css('border-bottom','0');
</script>
<?php 
}
?>
<?php 
//此处如果在_filter.php设置的话，后面的图表展示使用该设置时就成了当前结果字段而非上一次查询的结果字段，导致图表无法展示，所以在最后设置
Yii::app()->user->setstate('selfreport_prev_title_columns',serialize($title_columns));
?>

