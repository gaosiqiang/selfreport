<?php

/**
 * This is the model class for table "report_configuration".
 *
 * The followings are the available columns in table 'report_configuration':
 * @property string $id
 * @property string $report_name
 * @property integer $create_time
 * @property integer $update_time
 * @property integer $data_source
 * @property string $query_sql
 * @property integer $is_timed
 * @property string $crontab
 * @property string $show_parts
 * @property string $conditions
 * @property string $charts
 */
class ReportConfiguration extends CActiveRecord
{
    public static $join_types = array('inner'=>'内连接','left'=>'左外连接','right'=>'右外连接');
    public static $join_operator = array('='=>'=','<>'=>'<>','>'=>'>','>='=>'>=','<'=>'<','<='=>'<=');
    public static $calculation_functions = array('sum'=>'总计','count'=>'数量','avg'=>'平均','max'=>'最大','min'=>'最小');
    public static $calculation_count = array('all'=>'总数','col'=>'字段');
    public static $order = array('asc'=>'升序','desc'=>'降序');
    public static $show_parts = array('1'=>'筛选区', '3'=>'图表区', '4'=>'详细数据区', '5'=>'数据项定义区');
    public static $show_parts_default = array('1', '4');
    public static $select_items = array(''=>'请选择', 'day'=>'日历', 'week'=>'周数', 'month'=>'月历', 'dwysum'=>'日周月累计', 'text'=>'文本框', 'city'=>'城市框', 'list'=>'列表框');
    public static $list_value_type = array('0'=>'ID','1'=>'名称');
    public static $city_divisions = array(
            ColumnDefine::FUNCTION_DETAIL_CITY_DIV_AREA_REGION=>'大区-区域', 
            ColumnDefine::FUNCTION_DETAIL_CITY_DIV_WARZONE=>'战区',
    );
    public static $privileges_types = array(
            ''=>'请选择',
            Common::PRIVILEGE_TYPE_AREA =>'大区',
            Common::PRIVILEGE_TYPE_REGION =>'区域',
            Common::PRIVILEGE_TYPE_CITY =>'城市',
            Common::PRIVILEGE_TYPE_CATEGORY =>'分类',
            Common::PRIVILEGE_TYPE_MEDIA =>'媒体',
            Common::PRIVILEGE_TYPE_BRANCH =>'分部',
    );
    public static $periods = array('day'=>'日', 'week'=>'周', 'month'=>'月', 'all'=>'总累计', 'specified_time'=>'指定时间累计');
    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'report_configuration';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('id, report_name', 'required'),
            array('report_name', 'unique', 'message'=>'{attribute} “{value}” 已存在'),
            array('create_time, update_time, data_source, is_timed', 'numerical', 'integerOnly'=>true),
            array('id', 'length', 'max'=>16),
            array('report_name, crontab', 'length', 'max'=>100),
            array('show_parts', 'length', 'max'=>100),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, report_name, create_time, update_time, data_source, query_sql, is_timed, crontab, show_parts, conditions, charts', 'safe', 'on'=>'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
                'datasource'=>array(self::BELONGS_TO, 'DataSource', '', 'on'=>'datasource.id = t.data_source'),
                'menu'=>array(self::HAS_ONE, 'Menu', 'report_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => '报表标识',
            'report_name' => '报表名称',
            'create_time' => '创建时间',
            'update_time' => '更新时间',
            'data_source' => '数据源ID',
            'query_sql' => 'SQL查询语句',
            'is_timed' => '是否定时任务，0否1是',
            'crontab' => '执行时间，参照crontab，分 时 日 月 周',
            'show_parts' => '展示区域，包括1筛选区、2主题数据区、3图表区、4详细数据区（附带导出）、5数据项定义区',
            'conditions' => '筛选项配置',
            'charts' => '图表配置',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('report_name',$this->report_name,true);
        $criteria->with = array('datasource', 'menu');

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    public function beforeSave()
    {
        if (parent::beforeSave())
        {
            if ($this->isNewRecord)
            {
                $this->create_time = time();
            }
            else
            {
                $this->update_time = time();
            }
            return true;
        }
        return false;
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ReportConfiguration the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    /**
     * @return string 随机生成16位16进制数用于报表标识
     */
    public function generateRandomKey()
    {
        return sprintf('%08x%08x',mt_rand(),mt_rand());
    }
    
    /**
     * 根据ID取配置项
     * @param string $report_id
     */
    public static function getReportConfByID($report_id)
    {
        return self::model()->findByPk($report_id);
    }
    
    /**
     * 根据ID取配置项及关联数据
     * @param string $report_id
     */
    public static function getReportRelationByID($report_id)
    {
        $criteria=new CDbCriteria;
        $criteria->compare('t.id',$report_id);
        $criteria->with = array('datasource', 'menu');
        return self::model()->find($criteria);
    }
    
    /**
     * 取筛选项控件配置项
     */
    public static function getSelectItems()
    {
        $select_items = array(
            'day' => array('range'=>'区间','alone'=>'单独'),
            'week' => array('range'=>'区间','alone'=>'单独'),
            'month' => array('range'=>'区间','alone'=>'单独'),
            'dwysum' => array('range'=>'区间','alone'=>'单独'),
            'text' => array('exact'=>'精确匹配','partial'=>'模糊匹配'),
            'city' => array('exact'=>'精确匹配'),
            'list'=>array(),
            'linked'=>array(),
        );
        
        $normal_lists = DictList::getListByType(DictList::TYPE_NORMAL);
        foreach ($normal_lists as $list) {
            if (array_key_exists($list->name, DictList::$preset_list_id)) {
                $select_items['list'][DictList::$preset_list_id[$list->name]] = $list->name;
            } else {
                $select_items['list'][$list->id] = $list->name;
            }
        }
        /* 从数据库取
        $linked_lists = DictList::getListByType(DictList::TYPE_LINKED);
        foreach ($linked_lists as $list) {
            if (array_key_exists($list->name, DictList::$preset_list_id)) {
                $select_items['linked'][DictList::$preset_list_id[$list->name]] = $list->name;
            } else {
                $select_items['linked'][$list->id] = $list->name;
            }
        }
         */
        //直接使用预置联动列表
        $preset_list = array_flip(DictList::$preset_list_id);
        foreach (DictList::$preset_linked_list_default as $id => $default) {
            if (array_key_exists($id, $preset_list)) {
                $select_items['linked'][$id] = $preset_list[$id];
            }
        }
        
        return $select_items;
    }
    
    /**
     * 根据筛选项配置装配条件sql, For example
     * @param ReportConfiguration $configuration
     * @return string
     */
    public static function assembleConditionEG($configuration)
    {
        $sql = '';
        //判断$configuration是否为ReportConfiguration类的对象，也可以用is_a($configuration, 'ReportConfiguration')
        if ($configuration instanceof ReportConfiguration) {
            $conditions = unserialize($configuration->conditions);
            if (!empty($conditions)) {
                foreach ($conditions as $key => $condition) {
                    switch ($key) {
                        case 'date':
                            foreach ($condition as $col) {
                                if ($col['attr'] == 'alone') {
                                    $sql .= ' and '.$col['column'].'=\'{date}\'';
                                } elseif ($col['attr'] == 'range') {
                                    $sql .= ' and '.$col['column'].' between \'{start_date}\' and \'{end_date}\'';
                                }
                            }
                            break;
                        case 'text':
                            foreach ($condition as $col) {
                                if ($col['attr'] == 'exact') {
                                    $sql .= ' and '.$col['column'].'=\'{text}\'';
                                } elseif ($col['attr'] == 'partial') {
                                    $sql .= ' and '.$col['column'].' like \'%{text}%\'';
                                }
                            }
                            break;
                        case 'list':
                            foreach ($condition as $col) {
                                $sql .= ' and '.$col['column'].'=\'{list_value}\'';
                            }
                            break;
                        case 'linked':
                            foreach ($condition as $col) {
                                $first = array_key_exists('first', $col) ? $col['first'] : array();
                                $second = array_key_exists('second', $col) ? $col['second'] : array();
                                $third = array_key_exists('third', $col) ? $col['third'] : array();
                                $fourth = array_key_exists('fourth', $col) ? $col['fourth'] : array();
                            
                                if (!empty($first) && $first['is_default']==0) {
                                    $sql .= ' and '.$first['column'].'=\'{list_value}\'';
                                }
                                if (!empty($second) && $second['is_default']==0) {
                                    $sql .= ' and '.$second['column'].'=\'{list_value}\'';
                                }
                                if (!empty($third) && $third['is_default']==0) {
                                    $sql .= ' and '.$third['column'].'=\'{list_value}\'';
                                }
                                if (!empty($fourth) && $fourth['is_default']==0) {
                                    $sql .= ' and '.$fourth['column'].'=\'{list_value}\'';
                                }
                            }
                            break;
                    }
                }
            }
        }
        return $sql;
    }

    /**
     * 通过ajax加载数据源的数据表字段-图表用
     */
    public static function getColumnsChart($report_id)
    {
        $item_columns = array();
        //$report_id = Common::getStringParam('report_id');
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
        return $title_columns;
    }
}
