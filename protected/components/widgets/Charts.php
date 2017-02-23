<?php
/**
 * 图表展示
 * @author developan
 */

class Charts extends CWidget
{

    public $dataSource = array();
    public $id = '';
    public $conditions = '';
    public $fetchRecordCnt = 10;
    //public $dataChart = array();



    public function run()
    {
        // 接收 传过来的参数
        //$dataCharts = $dataSource;
        //$dataCharts['chart'] = 'line';
        $sql = '';
        $report_id = $this->id;
        //$conditions = $this->conditions;
        //$conditions = $this->conditions ? $this->conditions : array();var_dump($conditions);//exit;
        $configuration = ReportConfiguration::getReportConfByID($report_id);
        $fieldArr = ReportConfiguration::getColumnsChart($report_id);;
        //var_dump($fieldArr);exit;

        $platform = Common::getNumParam('platform');
        $module = Common::getNumParam('module');
        $mp = Common::getNumParam('mp');
        $report_id = Common::getStringParam('report_id');
        $period = Common::getStringParam('period', 'day');
        $params = array(
                'platform' => $platform,
                'module' => $module,
                'mp' => $mp,
                'report_id' => $report_id,
                'period' => $period,
        );

        $charts = $configuration->charts;//var_dump($charts);exit;
        //$conditionsPost = $this->conditions; 下面会生成$condition_params
        $conditions = unserialize($configuration->conditions);
        $linked = array_key_exists('linked', $conditions) ? $conditions['linked'] : array();
        $date_cdts = array_key_exists('date', $conditions) ? $conditions['date'] : array();
        //var_dump($conditions);exit;
        
        //根据查询条件配置
        $query_sql = unserialize($configuration->query_sql);
        $select = !empty($query_sql)&&array_key_exists('select', $query_sql) ? $query_sql['select'] : array();
        $columns = !empty($query_sql)&&array_key_exists('columns', $query_sql) ? $query_sql['columns'] : array();
        $distinct = !empty($query_sql)&&array_key_exists('distinct', $query_sql) ? $query_sql['distinct'] : 0;
        $from = !empty($query_sql)&&array_key_exists('from', $query_sql) ? $query_sql['from'] : '';
        $where = !empty($query_sql)&&array_key_exists('where', $query_sql)&&!empty($query_sql['where']) ? $query_sql['where'] : '1=1';
        $group = !empty($query_sql)&&array_key_exists('group', $query_sql) ? $query_sql['group'] : array();
        //$order = !empty($query_sql)&&array_key_exists('order', $query_sql) ? $query_sql['order'] : array();
        
        //数据项定义
        $columns_info = ColumnDefine::getColumnsInfoByReport($report_id,$columns);
        $columns_info_citydiv = ColumnDefine::getCityDivColumnsInfoByReport($report_id);
        
        if ($charts && $period != 'specified_time') {
            $break = false;             //不展示图表
            $chart_arr_all = unserialize($charts);
            //var_dump($chart_arr_all);
            $dataChartsAll = array();
            $dataCharts = array();
            foreach ($chart_arr_all as $key => $dataCharts) {
                $is_x_fixed = false;        //是否对x轴的无值项赋过初值
                $xAxis = $dataCharts['config']['xAxis'];
                if(!array_key_exists('yAxisGroup', $dataCharts['config']))
                    $dataCharts['config']['yAxisGroup'] = '';
                    
                $yAxisGroup = $dataCharts['config']['yAxisGroup'];
    
                // $chartArr['chart'] = $chart_type;
                // $chartArr['title'] = $title_text;
                // $chartArr['title_sub'] = $title_text_sub;
                // $chartArr['config']['xAxis'] = $xAxis;
                // $chartArr['config']['yAxis'] = $yAxis;
    
                $order = $xAxis ? array($xAxis=>'asc') : array();
                list($does_query, $table_exists, $show_columns, $title_columns, $sql, $command_params, $condition_params, $city_divisions, $city_div_cols) = Common::assembleConditions($configuration, $report_id, $params, $period, $conditions, $select, $columns, $distinct, $from, $where, $group, $order, $columns_info, $columns_info_citydiv);
                //list($query_condition, $command_params, $condition_params, $does_query) = Common::assembleQueryConditions($report_id, $conditions, $group, $order, $title_columns, $period);
    
                $reports = array();
                if ($does_query) {
                    list($db,$model) = Common::getDBConnection($configuration->data_source);
                    if ($db && $model) {
                        if ($table_exists) {
                            $sql_params = array();
                            
                            //对于符号的过滤，当这些符号是正常的查询内容时，也不会报错，因为采用参数绑定方式的sql中不包含查询值，所以检查的是原始sql是否包含这些字符
                            if (strpos($sql, ';') === false && strpos($sql, '#') === false && strpos($sql, '-- ') === false && strpos($sql, '/*') === false) {
                                $count_sql = "SELECT COUNT(*) FROM ($sql) AS table_tmp_count";
                                if (!empty($command_params)) {
                                    $count_command = $db->createCommand($count_sql);
                                    
                                    foreach ($command_params as $command) {
                                        $sql_params[$command['name']] = $command['value'];
                                        $count_command->bindValue($command['name'], $command['value'], $command['data_type']);
                                    }
                                    $count = $count_command->queryScalar();
                                } else {
                                    $count = $db->createCommand($count_sql)->queryScalar();
                                }
                                
                                $params = $params + $condition_params;
                                //echo $sql;
                                $dataProvider=new SqlDataProvider($sql,array(   //$sql
                                        'db' => $db,
                                        'keyField' => false,    //SqlDataProvider重写CSqlDataProvider的fetchKeys方法
                                        'totalItemCount' => $count,
                                        'pagination' => false,
                                        /* 'pagination'=>array( //分页导致图表展示数据不全
                                                'pageSize'=>15,
                                                'params'=>$params,
                                        ), */
                                        'params' => $sql_params,
                                ));
                                //var_dump($dataProvider);exit;
                                $reports = $dataProvider->data;
                                //$reports = $reportsArr[0];
                                //var_dump($reports);exit;
                            } else {
                                Yii::app()->user->setFlash('error', '查询sql的语句有误，包含结束或注释字符，请与管理员联系。');
                            }
                            $db->active = false;
                           } else {
                            Yii::app()->user->setFlash('error', '查询数据表不存在，请与管理员联系。');
                        }
                    } else {
                        Yii::app()->user->setFlash('error', '无法连接数据源，请与管理员联系。');
                    }
                }
    
                //取城市与归属分区的对应关系
                $city_div_relations = Common::getCityDivisions();
                $loop = 0;
                $output_data = array();
                foreach ($reports as $data) {
                    //var_dump($data);exit;
                    foreach ($data as $key => $value) {
                        if (array_key_exists($key, $city_div_cols)) {
                            $divs = $city_div_cols[$key];
                            foreach ($divs as $k => $div) {
                                $val = Utility::getDivisionByCity($city_div_relations, $value, $div);
                                $output_data[$loop][$div] = $val;
                            }
                        }
                        $value = Utility::turnNull($value);
                        $output_data[$loop][$key] = $value;
                    }
                    ++$loop;
                }
                //var_dump($output_data);exit;
                
                /////////////////////////////////////////////图表处理区开始/////////////////////////////////////////////
                if (!empty($output_data)) {
                    $xAxisField = $dataCharts['config']['xAxis'];
                    $yAxisField = $dataCharts['config']['yAxis'];
                    if (!in_array($xAxisField, $show_columns)) {
                        $xAxisField = '';
                    }
                    if (is_array($yAxisField) && !empty($yAxisField)) {
                        foreach ($yAxisField as $k => $v) {
                            if (!in_array($v, $show_columns)) {
                                unset($yAxisField[$k]);
                            }
                        }
                    }
                    
        
                    if(!empty($linked))
                    {
                        foreach ($linked as $link) {
                            $first = array_key_exists('first', $link) ? $link['first'] : array();
                            $second = array_key_exists('second', $link) ? $link['second'] : array();
                            $third = array_key_exists('third', $link) ? $link['third'] : array();
                            $fourth = array_key_exists('fourth', $link) ? $link['fourth'] : array();
                            $dict = array_key_exists('dict', $link) ? $link['dict'] : '';
                            
                            if (in_array($dict, DictList::$sum_detail_list)) {
                                $reference = '';
                                if (!empty($fourth)) {
                                    $reference = array_key_exists($fourth['column'], $condition_params) ? $condition_params[$fourth['column']] : '';
                                } elseif (!empty($third)) {
                                    $reference = array_key_exists($third['column'], $condition_params) ? $condition_params[$third['column']] : '';
                                } elseif (!empty($second)) {
                                    $reference = array_key_exists($second['column'], $condition_params) ? $condition_params[$second['column']] : '';
                                } elseif (!empty($first)) {
                                    $reference = array_key_exists($first['column'], $condition_params) ? $condition_params[$first['column']] : '';
                                }
                                if(!empty($reference))
                                {
                                    if($reference==Common::LINK_LIST_ALL_AREAS || $reference==Common::LINK_LIST_SUMMARY_AREA)
                                    {
                                        $yAxisGroupField = array_key_exists('extra_column', $first) ? $first['extra_column'] : '';
                                    }
                                    elseif($reference==Common::LINK_LIST_ALL_REGIONS || $reference==Common::LINK_LIST_SUMMARY_REGION)
                                    {
                                        $yAxisGroupField = array_key_exists('extra_column', $second) ? $second['extra_column'] : '';
                                    }
                                    elseif($reference==Common::LINK_LIST_ALL_CITIES || $reference==Common::LINK_LIST_SUMMARY_CITY || 
                                        $reference==Common::LINK_LIST_ALL_BRANCHES || $reference==Common::LINK_LIST_SUMMARY_BRANCH)
                                    {
                                        $yAxisGroupField = array_key_exists('extra_column', $third) ? $third['extra_column'] : '';
                                    }
                                    elseif($reference==Common::LINK_LIST_ALL_TEAMS)
                                    {
                                        //$yAxisGroupField = array_key_exists('extra_column', $fourth) ? $fourth['extra_column'] : '';
                                        $break = true;
                                    }
                                    else
                                    {
                                        $yAxisGroupField = "";
                                    }
                                }
                                else
                                {
                                    $yAxisGroupField = "";
                                }
                            }
                        }
                    }
                    else
                    {
                        $yAxisGroupField = $dataCharts['config']['yAxisGroup'];
                    }
                    
                    //初始化
                    if (!$break) {
                        //var_dump($yAxisField);exit;
                        $yAxisFieldArr = $yAxisField;
                        $xAxisArr = array(); 
                        $yAxisArr = array();
                        $yAxisShowArr = array();
                        $series = array();
                        //纵轴最大值
                        //$yAxisDataMax = 0;
            
                        //如果纵轴分组字段存在，计算分组字段
                        //set_time_limit(0);
                        //var_dump($yAxisGroupField);exit;
                        if($yAxisGroupField)
                        {
                            $yAxis = isset($yAxisFieldArr[0]) ? $yAxisFieldArr[0] : '';
                            $yAxisGroupArr = array();
                            //var_dump($output_data);exit;
                            foreach ($output_data as $key => $value) {
                                //var_dump($value[$yAxisGroupField]);exit;
                                //分组数不能超过10条
                                if (count($yAxisGroupArr)<10) {
                                    if(array_key_exists($yAxisGroupField, $value) && !empty($value[$yAxisGroupField]))
                                    {
                                        //var_dump($value[$yAxisGroupField]);
                                        if (!in_array($value[$yAxisGroupField], $yAxisGroupArr)) {
                                            $yAxisGroupArr[] = $value[$yAxisGroupField] ;
                                            $yAxisArr[$value[$yAxisGroupField]] = array();
                                        }
                                    }
                                }
                            }
                            //var_dump($yAxisArr);exit;
                            //var_dump($yAxisGroupArr);
                            foreach ($output_data as $key => $value) {
                                if (array_key_exists($xAxisField, $value)) {
                                    if (is_numeric($value[$xAxisField]) && strlen($value[$xAxisField])==10) {
                                            //var_dump(strlen($value[$xAxisField]));exit;
                                            $value[$xAxisField] = date("Y-m-d",$value[$xAxisField]);
                                        }
                                    if (!in_array($value[$xAxisField], $xAxisArr)) {
                                        //var_dump(strlen($value[$xAxisField]));exit;
                                        $xAxisArr[] = $value[$xAxisField];
                                    }
                                    //var_dump($value[$yAxisGroupField]);
                                    if (array_key_exists($yAxisGroupField, $value)) {
                                        if(in_array($value[$yAxisGroupField], $yAxisGroupArr))
                                        {
                                            $yAxisArr[$value[$yAxisGroupField]][$value[$xAxisField]] = array_key_exists($yAxis, $value) ? $value[$yAxis] : '';
                                            //var_dump($yAxisArr);exit;
                                        }
                                    }
                                }
                            }
                            //var_dump($yAxisArr);exit;
                        }
                        else
                        {
                            foreach ($output_data as $key => $value) {
                                if (array_key_exists($xAxisField, $value)) {
                                    if (!in_array($value[$xAxisField], $xAxisArr)) {
                                        $xAxisArr[] = $value[$xAxisField];
                                    }
                                    if(is_array($yAxisFieldArr))
                                    {
                                        foreach($yAxisFieldArr as $yAxis)
                                        {
                                            $yAxisArr[$yAxis][$value[$xAxisField]] = array_key_exists($yAxis, $value) ? $value[$yAxis] : '';
                                        }
                                    }
                                }
                            }
                        }
                        
                        //校验x轴为日期则补充缺失的日期值
                        if (!empty($date_cdts)) {
                            foreach ($date_cdts as $date) {
                                if ($date['column'] == $xAxisField) {
                                    $start = array_key_exists($date['column'].'_start', $condition_params) ? $condition_params[$date['column'].'_start'] : '';
                                    $end = array_key_exists($date['column'].'_end', $condition_params) ? $condition_params[$date['column'].'_end'] : '';
                                    if (!empty($start) && !empty($end) && $start <= $end) {
                                        if ($date['type'] == 'dwysum') {
                                            if($period == 'day' || $period == 'all'){
                                                $type = 'day';
                                            }elseif($period=='week'){
                                                $type = 'week';
                                            }elseif($period=='month'){
                                                $type = 'month';
                                            }
                                        } else {
                                            $type = $date['type'];
                                        }
                                        if (!empty($type)) {
                                            switch ($type) {
                                                case 'day':
                                                    do {
                                                        if (!in_array($start, $xAxisArr)) {
                                                            $xAxisArr[] = $start;
                                                            if (!$is_x_fixed) {
                                                                $is_x_fixed = true;
                                                            }
                                                        }
                                                        $start = date('Y-m-d', strtotime('+1 day', strtotime($start)));
                                                    } while ($start <= $end);
                                                    break;
                                                case 'week':
                                                    do {
                                                        if (!in_array($start, $xAxisArr)) {
                                                            $xAxisArr[] = $start;
                                                            if (!$is_x_fixed) {
                                                                $is_x_fixed = true;
                                                            }
                                                        }
                                                        $thursday = Utility::getDateByWeek(substr($start, 0, 4), substr($start, 4, 2), 4);
                                                        $start = date('oW', strtotime('+7 day', strtotime($thursday)));
                                                    } while ($start <= $end);
                                                    break;
                                                case 'month':
                                                    do {
                                                        if (!in_array($start, $xAxisArr)) {
                                                            $xAxisArr[] = $start;
                                                            if (!$is_x_fixed) {
                                                                $is_x_fixed = true;
                                                            }
                                                        }
                                                        $start = date('Y-m', strtotime('+1 month', strtotime($start)));
                                                    } while ($start <= $end);
                                                    break;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        
                        sort($xAxisArr);
                        $dataCharts['xAxisData'] = !empty($xAxisArr) ? "'".implode("','", $xAxisArr)."'" :"";
                        $dataCharts['xAxisDataCnt'] = count($xAxisArr);
                        //var_dump($yAxisField);
            
                        //如果纵轴分组字段存在，计算分组字段
                        //set_time_limit(0);
                        if($yAxisGroupField)
                        {
                            if(is_array($yAxisGroupArr))
                            {
                                foreach($yAxisGroupArr as $yAxis)
                                {
                                    $yAxisShowArr[] = $yAxis;
                                }
                            }
                        }
                        else
                        {
                            if(is_array($yAxisField))
                            {
                                foreach($yAxisFieldArr as $yAxis)
                                {
                                    $yAxisShowArr[] = $fieldArr[$yAxis];
                                }
                            }
                        }
            
                        $dataCharts['yAxisData'] = !empty($yAxisShowArr) ? "'".implode("','", $yAxisShowArr)."'" : "";
                        //$dataCharts['yAxisDataMax'] = $yAxisDataMax;
                        //var_dump($yAxisArr);exit;
            
                        //如果x轴补充了数据，那么y轴也要补充相应数据
                        if ($is_x_fixed) {
                            foreach ($yAxisArr as $yAxis => $value) {
                                foreach ($xAxisArr as $x) {
                                    if (!array_key_exists($x, $value)) {
                                        $value[$x] = 0;
                                    }
                                }
                                ksort($value);
                                $yAxisArr[$yAxis] = $value;
                            }
                        }
                        
                        if($yAxisGroupField)
                        {
                            foreach($yAxisArr  as $yAxis => $valueY)
                            {
                                $seriesArr = array(
                                    "name"=>$yAxis,
                                    "type" => $dataCharts['chart'],
                                    "data" => "'".implode("','", $valueY)."'"
                                );
                                $series[] = $seriesArr;
                            }
                        }
                        else
                        {
                            foreach($yAxisArr  as $yAxis => $valueY)
                            {
                                $seriesArr = array(
                                    "name"=>$fieldArr[$yAxis],
                                    "type" => $dataCharts['chart'],
                                    "data" => "'".implode("','", $valueY)."'"
                                );
                                $series[] = $seriesArr;
                            }
                        }
                        
                        $dataCharts['series'] = $series;
                        $dataChartsAll[] = $dataCharts;
                        //var_dump($dataCharts);exit;
                        //$dataCharts['chart'] = 'line_resize';
                    }
                }
            }

            //var_dump($dataChartsAll);
            if (!$break) {
                if(count($chart_arr_all)>1 && !empty($dataChartsAll))
                {
                    $this->render('charts/tabs', array(
                            'dataChartsAll' => $dataChartsAll,
                        ));
                }
                else
                {
                    if (array_key_exists('xAxisData', $dataCharts) && !empty($dataCharts['xAxisData']) && 
                        array_key_exists('yAxisData', $dataCharts) && !empty($dataCharts['yAxisData'])) {
                        if($dataCharts['chart'] && !is_null($dataCharts['chart']))
                        {
                            $this->render('charts/'.$dataCharts['chart'], array(
                                'dataCharts' => $dataCharts,
                                'key' => 1
                            ));
                        }
                        else
                        {
                            echo "模板不存在,请重新配置图表项";
                        }
                    }
                }
            }
        }
    }
}
