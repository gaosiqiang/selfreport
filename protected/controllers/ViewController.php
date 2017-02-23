<?php

class ViewController extends Controller
{
    // default layout
    public $layout='//layouts/view';
    
    public function filters()
    {
        return array(
                'accessControl',
                array(
                        'application.filters.AccessFilter',
                ),
        );
    }
    
    /**
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
                array('allow',
                        'users'=>array('@'),
                ),
                array('deny',
                        'users'=>array('*'),
                ),
        );
    }
    
    //校验权限
    protected function beforeAction($action) {
        $action_id = strtolower($action->id);
        if ($action_id === 'index' || preg_match('/^ajax/', $action_id)) {
            return true;
        }
        
        $super = Yii::app()->user->getstate(Common::SESSION_SUPER);
        if (isset($super) && $super > 0) {
            return true;
        } else {
            $report_id = Common::getStringParam('report_id');
            $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT);
            $reports = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT, Common::REDIS_PRIVILEGES_REPORTS) ? $priv[Common::REDIS_PRIVILEGES_REPORTS] : '';
        
            $array = !empty($reports) ? unserialize($reports) : array();
            if (!in_array($report_id, $array))
            {
                $this->redirect(array('site/failed/type/3'));
                return false;
            }
        
            return true;
        }
    }
    
    /**
     * 通过ajax实现一二级列表联动
     */
    public function actionAjaxsecondlist()
    {
        $dict = Common::getStringParam('dict');
        $first = Common::getStringParam('first');
        $second_value_type = Common::getStringParam('second_value_type');
        var_dump($dict);var_dump($first);
        $list = DictList::getLinkedPresetByID($dict,2,$first);var_dump($list);
        $response = '';
        if (!empty($list) && $second_value_type == 1 && $dict != 'media-type-list') {
            $name_list = array_combine($list, $list);
            unset($list);
            $list = $name_list;
            unset($name_list);
        }
        if (!in_array($dict, DictList::$sum_detail_list)) {
            $list = Common::addTitleToList($list);
        }
        foreach ($list as $key => $value) {
            $response .= "<option value='$key'>$value</option>";
        }
        echo $response;
    }
    
    /**
     * 通过ajax实现二三级列表联动
     */
    public function actionAjaxthirdlist()
    {
        $dict = Common::getStringParam('dict');
        $second = Common::getStringParam('second');
        $third_value_type = Common::getStringParam('third_value_type');
        
        $list = DictList::getLinkedPresetByID($dict,3,$second);
        $response = '';
        if (!empty($list) && $third_value_type == 1) {
            $name_list = array_combine($list, $list);
            unset($list);
            $list = $name_list;
            unset($name_list);
        }
        if (!in_array($dict, DictList::$sum_detail_list)) {
            $list = Common::addTitleToList($list);
        }
        foreach ($list as $key => $value) {
            $response .= "<option value='$key'>$value</option>";
        }
        echo $response;
    }
    
    /**
     * 通过ajax实现三四级列表联动
     */
    public function actionAjaxfourthlist()
    {
        $dict = Common::getStringParam('dict');
        $third = Common::getStringParam('third');
        $fourth_value_type = Common::getStringParam('fourth_value_type');
        
        $list = DictList::getLinkedPresetByID($dict,4,$third);
        $response = '';
        if (!empty($list) && $fourth_value_type == 1) {
            $name_list = array_combine($list, $list);
            unset($list);
            $list = $name_list;
            unset($name_list);
        }
        if (!in_array($dict, DictList::$sum_detail_list)) {
            $list = Common::addTitleToList($list);
        }
        foreach ($list as $key => $value) {
            $response .= "<option value='$key'>$value</option>";
        }
        echo $response;
    }
    
    /**
     * 根据用户的报表权限分发报表
     */
    public function actionIndex()
    {
        $priv_menu = Common::getUserMenuTree();
        $all_menu = Menu::getAllMenus();
        if (!empty($priv_menu)) {
            $module = Common::getNumParam('module');
            if ($module > 0) {    //传入一级菜单
                $menu_tree = array_key_exists($module, $priv_menu) ? $priv_menu[$module] : array();    //二级菜单
                if (!empty($menu_tree)) {
                    foreach ($menu_tree as $second_id => $menus_3rd) {
                        if (empty($menus_3rd)) {
                            $report_id = array_key_exists($second_id, $all_menu) ? $all_menu[$second_id]['report_id'] : '';
                            if (!empty($report_id)) {
                                $url = Yii::app()->createUrl('view/show',array('module'=>$module, 'mp'=>$module, 'report_id'=>$report_id));
                                Yii::app()->request->redirect($url);
                            }
                        } else {
                            foreach ($menus_3rd as $third_id => $menus_4th) {
                                if (empty($menus_4th)) {
                                    $report_id = array_key_exists($third_id, $all_menu) ? $all_menu[$third_id]['report_id'] : '';
                                    if (!empty($report_id)) {
                                        $url = Yii::app()->createUrl('view/show',array('module'=>$module, 'mp'=>$second_id, 'report_id'=>$report_id));
                                        Yii::app()->request->redirect($url);
                                    }
                                } else {
                                    foreach ($menus_4th as $fourth_id => $array) {
                                        $report_id = array_key_exists($fourth_id, $all_menu) ? $all_menu[$fourth_id]['report_id'] : '';
                                        if (!empty($report_id)) {
                                            $url = Yii::app()->createUrl('view/show',array('module'=>$module, 'mp'=>$third_id, 'report_id'=>$report_id));
                                            Yii::app()->request->redirect($url);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } else {        //无传入一级菜单，则从权限中取第一个一级菜单
                foreach ($priv_menu as $module_id => $menus) {
                    if (is_array($menus) && !empty($menus)) {
                        foreach ($menus as $second_id => $menus_3rd) {
                            if (empty($menus_3rd)) {
                                $report_id = array_key_exists($second_id, $all_menu) ? $all_menu[$second_id]['report_id'] : '';
                                if (!empty($report_id)) {
                                    $url = Yii::app()->createUrl('view/show',array('module'=>$module_id, 'mp'=>$module_id, 'report_id'=>$report_id));
                                    Yii::app()->request->redirect($url);
                                }
                            } else {
                                foreach ($menus_3rd as $third_id => $menus_4th) {
                                    var_dump($menus_3rd);
                                    if (empty($menus_4th)) {
                                        $report_id = array_key_exists($third_id, $all_menu) ? $all_menu[$third_id]['report_id'] : '';
                                        var_dump($report_id);
                                        if (!empty($report_id)) {
                                            $url = Yii::app()->createUrl('view/show',array('module'=>$module_id, 'mp'=>$second_id, 'report_id'=>$report_id));
                                            Yii::app()->request->redirect($url);
                                        }
                                    } else {
                                        foreach ($menus_4th as $fourth_id => $array) {
                                            $report_id = array_key_exists($fourth_id, $all_menu) ? $all_menu[$fourth_id]['report_id'] : '';
                                            if (!empty($report_id)) {
                                                $url = Yii::app()->createUrl('view/show',array('module'=>$module_id, 'mp'=>$third_id, 'report_id'=>$report_id));
                                                Yii::app()->request->redirect($url);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $this->render('index');
    }
    
    /**
     * 展示报表
     */
    public function actionShow()
    {
        $platform = Common::getNumParam('platform', 9);
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
        $base_params = $params;
        
        if (empty($module) || empty($report_id)) {
            $url = Yii::app()->createUrl('view/index');
            Yii::app()->request->redirect($url);
        }
        
        $configuration = ReportConfiguration::getReportConfByID($report_id);
        
        if ($configuration) {
            //取展示区域配置
            $show_parts = unserialize($configuration->show_parts);
            
            //取筛选项配置
            $conditions = unserialize($configuration->conditions);
            
            $sql = '';
            $query_sql = unserialize($configuration->query_sql);
            $select = !empty($query_sql)&&array_key_exists('select', $query_sql) ? $query_sql['select'] : array();
            $columns = !empty($query_sql)&&array_key_exists('columns', $query_sql) ? $query_sql['columns'] : array();
            $distinct = !empty($query_sql)&&array_key_exists('distinct', $query_sql) ? $query_sql['distinct'] : 0;
            $from = !empty($query_sql)&&array_key_exists('from', $query_sql) ? $query_sql['from'] : '';
            $where = !empty($query_sql)&&array_key_exists('where', $query_sql)&&!empty($query_sql['where']) ? $query_sql['where'] : '1=1';
            $group = !empty($query_sql)&&array_key_exists('group', $query_sql) ? $query_sql['group'] : array();
            $order = !empty($query_sql)&&array_key_exists('order', $query_sql) ? $query_sql['order'] : array();
            
            //数据项定义
            $columns_info = ColumnDefine::getColumnsInfoByReport($report_id,$columns);
            $columns_info_citydiv = ColumnDefine::getCityDivColumnsInfoByReport($report_id);
            list($does_query, $table_exists, $show_columns, $title_columns, $sql, $command_params, $condition_params, $city_divisions, $city_div_cols) = Common::assembleConditions($configuration, $report_id, $params, $period, $conditions, $select, $columns, $distinct, $from, $where, $group, $order, $columns_info, $columns_info_citydiv);
            /* 
            list($does_query, $change_table, $show_columns, $title_columns, $query_condition, $command_params, $condition_params, $city_divisions, $city_div_cols, $sql) = $this->assembleConditions($report_id, $params, $period, $conditions, $select, $columns, $distinct, $from, $where, $group, $order, $columns_info, $columns_info_citydiv);
            $original_table = '';
            $new_table = '';
            if (!empty($change_table)) {
                $arr = explode('##', $change_table);
                $original_table = $arr[0];
                $new_table = $arr[1];
            }
             */
            if ($does_query) {
                list($db,$model) = Common::getDBConnection($configuration->data_source);
                if ($db && $model) {
                    /* 
                    $table_exists = true;
                    if ($period == 'week' || $period == 'month' || $period == 'all') {
                        if (!empty($new_table)) {
                            $table_sql = "SELECT COUNT(*) FROM information_schema.tables WHERE TABLE_SCHEMA='".$model['database']."' AND TABLE_NAME='".$new_table."'";
                            $table_count = $db->createCommand($table_sql)->queryScalar();
                            if (empty($table_count)) {
                                $table_exists = false;
                            }
                        } else {
                            $table_exists = false;
                        }
                    }
                     */
                    if ($table_exists) {
                        $sql_params = array();
                        /* 
                        $sql .= $query_condition;
                        
                        if (!empty($original_table) && !empty($new_table)) {
                            $sql = str_replace($original_table, $new_table, $sql);
                        }
                         */
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
                            $dataProvider=new SqlDataProvider($sql,array(   //$sql
                                    'db' => $db,
                                    'keyField' => false,    //SqlDataProvider重写CSqlDataProvider的fetchKeys方法
                                    'totalItemCount' => $count,
                                    'pagination'=>array(
                                            'pageSize'=>15,
                                            'params'=>$params,
                                    ),
                                    'params' => $sql_params,
                            ));
                            
                            //用于分页导出
                            $export_page_size = !empty(Yii::app()->params['export_page_size']) ? Yii::app()->params['export_page_size'] : Common::EXPORT_PAGE_SIZE;
                            $page = $dataProvider->totalItemCount%$export_page_size!=0 ? intval(ceil($dataProvider->totalItemCount/$export_page_size)) : $dataProvider->totalItemCount/$export_page_size;
                        } else {
                            Yii::app()->user->setFlash('error', '查询sql的语句有误，包含结束或注释字符，请与管理员联系。');
                        }
                    } else {
                        Yii::app()->user->setFlash('error', '查询数据表不存在，请与管理员联系。');
                    }
                    $db->active = false;
                } else {
                    Yii::app()->user->setFlash('error', '无法连接数据源，请与管理员联系。');
                }
            }
        } else {
            Yii::app()->user->setFlash('error', '报表不存在，请与管理员联系。');
        }
        
        $this->render('show', array(
                'platform' => $platform,
                'module' => $module,
                'mp' => $mp,
                'report_id' => $report_id,
                'period' => $period,
                'show_parts' => isset($show_parts)&&!empty($show_parts) ? $show_parts : array(),
                'conditions' => isset($conditions)&&!empty($conditions) ? $conditions : array(),
                'columns' => isset($columns)&&!empty($columns) ? $columns : array(),
                'columns_info' => isset($columns_info)&&!empty($columns_info) ? $columns_info : array(),
                'columns_info_citydiv' => isset($columns_info_citydiv)&&!empty($columns_info_citydiv) ? $columns_info_citydiv : array(),
                'show_columns' => isset($show_columns) ? $show_columns : array(),
                'title_columns' => isset($title_columns) ? $title_columns : array(),
                'city_divisions' => isset($city_divisions) ? $city_divisions : array(),
                'city_div_cols' => isset($city_div_cols) ? $city_div_cols : array(),
                'dataProvider' => isset($dataProvider) ? $dataProvider : new CArrayDataProvider(array()),
                'condition_params' => isset($condition_params) ? $condition_params : array(),
                'params' => isset($params) ? $params : array(),
                'page' => isset($page) ? $page : 1,
                'base_params' => $base_params,
        ));
    }
    
    /**
     * 外部平台展示报表
     */
    public function actionReport()
    {
        $this->layout='//layouts/report_view';
        
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
        $base_params = $params;
    
        /* 如果用户只有其他平台某目录下的自助报表权限，而没有平台自身菜单权限，那么下面判断恒为false；beforeAction中已对报表权限做了校验，这里无需再做
        if (empty($module) || empty($mp) || empty($report_id) || 
            Common::checkPlatMenuPriv($platform, $mp) === false || Common::checkPlatMenuPriv($platform, $module) === false) {
            $this->redirect(array('site/failed/type/3'));
        }
         */
    
        $configuration = ReportConfiguration::getReportConfByID($report_id);
        if ($configuration) {
            //取展示区域配置
            $show_parts = unserialize($configuration->show_parts);
            
            //取筛选项配置
            $conditions = unserialize($configuration->conditions);
            
            $sql = '';
            $query_sql = unserialize($configuration->query_sql);
            $select = !empty($query_sql)&&array_key_exists('select', $query_sql) ? $query_sql['select'] : array();
            $columns = !empty($query_sql)&&array_key_exists('columns', $query_sql) ? $query_sql['columns'] : array();
            $distinct = !empty($query_sql)&&array_key_exists('distinct', $query_sql) ? $query_sql['distinct'] : 0;
            $from = !empty($query_sql)&&array_key_exists('from', $query_sql) ? $query_sql['from'] : '';
            $where = !empty($query_sql)&&array_key_exists('where', $query_sql)&&!empty($query_sql['where']) ? $query_sql['where'] : '1=1';
            $group = !empty($query_sql)&&array_key_exists('group', $query_sql) ? $query_sql['group'] : array();
            $order = !empty($query_sql)&&array_key_exists('order', $query_sql) ? $query_sql['order'] : array();
            
            //数据项定义
            $columns_info = ColumnDefine::getColumnsInfoByReport($report_id,$columns);
            $columns_info_citydiv = ColumnDefine::getCityDivColumnsInfoByReport($report_id);
            list($does_query, $table_exists, $show_columns, $title_columns, $sql, $command_params, $condition_params, $city_divisions, $city_div_cols) = Common::assembleConditions($configuration, $report_id, $params, $period, $conditions, $select, $columns, $distinct, $from, $where, $group, $order, $columns_info, $columns_info_citydiv);
            /* 
            list($does_query, $change_table, $show_columns, $title_columns, $query_condition, $command_params, $condition_params, $city_divisions, $city_div_cols, $sql) = $this->assembleConditions($report_id, $params, $period, $conditions, $select, $columns, $distinct, $from, $where, $group, $order, $columns_info, $columns_info_citydiv);
            $original_table = '';
            $new_table = '';
            if (!empty($change_table)) {
                $arr = explode('##', $change_table);
                $original_table = $arr[0];
                $new_table = $arr[1];
            }
             */
            if ($does_query) {
                list($db,$model) = Common::getDBConnection($configuration->data_source);
                if ($db && $model) {
                    /* 
                    $table_exists = true;
                    if ($period == 'week' || $period == 'month' || $period == 'all') {
                        if (!empty($new_table)) {
                            $table_sql = "SELECT COUNT(*) FROM information_schema.tables WHERE TABLE_SCHEMA='".$model['database']."' AND TABLE_NAME='".$new_table."'";
                            $table_count = $db->createCommand($table_sql)->queryScalar();
                            if (empty($table_count)) {
                                $table_exists = false;
                            }
                        } else {
                            $table_exists = false;
                        }
                    }
                     */
                    if ($table_exists) {
                        $sql_params = array();
                        /* 
                        $sql .= $query_condition;
                        
                        if (!empty($original_table) && !empty($new_table)) {
                            $sql = str_replace($original_table, $new_table, $sql);
                        }
                         */
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
                            $dataProvider=new SqlDataProvider($sql,array(   //$sql
                                'db' => $db,
                                'keyField' => false,    //SqlDataProvider重写CSqlDataProvider的fetchKeys方法
                                'totalItemCount' => $count,
                                'pagination'=>array(
                                    'pageSize'=>15,
                                    'params'=>$params,
                                ),
                                'params' => $sql_params,
                            ));
                            //var_dump($dataProvider->data);exit;
                            //用于分页导出
                            $export_page_size = !empty(Yii::app()->params['export_page_size']) ? Yii::app()->params['export_page_size'] : Common::EXPORT_PAGE_SIZE;
                            $page = $dataProvider->totalItemCount%$export_page_size!=0 ? intval(ceil($dataProvider->totalItemCount/$export_page_size)) : $dataProvider->totalItemCount/$export_page_size;
                        } else {
                            Yii::app()->user->setFlash('error', '查询sql的语句有误，包含结束或注释字符，请与管理员联系。');
                        }
                    } else {
                        Yii::app()->user->setFlash('error', '查询数据表不存在，请与管理员联系。');
                    }
                    $db->active = false;
                } else {
                    Yii::app()->user->setFlash('error', '无法连接数据源，请与管理员联系。');
                }
            }
        } else {
            Yii::app()->user->setFlash('error', '报表不存在，请与管理员联系。');
        }
        //var_dump($condition_params);
    
        $this->render('show', array(
                'platform' => $platform,
                'module' => $module,
                'mp' => $mp,
                'report_id' => $report_id,
                'period' => $period,
                'show_parts' => isset($show_parts)&&!empty($show_parts) ? $show_parts : array(),
                'conditions' => isset($conditions)&&!empty($conditions) ? $conditions : array(),
                'columns' => isset($columns)&&!empty($columns) ? $columns : array(),
                'columns_info' => isset($columns_info)&&!empty($columns_info) ? $columns_info : array(),
                'columns_info_citydiv' => isset($columns_info_citydiv)&&!empty($columns_info_citydiv) ? $columns_info_citydiv : array(),
                'show_columns' => isset($show_columns) ? $show_columns : array(),
                'title_columns' => isset($title_columns) ? $title_columns : array(),
                'city_divisions' => isset($city_divisions) ? $city_divisions : array(),
                'city_div_cols' => isset($city_div_cols) ? $city_div_cols : array(),
                'dataProvider' => isset($dataProvider) ? $dataProvider : new CArrayDataProvider(array()),
                'condition_params' => isset($condition_params) ? $condition_params : array(),
                'params' => isset($params) ? $params : array(),
                'page' => isset($page) ? $page : 1,
                'base_params' => $base_params,
        ));
    }
    
    /**
     * 导出报表
     */
    public function actionExport()
    {
        $platform = Common::getNumParam('platform');
        $module = Common::getNumParam('module');
        $mp = Common::getNumParam('mp');
        $report_id = Common::getStringParam('report_id');
        $page = Common::getNumParam('page');
        $period = Common::getStringParam('period', 'day');
        $params = array(
                'platform' => $platform,
                'module' => $module,
                'mp' => $mp,
                'report_id' => $report_id,
                'period' => $period,
        );
        
        $url = array('view/show');
        if (!empty($platform) && $platform != 9) {
            /* 如果用户只有其他平台某目录下的自助报表权限，而没有平台自身菜单权限，那么下面判断恒为false；beforeAction中已对报表权限做了校验，这里无需再做
            if (empty($module) || empty($mp) || empty($report_id) ||
                    Common::checkPlatMenuPriv($platform, $mp) === false || Common::checkPlatMenuPriv($platform, $module) === false) {
                $this->redirect(array('site/failed/type/3'));
            } else {
                $url = array('view/report');
            }
             */
            $url = array('view/report');
        } else {
            if (empty($module) || empty($report_id)) {
                $url = Yii::app()->createUrl('view/index');
                Yii::app()->request->redirect($url);
            }
        }
        
        $configuration = ReportConfiguration::getReportConfByID($report_id);
        if ($configuration) {
            //取展示区域配置
            $show_parts = unserialize($configuration->show_parts);
        
            //取筛选项配置
            $conditions = unserialize($configuration->conditions);
        
            $sql = '';
            $query_sql = unserialize($configuration->query_sql);
            $select = !empty($query_sql)&&array_key_exists('select', $query_sql) ? $query_sql['select'] : array();
            $columns = !empty($query_sql)&&array_key_exists('columns', $query_sql) ? $query_sql['columns'] : array();
            $distinct = !empty($query_sql)&&array_key_exists('distinct', $query_sql) ? $query_sql['distinct'] : 0;
            $from = !empty($query_sql)&&array_key_exists('from', $query_sql) ? $query_sql['from'] : '';
            $where = !empty($query_sql)&&array_key_exists('where', $query_sql)&&!empty($query_sql['where']) ? $query_sql['where'] : '1=1';
            $group = !empty($query_sql)&&array_key_exists('group', $query_sql) ? $query_sql['group'] : array();
            $order = !empty($query_sql)&&array_key_exists('order', $query_sql) ? $query_sql['order'] : array();
        
            //数据项定义
            $columns_info = ColumnDefine::getColumnsInfoByReport($report_id,$columns);
            $columns_info_citydiv = ColumnDefine::getCityDivColumnsInfoByReport($report_id);
            list($does_query, $table_exists, $show_columns, $title_columns, $sql, $command_params, $condition_params, $city_divisions, $city_div_cols) = Common::assembleConditions($configuration, $report_id, $params, $period, $conditions, $select, $columns, $distinct, $from, $where, $group, $order, $columns_info, $columns_info_citydiv);
            /* 
            list($does_query, $change_table, $show_columns, $title_columns, $query_condition, $command_params, $condition_params, $city_divisions, $city_div_cols, $sql) = $this->assembleConditions($report_id, $params, $period, $conditions, $select, $columns, $distinct, $from, $where, $group, $order, $columns_info, $columns_info_citydiv);
            $original_table = '';
            $new_table = '';
            if (!empty($change_table)) {
                $arr = explode('##', $change_table);
                $original_table = $arr[0];
                $new_table = $arr[1];
            }
             */
            /* 
            if (empty($show_columns)) {
                $url = $url + $params;
                Yii::app()->user->setFlash('error', '对不起，没选择任何查询字段');
                $this->redirect($url);
            }
             */
        
            $params = $params + $condition_params;
            $url = $url + $params;
            if ($does_query) {
                //字段对应关系
                $maps = array();
                foreach ($show_columns as $v) {    //为配合城市归属的展示，由选择的展示字段来生成列名，以前是用$select
                    if (array_key_exists($v, $title_columns)) {
                        $maps[$v] = $title_columns[$v];
                    }
                }
                $excel_name = $configuration->report_name;
                
                $offset = 0;
                if ($page > 0) {
                    $excel_name .= '_part'.$page;
                    $limit = !empty(Yii::app()->params['export_page_size']) ? Yii::app()->params['export_page_size'] : Common::EXPORT_PAGE_SIZE;
                    $offset = ($page-1)*$limit;
                    //$sql .= " limit $offset,$limit";
                }
                
                list($db,$model) = Common::getDBConnection($configuration->data_source);
                if ($db && $model) {
                    /* 
                    $table_exists = true;
                    if ($period == 'week' || $period == 'month' || $period == 'all') {
                        if (!empty($new_table)) {
                            $table_sql = "SELECT COUNT(*) FROM information_schema.tables WHERE TABLE_SCHEMA='".$model['database']."' AND TABLE_NAME='".$new_table."'";
                            $table_count = $db->createCommand($table_sql)->queryScalar();
                            if (empty($table_count)) {
                                $table_exists = false;
                            }
                        } else {
                            $table_exists = false;
                        }
                    }
                     */
                    if ($table_exists) {
                        $sql_params = array();
                        /* 
                        $sql .= $query_condition;
                        
                        if (!empty($original_table) && !empty($new_table)) {
                            $sql = str_replace($original_table, $new_table, $sql);
                        }
                         */
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
                            
                            if (empty($count)) {
                                $db->active = false;
                                Yii::app()->user->setFlash('error', '对不起，没有数据，无法导出');
                                $this->redirect($url);
                            } else {
                                //Utility::exportCSV($sql, $maps, $excel_name, $db, $offset);
                                $week_cols = array();
                                if (!empty($conditions) && array_key_exists('date', $conditions) && !empty($conditions['date'])) {
                                    foreach ($conditions['date'] as $date_conf) {
                                        if ($date_conf['type'] == 'week' || ($date_conf['type'] == 'dwysum' && $period == 'week')) {
                                            $week_cols[] = $date_conf['column'];
                                        }
                                    }
                                }
                                Utility::exportCSVWithCityDivisions($sql, $maps, $excel_name, $show_columns, $city_div_cols, $week_cols, $db, $offset, $sql_params, $count);
                                $db->active = false;
                            }
                        } else {
                            Yii::app()->user->setFlash('error', '查询sql的语句有误，包含结束或注释字符，请与管理员联系。');
                        }
                    } else {
                        Yii::app()->user->setFlash('error', '查询数据表不存在，请与管理员联系。');
                    }
                    $db->active = false;
                } else {
                    Yii::app()->user->setFlash('error', '数据库连接失败');
                }
            } else {
                $this->redirect($url);
            }
        
        } else {
            Yii::app()->user->setFlash('error', '报表不存在，请与管理员联系。');
        }
        
    }
}