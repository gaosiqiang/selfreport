<div class="page-header header-container">
    <div class="row">
        <?php $form=$this->beginWidget('CActiveForm', array(
            'action'=>Yii::app()->createUrl($this->route),
            'method'=>'post',
            'htmlOptions'=>array('class'=>'well','style'=>'padding: 12px;margin-bottom: 7px')
        ));?>
        <div class="row">
            <?php echo CHtml::hiddenField('platform',$platform);?>
            <?php echo CHtml::hiddenField('module',$module);?>
            <?php echo CHtml::hiddenField('mp',$mp);?>
            <?php echo CHtml::hiddenField('report_id',$report_id);?>
            <?php //echo CHtml::hiddenField('prev_title_columns',serialize($title_columns)); 页面参数占用请求头太大，使用session存储，见show.php?>
            <div class="col-md-9" style="padding:0">
                <div>
                    <?php 
                        if (array_key_exists('date', $conditions)) {
                            $count = 0;
                            foreach ($conditions['date'] as $date_filter) {
                                if (!empty($date_filter)) {
                                    echo '<span style="display:inline-block">';
                                    $filter_name = array_key_exists($date_filter['column'], $columns_info)&&!empty($columns_info[$date_filter['column']]['show_name']) ? $columns_info[$date_filter['column']]['show_name'] : $date_filter['column'];
                                    echo '<label>'.$filter_name.'：</label>';
                                    if ($date_filter['attr'] == 'alone') {
                                        if ($date_filter['type'] == 'dwysum' && $period == 'specified_time') {
                                            $start = array_key_exists($date_filter['column'].'_start', $condition_params) ? $condition_params[$date_filter['column'].'_start'] : '';
                                            $end = array_key_exists($date_filter['column'].'_end', $condition_params) ? $condition_params[$date_filter['column'].'_end'] : '';
                                            echo CHtml::radioButtonList('period', $period, ReportConfiguration::$periods, array('separator'=>'&nbsp;'));
                                            echo '&nbsp;&nbsp;';
                                            echo CHtml::textField($date_filter['column'].'_start',$start,array('id'=>'date_day_start_'.$count, 'class'=>'input-small filter-control','data-date-format'=>"yyyy-mm-dd",'title'=>'开始时间','placeholder'=>'开始时间','style'=>'width:120px;margin:3px 7px 3px 0;'));
                                            echo '---&nbsp;';
                                            echo CHtml::textField($date_filter['column'].'_end',$end,array('id'=>'date_day_end_'.$count,'class'=>'input-small filter-control','data-date-format'=>"yyyy-mm-dd",'title'=>'终止时间','placeholder'=>'终止时间','style'=>'width:120px;margin:3px 7px 3px 0;'));
                                        } else {
                                            $date = array_key_exists($date_filter['column'], $condition_params) ? $condition_params[$date_filter['column']] : '';
                                            if ($date_filter['type'] == 'day') {
                                                echo CHtml::textField($date_filter['column'],$date,array('id'=>'date_day_'.$count,'class'=>'input-small filter-control','data-date-format'=>"yyyy-mm-dd",'style'=>'width:120px;margin:3px 7px 3px 0;'));
                                            } elseif ($date_filter['type'] == 'week') {
                                                $thursday = Utility::getDateByWeek(substr($date, 0, 4), substr($date, 4, 2), 4);    //取参数表示周的周四的日期，用来高亮用户选择周
                                                $date_show = date('Ymd', strtotime('-3 day', strtotime($thursday))).'~'.date('Ymd', strtotime('+3 day', strtotime($thursday)));
                                                echo CHtml::hiddenField($date_filter['column'],$date,array('id'=>'date_week_'.$count));
                                                echo '<span class="dropdown">'.CHtml::link($date_show.' <b class="caret"></b>','javascript:;;',array('id'=>'calendar_week_'.$count, 'class'=>'dropdown-toggle input-small','data-date'=>$thursday,'style'=>'margin:3px 7px 3px 0;')).'</span>';
                                            } elseif ($date_filter['type'] == 'month') {
                                                echo CHtml::textField($date_filter['column'],$date,array('id'=>'date_month_'.$count,'class'=>'input-small filter-control','data-date-format'=>"yyyy-mm",'style'=>'width:120px;margin:3px 7px 3px 0;'));
                                            } elseif ($date_filter['type'] == 'dwysum') {
                                                echo CHtml::radioButtonList('period', $period, ReportConfiguration::$periods, array('separator'=>'&nbsp;'));
                                                echo '&nbsp;&nbsp;';
                                                if($period == 'day' || $period == 'all'){
                                                    echo CHtml::textField($date_filter['column'],$date,array('id'=>'date_day_'.$count,'class'=>'input-small filter-control','data-date-format'=>"yyyy-mm-dd",'style'=>'width:120px;margin:3px 7px 3px 0;'));
                                                }elseif($period=='week'){
                                                    $thursday = Utility::getDateByWeek(substr($date, 0, 4), substr($date, 4, 2), 4);    //取参数表示周的周四的日期，用来高亮用户选择周
                                                    $date_show = date('Ymd', strtotime('-3 day', strtotime($thursday))).'~'.date('Ymd', strtotime('+3 day', strtotime($thursday)));
                                                    echo CHtml::hiddenField($date_filter['column'],$date,array('id'=>'date_week_'.$count));
                                                    echo '<span class="dropdown">'.CHtml::link($date_show.' <b class="caret"></b>','javascript:;;',array('id'=>'calendar_week_'.$count, 'class'=>'dropdown-toggle input-small','data-date'=>$thursday,'style'=>'margin:3px 7px 3px 0;')).'</span>';
                                                }elseif($period=='month'){
                                                    echo CHtml::textField($date_filter['column'],$date,array('id'=>'date_month_'.$count,'class'=>'input-small filter-control','data-date-format'=>"yyyy-mm",'style'=>'width:120px;margin:3px 7px 3px 0;'));
                                                }
                                            }
                                        }
                                    } elseif ($date_filter['attr'] == 'range') {
                                        $start = array_key_exists($date_filter['column'].'_start', $condition_params) ? $condition_params[$date_filter['column'].'_start'] : '';
                                        $end = array_key_exists($date_filter['column'].'_end', $condition_params) ? $condition_params[$date_filter['column'].'_end'] : '';
                                        if ($date_filter['type'] == 'day') {
                                            echo CHtml::textField($date_filter['column'].'_start',$start,array('id'=>'date_day_start_'.$count, 'class'=>'input-small filter-control','data-date-format'=>"yyyy-mm-dd",'title'=>'开始时间','placeholder'=>'开始时间','style'=>'width:120px;margin:3px 7px 3px 0;'));
                                            echo '---&nbsp;';
                                            echo CHtml::textField($date_filter['column'].'_end',$end,array('id'=>'date_day_end_'.$count,'class'=>'input-small filter-control','data-date-format'=>"yyyy-mm-dd",'title'=>'终止时间','placeholder'=>'终止时间','style'=>'width:120px;margin:3px 7px 3px 0;'));
                                        } elseif ($date_filter['type'] == 'week') {
                                            $start_date = Utility::getDateByWeek(substr($start, 0, 4), substr($start, 4, 2), 4);    //取参数表示周的周四的日期，用来高亮用户选择周
                                            $end_date = Utility::getDateByWeek(substr($end, 0, 4), substr($end, 4, 2), 4);
                                            $start_date_show = date('Ymd', strtotime('-3 day', strtotime($start_date))).'~'.date('Ymd', strtotime('+3 day', strtotime($start_date)));
                                            $end_date_show = date('Ymd', strtotime('-3 day', strtotime($end_date))).'~'.date('Ymd', strtotime('+3 day', strtotime($end_date)));
                                            echo CHtml::hiddenField($date_filter['column'].'_start',$start,array('id'=>'date_week_start_'.$count));
                                            echo CHtml::hiddenField($date_filter['column'].'_end',$end,array('id'=>'date_week_end_'.$count));
                                            echo '<span class="dropdown">'.CHtml::link($start_date_show.' <b class="caret"></b>','javascript:;;',array('id'=>'calendar_week_start_'.$count, 'class'=>'dropdown-toggle input-small','data-date'=>$start_date,'style'=>'margin:3px 7px 3px 0;')).'</span>';
                                            echo '---&nbsp;';
                                            echo '<span class="dropdown">'.CHtml::link($end_date_show.' <b class="caret"></b>','javascript:;;',array('id'=>'calendar_week_end_'.$count, 'class'=>'dropdown-toggle input-small','data-date'=>$end_date,'style'=>'margin:3px 7px 3px 0;')).'</span>';
                                        } elseif ($date_filter['type'] == 'month') {
                                            echo CHtml::textField($date_filter['column'].'_start',$start,array('id'=>'date_month_start_'.$count, 'class'=>'input-small filter-control','data-date-format'=>"yyyy-mm",'title'=>'开始时间','placeholder'=>'开始时间','style'=>'width:120px;margin:3px 7px 3px 0;'));
                                            echo '---&nbsp;';
                                            echo CHtml::textField($date_filter['column'].'_end',$end,array('id'=>'date_month_end_'.$count,'class'=>'input-small filter-control','data-date-format'=>"yyyy-mm",'title'=>'终止时间','placeholder'=>'终止时间','style'=>'width:120px;margin:3px 7px 3px 0;'));
                                        } elseif ($date_filter['type'] == 'dwysum') {
                                            echo CHtml::radioButtonList('period', $period, ReportConfiguration::$periods, array('separator'=>'&nbsp;'));
                                            echo '&nbsp;&nbsp;';
                                            if($period == 'day' || $period == 'all' || $period == 'specified_time'){
                                                echo CHtml::textField($date_filter['column'].'_start',$start,array('id'=>'date_day_start_'.$count, 'class'=>'input-small filter-control','data-date-format'=>"yyyy-mm-dd",'title'=>'开始时间','placeholder'=>'开始时间','style'=>'width:120px;margin:3px 7px 3px 0;'));
                                                echo '---&nbsp;';
                                                echo CHtml::textField($date_filter['column'].'_end',$end,array('id'=>'date_day_end_'.$count,'class'=>'input-small filter-control','data-date-format'=>"yyyy-mm-dd",'title'=>'终止时间','placeholder'=>'终止时间','style'=>'width:120px;margin:3px 7px 3px 0;'));
                                            }elseif($period=='week'){
                                                $start_date = Utility::getDateByWeek(substr($start, 0, 4), substr($start, 4, 2), 4);    //取参数表示周的周四的日期，用来高亮用户选择周
                                                $end_date = Utility::getDateByWeek(substr($end, 0, 4), substr($end, 4, 2), 4);
                                                $start_date_show = date('Ymd', strtotime('-3 day', strtotime($start_date))).'~'.date('Ymd', strtotime('+3 day', strtotime($start_date)));
                                                $end_date_show = date('Ymd', strtotime('-3 day', strtotime($end_date))).'~'.date('Ymd', strtotime('+3 day', strtotime($end_date)));
                                                echo CHtml::hiddenField($date_filter['column'].'_start',$start,array('id'=>'date_week_start_'.$count));
                                                echo CHtml::hiddenField($date_filter['column'].'_end',$end,array('id'=>'date_week_end_'.$count));
                                                echo '<span class="dropdown">'.CHtml::link($start_date_show.' <b class="caret"></b>','javascript:;;',array('id'=>'calendar_week_start_'.$count, 'class'=>'dropdown-toggle input-small','data-date'=>$start_date,'style'=>'margin:3px 7px 3px 0;')).'</span>';
                                                echo '---&nbsp;';
                                                echo '<span class="dropdown">'.CHtml::link($end_date_show.' <b class="caret"></b>','javascript:;;',array('id'=>'calendar_week_end_'.$count, 'class'=>'dropdown-toggle input-small','data-date'=>$end_date,'style'=>'margin:3px 7px 3px 0;')).'</span>';
                                            }elseif($period=='month'){
                                                echo CHtml::textField($date_filter['column'].'_start',$start,array('id'=>'date_month_start_'.$count, 'class'=>'input-small filter-control','data-date-format'=>"yyyy-mm",'title'=>'开始时间','placeholder'=>'开始时间','style'=>'width:120px;margin:3px 7px 3px 0;'));
                                                echo '---&nbsp;';
                                                echo CHtml::textField($date_filter['column'].'_end',$end,array('id'=>'date_month_end_'.$count,'class'=>'input-small filter-control','data-date-format'=>"yyyy-mm",'title'=>'终止时间','placeholder'=>'终止时间','style'=>'width:120px;margin:3px 7px 3px 0;'));
                                            }
                                        }
                                    }
                                    echo '</span>';
                                    ++$count;
                                }
                            }
                        }
                        if (array_key_exists('list', $conditions)) {
                            foreach ($conditions['list'] as $list_filter) {
                                if (!empty($list_filter)) {
                                    echo '<span style="display:inline-block">';
                                    if (!empty($list_filter['name'])) {
                                        $filter_name = $list_filter['name'];
                                    } else {
                                        $filter_name = array_key_exists($list_filter['column'], $columns_info)&&!empty($columns_info[$list_filter['column']]['show_name']) ? $columns_info[$list_filter['column']]['show_name'] : $list_filter['column'];
                                    }
                                    echo '<label>'.$filter_name.'：</label>';
                                    $list_value = array_key_exists($list_filter['column'], $condition_params) ? $condition_params[$list_filter['column']] : '';
                                    $list = DictList::getNormalPresetByID($list_filter['dict']);//暂只支持预置列表
                                    if (!empty($list) && $list_filter['value_type'] == 1 && $list_filter['dict'] != 'media') {
                                        $name_list = array_combine($list, $list);
                                        unset($list);
                                        $list = $name_list;
                                        unset($name_list);
                                    }
                                    $list = Common::addTitleToList($list);
                                    echo CHtml::dropDownList($list_filter['column'], $list_value, $list, array('class'=>'filter-control', 'style'=>'width:120px;margin:3px 7px 3px 0;'));
                                    echo '</span>';
                                }
                            }
                        }
                        if (array_key_exists('linked', $conditions)) {
                            $count = 0;
                            foreach ($conditions['linked'] as $col) {
                                echo '<span style="display:inline-block">';
                                ++$count;
                                echo CHtml::hiddenField('linked_dict_'.$count, $col['dict']);
                                $first = array_key_exists('first', $col) ? $col['first'] : array();
                                $second = array_key_exists('second', $col) ? $col['second'] : array();
                                $third = array_key_exists('third', $col) ? $col['third'] : array();
                                $fourth = array_key_exists('fourth', $col) ? $col['fourth'] : array();
                                $first_value = $second_value = $third_value = '';
                            
                                if (!empty($first)) {
                                    $filter_name = array_key_exists($first['column'], $columns_info)&&!empty($columns_info[$first['column']]['show_name']) ? $columns_info[$first['column']]['show_name'] : $first['name'];
                                    echo '<label>'.$filter_name.'：</label>';
                                    $first_value = array_key_exists($first['column'], $condition_params) ? $condition_params[$first['column']] : '';
                                    $list = DictList::getLinkedPresetByID($col['dict']);//暂只支持预置列表
                                    if (!empty($list) && $first['value_type'] == 1 && $col['dict'] != 'media-type-list') {
                                        $name_list = array_combine($list, $list);
                                        unset($list);
                                        $list = $name_list;
                                        unset($name_list);
                                    }
                                    if (!in_array($col['dict'], DictList::$sum_detail_list)) {
                                        $list = Common::addTitleToList($list);
                                    }
                                    echo CHtml::dropDownList($first['column'], $first_value, $list, array('id'=>'first_list_'.$count, 'class'=>'filter-control', 'style'=>'width:120px;margin:3px 7px 3px 0;'));
                                }
                                if (!empty($second)) {
                                    echo CHtml::hiddenField('second_value_type_'.$count, $second['value_type']);
                                    $filter_name = array_key_exists($second['column'], $columns_info)&&!empty($columns_info[$second['column']]['show_name']) ? $columns_info[$second['column']]['show_name'] : $second['name'];
                                    echo '<label>'.$filter_name.'：</label>';
                                    $second_value = array_key_exists($second['column'], $condition_params) ? $condition_params[$second['column']] : '';
                                    $list = DictList::getLinkedPresetByID($col['dict'],2,$first_value);
                                    if (!empty($list) && $second['value_type'] == 1 && $col['dict'] != 'media-type-list') {
                                        $name_list = array_combine($list, $list);
                                        unset($list);
                                        $list = $name_list;
                                        unset($name_list);
                                    }
                                    if (!in_array($col['dict'], DictList::$sum_detail_list)) {
                                        $list = Common::addTitleToList($list);
                                    }
                                    echo CHtml::dropDownList($second['column'], $second_value, $list, array('id'=>'second_list_'.$count, 'class'=>'filter-control', 'style'=>'width:120px;margin:3px 7px 3px 0;'));
                                }
                                if (!empty($third)) {
                                    echo CHtml::hiddenField('third_value_type_'.$count, $third['value_type']);
                                    $filter_name = array_key_exists($third['column'], $columns_info)&&!empty($columns_info[$third['column']]['show_name']) ? $columns_info[$third['column']]['show_name'] : $third['name'];
                                    echo '<label>'.$filter_name.'：</label>';
                                    $third_value = array_key_exists($third['column'], $condition_params) ? $condition_params[$third['column']] : '';
                                    $list = DictList::getLinkedPresetByID($col['dict'],3,$second_value);
                                    if (!empty($list) && $third['value_type'] == 1) {
                                        $name_list = array_combine($list, $list);
                                        unset($list);
                                        $list = $name_list;
                                        unset($name_list);
                                    }
                                    if (!in_array($col['dict'], DictList::$sum_detail_list)) {
                                        $list = Common::addTitleToList($list);
                                    }
                                    echo CHtml::dropDownList($third['column'], $third_value, $list, array('id'=>'third_list_'.$count, 'class'=>'filter-control', 'style'=>'width:120px;margin:3px 7px 3px 0;'));
                                }
                                if (!empty($fourth)) {
                                    echo CHtml::hiddenField('fourth_value_type_'.$count, $fourth['value_type']);
                                    $filter_name = array_key_exists($fourth['column'], $columns_info)&&!empty($columns_info[$fourth['column']]['show_name']) ? $columns_info[$fourth['column']]['show_name'] : $fourth['name'];
                                    echo '<label>'.$filter_name.'：</label>';
                                    $list_value = array_key_exists($fourth['column'], $condition_params) ? $condition_params[$fourth['column']] : '';
                                    $list = DictList::getLinkedPresetByID($col['dict'],4,$third_value);
                                    if (!empty($list) && $fourth['value_type'] == 1) {
                                        $name_list = array_combine($list, $list);
                                        unset($list);
                                        $list = $name_list;
                                        unset($name_list);
                                    }
                                    if (!in_array($col['dict'], DictList::$sum_detail_list)) {
                                        $list = Common::addTitleToList($list);
                                    }
                                    echo CHtml::dropDownList($fourth['column'], $list_value, $list, array('id'=>'fourth_list_'.$count, 'class'=>'filter-control', 'style'=>'width:120px;margin:3px 7px 3px 0;'));
                                }
                                echo '</span>';
                            }
                        }
                        if (array_key_exists('text', $conditions)) {
                            $count = 0;
                            foreach ($conditions['text'] as $text_filter) {
                                if (!empty($text_filter)) {
                                    echo '<span style="display:inline-block">';
                                    $filter_name = array_key_exists($text_filter['column'], $columns_info)&&!empty($columns_info[$text_filter['column']]['show_name']) ? $columns_info[$text_filter['column']]['show_name'] : $text_filter['column'];
                                    echo '<label>'.$filter_name.'：</label>';
                                    $text = array_key_exists($text_filter['column'], $condition_params) ? $condition_params[$text_filter['column']] : '';
                                    if ($text_filter['type'] == 'text') {
                                        echo CHtml::textField($text_filter['column'],$text,array('class'=>'filter-control', 'style'=>'width:120px;margin:3px 7px 3px 0;'));
                                    } elseif ($text_filter['type'] == 'city') {
                                        echo CHtml::textField($text_filter['column'],$text,array('id'=>'text_city_'.$count, 'class'=>'filter-control typeahead', 'style'=>'width:120px;margin:3px 7px 3px 0;'));
                                        ++$count;
                                    }
                                    echo '</span>';
                                }
                            }
                        }
                    ?>
                </div>
            </div>
            <div class="col-md-3">
                <div class="pull-right">
                    <?php echo CHtml::submitButton('查询',array('class'=>'btn btn-info','name'=>'sub')); ?>
                    <div class="btn btn-success" data-toggle="collapse" data-target="#columns">选择数据项</div>
                </div>
            </div>
            <div id="columns" class="col-md-12 collapse" style="margin-top:5px;padding:0">
                <div class="row">
                    <div class='alert alert-warning' style="margin-bottom: 0px;">
                        <?php echo CHtml::checkBoxList('show_columns', $show_columns, $title_columns, array('separator'=>'&nbsp;&nbsp;','checkAll'=>'全选'));?>
                    </div>
                </div>
            </div>
        </div>
        <?php $this->endWidget(); ?>
    </div>
</div>
<style>
<!--
input[type="radio"], input[type="checkbox"] {
    margin-top: 0;
}
label {
    vertical-align: middle;
}
-->
</style>
<script type="text/javascript">
$(document).ready(function(){
    $(".tt-input").css("vertical-align","baseline");    //将城市提示框扶正，由于该属性是由控件写在标签内，所以需要JS介入
    /* $(".typeahead,.tt-query,.tt-hint").css("-webkit-border-radius","2px");
    $(".typeahead,.tt-query,.tt-hint").css("-moz-border-radius","2px");
    $(".typeahead,.tt-query,.tt-hint").css("border-radius","2px"); */
});

$('#columns').on('hidden.bs.collapse', function () {
    if($('#columns').hasClass('in')) {
        $('#columns').removeClass('in');
    }
})

$("[id^=first_list_]").change(function(){
    var id = $(this).attr('id');    //获取当前对象id属性 
    var arr = id.split("_");
    dict=$("#linked_dict_"+arr[2]).val();
    first=$("#first_list_"+arr[2]).val();
    second_value_type=$("#second_value_type_"+arr[2]).val();
    $("#second_list_"+arr[2]).load("/view/ajaxsecondlist",{dict:dict,first:first,second_value_type:second_value_type},function(response,status){
        if (status=="success")
        {
            $("#second_list_"+arr[2]).empty();
            $("#second_list_"+arr[2]).append(response);
            $("#third_list_"+arr[2]).empty();
            $("#fourth_list_"+arr[2]).empty();
            //console.log($("#fourth_list").length);
            if ($.inArray(dict, window.sum_detail_list)>=0) {   //window.sum_detail_list在common.js中定义
                second=$("#second_list_"+arr[2]).val();
                third_value_type=$("#third_value_type_"+arr[2]).val();
                $("#third_list_"+arr[2]).load("/view/ajaxthirdlist",{dict:dict,second:second,third_value_type:third_value_type},function(response,status){
                    if (status=="success")
                    {
                        $("#third_list_"+arr[2]).empty();
                        $("#third_list_"+arr[2]).append(response);
                        $("#fourth_list_"+arr[2]).empty();
                        //console.log($("#fourth_list").length);
                        third=$("#third_list_"+arr[2]).val();
                        fourth_value_type=$("#fourth_value_type_"+arr[2]).val();
                        $("#fourth_list_"+arr[2]).load("/view/ajaxfourthlist",{dict:dict,third:third,fourth_value_type:fourth_value_type},function(response,status){
                            if (status=="success")
                            {
                                $("#fourth_list_"+arr[2]).empty();
                                $("#fourth_list_"+arr[2]).append(response);
                            }
                        });
                    }
                });
            }
        }
    });
});

$("[id^=second_list_]").change(function(){
    var id = $(this).attr('id');    //获取当前对象id属性 
    var arr = id.split("_");
    dict=$("#linked_dict_"+arr[2]).val();
    second=$("#second_list_"+arr[2]).val();
    third_value_type=$("#third_value_type_"+arr[2]).val();
    $("#third_list_"+arr[2]).load("/view/ajaxthirdlist",{dict:dict,second:second,third_value_type:third_value_type},function(response,status){
        if (status=="success")
        {
            $("#third_list_"+arr[2]).empty();
            $("#third_list_"+arr[2]).append(response);
            $("#fourth_list_"+arr[2]).empty();
            //console.log($("#fourth_list").length);
            if ($.inArray(dict, window.sum_detail_list)>=0) {   //window.sum_detail_list在common.js中定义
                third=$("#third_list_"+arr[2]).val();
                fourth_value_type=$("#fourth_value_type_"+arr[2]).val();
                $("#fourth_list_"+arr[2]).load("/view/ajaxfourthlist",{dict:dict,third:third,fourth_value_type:fourth_value_type},function(response,status){
                    if (status=="success")
                    {
                        $("#fourth_list_"+arr[2]).empty();
                        $("#fourth_list_"+arr[2]).append(response);
                    }
                });
            }
        }
    });
});

$("[id^=third_list_]").change(function(){
    var id = $(this).attr('id');    //获取当前对象id属性 
    var arr = id.split("_");
    dict=$("#linked_dict_"+arr[2]).val();
    third=$("#third_list_"+arr[2]).val();
    fourth_value_type=$("#fourth_value_type_"+arr[2]).val();
    $("#fourth_list_"+arr[2]).load("/view/ajaxfourthlist",{dict:dict,third:third,fourth_value_type:fourth_value_type},function(response,status){
        if (status=="success")
        {
            $("#fourth_list_"+arr[2]).empty();
            $("#fourth_list_"+arr[2]).append(response);
        }
    });
});

$("input[name='period']").change(function(){
    var hostInfo="<?php echo Yii::app()->request->hostInfo;?>";
    var route="<?php echo $this->route;?>";
    var period = $("input[name='period']:checked").val();
    <?php 
        $params = '';
        foreach ($base_params as $k => $v) {
            if ($k != 'period') {
                $params .= "&$k=$v";
            }
        }
    ?>
    window.location.href=hostInfo + '/'+ route + "?period="+period+"<?php echo $params;?>";
})
</script>