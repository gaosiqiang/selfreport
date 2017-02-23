<?php

class ConfigureController extends Controller
{
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
        $super = Yii::app()->user->getstate(Common::SESSION_SUPER);
        if (isset($super) && ($super == 1 || $super == 2)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取数据来源详细信息
     * @param integer $data_source    数据源ID
     * @param array $tables_choosed   数据库表
     * @return array                  数据库表列表、查询条件Json
     */
    private function getDataSourceDetails($data_source, $tables_choosed)
    {
        $filters_json = '';
        $tables = array();

        list($db,$model) = Common::getDBConnection($data_source);
        if ($db && $model) {
            //$tables_db = $db->createCommand('show tables')->queryAll();
            $sql = "select table_name,table_comment from information_schema.tables where TABLE_SCHEMA='".$model['database']."'";
            $tables_db = $db->createCommand($sql)->queryAll();
            if (!empty($tables_db)) {
                foreach ($tables_db as $t)
                {
                    $tables[$t['table_name']] = $t['table_comment'];
                }
            }

            $alias_table = array();
            foreach ($tables_choosed as $table) {
                $alias_table[$table['table_alias']] = $table['table_name'];
            }

            if (!empty($alias_table)) {
                $config = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG);
                $config[Common::REDIS_REPORT_CONFIG_ALIAS_TABLE] = serialize($alias_table);
                Yii::app()->redis->getClient()->setTimeout(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, 12*Common::REDIS_DURATION);

                $sql = "select table_name,column_name,data_type,column_comment from information_schema.columns where TABLE_SCHEMA='".$model['database']."' AND TABLE_NAME in ('".implode("','", $alias_table)."')";
                $results = $db->createCommand($sql)->queryAll();
                $db->active = false;

                $table_columns = array();
                if (!empty($results)) {
                    foreach ($results as $result)
                    {
                        if (!empty($results)) {
                            foreach ($results as $result)
                            {
                                $table_columns[$result['table_name']][$result['column_name']] = array(
                                    'data_type' => $result['data_type'],
                                    'comment' => $result['column_comment'],
                                );
                            }
                        }
                    }
                }

                $column_comment = array();
                $filters = array();
                foreach ($alias_table as $alias => $table) {
                    if (array_key_exists($table, $table_columns)) {
                        foreach ($table_columns[$table] as $column => $data) {
                            $column_comment["$alias-$column"] = $data['comment'];

                            $c = $alias.'.'.$column;
                            //$cid = 'conditions-'.$alias.'-'.$column;

                            if (in_array($data['data_type'], Common::$db_int_type)) {
                                $type = 'integer';
                            } elseif (in_array($data['data_type'], Common::$db_double_type)) {
                                $type = 'double';
                            } else {
                                $type = 'string';
                            }

                            $filters[] = array(
                                'id' => $c,//$cid,
                                //'field' => $c,
                                'label' => $c,
                                'type' => $type,
                            );
                        }
                    }
                }
                $filters_json = CJSON::encode($filters);
                $config[Common::REDIS_REPORT_CONFIG_COLUMN_COMMENT] = serialize($column_comment);
            }
        }

        return array($tables, $filters_json);
    }

    /**
     * Step3.展示内容，组装筛选查询条件
     */
    private function step3AssembleConditions($configuration, $item_count, $alias_expression, $columns_choosed, $columns_info)
    {
        $has_error = false;
        $param_columns_calculation = Common::getJsonParam('columns_calculation');

        $select_items_conf = array();
        $sumcols_calculation = array();
        for ($i=0; $i<=$item_count; $i++) {
            $column = array_key_exists("column_$i", $_POST) ? $_POST["column_$i"] : '';
            $select_item = array_key_exists("select_item_$i", $_POST) ? $_POST["select_item_$i"] : '';
            $item_attr = array_key_exists("item_attr_$i", $_POST) ? $_POST["item_attr_$i"] : '';
            $dict = array_key_exists("dict_$i", $_POST) ? $_POST["dict_$i"] : '';
            $list_value_type = array_key_exists("list_value_type_$i", $_POST) ? $_POST["list_value_type_$i"] : '0';

            if ($column!==''&&$select_item!==''&&$item_attr!=='') {
                if ($select_item == 'day' || $select_item == 'week' || $select_item == 'month' || $select_item == 'dwysum') {
                    $data_type = '';
                    $table_name = '';
                    $column_name = '';
                    $expression = array_key_exists($column, $alias_expression) ? $alias_expression[$column] : '';

                    if (array_key_exists($column, $columns_choosed)) {
                        $table_name = $columns_choosed[$column]['table_name'];
                        if(preg_match('/^[a-zA-Z0-9_]+\.([a-zA-Z0-9_]+) as /', $columns_choosed[$column]['expression'], $matches)) {
                            $column_name = $matches[1];
                        }
                    }
                    list($db,$model) = Common::getDBConnection($configuration->data_source);
                    if ($db && $model && !empty($table_name) && !empty($column_name)) {
                        $sql = "select data_type from information_schema.columns where TABLE_SCHEMA='".$model['database']."' AND TABLE_NAME='".$table_name."' AND COLUMN_NAME='".$column_name."'";
                        $data_type = $db->createCommand($sql)->queryScalar();
                        $db->active = false;
                    } else {
                        Yii::app()->user->setFlash('error', '日期数值类型未保存，如果筛选字段不是原表字段请忽略该提示，否则请重试。');
                    }

                    $sumcols_select = array();
                    $sumcols_columns = array();
                    $sumcols_calculation = array();
                    foreach ($param_columns_calculation as $cal_col) {
                        if (strpos($cal_col['expression'], ';') === false && strpos($cal_col['expression'], '#') === false &&
                            strpos($cal_col['expression'], '-- ') === false && strpos($cal_col['expression'], '/*') === false) {
                            $sumcols_select[$cal_col['col_alias']] = $cal_col['expression'];
                            $sumcols_calculation[$cal_col['col_alias']] = array(
                                'function' => $cal_col['function'],
                                'expression' => $cal_col['expression'],
                            );
                            $arr = explode(' as ', $cal_col['expression']);
                            $sumcols_columns[] = $arr[1];
                        } else {
                            Yii::app()->user->setFlash('error', '查询sql的语句有误，包含结束或注释字符。');
                            $has_error = true;
                            break;
                        }
                    }
                    $sumcols = array(
                        'select' => $sumcols_select,
                        'columns' => $sumcols_columns,
                        'calculation' => $sumcols_calculation,
                    );

                    $select_items_conf['date'][] = array(
                        'column' => $column,
                        'expression' => $expression,
                        'type' => $select_item,
                        'attr' => $item_attr,
                        'data_type' => $data_type,
                        'sumcols' => $sumcols,
                    );
                } elseif ($select_item == 'text' || $select_item == 'city') {
                    $select_items_conf['text'][] = array(
                        'column' => $column,
                        'expression' => array_key_exists($column, $alias_expression) ? $alias_expression[$column] : '',
                        'type' => $select_item,
                        'attr' => $item_attr,
                    );
                } elseif ($select_item == 'list') {
                    //暂只支持预置列表
                    $name = '';
                    $preset_list_id_flip = array_flip(DictList::$preset_list_id);    //将数组键值对调
                    if (array_key_exists($item_attr, $preset_list_id_flip)) {
                        $name = $preset_list_id_flip[$item_attr];
                    }
                    $select_items_conf['list'][] = array(
                        'column' => $column,
                        'expression' => array_key_exists($column, $alias_expression) ? $alias_expression[$column] : '',
                        'type' => $select_item,
                        'dict' => $item_attr,
                        'name' => $name,
                        'value_type' => $list_value_type,
                    );
                }
            }

            if ($select_item == 'linked') {
                //暂只支持预置列表
                $first_column = array_key_exists("first_column_$i", $_POST) ? $_POST["first_column_$i"] : '';
                $first_extra_column = array_key_exists("first_extra_column_$i", $_POST) ? $_POST["first_extra_column_$i"] : '';
                $first_list_value_type = array_key_exists("first_list_value_type_$i", $_POST) ? $_POST["first_list_value_type_$i"] : '0';

                $second_column = array_key_exists("second_column_$i", $_POST) ? $_POST["second_column_$i"] : '';
                $second_extra_column = array_key_exists("second_extra_column_$i", $_POST) ? $_POST["second_extra_column_$i"] : '';
                $second_list_value_type = array_key_exists("second_list_value_type_$i", $_POST) ? $_POST["second_list_value_type_$i"] : '0';

                $third_column = array_key_exists("third_column_$i", $_POST) ? $_POST["third_column_$i"] : '';
                $third_extra_column = array_key_exists("third_extra_column_$i", $_POST) ? $_POST["third_extra_column_$i"] : '';
                $third_list_value_type = array_key_exists("third_list_value_type_$i", $_POST) ? $_POST["third_list_value_type_$i"] : '0';

                $fourth_column = array_key_exists("fourth_column_$i", $_POST) ? $_POST["fourth_column_$i"] : '';
                $fourth_extra_column = array_key_exists("fourth_extra_column_$i", $_POST) ? $_POST["fourth_extra_column_$i"] : '';
                $fourth_list_value_type = array_key_exists("fourth_list_value_type_$i", $_POST) ? $_POST["fourth_list_value_type_$i"] : '0';

                //各级列表配置，子级列表需要依赖于父级列表存在
                $first = $second = $third = $fourth = array();
                if (!empty($first_column)) {    //一级列表
                    if ($first_column === 'default') {
                        $first_column = DictList::$preset_linked_list_default[$dict]['first']['column'];
                        $first_name = DictList::$preset_linked_list_default[$dict]['first']['name'];
                        $first_default = 1;
                    } else {
                        $first_name = array_key_exists($first_column, $columns_info)&&!empty($columns_info[$first_column]['show_name']) ? $columns_info[$first_column]['show_name'] : $first_column;
                        $first_default = 0;
                    }
                    $first = array(
                        'column' => $first_column,
                        'expression' => array_key_exists($first_column, $alias_expression) ? $alias_expression[$first_column] : '',
                        'name' => $first_name,
                        'is_default' => $first_default,
                        'value_type' => $first_list_value_type,
                        'extra_column' => $first_extra_column,
                    );
                    if (!empty($second_column)) {    //二级列表
                        if ($second_column === 'default') {
                            $second_column = DictList::$preset_linked_list_default[$dict]['second']['column'];
                            $second_name = DictList::$preset_linked_list_default[$dict]['second']['name'];
                            $second_default = 1;
                        } else {
                            $second_name = array_key_exists($second_column, $columns_info)&&!empty($columns_info[$second_column]['show_name']) ? $columns_info[$second_column]['show_name'] : $second_column;
                            $second_default = 0;
                        }
                        $second = array(
                            'column' => $second_column,
                            'expression' => array_key_exists($second_column, $alias_expression) ? $alias_expression[$second_column] : '',
                            'name' => $second_name,
                            'is_default' => $second_default,
                            'value_type' => $second_list_value_type,
                            'extra_column' => $second_extra_column,
                        );
                        if (!empty($third_column)) {    //三级列表
                            if ($third_column === 'default') {
                                $third_column = DictList::$preset_linked_list_default[$dict]['third']['column'];
                                $third_name = DictList::$preset_linked_list_default[$dict]['third']['name'];
                                $third_default = 1;
                            } else {
                                $third_name = array_key_exists($third_column, $columns_info)&&!empty($columns_info[$third_column]['show_name']) ? $columns_info[$third_column]['show_name'] : $third_column;
                                $third_default = 0;
                            }
                            $third = array(
                                'column' => $third_column,
                                'expression' => array_key_exists($third_column, $alias_expression) ? $alias_expression[$third_column] : '',
                                'name' => $third_name,
                                'is_default' => $third_default,
                                'value_type' => $third_list_value_type,
                                'extra_column' => $third_extra_column,
                            );
                            if (!empty($fourth_column)) {    //四级列表
                                if ($fourth_column === 'default') {
                                    $fourth_column = DictList::$preset_linked_list_default[$dict]['fourth']['column'];
                                    $fourth_name = DictList::$preset_linked_list_default[$dict]['fourth']['name'];
                                    $fourth_default = 1;
                                } else {
                                    $fourth_name = array_key_exists($fourth_column, $columns_info)&&!empty($columns_info[$fourth_column]['show_name']) ? $columns_info[$fourth_column]['show_name'] : $fourth_column;
                                    $fourth_default = 0;
                                }
                                $fourth = array(
                                    'column' => $fourth_column,
                                    'expression' => array_key_exists($fourth_column, $alias_expression) ? $alias_expression[$fourth_column] : '',
                                    'name' => $fourth_name,
                                    'is_default' => $fourth_default,
                                    'value_type' => $fourth_list_value_type,
                                    'extra_column' => $fourth_extra_column,
                                );
                            }
                        }
                    }
                }

                if (!empty($first)) {
                    $select_items_conf['linked'][] = array(
                        'type' => $select_item,
                        'dict' => $dict,
                        'first' => $first,
                        'second' => $second,
                        'third' => $third,
                        'fourth' => $fourth,
                    );
                }
            }
        }

        return array($select_items_conf, $has_error, $sumcols_calculation);
    }

    /**
     * 获取选择结果字段的数据库注释
     * @param $configuration
     * @param string $query_sql
     */
    private function getChoosedColumnsComments($configuration, $query_sql)
    {
        $alias_comments = array();

        if (empty($query_sql)) {
            $query_sql = unserialize($configuration->query_sql);
        }
        $columns_choosed = !empty($query_sql)&&array_key_exists('columns_choosed', $query_sql) ? $query_sql['columns_choosed'] : array();

        $tables = array();
        $columns = array();
        $alias_table_column = array();
        foreach ($columns_choosed as $alias => $col) {
            $expression = explode(' as ', $col['expression']);
            $column_name_array = explode('.', $expression[0]);
            $column_name = isset($column_name_array[1]) && !empty($column_name_array[1]) ? $column_name_array[1] : '';
            if (!empty($column_name) && !in_array($column_name, $columns)) {
                $columns[] = $column_name;
            }
            if (!empty($col['table_name']) && !in_array($col['table_name'], $tables)) {
                $tables[] = $col['table_name'];
            }
            if (!empty($column_name) && !empty($col['table_name'])) {
                $alias_table_column[$alias] = array(
                    'table' => $col['table_name'],
                    'column' => $column_name,
                );
            }
        }

        $table_columns = array();
        list($db,$model) = Common::getDBConnection($configuration->data_source);
        if ($db && $model) {
            $sql = "select table_name,column_name,column_comment from information_schema.columns where TABLE_SCHEMA='".$model['database']."' AND TABLE_NAME in ('".implode("','", $tables)."') AND COLUMN_NAME in ('".implode("','", $columns)."')";
            $results = $db->createCommand($sql)->queryAll();
            $db->active = false;

            if (!empty($results)) {
                foreach ($results as $result)
                {
                    $table_columns[$result['table_name']][$result['column_name']] = $result['column_comment'];
                }
            }
        } else {
            Yii::app()->user->setFlash('error', '无法连接数据源，请与管理员联系。');
        }

        foreach ($alias_table_column as $alias => $col) {
            $table_columns_comment = array_key_exists($col['table'], $table_columns) ? $table_columns[$col['table']] : array();
            if (!empty($table_columns_comment)) {
                $alias_comments[$alias] = array_key_exists($col['column'], $table_columns_comment) ? $table_columns_comment[$col['column']] : '';
            }
        }

        return $alias_comments;
    }

    /**
     * 通过ajax加载分发平台菜单
     */
    public function actionAjaxmenugrade()
    {
        $platform = Common::getNumParam('platform');
        $grade = Common::getNumParam('grade');
        $parent_id = Common::getNumParam('parent_id');

        switch ($grade) {
            case 1:
                $response = '<option value="">请选择一级菜单</option>';
                switch ($platform) {
                    case 1:    //数据平台
                        $menu_list = MenuDataPlatform::getMenuListForSelect(1);
                        if (!empty($menu_list)) {
                            foreach ($menu_list as $id=>$menu)
                            {
                                if (!($id==8 && $menu=='平台管理')) {
                                    $response .= "<option value='$id'>$menu</option>";
                                }
                            }
                        }
                        break;
                    case 2:    //决策系统
                        break;
                    case 9:    //自助报表平台
                        $menu_list = Menu::getMenuListForSelect(1);
                        if (!empty($menu_list)) {
                            foreach ($menu_list as $id=>$menu)
                            {
                                $response .= "<option value='$id'>$menu</option>";
                            }
                        }
                        break;
                }
                break;
            case 2:
                $response = '<option value="">请选择二级菜单</option>';
                switch ($platform) {
                    case 1:    //数据平台
                        $menu_list = MenuDataPlatform::getSubMenu($parent_id, $grade);
                        if (!empty($menu_list)) {
                            foreach ($menu_list as $menu)
                            {
                                if (strpos($menu->url, '/') === false && $menu->tab_state < 2) {
                                    $response .= "<option value='$menu->id'>$menu->menu_name</option>";
                                }
                            }
                        }
                        break;
                    case 2:    //决策系统
                        break;
                    case 9:    //自助报表平台
                        $menu_list = Menu::getSubMenu($parent_id, $grade);
                        if (!empty($menu_list)) {
                            foreach ($menu_list as $menu)
                            {
                                if (empty($menu->report_id) && $menu->tab_state < 2) {
                                    $response .= "<option value='$menu->id'>$menu->menu_name</option>";
                                }
                            }
                        }
                        break;
                }
                break;
            case 3:
                $response = '<option value="">请选择三级菜单</option>';
                switch ($platform) {
                    case 1:    //数据平台
                        $menu_list = MenuDataPlatform::getSubMenu($parent_id, $grade);
                        if (!empty($menu_list)) {
                            foreach ($menu_list as $menu)
                            {
                                if (strpos($menu->url, '/') === false && $menu->tab_state < 2) {
                                    $response .= "<option value='$menu->id'>$menu->menu_name</option>";
                                }
                            }
                        }
                        break;
                    case 2:    //决策系统
                        break;
                    case 9:    //自助报表平台
                        $menu_list = Menu::getSubMenu($parent_id, $grade);
                        if (!empty($menu_list)) {
                            foreach ($menu_list as $menu)
                            {
                                if (empty($menu->report_id) && $menu->tab_state < 2) {
                                    $response .= "<option value='$menu->id'>$menu->menu_name</option>";
                                }
                            }
                        }
                        break;
                }
                break;
        }

        echo $response;
    }

    /**
     * 通过ajax加载数据源的数据表
     */
    public function actionAjaxtables()
    {
        $data_source = Common::getNumParam('data_source');
        $response = '<tr><th style="width: 30%">表名</th><th style="width: 40%">表注释</th><th style="width: 15%">操作</th></tr>';

        list($db,$model) = Common::getDBConnection($data_source);
        if ($db && $model) {
            $tables = array();
            $sql = "select table_name,table_comment from information_schema.tables where TABLE_SCHEMA='".$model['database']."'";
            $tables_db = $db->createCommand($sql)->queryAll();
            $db->active = false;

            if (!empty($tables_db)) {
                foreach ($tables_db as $t)
                {
                    $tables[$t['table_name']] = $t['table_comment'];
                }
            }

            if(!empty($tables)){
                foreach ($tables as $table => $comment){
                    $response .= '<tr><td style="width: 30%"><div style="width: 100%;word-wrap:break-word;text-align:left;">'.$table.'</div></td>'.
                        '<td style="width: 40%;text-align:left;">'.$comment.'</td><td style="width: 15%">'.
                        CHtml::link('选择', 'javascript:void(0)',array('id' => "choose-table-$table")).'</td></tr>';
                }
            }

            Common::clearCacheForConfig();
        } else {
            Yii::app()->user->setFlash('error', '无法连接数据源，请与管理员联系。');
        }

        echo $response;
    }

    /**
     * Step2.查询配置 -> 表 -> 选择可用表
     */
    public function actionAjaxchoosetable()
    {
        $response = '';
        $table = Common::getStringParam('table');
        $comment = Yii::app()->request->getParam('comment');

        $config = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG);

        $tables_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_TABLES) ? $config[Common::REDIS_REPORT_CONFIG_TABLES] : '';
        $tables_array= unserialize($tables_cache);
        $tables = $tables_array !== false && !empty($tables_array) ? $tables_array : array();

        $alias_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_ALIAS_TABLE) ? $config[Common::REDIS_REPORT_CONFIG_ALIAS_TABLE] : '';
        $alias_array= unserialize($alias_cache);
        $alias_table = $alias_array !== false && !empty($alias_array) ? $alias_array : array();

        $alias = $table;
        if (!empty($table)) {
            if (array_key_exists($table, $tables)) {    //对于选择的相同表，别名后加数字序号
                $index = $tables[$table] + 1;
                $alias = $table.'_'.$index;
                $tables[$table] = $index;
            } else {
                $tables[$table] = 1;
            }
            $alias_table[$alias] = $table;
            $config[Common::REDIS_REPORT_CONFIG_TABLES] = serialize($tables);
            $config[Common::REDIS_REPORT_CONFIG_ALIAS_TABLE] = serialize($alias_table);
            Yii::app()->redis->getClient()->setTimeout(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, 12*Common::REDIS_DURATION);

            //主表与关联表处理
            $del_params = '';
            reset($tables);                 //将数组指针指向第一个元素
            $main_table = key($tables);     //取数组第一个元素的key
            if ($main_table===$alias) {
                $edit = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                $del_params = '"'.$table.'","'.$alias.'"'.',1';
            } else {
                $edit = CHtml::Link('连接配置', 'javascript:tables_edit_join("'.$alias.'")',array('id' => "edit-join-$alias"));
                $del_params = '"'.$table.'","'.$alias.'"'.',0';
            }

            $response = '<tr id="tables-'.$alias.'"><td style="width: 22%"><div style="width: 100%;word-wrap:break-word;text-align:left;">'.$table.
                '</div></td><td style="width: 22%"><div style="width: 100%;word-wrap:break-word;text-align:left;">'.$alias.
                '</div></td><td style="width: 34%;text-align:left;">'.$comment.
                '</td><td style="width: 22%">'.
                '<span style="margin:5px">'.$edit.'</span>'.
                '<span style="margin:5px">'.CHtml::Link('删除', 'javascript:tables_inverse_table('.$del_params.')',array('id' => "inverse-table-$alias")).
                '</span></td></tr>';

            Yii::app()->user->setState(Common::SESSION_STEP2_TABLE_CHANGED, 1); //报表配置中Step2.查询配置，表操作，0为无，1为增，2为删
        }

        echo $response;
    }

    /**
     * Step2.查询配置 -> 表 -> 编辑连接属性
     */
    public function actionAjaxeditjoin()
    {
        $response = '';
        $data_source = Common::getNumParam('data_source');
        $alias = Common::getStringParam('alias');

        $config = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG);
        $modal_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_JOIN_MODAL_ALIAS) ? $config[Common::REDIS_REPORT_CONFIG_JOIN_MODAL_ALIAS] : '';
        $modal_array= unserialize($modal_cache);
        $modal_alias = $modal_array !== false && !empty($modal_array) ? $modal_array : array();

        if (!in_array($alias, $modal_alias)) {
            $modal_alias[$alias] = $alias;
            $config[Common::REDIS_REPORT_CONFIG_JOIN_MODAL_ALIAS] = serialize($modal_alias);

            $alias_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_ALIAS_TABLE) ? $config[Common::REDIS_REPORT_CONFIG_ALIAS_TABLE] : '';
            $alias_array= unserialize($alias_cache);
            $alias_table = $alias_array !== false && !empty($alias_array) ? $alias_array : array();
            $alias1 = $alias2 = array_keys($alias_table);
            $join_tables_alias = array_combine($alias1, $alias2);
            if (array_key_exists($alias, $join_tables_alias)) unset($join_tables_alias[$alias]);    //表不与自身连接

            $modal_head = '<div class="modal fade bs-example-modal-lg" id="tables-join-modal-'.$alias.'" tabindex="-1" role="dialog" aria-labelledby="tables-join-modal-label" aria-hidden="true">
                           <div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header">
                           <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                           <h4 class="modal-title" id="tables-join-modal-label">表关联配置</h4></div>';

            $modal_tail = '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>';

            reset($join_tables_alias);                 //将数组指针指向第一个元素
            $related_alias = key($join_tables_alias);     //取当前元素KEY
            $related_table = current($join_tables_alias);     //取当前元素值
            $main_table = array_key_exists($alias, $alias_table) ? $alias_table[$alias] : '';

            $main_table_cols = array();
            $related_table_cols = array();

            list($db,$model) = Common::getDBConnection($data_source);
            if ($db && $model) {
                if ($main_table === $related_table) {
                    $sql = "select column_name from information_schema.columns where TABLE_SCHEMA='".$model['database']."' AND TABLE_NAME='$main_table'";
                    $results = $db->createCommand($sql)->queryAll();
                    if (!empty($results)) {
                        foreach ($results as $result)
                        {
                            $c1 = $alias.'.'.$result['column_name'];
                            $c2 = $related_alias.'.'.$result['column_name'];
                            $main_table_cols[$c1] = $c1;
                            $related_table_cols[$c2] = $c2;
                        }
                    }
                } else {
                    $sql = "select table_name,column_name from information_schema.columns where TABLE_SCHEMA='".$model['database']."' AND TABLE_NAME in ('$main_table','$related_table')";
                    $results = $db->createCommand($sql)->queryAll();
                    if (!empty($results)) {
                        foreach ($results as $result)
                        {
                            if ($result['table_name'] === $main_table) {
                                $c = $alias.'.'.$result['column_name'];
                                $main_table_cols[$c] = $c;
                            }
                            if ($result['table_name'] === $related_table) {
                                $c = $related_alias.'.'.$result['column_name'];
                                $related_table_cols[$c] = $c;
                            }
                        }
                    }
                }
                $db->active = false;

            } else {
                Yii::app()->user->setFlash('error', '无法连接数据源，请与管理员联系。');
            }
            $modal_body = '<div class="modal-body">'.
                '<div class="container-fluid">'.
                '<div class="row" style="margin-bottom:10px;">'.
                '<div class="col-md-3"><label>数据源：</label>'.
                CHtml::dropDownList('id', 'name', DataSource::getSelectList(), array('id'=>'datasource-type-'.$alias, 'style'=>'width:125px')).
                '</div>'.
                '</div>'.
                '<div class="row" style="margin-bottom:10px;">'.
                '<div class="col-md-12">表别名：'.$alias.'</div>'.
                '</div>'.
                '<div class="row" style="margin-bottom:10px;">'.
                '<div class="col-md-3">'.
                '<label>连接类型：</label>'.
                CHtml::dropDownList('join_type', 'inner', ReportConfiguration::$join_types, array('id'=>'join-type-'.$alias, 'style'=>'width:125px')).
                '</div>'.
                '<div class="col-md-9">'.
                '<label>关联表别名：</label>'.
                CHtml::dropDownList('related_table', $related_table, $join_tables_alias, array('id'=>'related-table-'.$alias, 'style'=>'width:360px')).
                '</div>'.
                '</div>'.
                '<div class="row" style="margin-bottom:10px;">'.
                '<div class="col-md-5">'.
                '<label>连接字段：</label>'.
                CHtml::dropDownList('main_table_col', '', $main_table_cols, array('id'=>'main-table-col-'.$alias, 'style'=>'width:78%')).
                '</div>'.
                '<div class="col-md-1">'.
                CHtml::dropDownList('join_operator', '=', ReportConfiguration::$join_operator, array('id'=>'join-operator-'.$alias)).
                '</div>'.
                '<div class="col-md-5">'.
                CHtml::dropDownList('related_table_col', '', $related_table_cols, array('id'=>'related-table-col-'.$alias, 'style'=>'width:85%')).
                '</div>'.
                '<div class="col-md-1">'.
                CHtml::button('添加', array('class'=>'btn btn-success btn-xs', 'id'=>'add-join-relation-'.$alias)).
                '</div>'.
                '</div>'.
                '<div class="row">
                        <div class="col-md-12" style="height: 150px; overflow: auto;">
                            <table id="join-relation-'.$alias.'" class="table table-bordered table-hover" style="table-layout: fixed">
                                <tbody>
                                    <tr>
                                        <th style="width: 40%">表字段</th>
                                        <th style="width: 10%">运算符</th>
                                        <th style="width: 40%">关联表字段</th>
                                        <th style="width: 10%">操作</th>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                     </div>'.
                '</div>'.
                '</div>';

            $script = '<script type="text/javascript">'.
                '$("#datasource-type-'.$alias.'").change(function(){
                    data_source=$("#datasource").val();
                    alias=$("#related-table-'.$alias.'").val();
                    $("#related-table-'.$alias.'").load("/configure/ajaxtablesbyalias",{data_source:data_source,alias:alias},function(response,status){
                        if (status=="success")
                        {
                            $("#related-table-'.$alias.'").empty();
                            $("#related-table-'.$alias.'").append(response);
                        }
                    });
                });
                $("#related-table-'.$alias.'").change(function(){
                    data_source=$("#data_source").val();
                    alias=$("#related-table-'.$alias.'").val();
                    $("#related-table-col-'.$alias.'").load("/configure/ajaxcolumnsbyalias",{data_source:data_source,alias:alias},function(response,status){
                        if (status=="success")
                        {
                            $("#related-table-col-'.$alias.'").empty();
                            $("#related-table-col-'.$alias.'").append(response);
                        }
                    });
                });
                $("#add-join-relation-'.$alias.'").click(function(){
                    main_table_col=$("#main-table-col-'.$alias.'").val();
                    join_operator=$("#join-operator-'.$alias.'").val();
                    related_table_col=$("#related-table-col-'.$alias.'").val();
                    related_table_col_as_id=related_table_col.replace(".","-");
                    content = "<tr id=\"join-relation-'.$alias.'-"+related_table_col_as_id+"\"><td style=\"width: 40%\"><div style=\"width: 100%;word-wrap:break-word;text-align:left;\">"+main_table_col+"</div></td>"+
                        "<td style=\"width: 10%\">"+join_operator+"</td>"+
                        "<td style=\"width: 40%\"><div style=\"width: 100%;word-wrap:break-word;text-align:left;\">"+related_table_col+"</div></td>"+
                        "<td style=\"width: 10%\"><span><a href=\"javascript:tables_del_join_relation(&quot;'.$alias.'&quot;,&quot;"+related_table_col_as_id+"&quot;);\">删除</a></span></td></tr>";
                    $("#join-relation-'.$alias.'").append(content);
                });'.
                '</script>';
            $response = $modal_head.$modal_body.$modal_tail.'</div>'.$script.'</div></div></div>';
        }

        echo $response;
    }

    /**
     * Step2.查询配置 -> 表 -> 初始化连接属性
     */
    public function actionAjaxinittablerelations()
    {
        $response = '';
        $tables = array();

        $report_id = Common::getStringParam('report_id');
        $data_source = Common::getNumParam('data_source');

        $configuration = ReportConfiguration::getReportConfByID($report_id);
        if ($configuration) {
            $query_sql = unserialize($configuration->query_sql);
            $tables = !empty($query_sql)&&array_key_exists('tables', $query_sql) ? $query_sql['tables'] : array();
        }

        if (!empty($tables) && count($tables) > 1) {
            $config = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG);
            $modal_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_JOIN_MODAL_ALIAS) ? $config[Common::REDIS_REPORT_CONFIG_JOIN_MODAL_ALIAS] : '';
            $modal_array= unserialize($modal_cache);
            $modal_alias = $modal_array !== false && !empty($modal_array) ? $modal_array : array();

            $alias_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_ALIAS_TABLE) ? $config[Common::REDIS_REPORT_CONFIG_ALIAS_TABLE] : '';
            $alias_array= unserialize($alias_cache);
            $alias_table = $alias_array !== false && !empty($alias_array) ? $alias_array : array();
            $alias1 = $alias2 = array_keys($alias_table);

            $loop = 0;
            foreach ($tables as $table) {
                ++$loop;
                if ($loop > 1) {
                    $join_tables_alias = array_combine($alias1, $alias2);
                    $alias = $table['table_alias'];
                    if (!in_array($alias, $modal_alias)) {
                        $modal_alias[$alias] = $alias;
                        if (array_key_exists($alias, $join_tables_alias)) unset($join_tables_alias[$alias]);    //表不与自身连接

                        $relate_td = '';
                        $related_condition = !empty($table['related_condition']) ? $table['related_condition'] : array();
                        foreach ($related_condition as $single_relate) {
                            $related_table_col_as_id = str_replace('.', '-', $single_relate['related_column']);
                            $content = '<tr id="join-relation-'.$alias.'-'.$related_table_col_as_id.'"><td style="width: 40%"><div style="width: 100%;word-wrap:break-word;text-align:left;">'.$single_relate['self_column'].'</div></td>'.
                                '<td style="width: 10%">'.$single_relate['operator'].'</td>'.
                                '<td style="width: 40%"><div style="width: 100%;word-wrap:break-word;text-align:left;">'.$single_relate['related_column'].'</div></td>'.
                                '<td style="width: 10%"><span><a href="javascript:tables_del_join_relation(&quot;'.$alias.'&quot;,&quot;'.$related_table_col_as_id.'&quot;);">删除</a></span></td></tr>';
                            $relate_td .= $content;
                        }

                        $modal_head = '<div class="modal fade bs-example-modal-lg" id="tables-join-modal-'.$alias.'" tabindex="-1" role="dialog" aria-labelledby="tables-join-modal-label" aria-hidden="true">
                                       <div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header">
                                       <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                       <h4 class="modal-title" id="tables-join-modal-label">表关联配置</h4></div>';

                        $modal_tail = '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>';

                        reset($join_tables_alias);                 //将数组指针指向第一个元素
                        $related_alias = key($join_tables_alias);     //取当前元素KEY
                        $related_table = current($join_tables_alias);     //取当前元素值
                        $main_table = array_key_exists($alias, $alias_table) ? $alias_table[$alias] : '';

                        $main_table_cols = array();
                        $related_table_cols = array();

                        list($db,$model) = Common::getDBConnection($data_source);
                        if ($db && $model) {
                            if ($main_table === $related_table) {
                                $sql = "select column_name from information_schema.columns where TABLE_SCHEMA='".$model['database']."' AND TABLE_NAME='$main_table'";
                                $results = $db->createCommand($sql)->queryAll();
                                if (!empty($results)) {
                                    foreach ($results as $result)
                                    {
                                        $c1 = $alias.'.'.$result['column_name'];
                                        $c2 = $related_alias.'.'.$result['column_name'];
                                        $main_table_cols[$c1] = $c1;
                                        $related_table_cols[$c2] = $c2;
                                    }
                                }
                            } else {
                                $sql = "select table_name,column_name from information_schema.columns where TABLE_SCHEMA='".$model['database']."' AND TABLE_NAME in ('$main_table','$related_table')";
                                $results = $db->createCommand($sql)->queryAll();
                                if (!empty($results)) {
                                    foreach ($results as $result)
                                    {
                                        if ($result['table_name'] === $main_table) {
                                            $c = $alias.'.'.$result['column_name'];
                                            $main_table_cols[$c] = $c;
                                        }
                                        if ($result['table_name'] === $related_table) {
                                            $c = $related_alias.'.'.$result['column_name'];
                                            $related_table_cols[$c] = $c;
                                        }
                                    }
                                }
                            }
                            $db->active = false;

                        } else {
                            Yii::app()->user->setFlash('error', '无法连接数据源，请与管理员联系。');
                        }

                        $join_type = $table['join_type'];
                        $modal_body = '<div class="modal-body">'.
                            '<div class="container-fluid">'.
                            '<div class="row" style="margin-bottom:10px;">'.
                            '<div class="col-md-12">表别名：'.$alias.'</div>'.
                            '</div>'.
                            '<div class="row" style="margin-bottom:10px;">'.
                            '<div class="col-md-3">'.
                            '<label>连接类型：</label>'.
                            CHtml::dropDownList('join_type', $join_type, ReportConfiguration::$join_types, array('id'=>'join-type-'.$alias, 'style'=>'width:125px')).
                            '</div>'.
                            '<div class="col-md-9">'.
                            '<label>关联表别名：</label>'.
                            CHtml::dropDownList('related_table', $related_table, $join_tables_alias, array('id'=>'related-table-'.$alias, 'style'=>'width:360px')).
                            '</div>'.
                            '</div>'.
                            '<div class="row" style="margin-bottom:10px;">'.
                            '<div class="col-md-5">'.
                            '<label>连接字段：</label>'.
                            CHtml::dropDownList('main_table_col', '', $main_table_cols, array('id'=>'main-table-col-'.$alias, 'style'=>'width:78%')).
                            '</div>'.
                            '<div class="col-md-1">'.
                            CHtml::dropDownList('join_operator', '=', ReportConfiguration::$join_operator, array('id'=>'join-operator-'.$alias)).
                            '</div>'.
                            '<div class="col-md-5">'.
                            CHtml::dropDownList('related_table_col', '', $related_table_cols, array('id'=>'related-table-col-'.$alias, 'style'=>'width:85%')).
                            '</div>'.
                            '<div class="col-md-1">'.
                            CHtml::button('添加', array('class'=>'btn btn-success btn-xs', 'id'=>'add-join-relation-'.$alias)).
                            '</div>'.
                            '</div>'.
                            '<div class="row">
                                    <div class="col-md-12" style="height: 150px; overflow: auto;">
                                        <table id="join-relation-'.$alias.'" class="table table-bordered table-hover" style="table-layout: fixed">
                                            <tbody>
                                                <tr>
                                                    <th style="width: 40%">表字段</th>
                                                    <th style="width: 10%">运算符</th>
                                                    <th style="width: 40%">关联表字段</th>
                                                    <th style="width: 10%">操作</th>
                                                </tr>
                                                '.$relate_td.'
                                            </tbody>
                                        </table>
                                    </div>
                                 </div>'.
                            '</div>'.
                            '</div>';

                        $script = '<script type="text/javascript">'.
                            '$("#related-table-'.$alias.'").change(function(){
                                data_source=$("#data_source").val();
                                alias=$("#related-table-'.$alias.'").val();
                                $("#related-table-col-'.$alias.'").load("/configure/ajaxcolumnsbyalias",{data_source:data_source,alias:alias},function(response,status){
                                    if (status=="success")
                                    {
                                        $("#related-table-col-'.$alias.'").empty();
                                        $("#related-table-col-'.$alias.'").append(response);
                                    }
                                });
                            });
                            $("#add-join-relation-'.$alias.'").click(function(){
                                main_table_col=$("#main-table-col-'.$alias.'").val();
                                join_operator=$("#join-operator-'.$alias.'").val();
                                related_table_col=$("#related-table-col-'.$alias.'").val();
                                related_table_col_as_id=related_table_col.replace(".","-");
                                content = "<tr id=\"join-relation-'.$alias.'-"+related_table_col_as_id+"\"><td style=\"width: 40%\"><div style=\"width: 100%;word-wrap:break-word;text-align:left;\">"+main_table_col+"</div></td>"+
                                    "<td style=\"width: 10%\">"+join_operator+"</td>"+
                                    "<td style=\"width: 40%\"><div style=\"width: 100%;word-wrap:break-word;text-align:left;\">"+related_table_col+"</div></td>"+
                                    "<td style=\"width: 10%\"><span><a href=\"javascript:tables_del_join_relation(&quot;'.$alias.'&quot;,&quot;"+related_table_col_as_id+"&quot;);\">删除</a></span></td></tr>";
                                $("#join-relation-'.$alias.'").append(content);
                            });'.
                            '</script>';
                        $response .= $modal_head.$modal_body.$modal_tail.'</div>'.$script.'</div></div></div>';
                    }
                }
            }
            $config[Common::REDIS_REPORT_CONFIG_JOIN_MODAL_ALIAS] = serialize($modal_alias);
        }
        echo $response;
    }

    /**
     * Step2.查询配置 -> 表 -> 删除选择表
     */
    public function actionAjaxinversetable()
    {
        $table = Common::getStringParam('table');
        $alias = Common::getStringParam('alias');
        $is_main = Common::getNumParam('is_main');

        if ($is_main == 1) {
            Common::clearCacheForConfig();
        } else {
            $config = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG);

            $tables_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_TABLES) ? $config[Common::REDIS_REPORT_CONFIG_TABLES] : '';
            $tables_array= unserialize($tables_cache);
            $tables = $tables_array !== false && !empty($tables_array) ? $tables_array : array();

            $alias_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_ALIAS_TABLE) ? $config[Common::REDIS_REPORT_CONFIG_ALIAS_TABLE] : '';
            $alias_array= unserialize($alias_cache);
            $alias_table = $alias_array !== false && !empty($alias_array) ? $alias_array : array();

            $modal_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_JOIN_MODAL_ALIAS) ? $config[Common::REDIS_REPORT_CONFIG_JOIN_MODAL_ALIAS] : '';
            $modal_array= unserialize($modal_cache);
            $modal_alias = $modal_array !== false && !empty($modal_array) ? $modal_array : array();

            if (!empty($table)) {
                if (array_key_exists($table, $tables)) {    //对于选择的相同表，别名后加数字序号
                    if ($tables[$table] == 1) {
                        unset($tables[$table]);
                    }
                }
                $config[Common::REDIS_REPORT_CONFIG_TABLES] = serialize($tables);
            }

            if (!empty($alias)) {
                if (array_key_exists($alias, $modal_alias)) {
                    unset($modal_alias[$alias]);
                    $config[Common::REDIS_REPORT_CONFIG_JOIN_MODAL_ALIAS] = serialize($modal_alias);
                }
                if (array_key_exists($alias, $alias_table)) {
                    unset($alias_table[$alias]);
                    $config[Common::REDIS_REPORT_CONFIG_ALIAS_TABLE] = serialize($alias_table);
                }
            }
        }
        Yii::app()->user->setState(Common::SESSION_STEP2_TABLE_CHANGED, 2); //报表配置中Step2.查询配置，表操作，0为无，1为增，2为删
    }

    /**
     * 根据表别名通过ajax加载数据源的数据表字段，用于选择表关联字段
     */
    public function actionAjaxcolumnsbyalias()
    {
        $response = '';
        $data_source = Common::getNumParam('data_source');
        $alias = Common::getStringParam('alias');

        $config = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG);

        $alias_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_ALIAS_TABLE) ? $config[Common::REDIS_REPORT_CONFIG_ALIAS_TABLE] : '';
        $alias_array= unserialize($alias_cache);
        $alias_table = $alias_array !== false && !empty($alias_array) ? $alias_array : array();

        $table = array_key_exists($alias, $alias_table) ? $alias_table[$alias] : '';

        list($db,$model) = Common::getDBConnection($data_source);
        if ($db && $model) {
            $sql = "select column_name from information_schema.columns where TABLE_SCHEMA='".$model['database']."' AND TABLE_NAME='$table'";
            $results = $db->createCommand($sql)->queryAll();
            $db->active = false;

            if (!empty($results)) {
                foreach ($results as $result)
                {
                    $c = $alias.'.'.$result['column_name'];
                    $response .= "<option value='".$c."'>".$c."</option>";
                }
            }
        } else {
            Yii::app()->user->setFlash('error', '无法连接数据源，请与管理员联系。');
        }
        echo $response;
    }

    /**
     * 根据表别名通过ajax加载数据源的数据表字段，用于选择表关联字段
     */
    public function actionAjaxtablesbyalias()
    {
        $response = '';
        $data_source = Common::getNumParam('data_source');


        list($db,$model) = Common::getDBConnection($data_source);
        if ($db && $model) {
            $sql = "select column_name from information_schema.columns where TABLE_SCHEMA='".$model['database']."' AND TABLE_NAME='$table'";
            $results = $db->createCommand($sql)->queryAll();
            $db->active = false;

            if (!empty($results)) {
                foreach ($results as $result)
                {
                    $c = $result['column_name'];
                    $response .= "<option value='".$c."'>".$c."</option>";
                }
            }
        } else {
            Yii::app()->user->setFlash('error', '无法连接数据源，请与管理员联系。');
        }
        echo $response;
    }

    /**
     * 通过ajax加载数据源的已选数据表的所有字段
     */
    public function actionAjaxcolumns()
    {
        $response = '';
        $data_source = Common::getNumParam('data_source');

        $config = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG);

        $alias_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_ALIAS_TABLE) ? $config[Common::REDIS_REPORT_CONFIG_ALIAS_TABLE] : '';
        $alias_array= unserialize($alias_cache);
        $alias_table = $alias_array !== false && !empty($alias_array) ? $alias_array : array();

        if (!empty($alias_table)) {
            $table_columns = array();
            list($db,$model) = Common::getDBConnection($data_source);
            if ($db && $model) {
                $sql = "select table_name,column_name,column_comment from information_schema.columns where TABLE_SCHEMA='".$model['database']."' AND TABLE_NAME in ('".implode("','", $alias_table)."')";
                $results = $db->createCommand($sql)->queryAll();
                $db->active = false;

                if (!empty($results)) {
                    foreach ($results as $result)
                    {
                        $table_columns[$result['table_name']][$result['column_name']] = $result['column_comment'];
                    }
                }
            } else {
                Yii::app()->user->setFlash('error', '无法连接数据源，请与管理员联系。');
            }

            $column_comment = array();
            foreach ($alias_table as $alias => $table) {
                if (array_key_exists($table, $table_columns)) {
                    foreach ($table_columns[$table] as $column => $comment) {
                        $column_comment["$alias-$column"] = $comment;
                        $response .= '<tr>'.
                            '<td style="width: 15%"><div style="width: 100%;word-wrap:break-word;text-align:left;">'.$column.'</div></td>'.
                            '<td style="width: 20%"><div style="width: 100%;word-wrap:break-word;text-align:left;">'.$alias.'.'.$column.'</div></td>'.
                            '<td style="width: 15%"><div style="width: 100%;word-wrap:break-word;">'.$table.'</div></td>'.
                            '<td style="width: 15%"><div style="width: 100%;word-wrap:break-word;">'.$alias.'</div></td>'.
                            '<td style="width: 25%"><div style="width: 100%;word-wrap:break-word;text-align:left;">'.$comment.'</div></td>'.
                            '<td style="width: 10%">'.CHtml::link('选择', 'javascript:void(0)',array('id' => "choose-column-$alias-$column")).'</td>'.
                            '</tr>';
                    }
                }
            }
            $config[Common::REDIS_REPORT_CONFIG_COLUMN_COMMENT] = serialize($column_comment);
        }
        echo $response;
    }

    /**
     * Step2.查询配置 -> 结果字段 -> 选择可用字段
     */
    public function actionAjaxchoosecolumn()
    {
        $response = '';
        $table_alias = Common::getStringParam('alias');
        $column = Common::getStringParam('column');

        $config = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG);

        $columns_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_COLUMNS) ? $config[Common::REDIS_REPORT_CONFIG_COLUMNS] : '';
        $columns_array= unserialize($columns_cache);
        $columns = $columns_array !== false && !empty($columns_array) ? $columns_array : array();

        $column_alias_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_ALIAS_COLUMN) ? $config[Common::REDIS_REPORT_CONFIG_ALIAS_COLUMN] : '';
        $column_alias_array= unserialize($column_alias_cache);
        $alias_column = $column_alias_array !== false && !empty($column_alias_array) ? $column_alias_array : array();

        $alias_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_ALIAS_TABLE) ? $config[Common::REDIS_REPORT_CONFIG_ALIAS_TABLE] : '';
        $alias_array= unserialize($alias_cache);
        $alias_table = $alias_array !== false && !empty($alias_array) ? $alias_array : array();

        $table = array_key_exists($table_alias, $alias_table) ? $alias_table[$table_alias] : '';

        $comment_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_COLUMN_COMMENT) ? $config[Common::REDIS_REPORT_CONFIG_COLUMN_COMMENT] : '';
        $comment_array= unserialize($comment_cache);
        $column_comment = $comment_array !== false && !empty($comment_array) ? $comment_array : array();

        if (!empty($column)) {
            $alias = $column;
            if (array_key_exists($column, $columns)) {    //对于选择的相同表，别名后加数字序号
                $index = $columns[$column] + 1;
                $alias = $column.'_'.$index;
                $columns[$column] = $index;
            } else {
                $columns[$column] = 1;
            }

            if (array_key_exists($alias, $alias_column)) {
                $response = 'exist';
            } else {
                $alias_column[$alias] = $alias;   //$table_alias.'.'.$column
                $config[Common::REDIS_REPORT_CONFIG_COLUMNS] = serialize($columns);
                $config[Common::REDIS_REPORT_CONFIG_ALIAS_COLUMN] = serialize($alias_column);

                $comment = array_key_exists("$table_alias-$column", $column_comment) ? $column_comment["$table_alias-$column"] : '';
                $response .= '<tr id="columns-'.$alias.'">'.
                    '<td style="width: 15%"><div style="width: 100%;word-wrap:break-word;text-align:left;">'.$alias.'</div></td>'.
                    '<td style="width: 20%"><div style="width: 100%;word-wrap:break-word;text-align:left;">'.$table_alias.'.'.$column.' as '.$alias.'</div></td>'.
                    '<td style="width: 15%"><div style="width: 100%;word-wrap:break-word;">'.$table.'</div></td>'.
                    '<td style="width: 15%"><div style="width: 100%;word-wrap:break-word;">'.$table_alias.'</div></td>'.
                    '<td style="width: 25%"><div style="width: 100%;word-wrap:break-word;text-align:left;">'.$comment.'</div></td>'.
                    '<td style="width: 10%">'.CHtml::Link('删除', 'javascript:columns_inverse_column("'.$column.'","'.$alias.'")',array('id' => "inverse-column-$alias")).'</td>'.
                    '</tr>';
            }
        }

        echo $response;
    }

    /**
     * Step2.查询配置 -> 表 -> 删除选择字段
     */
    public function actionAjaxinversecolumn()
    {
        $column = Common::getStringParam('column');
        $alias = Common::getStringParam('alias');

        $config = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG);

        $columns_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_COLUMNS) ? $config[Common::REDIS_REPORT_CONFIG_COLUMNS] : '';
        $columns_array= unserialize($columns_cache);
        $columns = $columns_array !== false && !empty($columns_array) ? $columns_array : array();

        $column_alias_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_ALIAS_COLUMN) ? $config[Common::REDIS_REPORT_CONFIG_ALIAS_COLUMN] : '';
        $column_alias_array= unserialize($column_alias_cache);
        $alias_column = $column_alias_array !== false && !empty($column_alias_array) ? $column_alias_array : array();

        if (!empty($column)) {
            if (array_key_exists($column, $columns)) {    //对于选择的相同表，别名后加数字序号
                if ($columns[$column] == 1) {
                    unset($columns[$column]);
                }
            }
            $config[Common::REDIS_REPORT_CONFIG_COLUMNS] = serialize($columns);
        }

        if (!empty($alias)) {
            if (array_key_exists($alias, $alias_column)) {
                unset($alias_column[$alias]);
                $config[Common::REDIS_REPORT_CONFIG_ALIAS_COLUMN] = serialize($alias_column);
            }
        }
    }

    /**
     * Step2.查询配置 -> 结果字段 -> 全部字段
     */
    public function actionAjaxchooseallcolumns()
    {
        $response = '';
        $data_source = Common::getNumParam('data_source');

        $config = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG);

        $alias_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_ALIAS_TABLE) ? $config[Common::REDIS_REPORT_CONFIG_ALIAS_TABLE] : '';
        $alias_array= unserialize($alias_cache);
        $alias_table = $alias_array !== false && !empty($alias_array) ? $alias_array : array();

        $columns_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_COLUMNS) ? $config[Common::REDIS_REPORT_CONFIG_COLUMNS] : '';
        $columns_array= unserialize($columns_cache);
        $columns = $columns_array !== false && !empty($columns_array) ? $columns_array : array();

        $column_alias_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_ALIAS_COLUMN) ? $config[Common::REDIS_REPORT_CONFIG_ALIAS_COLUMN] : '';
        $column_alias_array= unserialize($column_alias_cache);
        $alias_column = $column_alias_array !== false && !empty($column_alias_array) ? $column_alias_array : array();

        if (!empty($alias_table)) {
            $table_columns = array();
            list($db,$model) = Common::getDBConnection($data_source);
            if ($db && $model) {
                $sql = "select table_name,column_name,column_comment from information_schema.columns where TABLE_SCHEMA='".$model['database']."' AND TABLE_NAME in ('".implode("','", $alias_table)."')";
                $results = $db->createCommand($sql)->queryAll();
                $db->active = false;

                if (!empty($results)) {
                    foreach ($results as $result)
                    {
                        $table_columns[$result['table_name']][$result['column_name']] = $result['column_comment'];
                    }
                }
            } else {
                Yii::app()->user->setFlash('error', '无法连接数据源，请与管理员联系。');
            }

            $alias_exist = false;
            foreach ($alias_table as $alias => $table) {
                if (array_key_exists($table, $table_columns)) {
                    foreach ($table_columns[$table] as $column => $comment) {
                        $column_alias = $column;
                        if (array_key_exists($column, $columns)) {    //对于选择的相同表，别名后加数字序号
                            $index = $columns[$column] + 1;
                            $column_alias = $column.'_'.$index;
                            $columns[$column] = $index;
                        } else {
                            $columns[$column] = 1;
                        }

                        if (array_key_exists($column_alias, $alias_column)) {
                            $response = 'exist';
                            $alias_exist = true;
                            break;
                        } else {
                            $alias_column[$column_alias] = $column_alias;   //$alias.'.'.$column;
                            $response .= '<tr id="columns-'.$column_alias.'">'.
                                '<td style="width: 15%"><div style="width: 100%;word-wrap:break-word;text-align:left;">'.$column_alias.'</div></td>'.
                                '<td style="width: 20%"><div style="width: 100%;word-wrap:break-word;text-align:left;">'.$alias.'.'.$column.' as '.$column_alias.'</div></td>'.
                                '<td style="width: 15%"><div style="width: 100%;word-wrap:break-word;">'.$table.'</div></td>'.
                                '<td style="width: 15%"><div style="width: 100%;word-wrap:break-word;">'.$alias.'</div></td>'.
                                '<td style="width: 25%"><div style="width: 100%;word-wrap:break-word;text-align:left;">'.$comment.'</div></td>'.
                                '<td style="width: 10%">'.CHtml::Link('删除', 'javascript:columns_inverse_column("'.$column.'","'.$column_alias.'")',array('id' => "inverse-column-$column_alias")).'</td>'.
                                '</tr>';
                        }
                    }
                    if ($alias_exist) {
                        break;
                    }
                }
            }

            if (!$alias_exist) {
                $config[Common::REDIS_REPORT_CONFIG_COLUMNS] = serialize($columns);
                $config[Common::REDIS_REPORT_CONFIG_ALIAS_COLUMN] = serialize($alias_column);
            }
        }
        echo $response;
    }

    /**
     * Step2.查询配置 -> 表 -> 删除全部字段
     */
    public function actionAjaxinverseallcolumns()
    {
        $config = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG);

        $columns_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_COLUMNS) ? $config[Common::REDIS_REPORT_CONFIG_COLUMNS] : '';
        $columns_array= unserialize($columns_cache);
        $columns = $columns_array !== false && !empty($columns_array) ? $columns_array : array();

        $column_alias_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_ALIAS_COLUMN) ? $config[Common::REDIS_REPORT_CONFIG_ALIAS_COLUMN] : '';
        $column_alias_array= unserialize($column_alias_cache);
        $alias_column = $column_alias_array !== false && !empty($column_alias_array) ? $column_alias_array : array();

        $config[Common::REDIS_REPORT_CONFIG_COLUMNS] = '';
        $config[Common::REDIS_REPORT_CONFIG_ALIAS_COLUMN] = '';
    }

    /**
     * Step2.查询配置 -> 表 -> 添加计算字段
     */
    public function actionAjaxaddcalculationcolumn()
    {
        $response = '';
        $alias = Common::getStringParam('alias');
        $config = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG);

        $column_alias_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_ALIAS_COLUMN) ? $config[Common::REDIS_REPORT_CONFIG_ALIAS_COLUMN] : '';
        $column_alias_array= unserialize($column_alias_cache);
        $alias_column = $column_alias_array !== false && !empty($column_alias_array) ? $column_alias_array : array();

        if (array_key_exists($alias, $alias_column)) {
            $response = 'exist';
        } else {
            $response = 'not exist';
            $alias_column[$alias] = $alias;
            $config[Common::REDIS_REPORT_CONFIG_ALIAS_COLUMN] = serialize($alias_column);
        }

        echo $response;
    }

    /**
     * Step2.查询配置 -> 表 -> 删除计算字段
     */
    public function actionAjaxinversecalculationcolumn()
    {
        $alias = Common::getStringParam('alias');

        $config = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG);

        $column_alias_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_ALIAS_COLUMN) ? $config[Common::REDIS_REPORT_CONFIG_ALIAS_COLUMN] : '';
        $column_alias_array= unserialize($column_alias_cache);
        $alias_column = $column_alias_array !== false && !empty($column_alias_array) ? $column_alias_array : array();

        if (!empty($alias)) {
            if (array_key_exists($alias, $alias_column)) {
                unset($alias_column[$alias]);
                $config[Common::REDIS_REPORT_CONFIG_ALIAS_COLUMN] = serialize($alias_column);
            }
        }
    }

    /**
     * 通过ajax加载数据源的数据表字段名称
     */
    public function actionAjaxcolumnsname()
    {
        $response = '';
        $data_source = Common::getNumParam('data_source');

        $config = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG);

        $alias_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_ALIAS_TABLE) ? $config[Common::REDIS_REPORT_CONFIG_ALIAS_TABLE] : '';
        $alias_array= unserialize($alias_cache);
        $alias_table = $alias_array !== false && !empty($alias_array) ? $alias_array : array();

        if (!empty($alias_table)) {
            $table_columns = array();
            list($db,$model) = Common::getDBConnection($data_source);
            if ($db && $model) {
                $sql = "select table_name,column_name from information_schema.columns where TABLE_SCHEMA='".$model['database']."' AND TABLE_NAME in ('".implode("','", $alias_table)."')";
                $results = $db->createCommand($sql)->queryAll();
                $db->active = false;

                if (!empty($results)) {
                    foreach ($results as $result)
                    {
                        $table_columns[$result['table_name']][] = $result['column_name'];
                    }
                }
            } else {
                Yii::app()->user->setFlash('error', '无法连接数据源，请与管理员联系。');
            }

            foreach ($alias_table as $alias => $table) {
                if (array_key_exists($table, $table_columns)) {
                    foreach ($table_columns[$table] as $column) {
                        $c = $alias.'.'.$column;
                        $response .= "<option value='".$c."'>".$c."</option>";
                    }
                }
            }
        }

        echo $response;
    }

    /**
     * 根据数据源的数据表通过ajax加载query builder的filter配置
     */
    public function actionAjaxqueryfilter()
    {
        $response = '';
        $data_source = Common::getNumParam('data_source');
        $new_add = Common::getNumParam('new_add');

        $table_changed = Yii::app()->user->getState(Common::SESSION_STEP2_TABLE_CHANGED);
        if ($new_add==1 || !empty($table_changed)) {   //当对表做过增删后，查询条件重置
            $config = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG);

            $alias_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_ALIAS_TABLE) ? $config[Common::REDIS_REPORT_CONFIG_ALIAS_TABLE] : '';
            $alias_array= unserialize($alias_cache);
            $alias_table = $alias_array !== false && !empty($alias_array) ? $alias_array : array();

            if (!empty($alias_table)) {
                $table_columns = array();
                list($db,$model) = Common::getDBConnection($data_source);
                if ($db && $model) {
                    $sql = "select table_name,column_name,data_type from information_schema.columns where TABLE_SCHEMA='".$model['database']."' AND TABLE_NAME in ('".implode("','", $alias_table)."')";
                    $results = $db->createCommand($sql)->queryAll();
                    $db->active = false;

                    if (!empty($results)) {
                        foreach ($results as $result)
                        {
                            if (!empty($results)) {
                                foreach ($results as $result)
                                {
                                    $table_columns[$result['table_name']][$result['column_name']] = $result['data_type'];
                                }
                            }
                        }
                    }
                } else {
                    Yii::app()->user->setFlash('error', '无法连接数据源，请与管理员联系。');
                }

                $filters = array();
                foreach ($alias_table as $alias => $table) {
                    if (array_key_exists($table, $table_columns)) {
                        foreach ($table_columns[$table] as $column => $data_type) {
                            $c = $alias.'.'.$column;
                            //$cid = 'conditions-'.$alias.'-'.$column;

                            if (in_array($data_type, Common::$db_int_type)) {
                                $type = 'integer';
                            } elseif (in_array($data_type, Common::$db_double_type)) {
                                $type = 'double';
                            } else {
                                $type = 'string';
                            }

                            $filters[] = array(
                                'id' => $c,//$cid
                                //'field' => $c,
                                'label' => $c,
                                'type' => $type,
                            );
                        }
                    }
                }
                $response = CJSON::encode($filters);
            }
            Yii::app()->user->setState(Common::SESSION_STEP2_TABLE_CHANGED, 0); //报表配置中Step2.查询配置，表操作，0为无，1为增，2为删
        }
        echo $response;
    }

    /**
     * 通过ajax加载各类型筛选项配置
     */
    public function actionAjaxselectitemattr()
    {
        $select_item = Common::getStringParam('select_item');
        $response = '';

        $select_items = ReportConfiguration::getSelectItems();
        if (array_key_exists($select_item, $select_items)) {
            foreach ($select_items[$select_item] as $k => $v)
            {
                $response .= "<option value='".$k."'>".$v."</option>";
            }
        }
        echo $response;
    }

    /**
     * 通过ajax添加筛选项
     */
    public function actionAjaxaddselectitem()
    {
        $item_columns = array();
        $report_id = Common::getStringParam('report_id');
        $item_count = Common::getNumParam('item_count');
        $type = Common::getStringParam('type');

        if ($type === 'linked') {
            $item_columns = array(''=>'无','default'=>'默认');
        } else {
            $item_columns = array(''=>'请选择');
        }

        $configuration = ReportConfiguration::getReportConfByID($report_id);
        if ($configuration) {
            $query_sql = unserialize($configuration->query_sql);
            $columns_choosed = !empty($query_sql)&&array_key_exists('columns_choosed', $query_sql) ? $query_sql['columns_choosed'] : array();
            $calculation = !empty($query_sql)&&array_key_exists('calculation', $query_sql) ? $query_sql['calculation'] : array();

            $alias_columns = array_merge($columns_choosed, $calculation);
            foreach ($alias_columns as $alias => $col) {
                if (!isset($col['function']) || empty($col['function'])) {  //目前没有having，所以筛选项字段不可以是汇总函数计算字段，但此处没有检查case when等情况
                    $item_columns[$alias] = $alias;
                }
            }
            /* 
            $columns = array_key_exists('columns', $query_sql) ? $query_sql['columns'] : array();
            if (!empty($columns)) {
                foreach ($columns as $column) {
                    $item_columns[$column] = $column;
                }
            }
             */
        }

        if ($type === 'item') {
            $response = '<li class="list-group-item"><label>筛选字段：</label> '.
                CHtml::dropDownList("column_$item_count",'',$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                '&nbsp;&nbsp;&nbsp;<label>控件类型：</label> '.
                CHtml::dropDownList("select_item_$item_count",'',ReportConfiguration::$select_items,array('class'=>'form-control','style'=>'display:inline-block;width:120px')).
                '&nbsp;&nbsp;&nbsp;<label>控件配置：</label> '.
                CHtml::dropDownList("item_attr_$item_count",'',array(''=>'请选择特性'),array('class'=>'form-control','style'=>'display:inline-block;width:150px')).
                '&nbsp;&nbsp;&nbsp;<span id="list-value-type-'.$item_count.'" class="hide"><label>列表值类型：</label> '.
                CHtml::radioButtonList("list_value_type_$item_count", '0', ReportConfiguration::$list_value_type,array('separator'=>'&nbsp;&nbsp;','disabled'=>'true')).
                '</span>'.CHtml::Link('汇总字段配置', 'javascript:awake_dwysum_config()',array('id'=>"dwysum-config-$item_count",'class'=>'hide')).
                '<span class="pull-right" style="margin: 8px 20px 8px 10px"><i class="glyphicon glyphicon-minus" style="color:red"></i>&nbsp;'.
                CHtml::link('删除','javascript:;',array('id'=>"del-select-item-$item_count")).
                '</span></li>';
        } elseif ($type === 'linked') {
            $select_items = ReportConfiguration::getSelectItems();
            $response = '<li class="list-group-item" style="height:150px;"><div class="col-md-11" style="padding:0"><div style="margin-bottom:10px;"><label>控件类型：</label> '.
                CHtml::dropDownList("select_item_$item_count",'linked',array('linked'=>'联动框'),array('class'=>'form-control','style'=>'display:inline-block;width:100px')).
                '&nbsp;&nbsp;<label>控件配置：</label> '.
                CHtml::dropDownList("dict_$item_count",'',$select_items['linked'],array('class'=>'form-control','style'=>'display:inline-block;width:250px')).
                '</div><div style="margin-bottom:10px;"><label>一级列表：</label> '.
                CHtml::dropDownList("first_column_$item_count",'',$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                '&nbsp;&nbsp;<span id="first-list-value-type-'.$item_count.'" style="margin-right:50px;"><label>值类型：</label> '.
                CHtml::radioButtonList("first_list_value_type_$item_count", '0', ReportConfiguration::$list_value_type,array('separator'=>'&nbsp;&nbsp;')).
                '</span><label>二级列表：</label> '.
                CHtml::dropDownList("second_column_$item_count",'',$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                '&nbsp;&nbsp;<span id="second-list-value-type-'.$item_count.'"><label>值类型：</label> '.
                CHtml::radioButtonList("second_list_value_type_$item_count", '0', ReportConfiguration::$list_value_type,array('separator'=>'&nbsp;&nbsp;')).
                '</span></div><div><label>三级列表：</label> '.
                CHtml::dropDownList("third_column_$item_count",'',$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                '&nbsp;&nbsp;<span id="third-list-value-type-'.$item_count.'" style="margin-right:50px;"><label>值类型：</label> '.
                CHtml::radioButtonList("third_list_value_type_$item_count", '0', ReportConfiguration::$list_value_type,array('separator'=>'&nbsp;&nbsp;')).
                '</span><label>四级列表：</label> '.
                CHtml::dropDownList("fourth_column_$item_count",'',$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                '&nbsp;&nbsp;<span id="fourth-list-value-type-'.$item_count.'"><label>值类型：</label> '.
                CHtml::radioButtonList("fourth_list_value_type_$item_count", '0', ReportConfiguration::$list_value_type,array('separator'=>'&nbsp;&nbsp;')).
                '</span></div></div><div class="col-md-1" style="padding:0"><span class="pull-right" style="margin: 8px 20px 8px 10px"><i class="glyphicon glyphicon-minus-sign" style="color:red"></i>&nbsp;'.
                CHtml::link('删除','javascript:;',array('id'=>"del-select-item-$item_count")).
                '</span></div></li>';
        }
        echo $response;
    }

    /**
     * 初始化报表筛选项
     */
    public function actionAjaxinitconditions()
    {
        $item_columns = array();
        $report_id = Common::getStringParam('report_id');
        $item_count = Common::getNumParam('item_count');

        $configuration = ReportConfiguration::getReportConfByID($report_id);
        if ($configuration) {
            $conditions = unserialize($configuration->conditions);
            $query_sql = unserialize($configuration->query_sql);
            $columns_choosed = !empty($query_sql)&&array_key_exists('columns_choosed', $query_sql) ? $query_sql['columns_choosed'] : array();
            $calculation = !empty($query_sql)&&array_key_exists('calculation', $query_sql) ? $query_sql['calculation'] : array();

            $alias_columns = array_merge($columns_choosed, $calculation);
            foreach ($alias_columns as $alias => $col) {
                if (!isset($col['function']) || empty($col['function'])) {  //目前没有having，所以筛选项字段不可以是汇总函数计算字段，但此处没有检查case when等情况
                    $item_columns[$alias] = $alias;
                }
            }
            /* 
            $columns = array_key_exists('columns', $query_sql) ? $query_sql['columns'] : array();
            if (!empty($columns)) {
                foreach ($columns as $column) {
                    $item_columns[$column] = $column;
                }
            }
             */
        }

        $response = '';
        if (!empty($conditions)) {
            $select_items = ReportConfiguration::getSelectItems();
            foreach ($conditions as $key => $condition) {
                switch ($key) {
                    case 'date':
                        foreach ($condition as $col) {
                            ++$item_count;
                            $item_attr_list = array_key_exists($col['type'], $select_items) ? $select_items[$col['type']] : array(''=>'请选择特性');
                            $dwysum_html_options = array('id'=>"dwysum-config-$item_count");
                            if ($col['type'] != 'dwysum') {
                                $dwysum_html_options['class'] = 'hide';
                            }
                            $response .= '<li class="list-group-item"><label>筛选字段：</label> '.
                                CHtml::dropDownList("column_$item_count",$col['column'],$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                                '&nbsp;&nbsp; <label>控件类型：</label> '.
                                CHtml::dropDownList("select_item_$item_count",$col['type'],ReportConfiguration::$select_items,array('class'=>'form-control','style'=>'display:inline-block;width:120px')).
                                '&nbsp;&nbsp; <label>控件配置：</label> '.
                                CHtml::dropDownList("item_attr_$item_count",$col['attr'],$item_attr_list,array('class'=>'form-control','style'=>'display:inline-block;width:150px')).
                                '&nbsp;&nbsp; <span id="list-value-type-'.$item_count.'" class="hide"><label>列表值类型：</label> '.
                                CHtml::radioButtonList("list_value_type_$item_count", '0', ReportConfiguration::$list_value_type,array('separator'=>'&nbsp;&nbsp;','disabled'=>'true')).
                                '</span>'.
                                CHtml::Link('汇总字段配置', 'javascript:awake_dwysum_config()',$dwysum_html_options).
                                '<span class="pull-right" style="margin: 8px 20px 8px 10px"><i class="glyphicon glyphicon-minus" style="color:red"></i>&nbsp;'.
                                CHtml::link('删除','javascript:;',array('id'=>"del-select-item-$item_count")).
                                '</span></li>';
                        }
                        break;
                    case 'text':
                        foreach ($condition as $col) {
                            ++$item_count;
                            $item_attr_list = array_key_exists($col['type'], $select_items) ? $select_items[$col['type']] : array(''=>'请选择特性');
                            $response .= '<li class="list-group-item"><label>筛选字段：</label> '.
                                CHtml::dropDownList("column_$item_count",$col['column'],$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                                '&nbsp;&nbsp; <label>控件类型：</label> '.
                                CHtml::dropDownList("select_item_$item_count",$col['type'],ReportConfiguration::$select_items,array('class'=>'form-control','style'=>'display:inline-block;width:120px')).
                                '&nbsp;&nbsp; <label>控件配置：</label> '.
                                CHtml::dropDownList("item_attr_$item_count",$col['attr'],$item_attr_list,array('class'=>'form-control','style'=>'display:inline-block;width:150px')).
                                '&nbsp;&nbsp; <span id="list-value-type-'.$item_count.'" class="hide"><label>列表值类型：</label> '.
                                CHtml::radioButtonList("list_value_type_$item_count", '0', ReportConfiguration::$list_value_type,array('separator'=>'&nbsp;&nbsp;','disabled'=>'true')).
                                '</span><span class="pull-right" style="margin: 8px 20px 8px 10px"><i class="glyphicon glyphicon-minus" style="color:red"></i>&nbsp;'.
                                CHtml::link('删除','javascript:;',array('id'=>"del-select-item-$item_count")).
                                '</span></li>';
                        }
                        break;
                    case 'list':
                        foreach ($condition as $col) {
                            ++$item_count;
                            $item_attr_list = array_key_exists($col['type'], $select_items) ? $select_items[$col['type']] : array(''=>'请选择特性');
                            $response .= '<li class="list-group-item"><label>筛选字段：</label> '.
                                CHtml::dropDownList("column_$item_count",$col['column'],$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                                '&nbsp;&nbsp; <label>控件类型：</label> '.
                                CHtml::dropDownList("select_item_$item_count",$col['type'],ReportConfiguration::$select_items,array('class'=>'form-control','style'=>'display:inline-block;width:120px')).
                                '&nbsp;&nbsp; <label>控件配置：</label> '.
                                CHtml::dropDownList("item_attr_$item_count",$col['dict'],$item_attr_list,array('class'=>'form-control','style'=>'display:inline-block;width:150px')).
                                '&nbsp;&nbsp; <span id="list-value-type-'.$item_count.'"><label>列表值类型：</label> '.
                                CHtml::radioButtonList("list_value_type_$item_count", $col['value_type'], ReportConfiguration::$list_value_type,array('separator'=>'&nbsp;&nbsp;')).
                                '</span><span class="pull-right" style="margin: 8px 20px 8px 10px"><i class="glyphicon glyphicon-minus" style="color:red"></i>&nbsp;'.
                                CHtml::link('删除','javascript:;',array('id'=>"del-select-item-$item_count")).
                                '</span></li>';
                        }
                        break;
                    case 'linked':
                        foreach ($condition as $col) {
                            ++$item_count;

                            if (in_array($col['dict'], DictList::$sum_detail_list)) {
                                $title_id = array(''=>'请选择ID');
                                $title_name = array(''=>'请选择名称');
                            } else {
                                $item_head = array(''=>'无','default'=>'默认');
                                $item_columns = $item_head + $item_columns;
                            }

                            $value = array(
                                'first_column' => '',
                                'first_extra_column' => '',
                                'first_value_type' => 0,
                                'second_column' => '',
                                'second_extra_column' => '',
                                'second_value_type' => 0,
                                'third_column' => '',
                                'third_extra_column' => '',
                                'third_value_type' => 0,
                                'fourth_column' => '',
                                'fourth_extra_column' => '',
                                'fourth_value_type' => 0,
                            );

                            $first = array_key_exists('first', $col) ? $col['first'] : array();
                            $second = array_key_exists('second', $col) ? $col['second'] : array();
                            $third = array_key_exists('third', $col) ? $col['third'] : array();
                            $fourth = array_key_exists('fourth', $col) ? $col['fourth'] : array();

                            if (!empty($first)) {
                                $value['first_column'] = $first['is_default']==1 ? 'default' : $first['column'];
                                $value['first_extra_column'] = array_key_exists('extra_column', $first) ? $first['extra_column'] : '';
                                $value['first_value_type'] = $first['value_type'];
                            }
                            if (!empty($second)) {
                                $value['second_column'] = $second['is_default']==1 ? 'default' : $second['column'];
                                $value['second_extra_column'] = array_key_exists('extra_column', $second) ? $second['extra_column'] : '';
                                $value['second_value_type'] = $second['value_type'];
                            }
                            if (!empty($third)) {
                                $value['third_column'] = $third['is_default']==1 ? 'default' : $third['column'];
                                $value['third_extra_column'] = array_key_exists('extra_column', $third) ? $third['extra_column'] : '';
                                $value['third_value_type'] = $third['value_type'];
                            }
                            if (!empty($fourth)) {
                                $value['fourth_column'] = $fourth['is_default']==1 ? 'default' : $fourth['column'];
                                $value['fourth_extra_column'] = array_key_exists('extra_column', $fourth) ? $fourth['extra_column'] : '';
                                $value['fourth_value_type'] = $fourth['value_type'];
                            }

                            $select_items = ReportConfiguration::getSelectItems();
                            if (in_array($col['dict'], DictList::$sum_detail_list)) {
                                $response .= '<li class="list-group-item" style="height:150px;"><div class="col-md-11" style="padding:0"><div style="margin-bottom:10px;"><label>控件类型：</label> '.
                                    CHtml::dropDownList("select_item_$item_count",'linked',array('linked'=>'联动框'),array('class'=>'form-control','style'=>'display:inline-block;width:100px')).
                                    '&nbsp;&nbsp;<label>控件配置：</label> '.
                                    CHtml::dropDownList("dict_$item_count",$col['dict'],$select_items['linked'],array('class'=>'form-control','style'=>'display:inline-block;width:250px')).
                                    '</div><div style="margin-bottom:10px;"><label>一级列表：</label> '.
                                    CHtml::dropDownList("first_column_$item_count",$value['first_column'],$title_id+$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                                    '&nbsp;&nbsp;<span style="margin-right:10px;">'.
                                    CHtml::dropDownList("first_extra_column_$item_count",$value['first_extra_column'],$title_name+$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                                    '</span><label>二级列表：</label> '.
                                    CHtml::dropDownList("second_column_$item_count",$value['second_column'],$title_id+$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                                    '&nbsp;&nbsp;'.
                                    CHtml::dropDownList("second_extra_column_$item_count",$value['second_extra_column'],$title_name+$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                                    '</div><div><label>三级列表：</label> '.
                                    CHtml::dropDownList("third_column_$item_count",$value['third_column'],$title_id+$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                                    '&nbsp;&nbsp;<span style="margin-right:10px;">'.
                                    CHtml::dropDownList("third_extra_column_$item_count",$value['third_extra_column'],$title_name+$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                                    '</span><label>四级列表：</label> '.
                                    CHtml::dropDownList("fourth_column_$item_count",$value['fourth_column'],$title_id+$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                                    '&nbsp;&nbsp;'.
                                    CHtml::dropDownList("fourth_extra_column_$item_count",$value['fourth_extra_column'],$title_name+$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                                    '</div></div><div class="col-md-1" style="padding:0"><span class="pull-right" style="margin: 8px 20px 8px 10px"><i class="glyphicon glyphicon-minus-sign" style="color:red"></i>&nbsp;'.
                                    CHtml::link('删除','javascript:;',array('id'=>"del-select-item-$item_count")).
                                    '</span></div></li>';;
                            } else {
                                $response .= '<li class="list-group-item" style="height:150px;"><div class="col-md-11" style="padding:0"><div style="margin-bottom:10px;"><label>控件类型：</label> '.
                                    CHtml::dropDownList("select_item_$item_count",'linked',array('linked'=>'联动框'),array('class'=>'form-control','style'=>'display:inline-block;width:100px')).
                                    '&nbsp;&nbsp;<label>控件配置：</label> '.
                                    CHtml::dropDownList("dict_$item_count",$col['dict'],$select_items['linked'],array('class'=>'form-control','style'=>'display:inline-block;width:250px')).
                                    '</div><div style="margin-bottom:10px;"><label>一级列表：</label> '.
                                    CHtml::dropDownList("first_column_$item_count",$value['first_column'],$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                                    '&nbsp;&nbsp;<span id="first-list-value-type-'.$item_count.'" style="margin-right:50px;"><label>值类型：</label> '.
                                    CHtml::radioButtonList("first_list_value_type_$item_count", $value['first_value_type'], ReportConfiguration::$list_value_type,array('separator'=>'&nbsp;&nbsp;')).
                                    '</span><label>二级列表：</label> '.
                                    CHtml::dropDownList("second_column_$item_count",$value['second_column'],$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                                    '&nbsp;&nbsp;<span id="second-list-value-type-'.$item_count.'"><label>值类型：</label> '.
                                    CHtml::radioButtonList("second_list_value_type_$item_count", $value['second_value_type'], ReportConfiguration::$list_value_type,array('separator'=>'&nbsp;&nbsp;')).
                                    '</span></div><div><label>三级列表：</label> '.
                                    CHtml::dropDownList("third_column_$item_count",$value['third_column'],$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                                    '&nbsp;&nbsp;<span id="third-list-value-type-'.$item_count.'" style="margin-right:50px;"><label>值类型：</label> '.
                                    CHtml::radioButtonList("third_list_value_type_$item_count", $value['third_value_type'], ReportConfiguration::$list_value_type,array('separator'=>'&nbsp;&nbsp;')).
                                    '</span><label>四级列表：</label> '.
                                    CHtml::dropDownList("fourth_column_$item_count",$value['fourth_column'],$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                                    '&nbsp;&nbsp;<span id="fourth-list-value-type-'.$item_count.'"><label>值类型：</label> '.
                                    CHtml::radioButtonList("fourth_list_value_type_$item_count", $value['fourth_value_type'], ReportConfiguration::$list_value_type,array('separator'=>'&nbsp;&nbsp;')).
                                    '</span></div></div><div class="col-md-1" style="padding:0"><span class="pull-right" style="margin: 8px 20px 8px 10px"><i class="glyphicon glyphicon-minus-sign" style="color:red"></i>&nbsp;'.
                                    CHtml::link('删除','javascript:;',array('id'=>"del-select-item-$item_count")).
                                    '</span></div></li>';
                            }
                        }
                        break;
                }
            }
        }
        echo $response."#####$item_count";    //传回$item_count
    }

    /**
     * 通过ajax转换联动列表配置项
     */
    public function actionAjaxexchangelinkitem()
    {
        $item_columns = array();
        $report_id = Common::getStringParam('report_id');
        $item_count = Common::getNumParam('item_count');
        $dict = Common::getStringParam('dict');

        if (in_array($dict, DictList::$sum_detail_list)) {
            $title_id = array(''=>'请选择ID');
            $title_name = array(''=>'请选择名称');
            $item_columns = array();
        } else {
            $item_columns = array(''=>'无','default'=>'默认');
        }

        $configuration = ReportConfiguration::getReportConfByID($report_id);
        if ($configuration) {
            $query_sql = unserialize($configuration->query_sql);
            $columns_choosed = !empty($query_sql)&&array_key_exists('columns_choosed', $query_sql) ? $query_sql['columns_choosed'] : array();
            $calculation = !empty($query_sql)&&array_key_exists('calculation', $query_sql) ? $query_sql['calculation'] : array();

            $alias_columns = array_merge($columns_choosed, $calculation);
            foreach ($alias_columns as $alias => $col) {
                if (!isset($col['function']) || empty($col['function'])) {  //目前没有having，所以筛选项字段不可以是汇总函数计算字段，但此处没有检查case when等情况
                    $item_columns[$alias] = $alias;
                }
            }
        }

        if (in_array($dict, DictList::$sum_detail_list)) {
            $response = '<div style="margin-bottom:10px;"><label>一级列表：</label> '.
                CHtml::dropDownList("first_column_$item_count",'',$title_id+$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                '&nbsp;&nbsp;<span style="margin-right:10px;">'.
                CHtml::dropDownList("first_extra_column_$item_count",'',$title_name+$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                '</span><label>二级列表：</label> '.
                CHtml::dropDownList("second_column_$item_count",'',$title_id+$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                '&nbsp;&nbsp;'.
                CHtml::dropDownList("second_extra_column_$item_count",'',$title_name+$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                '</div><div><label>三级列表：</label> '.
                CHtml::dropDownList("third_column_$item_count",'',$title_id+$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                '&nbsp;&nbsp;<span style="margin-right:10px;">'.
                CHtml::dropDownList("third_extra_column_$item_count",'',$title_name+$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                '</span><label>四级列表：</label> '.
                CHtml::dropDownList("fourth_column_$item_count",'',$title_id+$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                '&nbsp;&nbsp;'.
                CHtml::dropDownList("fourth_extra_column_$item_count",'',$title_name+$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                '</div>';
        } else {
            $response = '<div style="margin-bottom:10px;"><label>一级列表：</label> '.
                CHtml::dropDownList("first_column_$item_count",'',$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                '&nbsp;&nbsp;<span id="first-list-value-type-'.$item_count.'" style="margin-right:50px;"><label>值类型：</label> '.
                CHtml::radioButtonList("first_list_value_type_$item_count", '0', ReportConfiguration::$list_value_type,array('separator'=>'&nbsp;&nbsp;')).
                '</span><label>二级列表：</label> '.
                CHtml::dropDownList("second_column_$item_count",'',$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                '&nbsp;&nbsp;<span id="second-list-value-type-'.$item_count.'"><label>值类型：</label> '.
                CHtml::radioButtonList("second_list_value_type_$item_count", '0', ReportConfiguration::$list_value_type,array('separator'=>'&nbsp;&nbsp;')).
                '</span></div><div><label>三级列表：</label> '.
                CHtml::dropDownList("third_column_$item_count",'',$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                '&nbsp;&nbsp;<span id="third-list-value-type-'.$item_count.'" style="margin-right:50px;"><label>值类型：</label> '.
                CHtml::radioButtonList("third_list_value_type_$item_count", '0', ReportConfiguration::$list_value_type,array('separator'=>'&nbsp;&nbsp;')).
                '</span><label>四级列表：</label> '.
                CHtml::dropDownList("fourth_column_$item_count",'',$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                '&nbsp;&nbsp;<span id="fourth-list-value-type-'.$item_count.'"><label>值类型：</label> '.
                CHtml::radioButtonList("fourth_list_value_type_$item_count", '0', ReportConfiguration::$list_value_type,array('separator'=>'&nbsp;&nbsp;')).
                '</span></div>';
        }
        echo $response;
    }

    /**
     * 通过ajax添加城市归属展示目标城市字段
     */
    public function actionAjaxaddcitydivision()
    {
        $item_columns = array();
        $report_id = Common::getStringParam('report_id');
        $item_count = Common::getNumParam('city_item_count');

        $configuration = ReportConfiguration::getReportConfByID($report_id);
        if ($configuration) {
            $conditions = unserialize($configuration->conditions);
            $query_sql = unserialize($configuration->query_sql);
            $columns = array_key_exists('columns', $query_sql) ? $query_sql['columns'] : array();
            if (!empty($columns)) {
                foreach ($columns as $column) {
                    $item_columns[$column] = $column;
                }
            }
        }

        $title = array(''=>'请选择');
        $response = '<li class="list-group-item"><label>城市字段：</label> '.
            CHtml::dropDownList("city_column_$item_count",'',$title+$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
            '&nbsp;&nbsp; <label>归属类型：</label> '.
            CHtml::checkBoxList("city_divisions_$item_count", '', ReportConfiguration::$city_divisions, array('separator'=>'&nbsp;&nbsp;')).
            '<span class="pull-right" style="margin: 8px 20px 8px 10px"><i class="glyphicon glyphicon-minus" style="color:red"></i>&nbsp;'.
            CHtml::link('删除','javascript:;',array('id'=>"del-city-column-$item_count")).
            '</span></li>';
        echo $response;
    }

    /**
     * 初始化城市归属展示目标城市字段
     */
    public function actionAjaxinitcitydivision()
    {
        $item_columns = array();
        $report_id = Common::getStringParam('report_id');
        $item_count = Common::getNumParam('city_item_count');

        $configuration = ReportConfiguration::getReportConfByID($report_id);
        if ($configuration) {
            $conditions = unserialize($configuration->conditions);
            $query_sql = unserialize($configuration->query_sql);
            $columns = array_key_exists('columns', $query_sql) ? $query_sql['columns'] : array();
            if (!empty($columns)) {
                foreach ($columns as $column) {
                    $item_columns[$column] = $column;
                }
            }
        }

        $response = '';
        $city_div_columns = ColumnDefine::getCityDivisionColumnsByReport($report_id, false);
        if (!empty($city_div_columns)) {
            foreach ($city_div_columns as $col => $function_detail) {
                ++$item_count;
                $title = array(''=>'请选择');
                $response .= '<li class="list-group-item"><label>城市字段：</label> '.
                    CHtml::dropDownList("city_column_$item_count",$col,$title+$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:180px')).
                    '&nbsp;&nbsp; <label>归属类型：</label> '.
                    CHtml::checkBoxList("city_divisions_$item_count", $function_detail, ReportConfiguration::$city_divisions, array('separator'=>'&nbsp;&nbsp;')).
                    '<span class="pull-right" style="margin: 8px 20px 8px 10px"><i class="glyphicon glyphicon-minus" style="color:red"></i>&nbsp;'.
                    CHtml::link('删除','javascript:;',array('id'=>"del-city-column-$item_count")).
                    '</span></li>';
            }
        }
        echo $response."#####$item_count";    //传回$item_count
    }

    /**
     * 通过ajax添加行级权限控制字段
     */
    public function actionAjaxaddprivilegecolumn()
    {
        $item_columns = array();
        $report_id = Common::getStringParam('report_id');
        $item_count = Common::getNumParam('item_count');

        $configuration = ReportConfiguration::getReportConfByID($report_id);
        if ($configuration) {
            $query_sql = unserialize($configuration->query_sql);
            $tables = !empty($query_sql)&&array_key_exists('tables', $query_sql) ? $query_sql['tables'] : '';

            //权限控制字段从所有表字段中取
            list($db,$model) = Common::getDBConnection($configuration->data_source);
            if ($db && $model) {
                if (!empty($tables)) {
                    foreach ($tables as $table) {
                        $sql = "select column_name from information_schema.columns where TABLE_SCHEMA='".$model['database']."' AND TABLE_NAME='".$table['table_name']."'";
                        $results = $db->createCommand($sql)->queryAll();

                        if (!empty($results)) {
                            foreach ($results as $result)
                            {
                                $column = $table['table_alias'].'.'.$result['column_name'];
                                $item_columns[$column] = $column;
                            }
                        }
                    }
                }
                $db->active = false;

            } else {
                Yii::app()->user->setFlash('error', '无法连接数据源，请与管理员联系。');
            }
        }

        $title = array(''=>'请选择');
        $response = '<li class="list-group-item"><label>权限类型：</label> '.
            CHtml::dropDownList("privilege_type_$item_count",'',ReportConfiguration::$privileges_types,array('class'=>'form-control','style'=>'display:inline-block;width:150px')).
            '&nbsp;&nbsp; <label>控制字段：</label> '.
            CHtml::dropDownList("column_$item_count",'',$title+$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:500px')).
            '<span class="pull-right" style="margin: 8px 40px 8px 10px"><i class="glyphicon glyphicon-minus" style="color:red"></i>&nbsp;'.
            CHtml::link('删除','javascript:;',array('id'=>"del-privilege-column-$item_count")).
            '</span></li>';
        echo $response;
    }

    /**
     * 初始化行级权限控制字段
     */
    public function actionAjaxinitprivcolumns()
    {
        $item_columns = array();
        $report_id = Common::getStringParam('report_id');
        $item_count = Common::getNumParam('item_count');

        $configuration = ReportConfiguration::getReportConfByID($report_id);
        if ($configuration) {
            $query_sql = unserialize($configuration->query_sql);
            $tables = !empty($query_sql)&&array_key_exists('tables', $query_sql) ? $query_sql['tables'] : '';

            //权限控制字段从所有表字段中取
            list($db,$model) = Common::getDBConnection($configuration->data_source);
            if ($db && $model) {
                if (!empty($tables)) {
                    foreach ($tables as $table) {
                        $sql = "select column_name from information_schema.columns where TABLE_SCHEMA='".$model['database']."' AND TABLE_NAME='".$table['table_name']."'";
                        $results = $db->createCommand($sql)->queryAll();

                        if (!empty($results)) {
                            foreach ($results as $result)
                            {
                                $column = $table['table_alias'].'.'.$result['column_name'];
                                $item_columns[$column] = $column;
                            }
                        }
                    }
                }
                $db->active = false;

            } else {
                Yii::app()->user->setFlash('error', '无法连接数据源，请与管理员联系。');
            }
        }

        $response = '';
        $privilege_columns = ColumnForPrivilege::getPrivilegeColumnsByReport($report_id,false);
        if (!empty($privilege_columns)) {
            foreach ($privilege_columns as $col => $pritype) {
                ++$item_count;
                $title = array(''=>'请选择');
                $response .= '<li class="list-group-item"><label>权限类型：</label> '.
                    CHtml::dropDownList("privilege_type_$item_count",$pritype,ReportConfiguration::$privileges_types,array('class'=>'form-control','style'=>'display:inline-block;width:150px')).
                    '&nbsp;&nbsp; <label>控制字段：</label> '.
                    CHtml::dropDownList("column_$item_count",$col,$title+$item_columns,array('class'=>'form-control','style'=>'display:inline-block;width:500px')).
                    '<span class="pull-right" style="margin: 8px 40px 8px 10px"><i class="glyphicon glyphicon-minus" style="color:red"></i>&nbsp;'.
                    CHtml::link('删除','javascript:;',array('id'=>"del-privilege-column-$item_count")).
                    '</span></li>';
            }
        }
        echo $response."#####$item_count";    //传回$item_count
    }

    /**
     * 报表列表
     */
    public function actionIndex()
    {
        $report_name = Common::getStringParam('report_name');
        $criteria = new CDbCriteria();
        if (!empty($report_name))
            $criteria->compare('report_name', $report_name, true);
        $criteria->with = array('datasource', 'menu');
        $criteria->order = 'update_time desc, create_time desc';
        $dataProvider = new CActiveDataProvider('ReportConfiguration', array(
            'pagination'=>array(
                'pageSize'=>10,
                'params'=>array(
                    'report_name' => $report_name,
                ),
            ),
            'criteria' => $criteria
        ));

        $this->render('index', array(
            'dataProvider' => $dataProvider,
            'report_name' => $report_name,
        ));
    }

    /**
     * 显示或隐藏报表
     */
    public function actionToggle()
    {
        $report_name = Common::getStringParam('report_name');
        $page = Common::getNumParam('ReportConfiguration_page');
        $report_id = Common::getStringParam('report_id');

        $configuration = ReportConfiguration::getReportRelationByID($report_id);
        if (empty($configuration))
        {
            Yii::app()->user->setFlash('error', '未找到指定报表');
        }
        else
        {
            if (Menu::model()->updateAll(array(
                'status' => $configuration->menu->status == 0 ? 1 : 0
            ),
                'report_id=:report_id',
                array(':report_id'=>$report_id)))
            {
                $redirect = array('configure/index',
                    'report_name' => $report_name,
                );
                if (!empty($page))
                    $redirect['ReportConfiguration_page'] = $page;
                $this->redirect($redirect);
            }
        }
    }

    /**
     * 删除报表
     */
    public function actionDelete()
    {
        $report_id = Common::getStringParam('report_id');
        $configuration = ReportConfiguration::getReportConfByID($report_id);
        if(empty($configuration))
        {
            Yii::app()->user->setFlash('error', '未找到指定报表');
        }
        else
        {
            if ($configuration->deleteByPk($report_id))
            {
                ColumnDefine::model()->deleteAll('report_id=:report_id',array(':report_id'=>$report_id));
                ReportPrivileges::model()->deleteAll('report_id=:report_id',array(':report_id'=>$report_id));
                Menu::model()->deleteAll('report_id=:report_id',array(':report_id'=>$report_id));
                $this->redirect(array('configure/index'));
            }
        }
    }

    /**
     * 创建报表
     */
    public function actionCreate()
    {
        $visiable = 0;
        $step = Common::getNumParam('step', 1);

        switch ($step) {
            case 1:
                $report_id = Common::getStringParam('report_id');
                if (!empty($report_id)) {
                    $config = ReportConfiguration::getReportRelationByID($report_id);
                }
                $report_name = isset($config) ? $config->report_name : '';
//                $platform = isset($config)&&isset($config->menu) ? $config->menu->platform : '';
                $platform = 9;
                $parent_id = isset($config)&&isset($config->menu) ? $config->menu->parent_id : 0;
                $tab_state = isset($config)&&isset($config->menu) ? $config->menu->tab_state : 0;

                $next = false;

                if (!empty($_POST)) {

                    $report_name = Common::getStringParam('report_name');
                    $platform = 9;
                    $first_grade = Common::getNumParam('first_grade');

                    $second_grade = Common::getNumParam('second_grade');

                    $third_grade = Common::getNumParam('third_grade');

                    $tab_state = Common::getNumParam('tab_state');

                    if (!empty($third_grade)) {
                        $menu_grade = 4;
                        $tab_state = 2;
                        $parent_id = $third_grade;
                    } elseif (!empty($second_grade)) {
                        $menu_grade = 3;
                        $parent_id = $second_grade;
                    } elseif (!empty($first_grade)) {
                        $menu_grade = 2;
                        $parent_id = $first_grade;
                    } else {
                        $menu_grade = 0;
                        $tab_state = 0;
                        $parent_id = 0;
                    }

                    if (!empty($report_name)) {
                        $report_id = Common::getStringParam('report_id');
                        if (!empty($report_id)) {
                            $configuration = ReportConfiguration::getReportConfByID($report_id);
                            if(empty($configuration)) {
                                Yii::app()->user->setFlash('error', '未找到指定报表');
                                $this->redirect(array('configure/index'));
                            }
                        } else {
                            $configuration = new ReportConfiguration();
                            $report_id = $configuration->generateRandomKey();

                            $duplicate = ReportConfiguration::getReportConfByID($report_id);;
                            if (!empty($duplicate)) {
                                Yii::app()->user->setFlash('error', '意外!报表ID已存在，请重新创建!');
                            }
                        }
                        $attributes = array(
                            'id' => $report_id,
                            'report_name' => $report_name,
                            //'create_time' => time(),    //ReportConfiguration::beforeAction()
                        );
                        $configuration->attributes = $attributes;

                        if($configuration->save()) {
                            $menu = Menu::getMenuByReportID($report_id);
                            if (!$menu) {
                                $menu = new Menu();
                            }
                            $attributes_menu = array(
                                'status' => 0,
                                'platform' => $platform,
                                'menu_grade' => $menu_grade,
                                'parent_id' => $parent_id,
//                                    'parent_id' => $third_grade,
                                'tab_state' => $tab_state,
                                'menu_name' => $report_name,
                                'report_id' => $report_id,
                            );

                            $menu->attributes = $attributes_menu;
                            if($menu->save()) {
                                Common::dealParentTabState($platform, $parent_id);
                                Yii::app()->user->setFlash('success', 'Step1.名称与路径添加成功！');
                                $next = true;
                            } else {
                                Yii::app()->user->setFlash('error', '报表菜单添加失败，请创建完报表后对本报表“编辑”来重新配置！');
                            }
                        } elseif ($configuration->hasErrors('report_name')) {
                            Yii::app()->user->setFlash('error', $configuration->getError('report_name'));
                        } else {
                            Yii::app()->user->setFlash('error', '名称与路径添加失败！');
                        }
                    }
                }

                list($first_grade,$second_grade,$third_grade, $first_menus, $second_menus, $third_menus) = Common::getParentsByPlatAndID($platform, $parent_id);

                Yii::app()->redis->getClient()->delete(Common::REDIS_COMMEN_TAB_REPORTS);    //删除标签页报表公用缓存
                Yii::app()->redis->getClient()->delete(Common::REDIS_COMMEN_DCPLAT_SELFREPORT_WITH_PARENT);    //删除数据平台中自助报表父子结构缓存

                if ($next) {
                    Yii::app()->request->redirect("/configure/create?step=2&report_id=".$report_id);
                } else {
                    $this->render('configure', array(
                        'visiable' => 1,
                        'step' => 1,
                        'report_name' => $report_name,
                        'report_id' => $report_id,
                        'platform' => $platform,
                        'tab_state' => $tab_state,
                        'first_grade' => $first_grade,
                        'second_grade' => $second_grade,
                        'third_grade' => $third_grade,
                        'first_menus' => $first_menus,
                        'second_menus' => $second_menus,
                        'third_menus' => $third_menus,
                    ));
                }

                break;
            case 2:
                if (empty($_POST)) {
                    Common::clearCacheForConfig();    //清除缓存
                }

                $next = false;
                $report_id = Common::getStringParam('report_id');
                $configuration = ReportConfiguration::getReportConfByID($report_id);
                if(empty($configuration)) {
                    Yii::app()->user->setFlash('error', '查询配置配置失败！由于Step1设置有误，未生成名称与路径，已返回。请重新配置。');
                    Yii::app()->request->redirect("/configure/create?step=1");
                }

                $data_sources = DataSource::getSelectList();
                $data_source = !empty($configuration->data_source) ? $configuration->data_source : '';
                $query_sql_model = !empty($configuration->query_sql) ? $configuration->query_sql : '';
                $query_sql = unserialize($query_sql_model);
                $columns = !empty($query_sql)&&array_key_exists('columns', $query_sql) ? $query_sql['columns'] : array();
                $columns_choosed = !empty($query_sql)&&array_key_exists('columns_choosed', $query_sql) ? $query_sql['columns_choosed'] : array();
                $distinct = !empty($query_sql)&&array_key_exists('distinct', $query_sql) ? $query_sql['distinct'] : 0;
                $tables_choosed = !empty($query_sql)&&array_key_exists('tables', $query_sql) ? $query_sql['tables'] : array();
                $calculation = !empty($query_sql)&&array_key_exists('calculation', $query_sql) ? $query_sql['calculation'] : array();
                $table_used_times = !empty($query_sql)&&array_key_exists('table_used_times', $query_sql) ? $query_sql['table_used_times'] : array();
                $column_used_times = !empty($query_sql)&&array_key_exists('column_used_times', $query_sql) ? $query_sql['column_used_times'] : array();
                $condition_json = !empty($query_sql)&&array_key_exists('condition_json', $query_sql) ? $query_sql['condition_json'] : '';
                $group = !empty($query_sql)&&array_key_exists('group', $query_sql) ? $query_sql['group'] : array();
                $order = !empty($query_sql)&&array_key_exists('order', $query_sql) ? $query_sql['order'] : array();

                $config = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG);
                if (!empty($table_used_times) && !Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_TABLES)) {
                    $config[Common::REDIS_REPORT_CONFIG_TABLES] = serialize($table_used_times);
                }
                if (!empty($column_used_times) && !Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_COLUMNS)) {
                    $config[Common::REDIS_REPORT_CONFIG_COLUMNS] = serialize($column_used_times);
                }
                if (!empty($columns) && !Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_ALIAS_COLUMN)) {
                    $alias_columns = array_combine($columns, $columns);
                    $config[Common::REDIS_REPORT_CONFIG_ALIAS_COLUMN] = serialize($alias_columns);
                }
                Yii::app()->redis->getClient()->setTimeout(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, 12*Common::REDIS_DURATION);

                list($tables, $filters_json) = $this->getDataSourceDetails($data_source, $tables_choosed);

                if (!empty($_POST)) {
                    $data_source = Common::getNumParam('data_source');
                    //$table = Common::getStringParam('table');
                    //$select = Common::getArrayParam('select');
                    $condition = $_POST['condition'];
                    $condition_json = $_POST['condition_json'];
                    $param_distinct = Common::getNumParam('is_distinct');
                    $param_tables = Common::getJsonParam('tables');
                    $param_columns = Common::getJsonParam('columns');
                    $param_columns_choosed = Common::getJsonParam('columns_choosed');
                    $param_columns_calculation = Common::getJsonParam('columns_calculation');
                    $param_group_columns = Common::getJsonParam('group_columns');
                    $param_order_columns = Common::getJsonParam('order_columns');

                    if (!empty($data_source) && !empty($param_tables) && !empty($param_columns)) {
                        $has_error = false;

                        $choosed_columns = array();
                        foreach ($param_columns_choosed as $choosed_col) {
                            $choosed_columns[$choosed_col['col_alias']] = array(
                                'expression' => $choosed_col['expression'],
                                'table_name' => $choosed_col['table_name'],
                                'table_alias' => $choosed_col['table_alias'],
                            );
                        }

                        $from = '';
                        $idx = 0;
                        foreach ($param_tables as $table) {
                            ++$idx;
                            if ($idx == 1) {
                                $from = $table['table_name'].' '.$table['table_alias'].' ';
                            } else {
                                if (!isset($table['join_type'])) {
                                    $from = '';
                                } else {
                                    $from .= ' '.$table['join_type'].' join '.$table['table_name'].' '.$table['table_alias'].' on ';
                                    $on = '';
                                    if (!empty($table['related_condition'])) {
                                        $loop = 0;
                                        foreach ($table['related_condition'] as $related_condition) {
                                            ++$loop;
                                            if ($loop > 1) {
                                                $on .= ' and ';
                                            }
                                            $on .= $related_condition['self_column'].$related_condition['operator'].$related_condition['related_column'];
                                        }
                                    }
                                    if (empty($on)) {
                                        $from = '';
                                    } else {
                                        $from .= $on;
                                    }
                                }
                            }
                        }

                        $calculation = array();
                        foreach ($param_columns_calculation as $cal_col) {
                            if (strpos($cal_col['expression'], ';') === false && strpos($cal_col['expression'], '#') === false &&
                                strpos($cal_col['expression'], '-- ') === false && strpos($cal_col['expression'], '/*') === false) {
                                $calculation[$cal_col['col_alias']] = array(
                                    'function' => $cal_col['function'],
                                    'expression' => $cal_col['expression'],
                                );
                            } else {
                                Yii::app()->user->setFlash('error', '查询sql的语句有误，包含结束或注释字符。');
                                $has_error = true;
                                break;
                            }
                        }

                        $order = array();
                        foreach ($param_order_columns as $order_column) {
                            $order[$order_column['column']] = $order_column['order'];
                        }

                        $config = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG);

                        $tables_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_TABLES) ? $config[Common::REDIS_REPORT_CONFIG_TABLES] : '';
                        $tables_array= unserialize($tables_cache);
                        $table_used_times = $tables_array !== false && !empty($tables_array) ? $tables_array : array();

                        $columns_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_COLUMNS) ? $config[Common::REDIS_REPORT_CONFIG_COLUMNS] : '';
                        $columns_array= unserialize($columns_cache);
                        $column_used_times = $columns_array !== false && !empty($columns_array) ? $columns_array : array();

                        $select = array();
                        $ret_cols = array_merge($choosed_columns, $calculation);
                        foreach ($param_columns as $col) {
                            if (array_key_exists($col, $ret_cols)) {
                                $select[$col] = $ret_cols[$col]['expression'];
                            }
                        }

                        if (empty($select)) {
                            Yii::app()->user->setFlash('error', '查询配置配置失败！结果字段配置有误，请重新配置。');
                        } elseif (empty($from)) {
                            Yii::app()->user->setFlash('error', '查询配置配置失败！数据表关联配置有误，请重新配置。');
                        } else {
                            $query = array(
                                'select' => $select,
                                'columns' => $param_columns,
                                'columns_choosed' => $choosed_columns,
                                'distinct' => $param_distinct,
                                'from' => $from,
                                'tables' => $param_tables,
                                'calculation' => $calculation,
                                'table_used_times' => $table_used_times,
                                'column_used_times' => $column_used_times,
                                'where' => $condition,
                                'condition_json' => $condition_json,
                                'group' => $param_group_columns,
                                'order' => $order,
                            );

                            $configuration->data_source = $data_source;
                            $configuration->query_sql = serialize($query);
                            if (!$has_error) {
                                if ($configuration->save()) {
                                    $next = true;
                                    Yii::app()->user->setFlash('success', 'Step2.查询配置配置成功！');
                                    list($tables, $filters_json) = $this->getDataSourceDetails($data_source, $tables_choosed);
                                    if (Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_JOIN_MODAL_ALIAS)) {
                                        unset($config[Common::REDIS_REPORT_CONFIG_JOIN_MODAL_ALIAS]);
                                    }
                                } else {
                                    Yii::app()->user->setFlash('error', '查询配置配置失败！由于数据库未成功写入，请重新配置。');
                                }
                            }
                        }
                    } else {
                        if (empty($data_source)) {
                            Yii::app()->user->setFlash('error', '查询配置配置失败！没有配置数据源，请重新配置。');
                        } elseif (empty($param_tables)) {
                            Yii::app()->user->setFlash('error', '查询配置配置失败！没有配置数据表，请重新配置。');
                        } elseif (empty($param_columns)){
                            Yii::app()->user->setFlash('error', '查询配置配置失败！没有配置结果字段，请重新配置。');
                        }
                    }
                }

                if ($next) {
                    Yii::app()->request->redirect("/configure/create?step=3&report_id=".$report_id);
                } else {
                    $this->render('configure', array(
                        'visiable' => 2,
                        'step' => 2,
                        'report_id' => $report_id,
                        'data_source' => !empty($data_source) ? $data_source : '',
                        'data_sources' => $data_sources,
                        'tables' => $tables,
                        'tables_choosed' => $tables_choosed,
                        'columns' => $columns,
                        'columns_choosed' => $columns_choosed,
                        'distinct' => $distinct,
                        'calculation' => $calculation,
                        'condition_json' => $condition_json,
                        'filters_json' => $filters_json, //isset($filters_json) ? $filters_json : '',
                        'group' => $group,
                        'order' => $order,
                    ));
                }
                break;
            case 3:
                $next = false;
                $columns = array();
                $item_columns = array();
                $alias_expression = array();
                $dwysum_columns = array();
                $report_id = Common::getStringParam('report_id');

                $configuration = ReportConfiguration::getReportConfByID($report_id);
                if ($configuration) {
                    $query_sql = unserialize($configuration->query_sql);
                    $columns = !empty($query_sql)&&array_key_exists('columns', $query_sql) ? $query_sql['columns'] : array();
                    $columns_choosed = !empty($query_sql)&&array_key_exists('columns_choosed', $query_sql) ? $query_sql['columns_choosed'] : array();
                    $calculation = !empty($query_sql)&&array_key_exists('calculation', $query_sql) ? $query_sql['calculation'] : array();

                    $alias_columns = array_merge($columns_choosed, $calculation);
                    foreach ($alias_columns as $alias => $col) {
                        if (!isset($col['function']) || empty($col['function'])) {  //目前没有having，所以筛选项字段不可以是汇总函数计算字段，但此处没有检查case when等情况
                            $item_columns[$alias] = $alias;
                            $expression = explode(' as ', $col['expression']);
                            $alias_expression[$alias] = $expression[0];
                        }
                    }

                    foreach ($columns_choosed as $alias => $col) {
                        $expression = explode(' as ', $col['expression']);
                        $dwysum_columns[$expression[0]] = $expression[0];
                    }

                } else {
                    Yii::app()->user->setFlash('error', '查询配置配置失败！由于Step1设置有误，未生成名称与路径，已返回。请重新配置。');
                    Yii::app()->request->redirect("/configure/create?step=1");
                }

                $show_parts_model = !empty($configuration->show_parts) ? $configuration->show_parts : '';
                $show_parts = unserialize($show_parts_model);
                $show_parts = empty($show_parts) ? ReportConfiguration::$show_parts_default : $show_parts;
                $conditions_model = !empty($configuration->conditions) ? $configuration->conditions : '';
                $conditions = unserialize($conditions_model);

                $dwysum_calculation = array();
                if (!empty($conditions) && array_key_exists('date', $conditions)) {
                    foreach ($conditions['date'] as $col) {
                        if (array_key_exists('sumcols', $col) && !empty($col['sumcols']) &&
                            array_key_exists('calculation', $col['sumcols']) && !empty($col['sumcols']['calculation'])) {
                            $dwysum_calculation = $col['sumcols']['calculation'];
                        }
                    }
                }
                $columns_info = ColumnDefine::getColumnsInfoByReport($report_id,$columns,false);

                if (!empty($_POST)) {
                    $success = true;
                    $show_parts = Common::getArrayParam('show_parts', ReportConfiguration::$show_parts_default);
                    $item_count = Common::getNumParam('item_count');
                    list($select_items_conf, $has_error, $dwysum_calculation) = $this->step3AssembleConditions($configuration, $item_count, $alias_expression, $columns_choosed, $columns_info);

                    $configuration->show_parts = serialize($show_parts);
                    $configuration->conditions = serialize($select_items_conf);
                    if (!$has_error) {
                        $succ = $configuration->save();
                        $success = $success && $succ;

                        //城市归属展示
                        $city_item_count = Common::getNumParam('city_item_count');
                        ColumnDefine::emptyCityDivisionColumns($report_id);
                        for ($i=0; $i<=$city_item_count; $i++) {
                            $city_column = array_key_exists("city_column_$i", $_POST) ? $_POST["city_column_$i"] : '';
                            $city_divisions = array_key_exists("city_divisions_$i", $_POST) ? $_POST["city_divisions_$i"] : '';

                            if (!empty($city_column)) {
                                $attributes = array(
                                    'report_id' => $report_id,
                                    'column_name' => $city_column,
                                    'expression' => array_key_exists($city_column, $alias_expression) ? $alias_expression[$city_column] : '',
                                    'function' => ColumnDefine::FUNCTION_CITY_DIVISION_COLUMN,
                                    'function_detail' => is_array($city_divisions) ? serialize($city_divisions) : '',
                                );

                                $pk = array('report_id'=>$report_id,'column_name'=>$city_column);
                                $coldef = ColumnDefine::model()->findByPk($pk);
                                if (!$coldef) {
                                    $coldef = new ColumnDefine();
                                }
                                $coldef->attributes = $attributes;
                                $succ = $coldef->save();
                                $success = $success && $succ;
                            }
                        }

                        if ($success) {
                            Yii::app()->user->setFlash('success', 'Step3.展示内容配置成功！');
                            $next = true;
                        } else {
                            Yii::app()->user->setFlash('error', '展示内容配置失败！由于数据库未成功写入，请重新配置。');
                        }
                    }
                }

                $city_div_columns = ColumnDefine::getCityDivisionColumnsByReport($report_id, false);
                if ($next) {
                    Yii::app()->request->redirect("/configure/create?step=4&report_id=".$report_id);
                } else {
                    $title = array(''=>'请选择');
                    $this->render('configure', array(
                        'visiable' => 3,
                        'step' => 3,
                        'report_id' => $report_id,
                        'show_parts' => $show_parts,
                        'item_columns' => $title+$item_columns,
                        'dwysum_columns' => $title+$dwysum_columns,
                        'dwysum_calculation' => $dwysum_calculation,
                        'columns_info' => $columns_info,
                        'city_div_columns' => $city_div_columns,
                        'conditions' => $conditions,
                        'item_count' => isset($item_count) ? $item_count : 0,
                        'city_item_count' => isset($city_item_count) ? $city_item_count : 0,
                    ));
                }
                break;
            case 4:
                $next = false;
                $item_columns = array();
                $report_id = Common::getStringParam('report_id');

                $user_groups = UserGroup::getAllGroups();

                $configuration = ReportConfiguration::getReportConfByID($report_id);
                if ($configuration) {
                    $query_sql = unserialize($configuration->query_sql);
                    $tables = !empty($query_sql)&&array_key_exists('tables', $query_sql) ? $query_sql['tables'] : '';

                    //权限控制字段从所有表字段中取
                    list($db,$model) = Common::getDBConnection($configuration->data_source);
                    if ($db && $model) {
                        if (!empty($tables)) {
                            foreach ($tables as $table) {
                                $sql = "select column_name from information_schema.columns where TABLE_SCHEMA='".$model['database']."' AND TABLE_NAME='".$table['table_name']."'";
                                $results = $db->createCommand($sql)->queryAll();

                                if (!empty($results)) {
                                    foreach ($results as $result)
                                    {
                                        $column = $table['table_alias'].'.'.$result['column_name'];
                                        $item_columns[$column] = $column;
                                    }
                                }
                            }
                        }
                        $db->active = false;

                    } else {
                        Yii::app()->user->setFlash('error', '无法连接数据源，请与管理员联系。');
                    }
                } else {
                    Yii::app()->user->setFlash('error', '查询配置配置失败！由于Step1设置有误，未生成名称与路径，已返回。请重新配置。');
                    Yii::app()->request->redirect("/configure/create?step=1");
                }

                $user_group = ReportPrivileges::getUsergroupsByReport($report_id);
                $privilege_columns = ColumnForPrivilege::getPrivilegeColumnsByReport($report_id,false);

                if (!empty($_POST)) {
                    $success = true;
                    $item_count = Common::getNumParam('item_count');
                    $user_group = Common::getArrayParam('user_group');

                    if (!empty($user_group)) {
                        if (count($user_group) == 1 && empty($user_group[0])) {
                            ReportPrivileges::model()->deleteAll('report_id=:report_id',array(':report_id'=>$report_id));
                        } else {
                            foreach ($user_group as $user_group_id) {
                                $pk = array('report_id'=>$report_id,'user_group_id'=>$user_group_id);
                                $rp = ReportPrivileges::model()->findByPk($pk);
                                if (!$rp) {
                                    $rp = new ReportPrivileges();
                                    $rp->attributes = $pk;
                                    $succ = $rp->save();
                                    $success = $success && $succ;
                                }
                            }
                        }
                    } else {
                        ReportPrivileges::model()->deleteAll('report_id=:report_id',array(':report_id'=>$report_id));
                    }

                    ColumnForPrivilege::emptyReportPrivilegeColumns($report_id);
                    for ($i=0; $i<=$item_count; $i++) {
                        $column = array_key_exists("column_$i", $_POST) ? $_POST["column_$i"] : '';
                        $privilege_type = array_key_exists("privilege_type_$i", $_POST) ? $_POST["privilege_type_$i"] : '';

                        if ($column != '' && $privilege_type != '') {
                            $attributes = array(
                                'report_id' => $report_id,
                                'expression' => $column,
                                'privilege_type' => $privilege_type,
                            );

                            $pk = array('report_id'=>$report_id,'expression'=>$column);
                            $coldef = ColumnForPrivilege::model()->findByPk($pk);
                            if (!$coldef) {
                                $coldef = new ColumnForPrivilege();
                            }
                            $coldef->attributes = $attributes;
                            $succ = $coldef->save();
                            $success = $success && $succ;
                        }
                    }

                    //清除数据项定义公用缓存
                    $cache_priv_cols = new ARedisHash(Common::REDIS_COMMON_PRIVILEGE_COLUMNS);
                    if (Yii::app()->redis->getClient()->hExists(Common::REDIS_COMMON_PRIVILEGE_COLUMNS, $report_id)) {
                        $cache_priv_cols->remove($report_id);
                    }

                    if ($success) {
                        Yii::app()->user->setFlash('success', 'Step4.权限分配成功！');
                        $next = true;
                    } else {
                        Yii::app()->user->setFlash('error', '权限分配失败！由于数据库未成功写入，请重新配置。');
                    }
                }

                $title = array(''=>'请选择');
                if ($next) {
                    Yii::app()->request->redirect("/configure/create?step=5&report_id=".$report_id);
                } else {
                    $this->render('configure', array(
                        'visiable' => 4,
                        'step' => 4,
                        'report_id' => $report_id,
                        'user_group' => $user_group,
                        'user_groups' => $user_groups,
                        'item_columns' => $title+$item_columns,
                        'privilege_columns' => $privilege_columns,
                    ));
                }
                break;
            case 5:
                $next = false;
                $columns = array();
                $item_columns = array();
                $city_div_columns = array();
                $report_id = Common::getStringParam('report_id');

                $configuration = ReportConfiguration::getReportConfByID($report_id);
                if ($configuration) {
                    $query_sql = unserialize($configuration->query_sql);
                    $columns = !empty($query_sql)&&array_key_exists('columns', $query_sql) ? $query_sql['columns'] : array();
                } else {
                    Yii::app()->user->setFlash('error', '查询配置配置失败！由于Step1设置有误，未生成名称与路径，已返回。请重新配置。');
                    Yii::app()->request->redirect("/configure/create?step=1");
                }

                $columns_info_exists = true;
                $columns_info = ColumnDefine::getColumnsInfoByReport($report_id,$columns,false,$columns_info_exists);
                if (!empty($columns)) {
                    foreach ($columns as $column) {
                        if (array_key_exists($column, $columns_info)) {
                            if ($columns_info[$column]['function'] == ColumnDefine::FUNCTION_CITY_DIVISION_COLUMN) {
                                $function_detail = !empty($columns_info[$column]['function_detail']) ? unserialize($columns_info[$column]['function_detail']) : array();
                                if (in_array(ColumnDefine::FUNCTION_DETAIL_CITY_DIV_AREA_REGION, $function_detail)) {
                                    $column_area = $column."_area_".$report_id;
                                    $column_region = $column."_region_".$report_id;
                                    $item_columns[$column_area] = $column_area;
                                    $item_columns[$column_region] = $column_region;
                                    $city_div_columns[] = $column_area;
                                    $city_div_columns[] = $column_region;
                                }
                                if (in_array(ColumnDefine::FUNCTION_DETAIL_CITY_DIV_WARZONE, $function_detail)) {
                                    $column_warzone = $column."_warzone_".$report_id;
                                    $item_columns[$column_warzone] = $column_warzone;
                                    $city_div_columns[] = $column_warzone;
                                }
                            }
                        }
                        $item_columns[$column] = $column;
                    }
                }
                $columns_info_citydiv = ColumnDefine::getCityDivColumnsInfoByReport($report_id,false);

                $alias_comments = self::getChoosedColumnsComments($configuration, $query_sql);
                if (!empty($_POST)) {
                    $success = true;

                    //数据项定义
                    if (!empty($columns)) {
                        foreach ($columns as $column) {
                            $pk = array('report_id'=>$report_id,'column_name'=>$column);
                            $coldef = ColumnDefine::model()->findByPk($pk);
                            if (!$coldef) {
                                $coldef = new ColumnDefine();
                            }
                            $attributes = array(
                                'report_id' => $report_id,
                                'column_name' => $column,
                                'show_name' => $_POST['col_'.$column.'_name'],
                                'define' => $_POST['col_'.$column.'_define'],
                            );
                            $coldef->attributes = $attributes;
                            $succ = $coldef->save();
                            $success = $success && $succ;
                        }
                    }

                    if (!empty($city_div_columns)) {
                        foreach ($city_div_columns as $column) {
                            $pk = array('report_id'=>$report_id,'column_name'=>$column);
                            $coldef = ColumnDefine::model()->findByPk($pk);
                            if (!$coldef) {
                                $coldef = new ColumnDefine();
                            }
                            $attributes = array(
                                'report_id' => $report_id,
                                'column_name' => $column,
                                'show_name' => $_POST['col_'.$column.'_name'],
                                'define' => $_POST['col_'.$column.'_define'],
                                'function' => 2,
                            );
                            $coldef->attributes = $attributes;
                            $succ = $coldef->save();
                            $success = $success && $succ;
                        }
                    }

                    /* 
                    //清除数据项定义公用缓存
                    $cache_cols_info = new ARedisHash(Common::REDIS_COMMON_COLUMNS_INFO);
                    if (Yii::app()->redis->getClient()->hExists(Common::REDIS_COMMON_COLUMNS_INFO, $report_id)) {
                        $cache_cols_info->remove($report_id);
                    }
                     */
                    $columns_info = ColumnDefine::getColumnsInfoByReport($report_id,$columns,false);    //该方法已重置缓存
                    $columns_info_citydiv = ColumnDefine::getCityDivColumnsInfoByReport($report_id,false);
                    if ($success) {
                        Yii::app()->user->setFlash('success', 'Step5.数据项定义配置成功！');
                        $next = true;
                    } else {
                        Yii::app()->user->setFlash('error', '展示内容配置失败！由于数据库未成功写入，请重新配置。');
                    }
                }

                if ($next) {
                    Yii::app()->request->redirect("/configure/create?step=6&report_id=".$report_id);
                } else {
                    $title = array(''=>'请选择');
                    $this->render('configure', array(
                        'visiable' => 5,
                        'step' => 5,
                        'report_id' => $report_id,
                        'item_columns' => $title+$item_columns,
                        'columns_info' => $columns_info,
                        'columns_info_citydiv' => $columns_info_citydiv,
                        'columns_info_exists' => $columns_info_exists,
                        'alias_comments' => $alias_comments,
                    ));
                }
                break;
            case 6:
                $report_id = Common::getStringParam('report_id');
                $sql = '';
                $reports = array();

                $configuration = ReportConfiguration::getReportConfByID($report_id);
                //var_dump($configuration);exit;
                if ($configuration) {
                    $charts = $configuration->charts;
                    if($charts)
                    {
                        $chart_arr_all = unserialize($charts);
                        //var_dump($chart_arr);
                        $chart_arr = $chart_arr_all;
                    }
                    else
                    {
                        $chart_arr = array(1=>array(
                            'title'=>'',
                            'chart'=>'',
                            'config'=>array(
                                "xAxis"=>"",
                                "yAxis"=>array(),
                                "zAxis"=>"",
                                "yAxisGroup"=>""
                            )));
                    }


                    $chart_types = Common::getChartTypeList();
                    $chart_type = '';


                    $query_sql = unserialize($configuration->query_sql);
                    $select = !empty($query_sql)&&array_key_exists('select', $query_sql) ? $query_sql['select'] : array();
                    $columns = !empty($query_sql)&&array_key_exists('columns', $query_sql) ? $query_sql['columns'] : array();
                    $distinct = !empty($query_sql)&&array_key_exists('distinct', $query_sql) ? $query_sql['distinct'] : 0;
                    $from = !empty($query_sql)&&array_key_exists('from', $query_sql) ? $query_sql['from'] : '';
                    $where = !empty($query_sql)&&array_key_exists('where', $query_sql)&&!empty($query_sql['where']) ? $query_sql['where'] : '1=1';
                    $group = !empty($query_sql)&&array_key_exists('group', $query_sql) ? $query_sql['group'] : array();
                    $order = !empty($query_sql)&&array_key_exists('order', $query_sql) ? $query_sql['order'] : array();

                    //结果表头
                    $columns_info = ColumnDefine::getColumnsInfoByReport($report_id,$columns,false);

                    $city_div_cols = array();
                    foreach ($columns as $column) {
                        if (array_key_exists($column, $columns_info)) {
                            if ($columns_info[$column]['function'] == ColumnDefine::FUNCTION_CITY_DIVISION_COLUMN) {
                                $function_detail = !empty($columns_info[$column]['function_detail']) ? unserialize($columns_info[$column]['function_detail']) : array();
                                if (in_array(ColumnDefine::FUNCTION_DETAIL_CITY_DIV_AREA_REGION, $function_detail)) {
                                    $column_area = $column."_area_".$report_id;
                                    $column_region = $column."_region_".$report_id;
                                    $city_div_cols[$column]['area'] = $column_area;
                                    $city_div_cols[$column]['region'] = $column_region;
                                }
                                if (in_array(ColumnDefine::FUNCTION_DETAIL_CITY_DIV_WARZONE, $function_detail)) {
                                    $column_warzone = $column."_warzone_".$report_id;
                                    $city_div_cols[$column]['warzone'] = $column_warzone;
                                }
                            }
                        }
                    }

                    //sql预览
                    $query = 'SELECT ';
                    if ($distinct == 1) {
                        $query .= 'distinct ';
                    }
                    $query .= implode(',', $select).' FROM '.$from.' WHERE '.$where;
                    $sql .= $query.ReportConfiguration::assembleConditionEG($configuration);

                    $privilege_columns = ColumnForPrivilege::getPrivilegeColumnsByReport($report_id,false);
                    if (!empty($privilege_columns)) {
                        foreach ($privilege_columns as $col => $pri_type) {
                            $sql .= ' and '.$col.' in ({'.$pri_type.'})';
                        }
                    }

                    if (!empty($group)) {
                        $query .= ' group by '.implode(',', $group);
                        $sql .= ' group by '.implode(',', $group);
                    }

                    if (!empty($order)) {
                        $query .= ' order by ';
                        $sql .= ' order by ';
                        foreach ($order as $col => $or) {
                            $query .= $col.' '.$or;
                            $sql .= $col.' '.$or;
                        }
                    }

                    //结果预览，只查询固定条件
                    list($db,$model) = Common::getDBConnection($configuration->data_source);
                    if ($db && $model) {
                        $reports = $db->createCommand($query.' limit 10000')->queryAll();
                        $db->active = false;
                    } else {
                        Yii::app()->user->setFlash('error', '无法连接数据源，请与管理员联系。');
                    }
                } else {
                    Yii::app()->user->setFlash('error', '查询配置配置失败！由于Step1设置有误，未生成名称与路径，已返回。请重新配置。');
                    Yii::app()->request->redirect("/configure/create?step=1");
                }

                //取城市与归属分区的对应关系
                $city_div_relations = Common::getCityDivisions();
                $loop = 0;
                $output_data = array();
                foreach ($reports as $data) {
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

                $dataProvider = new CArrayDataProvider($output_data,array(
                    'keyField' => false,    //字段的名称。默认值为‘id’。如果设置为false， $rawData数组中的值将被使用。
                    'id' => 'create_report_preview',
                    'pagination'=>array(
                        'pageSize'=>15,
                    ),
                ));

                $columns_info_citydiv = ColumnDefine::getCityDivColumnsInfoByReport($report_id);
                $next = false;
                if (!empty($_POST)) {
                    $success = true;
                    $chartArr = array();
                    $li_cnt = Yii::app()->request->getParam("li_cnt");
                    $j = 1;
                    for($i=1;$i<=$li_cnt;$i++)
                    {
                        $title_text = Yii::app()->request->getParam("chart_title_".$i);
                        if($title_text)
                        {
                            $title_text_sub = Yii::app()->request->getParam("chart_title_sub_".$i);
                            $chart_type = Yii::app()->request->getParam("chart_type_".$i);
                            $report_id = Yii::app()->request->getParam("report_id");
                            $xAxis = Yii::app()->request->getParam("xAxis_".$i);
                            $yAxis = Yii::app()->request->getParam("yAxis_".$i);
                            $zAxis = Yii::app()->request->getParam("zAxis_".$i);
                            $yAxisGroup = Yii::app()->request->getParam("yAxisGroup_".$i);


                            $chartArr[$j]['chart'] = $chart_type;
                            $chartArr[$j]['title'] = $title_text;
                            //$chartArr['title_sub'] = $title_text_sub;
                            $chartArr[$j]['config']['xAxis'] = $xAxis;
                            $chartArr[$j]['config']['yAxis'] = $yAxis;
                            $chartArr[$j]['config']['zAxis'] = $zAxis;
                            $chartArr[$j]['config']['yAxisGroup'] = $yAxisGroup;
                            $j++;
                        }

                    }



                    //$chartArrAll = array($chartArr);
                    //var_dump($chartArr);exit;
                    $charts = serialize($chartArr);

                    $attributes = array(
                        'charts' => $charts,
                        //'update_time' => time(),    //ReportConfiguration::beforeAction()
                    );
                    $configuration->charts = $charts;

                    $succ = $configuration->update();
                    $success = $success && $succ;


                    $columns_info = ColumnDefine::getColumnsInfoByReport($report_id,$columns,false);    //该方法已重置缓存
                    $columns_info_citydiv = ColumnDefine::getCityDivColumnsInfoByReport($report_id,false);
                    if ($success) {
                        Yii::app()->user->setFlash('success', 'Step6.图表配置成功！');
                        $next = true;
                    } else {
                        Yii::app()->user->setFlash('error', '展示内容配置失败！由于数据库未成功写入，请重新配置。');
                    }
                }
                if ($next) {
                    Yii::app()->request->redirect("/configure/create?step=7&report_id=".$report_id);
                } else {
                    $this->render('configure', array(
                        'visiable' => 6,
                        'step' => 6,
                        'report_id' => $report_id,
                        'sql' => $sql,
                        'reports' => !empty($output_data) ? $output_data : array(),
                        'dataProvider' => $dataProvider,
                        'columns_info' => !empty($columns_info) ? $columns_info : array(),
                        'columns_info_citydiv' => !empty($columns_info_citydiv) ? $columns_info_citydiv : array(),
                        'chart_types' => $chart_types,
                        'chart_arr' => $chart_arr
                    ));
                }

                break;
            case 7:
                $report_id = Common::getStringParam('report_id');
                $sql = '';
                $reports = array();

                $configuration = ReportConfiguration::getReportConfByID($report_id);
                if ($configuration) {
                    $menu = Menu::getMenuByReportID($report_id);
                    $attributes_menu = array(
                        'status' => 1,
                    );
                    $menu->attributes = $attributes_menu;
                    $menu->update();
                    $show_parts = unserialize($configuration->show_parts);
                    $show_parts = !empty($show_parts) ? $show_parts : ReportConfiguration::$show_parts_default;

                    $query_sql = unserialize($configuration->query_sql);
                    $select = !empty($query_sql)&&array_key_exists('select', $query_sql) ? $query_sql['select'] : array();
                    $columns = !empty($query_sql)&&array_key_exists('columns', $query_sql) ? $query_sql['columns'] : array();
                    $distinct = !empty($query_sql)&&array_key_exists('distinct', $query_sql) ? $query_sql['distinct'] : 0;
                    $from = !empty($query_sql)&&array_key_exists('from', $query_sql) ? $query_sql['from'] : '';
                    $where = !empty($query_sql)&&array_key_exists('where', $query_sql)&&!empty($query_sql['where']) ? $query_sql['where'] : '1=1';
                    $group = !empty($query_sql)&&array_key_exists('group', $query_sql) ? $query_sql['group'] : array();
                    $order = !empty($query_sql)&&array_key_exists('order', $query_sql) ? $query_sql['order'] : array();

                    //结果表头
                    $columns_info = ColumnDefine::getColumnsInfoByReport($report_id,$columns,false);

                    $city_div_cols = array();
                    foreach ($columns as $column) {
                        if (array_key_exists($column, $columns_info)) {
                            if ($columns_info[$column]['function'] == ColumnDefine::FUNCTION_CITY_DIVISION_COLUMN) {
                                $function_detail = !empty($columns_info[$column]['function_detail']) ? unserialize($columns_info[$column]['function_detail']) : array();
                                if (in_array(ColumnDefine::FUNCTION_DETAIL_CITY_DIV_AREA_REGION, $function_detail)) {
                                    $column_area = $column."_area_".$report_id;
                                    $column_region = $column."_region_".$report_id;
                                    $city_div_cols[$column]['area'] = $column_area;
                                    $city_div_cols[$column]['region'] = $column_region;
                                }
                                if (in_array(ColumnDefine::FUNCTION_DETAIL_CITY_DIV_WARZONE, $function_detail)) {
                                    $column_warzone = $column."_warzone_".$report_id;
                                    $city_div_cols[$column]['warzone'] = $column_warzone;
                                }
                            }
                        }
                    }

                    //sql预览
                    $query = 'SELECT ';
                    if ($distinct == 1) {
                        $query .= 'distinct ';
                    }
                    $query .= implode(',', $select).' FROM '.$from.' WHERE '.$where;
                    $sql .= $query.ReportConfiguration::assembleConditionEG($configuration);

                    $privilege_columns = ColumnForPrivilege::getPrivilegeColumnsByReport($report_id,false);
                    if (!empty($privilege_columns)) {
                        foreach ($privilege_columns as $col => $pri_type) {
                            $sql .= ' and '.$col.' in ({'.$pri_type.'})';
                        }
                    }

                    if (!empty($group)) {
                        $query .= ' group by '.implode(',', $group);
                        $sql .= ' group by '.implode(',', $group);
                    }

                    if (!empty($order)) {
                        $query .= ' order by ';
                        $sql .= ' order by ';
                        foreach ($order as $col => $or) {
                            $query .= $col.' '.$or;
                            $sql .= $col.' '.$or;
                        }
                    }

                    //结果预览，只查询固定条件
                    list($db,$model) = Common::getDBConnection($configuration->data_source);
                    if ($db && $model) {
                        $reports = $db->createCommand($query.' limit 10000')->queryAll();
                        $db->active = false;
                    } else {
                        Yii::app()->user->setFlash('error', '无法连接数据源，请与管理员联系。');
                    }
                } else {
                    Yii::app()->user->setFlash('error', '查询配置配置失败！由于Step1设置有误，未生成名称与路径，已返回。请重新配置。');
                    Yii::app()->request->redirect("/configure/create?step=1");
                }

                //取城市与归属分区的对应关系
                $city_div_relations = Common::getCityDivisions();
                $loop = 0;
                $output_data = array();
                foreach ($reports as $data) {
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

                $dataProvider = new CArrayDataProvider($output_data,array(
                    'keyField' => false,    //字段的名称。默认值为‘id’。如果设置为false， $rawData数组中的值将被使用。
                    'id' => 'create_report_preview',
                    'pagination'=>array(
                        'pageSize'=>15,
                    ),
                ));

                $columns_info_citydiv = ColumnDefine::getCityDivColumnsInfoByReport($report_id);
                $this->render('configure', array(
                    'visiable' => 7,
                    'step' => 7,
                    'report_id' => $report_id,
                    'sql' => $sql,
                    'reports' => !empty($output_data) ? $output_data : array(),
                    'conditions' => '',
                    'dataProvider' => $dataProvider,
                    'columns_info' => !empty($columns_info) ? $columns_info : array(),
                    'columns_info_citydiv' => !empty($columns_info_citydiv) ? $columns_info_citydiv : array(),
                    'show_parts' => $show_parts,
                ));

                break;
        }
    }

    /**
     * 编辑报表
     */
    public function actionUpdate()
    {
        $step = Common::getNumParam('step', 1);
        $report_id = Common::getStringParam('report_id');
        $configuration = ReportConfiguration::getReportConfByID($report_id);
        if(empty($configuration)) {
            Yii::app()->user->setFlash('error', '未找到指定报表');
            $this->redirect(array('configure/index'));
        }

        switch ($step) {
            case 1:
                $config = ReportConfiguration::getReportRelationByID($report_id);
                $report_name = isset($config) ? $config->report_name : '';
//                $platform = isset($config)&&isset($config->menu) ? $config->menu->platform : '';
                $platform = 9;
                $parent_id = isset($config)&&isset($config->menu) ? $config->menu->parent_id : 0;
                $tab_state = isset($config)&&isset($config->menu) ? $config->menu->tab_state : 0;
                $report_res = Menu::getMenuByReportID($report_id);
                if (!empty($_POST)) {
                    $report_name = Common::getStringParam('report_name');
//                    $platform = Common::getNumParam('platform');
                    $platform = 9;
                    $first_grade = Common::getNumParam('first_grade');
                    $second_grade = Common::getNumParam('second_grade');
                    $third_grade = Common::getNumParam('third_grade');

                    $tab_state = Common::getNumParam('tab_state');

                    if (!empty($third_grade)) {
                        $menu_grade = 4;
                        $tab_state = 2;
                        $parent_id = $third_grade;
                    } elseif (!empty($second_grade)) {
                        $menu_grade = 3;
                        $parent_id = $second_grade;
                    } elseif (!empty($first_grade)) {
                        $menu_grade = 2;
                        $parent_id = $first_grade;
                    } else {
                        $menu_grade = 0;
                        $tab_state = 0;
                        $parent_id = 0;
                    }

                    $attributes = array(
                        'report_name' => $report_name,
                        //'update_time' => time(),    //ReportConfiguration::beforeAction()
                    );
                    $configuration->attributes = $attributes;

                    if($configuration->save()) {
                        $menu = Menu::getMenuByReportID($report_id);
                        $attributes_menu = array(
                            'platform' => $platform,
                            'menu_grade' => $menu_grade,
                            'parent_id' => $parent_id,
                            'tab_state' => $tab_state,
                            'menu_name' => $report_name,
                        );
                        $menu->attributes = $attributes_menu;
                        if($menu->save()) {
                            Common::dealParentTabState($platform, $parent_id);
                            Yii::app()->user->setFlash('success', 'Step1.名称与路径修改成功！');
                        } else {
                            Yii::app()->user->setFlash('error', '报表菜单修改失败，请重试！');
                        }
                    } elseif ($configuration->hasErrors('report_name')) {
                        Yii::app()->user->setFlash('error', $configuration->getError('report_name'));
                    } else {
                        Yii::app()->user->setFlash('error', '名称与路径修改失败！');
                    }
                }

                list($first_grade,$second_grade,$third_grade, $first_menus, $second_menus, $third_menus) = Common::getParentsByPlatAndID($platform, $parent_id);

                Yii::app()->redis->getClient()->delete(Common::REDIS_COMMEN_TAB_REPORTS);    //删除标签页报表公用缓存
                Yii::app()->redis->getClient()->delete(Common::REDIS_COMMEN_DCPLAT_SELFREPORT_WITH_PARENT);    //删除数据平台中自助报表父子结构缓存

                $this->render('update', array(
                    'step' => 1,
                    'report_name' => $report_name,
                    'report_id' => $report_id,
                    'platform' => $platform,
                    'tab_state' => $tab_state,
                    'first_grade' => $first_grade,
                    'second_grade' => $second_grade,
                    'third_grade' => $third_grade,
                    'first_menus' => $first_menus,
                    'second_menus' => $second_menus,
                    'third_menus' => $third_menus,
                    'res' => $report_res,
                ));

                break;
            case 2:
                if (empty($_POST)) {
                    Common::clearCacheForConfig();    //清除缓存
                }

                $data_sources = DataSource::getSelectList();
                $data_source = $configuration->data_source;
                $query_sql = unserialize($configuration->query_sql);
                $columns = !empty($query_sql)&&array_key_exists('columns', $query_sql) ? $query_sql['columns'] : array();
                $columns_choosed = !empty($query_sql)&&array_key_exists('columns_choosed', $query_sql) ? $query_sql['columns_choosed'] : array();
                $distinct = !empty($query_sql)&&array_key_exists('distinct', $query_sql) ? $query_sql['distinct'] : 0;
                $tables_choosed = !empty($query_sql)&&array_key_exists('tables', $query_sql) ? $query_sql['tables'] : array();
                $calculation = !empty($query_sql)&&array_key_exists('calculation', $query_sql) ? $query_sql['calculation'] : array();
                $table_used_times = !empty($query_sql)&&array_key_exists('table_used_times', $query_sql) ? $query_sql['table_used_times'] : array();
                $column_used_times = !empty($query_sql)&&array_key_exists('column_used_times', $query_sql) ? $query_sql['column_used_times'] : array();
                $condition_json = !empty($query_sql)&&array_key_exists('condition_json', $query_sql) ? $query_sql['condition_json'] : '';
                $group = !empty($query_sql)&&array_key_exists('group', $query_sql) ? $query_sql['group'] : array();
                $order = !empty($query_sql)&&array_key_exists('order', $query_sql) ? $query_sql['order'] : array();

                $config = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG);
                if (!empty($table_used_times) && !Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_TABLES)) {
                    $config[Common::REDIS_REPORT_CONFIG_TABLES] = serialize($table_used_times);
                }
                if (!empty($column_used_times) && !Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_COLUMNS)) {
                    $config[Common::REDIS_REPORT_CONFIG_COLUMNS] = serialize($column_used_times);
                }
                if (!empty($columns) && !Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_ALIAS_COLUMN)) {
                    $alias_columns = array_combine($columns, $columns);
                    $config[Common::REDIS_REPORT_CONFIG_ALIAS_COLUMN] = serialize($alias_columns);
                }
                Yii::app()->redis->getClient()->setTimeout(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, 12*Common::REDIS_DURATION);

                list($tables, $filters_json) = $this->getDataSourceDetails($data_source, $tables_choosed);

                if (!empty($_POST)) {
                    $data_source = Common::getNumParam('data_source');
                    //$table = Common::getStringParam('table');
                    //$select = Common::getArrayParam('select');
                    $condition = $_POST['condition'];
                    $condition_json = $_POST['condition_json'];
                    $param_distinct = Common::getNumParam('is_distinct');
                    $param_tables = Common::getJsonParam('tables');
                    $param_columns = Common::getJsonParam('columns');
                    $param_columns_choosed = Common::getJsonParam('columns_choosed');
                    $param_columns_calculation = Common::getJsonParam('columns_calculation');
                    $param_group_columns = Common::getJsonParam('group_columns');
                    $param_order_columns = Common::getJsonParam('order_columns');

                    if (!empty($data_source) && !empty($param_tables) && !empty($param_columns)) {
                        $has_error = false;

                        $choosed_columns = array();
                        foreach ($param_columns_choosed as $choosed_col) {
                            $choosed_columns[$choosed_col['col_alias']] = array(
                                'expression' => $choosed_col['expression'],
                                'table_name' => $choosed_col['table_name'],
                                'table_alias' => $choosed_col['table_alias'],
                            );
                        }

                        $from = '';
                        $idx = 0;
                        foreach ($param_tables as $table) {
                            ++$idx;
                            if ($idx == 1) {
                                $from = $table['table_name'].' '.$table['table_alias'];
                            } else {
                                if (!isset($table['join_type'])) {
                                    $from = '';
                                } else {
                                    $from .= ' '.$table['join_type'].' join '.$table['table_name'].' '.$table['table_alias'].' on ';
                                    $on = '';
                                    if (!empty($table['related_condition'])) {
                                        $loop = 0;
                                        foreach ($table['related_condition'] as $related_condition) {
                                            ++$loop;
                                            if ($loop > 1) {
                                                $on .= ' and ';
                                            }
                                            $on .= $related_condition['self_column'].$related_condition['operator'].$related_condition['related_column'];
                                        }
                                    }
                                    if (empty($on)) {
                                        $from = '';
                                    } else {
                                        $from .= $on;
                                    }
                                }
                            }
                        }

                        $calculation = array();
                        foreach ($param_columns_calculation as $cal_col) {
                            if (strpos($cal_col['expression'], ';') === false && strpos($cal_col['expression'], '#') === false &&
                                strpos($cal_col['expression'], '-- ') === false && strpos($cal_col['expression'], '/*') === false) {
                                $calculation[$cal_col['col_alias']] = array(
                                    'function' => $cal_col['function'],
                                    'expression' => $cal_col['expression'],
                                );
                            } else {
                                Yii::app()->user->setFlash('error', '查询sql的语句有误，包含结束或注释字符。');
                                $has_error = true;
                                break;
                            }
                        }

                        $order = array();
                        foreach ($param_order_columns as $order_column) {
                            $order[$order_column['column']] = $order_column['order'];
                        }

                        $config = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG);

                        $tables_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_TABLES) ? $config[Common::REDIS_REPORT_CONFIG_TABLES] : '';
                        $tables_array= unserialize($tables_cache);
                        $table_used_times = $tables_array !== false && !empty($tables_array) ? $tables_array : array();

                        $columns_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_COLUMNS) ? $config[Common::REDIS_REPORT_CONFIG_COLUMNS] : '';
                        $columns_array= unserialize($columns_cache);
                        $column_used_times = $columns_array !== false && !empty($columns_array) ? $columns_array : array();

                        $select = array();
                        $ret_cols = array_merge($choosed_columns, $calculation);
                        foreach ($param_columns as $col) {
                            if (array_key_exists($col, $ret_cols)) {
                                $select[$col] = $ret_cols[$col]['expression'];
                            }
                        }

                        if (empty($select)) {
                            Yii::app()->user->setFlash('error', '查询配置配置失败！结果字段配置有误，请重新配置。');
                        } elseif (empty($from)) {
                            Yii::app()->user->setFlash('error', '查询配置配置失败！数据表关联配置有误，请重新配置。');
                        } else {
                            $query = array(
                                'select' => $select,
                                'columns' => $param_columns,
                                'columns_choosed' => $choosed_columns,
                                'distinct' => $param_distinct,
                                'from' => $from,
                                'tables' => $param_tables,
                                'calculation' => $calculation,
                                'table_used_times' => $table_used_times,
                                'column_used_times' => $column_used_times,
                                'where' => $condition,
                                'condition_json' => $condition_json,
                                'group' => $param_group_columns,
                                'order' => $order,
                            );

                            $configuration->data_source = $data_source;
                            $configuration->query_sql = serialize($query);
                            if (!$has_error) {
                                if ($configuration->update()) {
                                    Yii::app()->user->setFlash('success', 'Step2.查询配置配置成功！');
                                    list($tables, $filters_json) = $this->getDataSourceDetails($data_source, $tables_choosed);
                                    if (Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_JOIN_MODAL_ALIAS)) {
                                        unset($config[Common::REDIS_REPORT_CONFIG_JOIN_MODAL_ALIAS]);
                                    }
                                    $tables_choosed = $param_tables;
                                    $columns = $param_columns;
                                    $columns_choosed = $choosed_columns;
                                    $distinct = $param_distinct;
                                    $group = $param_group_columns;
                                } else {
                                    Yii::app()->user->setFlash('error', '查询配置配置失败！由于数据库未成功写入，请重新配置。');
                                }
                            }
                        }
                    } else {
                        if (empty($data_source)) {
                            Yii::app()->user->setFlash('error', '查询配置配置失败！没有配置数据源，请重新配置。');
                        } elseif (empty($param_tables)) {
                            Yii::app()->user->setFlash('error', '查询配置配置失败！没有配置数据表，请重新配置。');
                        } elseif (empty($param_columns)){
                            Yii::app()->user->setFlash('error', '查询配置配置失败！没有配置结果字段，请重新配置。');
                        }
                    }
                }

                ColumnDefine::getColumnsInfoByReport($report_id,$columns,false);    //更新数据项定义缓存
                $this->render('configure', array(
                    'step' => 2,
                    'report_id' => $report_id,
                    'data_source' => !empty($data_source) ? $data_source : '',
                    'data_sources' => $data_sources,
                    'tables' => $tables,
                    'tables_choosed' => $tables_choosed,
                    'columns' => $columns,
                    'columns_choosed' => $columns_choosed,
                    'distinct' => $distinct,
                    'calculation' => $calculation,
                    'condition_json' => $condition_json,
                    'filters_json' => $filters_json, //isset($filters_json) ? $filters_json : '',
                    'group' => $group,
                    'order' => $order,
                ));
                break;
            case 3:
                $show_parts = unserialize($configuration->show_parts);
                $show_parts = !empty($show_parts) ? $show_parts : ReportConfiguration::$show_parts_default;
                $conditions = unserialize($configuration->conditions);
                $query_sql = unserialize($configuration->query_sql);
                $columns = !empty($query_sql)&&array_key_exists('columns', $query_sql) ? $query_sql['columns'] : array();
                $columns_choosed = !empty($query_sql)&&array_key_exists('columns_choosed', $query_sql) ? $query_sql['columns_choosed'] : array();
                $calculation = !empty($query_sql)&&array_key_exists('calculation', $query_sql) ? $query_sql['calculation'] : array();

                $alias_columns = array_merge($columns_choosed, $calculation);
                $item_columns = array();
                $alias_expression = array();
                foreach ($alias_columns as $alias => $col) {
                    if (!isset($col['function']) || empty($col['function'])) {  //目前没有having，所以筛选项字段不可以是汇总函数计算字段，但此处没有检查case when等情况
                        $item_columns[$alias] = $alias;
                        $expression = explode(' as ', $col['expression']);
                        $alias_expression[$alias] = $expression[0];
                    }
                }

                $dwysum_columns = array();
                foreach ($columns_choosed as $alias => $col) {
                    $expression = explode(' as ', $col['expression']);
                    $dwysum_columns[$expression[0]] = $expression[0];
                }

                $dwysum_calculation = array();
                if (!empty($conditions) && array_key_exists('date', $conditions)) {
                    foreach ($conditions['date'] as $col) {
                        if (array_key_exists('sumcols', $col) && !empty($col['sumcols']) &&
                            array_key_exists('calculation', $col['sumcols']) && !empty($col['sumcols']['calculation'])) {
                            $dwysum_calculation = $col['sumcols']['calculation'];
                        }
                    }
                }

                $columns_info = ColumnDefine::getColumnsInfoByReport($report_id,$columns,false);

                if (!empty($_POST)) {
                    $success = true;
                    $item_count = Common::getNumParam('item_count');
                    $show_parts = Common::getArrayParam('show_parts', ReportConfiguration::$show_parts_default);
                    list($select_items_conf, $has_error, $dwysum_calculation) = $this->step3AssembleConditions($configuration, $item_count, $alias_expression, $columns_choosed, $columns_info);

                    $configuration->show_parts = serialize($show_parts);
                    $configuration->conditions = serialize($select_items_conf);
                    $conditions = $select_items_conf;
                    if (!$has_error) {
                        $succ = $configuration->update();
                        $success = $success && $succ;

                        //城市归属展示
                        $city_item_count = Common::getNumParam('city_item_count');
                        ColumnDefine::emptyCityDivisionColumns($report_id);
                        for ($i=0; $i<=$city_item_count; $i++) {
                            $city_column = array_key_exists("city_column_$i", $_POST) ? $_POST["city_column_$i"] : '';
                            $city_divisions = array_key_exists("city_divisions_$i", $_POST) ? $_POST["city_divisions_$i"] : '';

                            if (!empty($city_column)) {
                                $attributes = array(
                                    'report_id' => $report_id,
                                    'column_name' => $city_column,
                                    'expression' => array_key_exists($city_column, $alias_expression) ? $alias_expression[$city_column] : '',
                                    'function' => ColumnDefine::FUNCTION_CITY_DIVISION_COLUMN,
                                    'function_detail' => is_array($city_divisions) ? serialize($city_divisions) : '',
                                );

                                $pk = array('report_id'=>$report_id,'column_name'=>$city_column);
                                $coldef = ColumnDefine::model()->findByPk($pk);
                                if (!$coldef) {
                                    $coldef = new ColumnDefine();
                                }
                                $coldef->attributes = $attributes;
                                $succ = $coldef->save();
                                $success = $success && $succ;
                            }
                        }

                        if ($success) {
                            Yii::app()->user->setFlash('success', 'Step3.展示内容配置成功！');
                        } else {
                            Yii::app()->user->setFlash('error', '展示内容配置失败！由于数据库未成功写入，请重新配置。');
                        }
                    }
                }

                $city_div_columns = ColumnDefine::getCityDivisionColumnsByReport($report_id, false);
                $title = array(''=>'请选择');
                $this->render('configure', array(
                    'step' => 3,
                    'report_id' => $report_id,
                    'show_parts' => $show_parts,
                    'item_columns' => $title+$item_columns,
                    'dwysum_columns' => $title+$dwysum_columns,
                    'dwysum_calculation' => $dwysum_calculation,
                    'columns_info' => $columns_info,
                    'city_div_columns' => $city_div_columns,
                    'conditions' => $conditions,
                    'item_count' => isset($item_count) ? $item_count : 0,
                    'city_item_count' => isset($city_item_count) ? $city_item_count : 0,
                ));
                break;
            case 4:
                $item_columns = array();
                $user_groups = UserGroup::getAllGroups();
                $query_sql = unserialize($configuration->query_sql);
                $tables = !empty($query_sql)&&array_key_exists('tables', $query_sql) ? $query_sql['tables'] : '';

                //权限控制字段从所有表字段中取
                list($db,$model) = Common::getDBConnection($configuration->data_source);
                if ($db && $model) {
                    if (!empty($tables)) {
                        foreach ($tables as $table) {
                            $sql = "select column_name from information_schema.columns where TABLE_SCHEMA='".$model['database']."' AND TABLE_NAME='".$table['table_name']."'";
                            $results = $db->createCommand($sql)->queryAll();

                            if (!empty($results)) {
                                foreach ($results as $result)
                                {
                                    $column = $table['table_alias'].'.'.$result['column_name'];
                                    $item_columns[$column] = $column;
                                }
                            }
                        }
                    }
                    $db->active = false;

                } else {
                    Yii::app()->user->setFlash('error', '无法连接数据源，请与管理员联系。');
                }

                $user_group = ReportPrivileges::getUsergroupsByReport($report_id);
                $privilege_columns = ColumnForPrivilege::getPrivilegeColumnsByReport($report_id,false);

                if (!empty($_POST)) {
                    $success = true;
                    $item_count = Common::getNumParam('item_count');
                    $user_group = Common::getArrayParam('user_group');

                    if (!empty($user_group)) {
                        if (count($user_group) == 1 && empty($user_group[0])) {
                            ReportPrivileges::model()->deleteAll('report_id=:report_id',array(':report_id'=>$report_id));
                        } else {
                            foreach ($user_group as $user_group_id) {
                                $pk = array('report_id'=>$report_id,'user_group_id'=>$user_group_id);
                                $rp = ReportPrivileges::model()->findByPk($pk);
                                if (!$rp) {
                                    $rp = new ReportPrivileges();
                                    $rp->attributes = $pk;
                                    $succ = $rp->save();
                                    $success = $success && $succ;
                                }
                            }
                        }
                    } else {
                        ReportPrivileges::model()->deleteAll('report_id=:report_id',array(':report_id'=>$report_id));
                    }

                    ColumnForPrivilege::emptyReportPrivilegeColumns($report_id);
                    for ($i=0; $i<=$item_count; $i++) {
                        $column = array_key_exists("column_$i", $_POST) ? $_POST["column_$i"] : '';
                        $privilege_type = array_key_exists("privilege_type_$i", $_POST) ? $_POST["privilege_type_$i"] : '';

                        if (!empty($column) && !empty($privilege_type)) {
                            $attributes = array(
                                'report_id' => $report_id,
                                'expression' => $column,
                                'privilege_type' => $privilege_type,
                            );

                            $pk = array('report_id'=>$report_id,'expression'=>$column);
                            $coldef = ColumnForPrivilege::model()->findByPk($pk);
                            if (!$coldef) {
                                $coldef = new ColumnForPrivilege();
                            }
                            $coldef->attributes = $attributes;
                            $succ = $coldef->save();
                            $success = $success && $succ;
                        }
                    }
                    $privilege_columns = ColumnForPrivilege::getPrivilegeColumnsByReport($report_id,false);    //该方法已重置缓存

                    if ($success) {
                        $configuration->update();
                        Yii::app()->user->setFlash('success', 'Step4.权限分配成功！');
                    } else {
                        Yii::app()->user->setFlash('error', '权限分配失败！由于数据库未成功写入，请重新配置。');
                    }
                }

                $title = array(''=>'请选择');
                $this->render('configure', array(
                    'step' => 4,
                    'report_id' => $report_id,
                    'user_group' => $user_group,
                    'user_groups' => $user_groups,
                    'item_columns' => $title+$item_columns,
                    'privilege_columns' => $privilege_columns,
                ));
                break;
            case 5:
                $columns = array();
                $query_sql = unserialize($configuration->query_sql);
                $columns = !empty($query_sql)&&array_key_exists('columns', $query_sql) ? $query_sql['columns'] : array();
                $columns_info_exists = true;
                $columns_info = ColumnDefine::getColumnsInfoByReport($report_id,$columns,false,$columns_info_exists);

                $city_div_columns = array();
                $item_columns = array();
                if (!empty($columns)) {
                    foreach ($columns as $column) {
                        if (array_key_exists($column, $columns_info)) {
                            if ($columns_info[$column]['function'] == ColumnDefine::FUNCTION_CITY_DIVISION_COLUMN) {
                                $function_detail = !empty($columns_info[$column]['function_detail']) ? unserialize($columns_info[$column]['function_detail']) : array();
                                if (in_array(ColumnDefine::FUNCTION_DETAIL_CITY_DIV_AREA_REGION, $function_detail)) {
                                    $column_area = $column."_area_".$report_id;
                                    $column_region = $column."_region_".$report_id;
                                    $item_columns[$column_area] = $column_area;
                                    $item_columns[$column_region] = $column_region;
                                    $city_div_columns[] = $column_area;
                                    $city_div_columns[] = $column_region;
                                }
                                if (in_array(ColumnDefine::FUNCTION_DETAIL_CITY_DIV_WARZONE, $function_detail)) {
                                    $column_warzone = $column."_warzone_".$report_id;
                                    $item_columns[$column_warzone] = $column_warzone;
                                    $city_div_columns[] = $column_warzone;
                                }
                            }
                        }
                        $item_columns[$column] = $column;
                    }
                }
                $columns_info_citydiv = ColumnDefine::getCityDivColumnsInfoByReport($report_id,false);

                $alias_comments = self::getChoosedColumnsComments($configuration, $query_sql);
                if (!empty($_POST)) {
                    $success = true;

                    //数据项定义
                    if (!empty($columns)) {
                        foreach ($columns as $column) {
                            $pk = array('report_id'=>$report_id,'column_name'=>$column);
                            $coldef = ColumnDefine::model()->findByPk($pk);
                            if (!$coldef) {
                                $coldef = new ColumnDefine();
                            }
                            $attributes = array(
                                'report_id' => $report_id,
                                'column_name' => $column,
                                'show_name' => $_POST['col_'.$column.'_name'],
                                'define' => $_POST['col_'.$column.'_define'],
                            );
                            $coldef->attributes = $attributes;
                            $succ = $coldef->save();
                            $success = $success && $succ;
                        }
                    }

                    if (!empty($city_div_columns)) {
                        foreach ($city_div_columns as $column) {
                            $pk = array('report_id'=>$report_id,'column_name'=>$column);
                            $coldef = ColumnDefine::model()->findByPk($pk);
                            if (!$coldef) {
                                $coldef = new ColumnDefine();
                            }
                            $attributes = array(
                                'report_id' => $report_id,
                                'column_name' => $column,
                                'show_name' => $_POST['col_'.$column.'_name'],
                                'define' => $_POST['col_'.$column.'_define'],
                                'function' => 2,
                            );
                            $coldef->attributes = $attributes;
                            $succ = $coldef->save();
                            $success = $success && $succ;
                        }
                    }

                    $columns_info = ColumnDefine::getColumnsInfoByReport($report_id,$columns,false);    //该方法已重置缓存
                    $columns_info_citydiv = ColumnDefine::getCityDivColumnsInfoByReport($report_id,false);
                    if ($success) {
                        Yii::app()->user->setFlash('success', 'Step5.数据项定义配置成功！');
                    } else {
                        Yii::app()->user->setFlash('error', '展示内容配置失败！由于数据库未成功写入，请重新配置。');
                    }
                }

                $title = array(''=>'请选择');
                $this->render('configure', array(
                    'step' => 5,
                    'report_id' => $report_id,
                    'item_columns' => $title+$item_columns,
                    'columns_info' => $columns_info,
                    'columns_info_citydiv' => $columns_info_citydiv,
                    'columns_info_exists' => $columns_info_exists,
                    'alias_comments' => $alias_comments,
                ));
                break;
            case 6:
                $sql = '';
                $reports = array();
                $charts = $configuration->charts;
                if($charts)
                {
                    $chart_arr_all = unserialize($charts);
                    //var_dump($chart_arr);
                    $chart_arr = $chart_arr_all;
                }
                else
                {
                    $chart_arr = array(1=>array(
                        'title'=>'',
                        'chart'=>'',
                        'config'=>array(
                            "xAxis"=>"",
                            "yAxis"=>array(),
                            "zAxis"=>"",
                            "yAxisGroup"=>""
                        )));
                }

                $chart_types = Common::getChartTypeList();
                //$chart_type = $chart_arr['chart_type'];

                $query_sql = unserialize($configuration->query_sql);
                $select = !empty($query_sql)&&array_key_exists('select', $query_sql) ? $query_sql['select'] : array();
                $columns = !empty($query_sql)&&array_key_exists('columns', $query_sql) ? $query_sql['columns'] : array();
                $distinct = !empty($query_sql)&&array_key_exists('distinct', $query_sql) ? $query_sql['distinct'] : 0;
                $from = !empty($query_sql)&&array_key_exists('from', $query_sql) ? $query_sql['from'] : '';
                $where = !empty($query_sql)&&array_key_exists('where', $query_sql)&&!empty($query_sql['where']) ? $query_sql['where'] : '1=1';
                $group = !empty($query_sql)&&array_key_exists('group', $query_sql) ? $query_sql['group'] : array();
                $order = !empty($query_sql)&&array_key_exists('order', $query_sql) ? $query_sql['order'] : array();

                //结果表头
                $columns_info = ColumnDefine::getColumnsInfoByReport($report_id,$columns,false);

                $city_div_cols = array();
                foreach ($columns as $column) {
                    if (array_key_exists($column, $columns_info)) {
                        if ($columns_info[$column]['function'] == ColumnDefine::FUNCTION_CITY_DIVISION_COLUMN) {
                            $function_detail = !empty($columns_info[$column]['function_detail']) ? unserialize($columns_info[$column]['function_detail']) : array();
                            if (in_array(ColumnDefine::FUNCTION_DETAIL_CITY_DIV_AREA_REGION, $function_detail)) {
                                $column_area = $column."_area_".$report_id;
                                $column_region = $column."_region_".$report_id;
                                $city_div_cols[$column]['area'] = $column_area;
                                $city_div_cols[$column]['region'] = $column_region;
                            }
                            if (in_array(ColumnDefine::FUNCTION_DETAIL_CITY_DIV_WARZONE, $function_detail)) {
                                $column_warzone = $column."_warzone_".$report_id;
                                $city_div_cols[$column]['warzone'] = $column_warzone;
                            }
                        }
                    }
                }

                //sql预览
                $query = 'SELECT ';
                if ($distinct == 1) {
                    $query .= 'distinct ';
                }
                $query .= implode(',', $select).' FROM '.$from.' WHERE '.$where;
                $sql .= $query.ReportConfiguration::assembleConditionEG($configuration);

                $privilege_columns = ColumnForPrivilege::getPrivilegeColumnsByReport($report_id,false);
                if (!empty($privilege_columns)) {
                    foreach ($privilege_columns as $col => $pri_type) {
                        $sql .= ' and '.$col.' in ({'.$pri_type.'})';
                    }
                }

                if (!empty($group)) {
                    $query .= ' group by '.implode(',', $group);
                    $sql .= ' group by '.implode(',', $group);
                }

                if (!empty($order)) {
                    $query .= ' order by ';
                    $sql .= ' order by ';
                    $order_str = '';
                    foreach ($order as $col => $or) {
                        $order_str .= ', '.$col.' '.$or;
                    }
                    $order_str = substr($order_str, 1);
                    $query .= $order_str;
                    $sql .= $order_str;
                }

                //结果预览，只查询固定条件
                $reports = array();
                list($db,$model) = Common::getDBConnection($configuration->data_source);
                if ($db && $model) {
                    $reports = $db->createCommand($query.' limit 10000')->queryAll();
                    $db->active = false;
                } else {
                    Yii::app()->user->setFlash('error', '无法连接数据源，请与管理员联系。');
                }

                //取城市与归属分区的对应关系
                $city_div_relations = Common::getCityDivisions();
                $loop = 0;
                $output_data = array();
                foreach ($reports as $data) {
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

                $dataProvider = new CArrayDataProvider($output_data,array(
                    'keyField' => false,    //字段的名称。默认值为‘id’。如果设置为false， $rawData数组中的值将被使用。
                    'id' => 'update_report_preview',
                    'pagination'=>array(
                        'pageSize'=>15,
                    ),
                ));

                $columns_info_citydiv = ColumnDefine::getCityDivColumnsInfoByReport($report_id);

                if (!empty($_POST)) {
                    $success = true;
                    $chartArr = array();
                    $li_cnt = Yii::app()->request->getParam("li_cnt");
                    $j = 1;
                    for($i=1;$i<=$li_cnt;$i++)
                    {
                        $title_text = Yii::app()->request->getParam("chart_title_".$i);
                        if($title_text)
                        {
                            $title_text_sub = Yii::app()->request->getParam("chart_title_sub_".$i);
                            $chart_type = Yii::app()->request->getParam("chart_type_".$i);
                            $report_id = Yii::app()->request->getParam("report_id");
                            $xAxis = Yii::app()->request->getParam("xAxis_".$i);
                            $yAxis = Yii::app()->request->getParam("yAxis_".$i);
                            $zAxis = Yii::app()->request->getParam("zAxis_".$i);
                            $yAxisGroup = Yii::app()->request->getParam("yAxisGroup_".$i);


                            $chartArr[$j]['chart'] = $chart_type;
                            $chartArr[$j]['title'] = $title_text;
                            //$chartArr['title_sub'] = $title_text_sub;
                            $chartArr[$j]['config']['xAxis'] = $xAxis;
                            $chartArr[$j]['config']['yAxis'] = $yAxis;
                            $chartArr[$j]['config']['zAxis'] = $zAxis;
                            $chartArr[$j]['config']['yAxisGroup'] = $yAxisGroup;
                            $j++;
                        }

                    }



                    //$chartArrAll = array($chartArr);
                    //var_dump($chartArr);exit;
                    $charts = serialize($chartArr);

                    $attributes = array(
                        'charts' => $charts,
                        //'update_time' => time(),    //ReportConfiguration::beforeAction()
                    );
                    $configuration->charts = $charts;

                    $succ = $configuration->update();
                    $success = $success && $succ;


                    $columns_info = ColumnDefine::getColumnsInfoByReport($report_id,$columns,false);    //该方法已重置缓存
                    $columns_info_citydiv = ColumnDefine::getCityDivColumnsInfoByReport($report_id,false);
                    if ($success) {
                        Yii::app()->user->setFlash('success', 'Step6.图表配置成功！');
                        $configuration = ReportConfiguration::getReportConfByID($report_id);
                        $charts = $configuration->charts;
                        $chart_arr_all = unserialize($charts);
                        //var_dump($chart_arr);
                        $chart_arr = $chart_arr_all;

                    } else {
                        Yii::app()->user->setFlash('error', '展示内容配置失败！由于数据库未成功写入，请重新配置。');
                    }
                }
                //var_dump($chart_arr);exit;
                $this->render('configure', array(
                    'step' => 6,
                    'report_id' => $report_id,
                    'sql' => $sql,
                    'reports' => !empty($output_data) ? $output_data : array(),
                    'dataProvider' => $dataProvider,
                    'columns_info' => !empty($columns_info) ? $columns_info : array(),
                    'columns_info_citydiv' => !empty($columns_info_citydiv) ? $columns_info_citydiv : array(),
                    'chart_types' => $chart_types,
                    'chart_arr' => $chart_arr
                ));

                break;
            case 7:
                $report_id = Common::getStringParam('report_id');
                $sql = '';
                $reports = array();

                $query_sql = unserialize($configuration->query_sql);
                $select = !empty($query_sql)&&array_key_exists('select', $query_sql) ? $query_sql['select'] : array();
                $columns = !empty($query_sql)&&array_key_exists('columns', $query_sql) ? $query_sql['columns'] : array();
                $distinct = !empty($query_sql)&&array_key_exists('distinct', $query_sql) ? $query_sql['distinct'] : 0;
                $from = !empty($query_sql)&&array_key_exists('from', $query_sql) ? $query_sql['from'] : '';
                $where = !empty($query_sql)&&array_key_exists('where', $query_sql)&&!empty($query_sql['where']) ? $query_sql['where'] : '1=1';
                $group = !empty($query_sql)&&array_key_exists('group', $query_sql) ? $query_sql['group'] : array();
                $order = !empty($query_sql)&&array_key_exists('order', $query_sql) ? $query_sql['order'] : array();

                //是否展示图表
                $show_parts = unserialize($configuration->show_parts);
                $show_parts = !empty($show_parts) ? $show_parts : ReportConfiguration::$show_parts_default;

                //结果表头
                $columns_info = ColumnDefine::getColumnsInfoByReport($report_id,$columns,false);

                $city_div_cols = array();
                foreach ($columns as $column) {
                    if (array_key_exists($column, $columns_info)) {
                        if ($columns_info[$column]['function'] == ColumnDefine::FUNCTION_CITY_DIVISION_COLUMN) {
                            $function_detail = !empty($columns_info[$column]['function_detail']) ? unserialize($columns_info[$column]['function_detail']) : array();
                            if (in_array(ColumnDefine::FUNCTION_DETAIL_CITY_DIV_AREA_REGION, $function_detail)) {
                                $column_area = $column."_area_".$report_id;
                                $column_region = $column."_region_".$report_id;
                                $city_div_cols[$column]['area'] = $column_area;
                                $city_div_cols[$column]['region'] = $column_region;
                            }
                            if (in_array(ColumnDefine::FUNCTION_DETAIL_CITY_DIV_WARZONE, $function_detail)) {
                                $column_warzone = $column."_warzone_".$report_id;
                                $city_div_cols[$column]['warzone'] = $column_warzone;
                            }
                        }
                    }
                }

                //sql预览
                $query = 'SELECT ';
                if ($distinct == 1) {
                    $query .= 'distinct ';
                }
                $query .= implode(',', $select).' FROM '.$from.' WHERE '.$where;
                $sql .= $query.ReportConfiguration::assembleConditionEG($configuration);

                $privilege_columns = ColumnForPrivilege::getPrivilegeColumnsByReport($report_id,false);
                if (!empty($privilege_columns)) {
                    foreach ($privilege_columns as $col => $pri_type) {
                        $sql .= ' and '.$col.' in ({'.$pri_type.'})';
                    }
                }

                if (!empty($group)) {
                    $query .= ' group by '.implode(',', $group);
                    $sql .= ' group by '.implode(',', $group);
                }

                if (!empty($order)) {
                    $query .= ' order by ';
                    $sql .= ' order by ';
                    foreach ($order as $col => $or) {
                        $query .= $col.' '.$or;
                        $sql .= $col.' '.$or;
                    }
                }

                //结果预览，只查询固定条件
                $reports = array();
                list($db,$model) = Common::getDBConnection($configuration->data_source);
                if ($db && $model) {
                    $reports = $db->createCommand($query.' limit 10000')->queryAll();
                    $db->active = false;
                } else {
                    Yii::app()->user->setFlash('error', '无法连接数据源，请与管理员联系。');
                }

                //取城市与归属分区的对应关系
                $city_div_relations = Common::getCityDivisions();
                $loop = 0;
                $output_data = array();
                foreach ($reports as $data) {
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

                $dataProvider = new CArrayDataProvider($output_data,array(
                    'keyField' => false,    //字段的名称。默认值为‘id’。如果设置为false， $rawData数组中的值将被使用。
                    'id' => 'update_report_preview',
                    'pagination'=>array(
                        'pageSize'=>15,
                    ),
                ));

                $columns_info_citydiv = ColumnDefine::getCityDivColumnsInfoByReport($report_id);
                $this->render('configure', array(
                    'step' => 7,
                    'report_id' => $report_id,
                    'conditions' => '',
                    'sql' => $sql,
                    'reports' => !empty($output_data) ? $output_data : array(),
                    'dataProvider' => $dataProvider,
                    'columns_info' => !empty($columns_info) ? $columns_info : array(),
                    'columns_info_citydiv' => !empty($columns_info_citydiv) ? $columns_info_citydiv : array(),
                    'show_parts' => $show_parts,
                ));

                break;
        }
    }


    /**
     * 通过ajax加载数据源的数据表字段-图表用
     */
    public function actionAjaxcolumnschart()
    {
        $item_columns = array();
        $report_id = Common::getStringParam('report_id');
        $configuration = ReportConfiguration::getReportConfByID($report_id);
        //var_dump($configuration);exit;

        //数据项定义
        $query_sql = unserialize($configuration->query_sql);
        $columns = !empty($query_sql)&&array_key_exists('columns', $query_sql) ? $query_sql['columns'] : array();

        $columns_info = ColumnDefine::getColumnsInfoByReport($report_id,$columns);
        $columns_info_citydiv = ColumnDefine::getCityDivColumnsInfoByReport($report_id);

        //选择数据项
        $show_columns = Common::getArrayParam('show_columns');
        $title_columns = array();
        $city_divisions = array();
        $city_div_cols = array();
        foreach ($columns as $column) {
            //$title_columns[$column] = array_key_exists($column, $columns_info)&&!empty($columns_info[$column]['show_name']) ? $columns_info[$column]['show_name'] : $column;
            if (array_key_exists($column, $columns_info)) {
                if ($columns_info[$column]['function'] == ColumnDefine::FUNCTION_CITY_DIVISION_COLUMN) {
                    $function_detail = !empty($columns_info[$column]['function_detail']) ? unserialize($columns_info[$column]['function_detail']) : array();
                    if (in_array(ColumnDefine::FUNCTION_DETAIL_CITY_DIV_AREA_REGION, $function_detail)) {
                        $column_area = $column."_area_".$report_id;
                        $column_region = $column."_region_".$report_id;
                        $city_divisions[$column_area] = $column;
                        $city_divisions[$column_region] = $column;
                        $city_div_cols[$column]['area'] = $column_area;
                        $city_div_cols[$column]['region'] = $column_region;
                        $title_columns[$column_area] = !empty($columns_info_citydiv[$column_area]['show_name']) ? $columns_info_citydiv[$column_area]['show_name'] : $column_area;
                        $title_columns[$column_region] = !empty($columns_info_citydiv[$column_region]['show_name']) ? $columns_info_citydiv[$column_region]['show_name'] : $column_region;
                    }
                    if (in_array(ColumnDefine::FUNCTION_DETAIL_CITY_DIV_WARZONE, $function_detail)) {
                        $column_warzone = $column."_warzone_".$report_id;
                        $city_divisions[$column_warzone] = $column;
                        $city_div_cols[$column]['warzone'] = $column_warzone;
                        $title_columns[$column_warzone] = !empty($columns_info_citydiv[$column_warzone]['show_name']) ? $columns_info_citydiv[$column_warzone]['show_name'] : $column_warzone;
                    }
                }
                $title_columns[$column] = !empty($columns_info[$column]['show_name']) ? $columns_info[$column]['show_name'] : $column;
            } else {
                $title_columns[$column] = $column;
            }
        }
        //var_dump($title_columns);
        echo json_encode($title_columns);
    }


    /**
     * 通过ajax添加筛选项
     */
    public function actionAjaxaddtabitem()
    {
        $report_id = Common::getStringParam('report_id');
        $li_cnt = Common::getStringParam('li_cnt');
        $sql = '';
        $reports = array();

        $configuration = ReportConfiguration::getReportConfByID($report_id);
        //var_dump($configuration);exit;
        if ($configuration) {
            $charts = $configuration->charts;
            if($charts)
            {
                $chart_arr_all = unserialize($charts);
                //var_dump($chart_arr);
                $chart_arr = $chart_arr_all;
            }
            else
            {
                $chart_arr = array(array(
                    'title'=>'',
                    'chart'=>'',
                    'config'=>array(
                        "xAxis"=>"",
                        "yAxis"=>array(),
                        "zAxis"=>"",
                        "yAxisGroup"=>""
                    )));
            }


            $chart_types = Common::getChartTypeList();
            $chart_type = '';


            $query_sql = unserialize($configuration->query_sql);
            $select = !empty($query_sql)&&array_key_exists('select', $query_sql) ? $query_sql['select'] : array();
            $columns = !empty($query_sql)&&array_key_exists('columns', $query_sql) ? $query_sql['columns'] : array();
            $distinct = !empty($query_sql)&&array_key_exists('distinct', $query_sql) ? $query_sql['distinct'] : 0;
            $from = !empty($query_sql)&&array_key_exists('from', $query_sql) ? $query_sql['from'] : '';
            $where = !empty($query_sql)&&array_key_exists('where', $query_sql)&&!empty($query_sql['where']) ? $query_sql['where'] : '1=1';
            $group = !empty($query_sql)&&array_key_exists('group', $query_sql) ? $query_sql['group'] : array();
            $order = !empty($query_sql)&&array_key_exists('order', $query_sql) ? $query_sql['order'] : array();

            //结果表头
            $columns_info = ColumnDefine::getColumnsInfoByReport($report_id,$columns,false);

            $city_div_cols = array();
            foreach ($columns as $column) {
                if (array_key_exists($column, $columns_info)) {
                    if ($columns_info[$column]['function'] == ColumnDefine::FUNCTION_CITY_DIVISION_COLUMN) {
                        $function_detail = !empty($columns_info[$column]['function_detail']) ? unserialize($columns_info[$column]['function_detail']) : array();
                        if (in_array(ColumnDefine::FUNCTION_DETAIL_CITY_DIV_AREA_REGION, $function_detail)) {
                            $column_area = $column."_area_".$report_id;
                            $column_region = $column."_region_".$report_id;
                            $city_div_cols[$column]['area'] = $column_area;
                            $city_div_cols[$column]['region'] = $column_region;
                        }
                        if (in_array(ColumnDefine::FUNCTION_DETAIL_CITY_DIV_WARZONE, $function_detail)) {
                            $column_warzone = $column."_warzone_".$report_id;
                            $city_div_cols[$column]['warzone'] = $column_warzone;
                        }
                    }
                }
            }

            //sql预览
            $query = 'SELECT ';
            if ($distinct == 1) {
                $query .= 'distinct ';
            }
            $query .= implode(',', $select).' FROM '.$from.' WHERE '.$where;
            $sql .= $query.ReportConfiguration::assembleConditionEG($configuration);

            $privilege_columns = ColumnForPrivilege::getPrivilegeColumnsByReport($report_id,false);
            if (!empty($privilege_columns)) {
                foreach ($privilege_columns as $col => $pri_type) {
                    $sql .= ' and '.$col.' in ({'.$pri_type.'})';
                }
            }

            if (!empty($group)) {
                $query .= ' group by '.implode(',', $group);
                $sql .= ' group by '.implode(',', $group);
            }

            if (!empty($order)) {
                $query .= ' order by ';
                $sql .= ' order by ';
                foreach ($order as $col => $or) {
                    $query .= $col.' '.$or;
                    $sql .= $col.' '.$or;
                }
            }

            //结果预览，只查询固定条件
            list($db,$model) = Common::getDBConnection($configuration->data_source);
            if ($db && $model) {
                $reports = $db->createCommand($query.' limit 10000')->queryAll();
                $db->active = false;
            } else {
                Yii::app()->user->setFlash('error', '无法连接数据源，请与管理员联系。');
            }
        } else {
            Yii::app()->user->setFlash('error', '查询配置配置失败！由于Step1设置有误，未生成名称与路径，已返回。请重新配置。');
            Yii::app()->request->redirect("/configure/create?step=1");
        }

        //取城市与归属分区的对应关系
        $city_div_relations = Common::getCityDivisions();
        $loop = 0;
        $output_data = array();
        foreach ($reports as $data) {
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

        $dataProvider = new CArrayDataProvider($output_data,array(
            'keyField' => false,    //字段的名称。默认值为‘id’。如果设置为false， $rawData数组中的值将被使用。
            'id' => 'create_report_preview',
            'pagination'=>array(
                'pageSize'=>15,
            ),
        ));

        $columns_info_citydiv = ColumnDefine::getCityDivColumnsInfoByReport($report_id);
        $next = false;

        $response = '<li class="list-tab-item">
                        <div class="col-md-12">
                            <!-- <label>图表项名称及定义：</label> -->
                            <div class="col-md-12" style="margin-bottom: 10px">
                                <label>图表标题：</label>';
        $response .= CHtml::textField('chart_title_'.$li_cnt,array_key_exists('title', $chart_arr) ? $chart_arr['title'] :"" ,array('class'=>'form-control','style'=>'display:inline-block;width:150px','id'=>'chart_title_'.$li_cnt));
        $response .= '<span style="margin: 8px -23px 8px 10px" class="pull-right"><i style="color:red" class="glyphicon glyphicon-minus"></i>&nbsp;<a href="javascript:;" class="del-tab-item" data="'.$li_cnt.'">删除图表标签项</a></span>';

        $response .= '</div>
                            
                            <div class="col-md-12  class_chart_type" style="margin-bottom: 10px">
                                <label>图表类型：</label>';
        $response .= CHtml::dropDownList('chart_type_'.$li_cnt,array_key_exists('chart', $chart_arr) ? $chart_arr['chart'] :"",$chart_types,array('class'=>'form-control chart_type','style'=>'display:inline-block;width:150px','id'=>'chart_type_'.$li_cnt));
        $response .= '</div>
                            <div   class="step6-area" style="display:block">
                                
                            </div>
                        </div>
                        <div class="col-md-12" style="margin: 20px 0 20px 0">';

        if(!array_key_exists($li_cnt, $chart_arr))
        {
            $chart_arr[$li_cnt] = array(
                'title'=>'',
                'chart'=>'',
                'config'=>array(
                    "xAxis"=>"",
                    "yAxis"=>array(),
                    "zAxis"=>"",
                    "yAxisGroup"=>""
                ));
        }

        $response .= CHtml::hiddenField('xAxisHidden_'.$li_cnt,array_key_exists('xAxis', $chart_arr[$li_cnt]['config']) ? $chart_arr[$li_cnt]['config']['xAxis'] : '', array('id'=>'xAxisHidden_'.$li_cnt));
        $response .= CHtml::hiddenField('yAxisHidden_'.$li_cnt,array_key_exists('yAxis', $chart_arr[$li_cnt]['config']) ? (is_array($chart_arr[$li_cnt]['config']['yAxis']) ? implode(",",$chart_arr[$li_cnt]['config']['yAxis']):$chart_arr[$li_cnt]['config']['yAxis']) : '', array('id'=>'yAxisHidden_'.$li_cnt));
        $response .= CHtml::hiddenField('zAxisHidden_'.$li_cnt,array_key_exists('zAxis', $chart_arr[$li_cnt]['config']) ? $chart_arr[$li_cnt]['config']['zAxis'] : '', array('id'=>'zAxisHidden_'.$li_cnt));
        $response .= CHtml::hiddenField('yAxisGroupHidden_'.$li_cnt,array_key_exists('yAxisGroup', $chart_arr[$li_cnt]['config']) ? $chart_arr[$li_cnt]['config']['yAxisGroup'] : '', array('id'=>'yAxisGroupHidden_'.$li_cnt));

        $response .= '</div>
                    </li>';
        echo $response;
    }

}
