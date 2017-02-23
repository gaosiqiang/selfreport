<?php

/**
 * This is the model class for table "column_define".
 *
 * The followings are the available columns in table 'column_define':
 * @property string $report_id
 * @property string $column_name
 * @property string $show_name
 * @property string $define
 * @property string $privilege_type
 * @property integer $function
 * @property string $function_detail
 * @property integer $status
 */
class ColumnDefine extends CActiveRecord
{
    const PRIVILEGE_TYPE_CITY = 'cities';
    const PRIVILEGE_TYPE_BRANCH = 'branches';
    const PRIVILEGE_TYPE_AREA = 'areas';
    const PRIVILEGE_TYPE_REGION = 'regions';
    const PRIVILEGE_TYPE_CATEGORY = 'categories';
    const PRIVILEGE_TYPE_MEDIA = 'medias';
    
    const FUNCTION_CITY_DIVISION_COLUMN = 1;            //关联城市归属的目标城市字段
    const FUNCTION_CITY_DIVISIONS = 2;                  //城市归属展示字段
    const FUNCTION_DETAIL_CITY_DIV_AREA_REGION = 'area-region';    //'area-region'=>'大区-区域'
    const FUNCTION_DETAIL_CITY_DIV_WARZONE = 'warzone';        //'warzone'=>'战区'
    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'column_define';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('report_id, column_name', 'required'),
            array('function, status', 'numerical', 'integerOnly'=>true),
            array('report_id', 'length', 'max'=>16),
            array('column_name', 'length', 'max'=>50),
            array('show_name', 'length', 'max'=>32),
            array('privilege_type', 'length', 'max'=>18),
            array('define, function_detail', 'safe'),    //设置属性不需要具体验证，否则无法保存
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('report_id, column_name, show_name, define, privilege_type, function, function_detail, status', 'safe', 'on'=>'search'),
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
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'report_id' => '自助报表ID',
            'column_name' => '数据项，即select数据库字段',
            'show_name' => '数据项名称',
            'define' => '数据项定义',
            'privilege_type' => '行级权限类型，如city,category,media等',
            'function' => '附加功能，0无，1关联城市归属，2城市归属',
            'status' => '状态，0无效，1有效',
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

        $criteria->compare('report_id',$this->report_id,true);
        $criteria->compare('column_name',$this->column_name,true);
        $criteria->compare('show_name',$this->show_name,true);
        $criteria->compare('define',$this->define,true);
        $criteria->compare('privilege_type',$this->privilege_type,true);
        $criteria->compare('status',$this->status);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ColumnDefine the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    /**
     * 获取城市归属展示目标字段
     * @param string $report_id
     * @param $cache    是否使用缓存，默认为使用
     * @return array
     */
    public static function getCityDivisionColumnsByReport($report_id,$cache=true)
    {
        $results = array();
    
        /* if ($cache) {
            $cache_priv_cols = new ARedisHash(Common::REDIS_COMMON_PRIVILEGE_COLUMNS);
            $results = Yii::app()->redis->getClient()->hExists(Common::REDIS_COMMON_PRIVILEGE_COLUMNS, $report_id) && !empty($cache_priv_cols[$report_id]) ? unserialize($cache_priv_cols[$report_id]) : array();
        } */
    
        if (empty($results)) {
            $models = self::model()->findAll('report_id=:report_id and status=1 and function='.self::FUNCTION_CITY_DIVISION_COLUMN, array(':report_id'=>$report_id));
            if ($models) {
                foreach ($models as $model) {
                    $results[$model->column_name] = !empty($model->function_detail) ? unserialize($model->function_detail) : array();
                }
            }
    
            /* $cache_priv_cols = new ARedisHash(Common::REDIS_COMMON_PRIVILEGE_COLUMNS);
            $cache_priv_cols[$report_id] = serialize($results);
            Yii::app()->redis->getClient()->setTimeout(Common::REDIS_COMMON_PRIVILEGE_COLUMNS, 12*Common::REDIS_DURATION); */
        }
    
        return $results;
    }
    
    /**
     * 获取报表的权限控制字段
     * @param string $report_id
     * @param $cache    是否使用缓存，默认为使用
     * @return array
     */
    public static function getPrivilegeColumnsByReport($report_id,$cache=true)
    {
        $results = array();
        
        if ($cache) {
            $cache_priv_cols = new ARedisHash(Common::REDIS_COMMON_PRIVILEGE_COLUMNS);
            $results = Yii::app()->redis->getClient()->hExists(Common::REDIS_COMMON_PRIVILEGE_COLUMNS, $report_id) && !empty($cache_priv_cols[$report_id]) ? unserialize($cache_priv_cols[$report_id]) : array();
        }
        
        if (empty($results)) {
            $models = self::model()->findAll('report_id=:report_id and privilege_type!="" and status=1', array(':report_id'=>$report_id));
            if ($models) {
                foreach ($models as $model) {
                    $results[$model->column_name] = $model->privilege_type;
                }
            }
        
            $cache_priv_cols = new ARedisHash(Common::REDIS_COMMON_PRIVILEGE_COLUMNS);
            $cache_priv_cols[$report_id] = serialize($results);
            Yii::app()->redis->getClient()->setTimeout(Common::REDIS_COMMON_PRIVILEGE_COLUMNS, 12*Common::REDIS_DURATION);
        }
        
        return $results;
    }
    
    /**
     * 获取报表的字段名称及定义
     * @param string $report_id
     * @param array $columns    结果字段，排序标准
     * @param boolean $cache    是否使用缓存，默认为使用
     * @param boolean $exists   数据库表中是否存在记录
     * @return array
     */
    public static function getColumnsInfoByReport($report_id, $columns,$cache=true,&$exists=true)
    {
        $results = array();
        
        if ($cache) {
            $cache_cols_info = new ARedisHash(Common::REDIS_COMMON_COLUMNS_INFO);
            $results = Yii::app()->redis->getClient()->hExists(Common::REDIS_COMMON_COLUMNS_INFO, $report_id) && !empty($cache_cols_info[$report_id]) ? unserialize($cache_cols_info[$report_id]) : array();
        }
        
        if (empty($results)) {
            if (!empty($columns)) {
                foreach ($columns as $col) {
                    $results[$col] = array(
                            'show_name' => '',
                            'define' => '',
                            'function' => 0,
                            'function_detail' => '',
                    );
                }
            }
            
            $models = self::model()->findAll('report_id=:report_id and status=1', array(':report_id'=>$report_id));
            if ($models) {
                //$extra = array();
                foreach ($models as $model) {
                    if (array_key_exists($model->column_name, $results)) {
                        $results[$model->column_name] = array(
                                'show_name' => $model->show_name,
                                'define' => $model->define,
                                'function' => $model->function,
                                'function_detail' => $model->function_detail,
                        );
                    }
                    /*  else {
                        $extra[$model->column_name] = array(
                                'show_name' => $model->show_name,
                                'define' => $model->define,
                        );
                    } */
                }
                /* if (!empty($extra)) {
                    $results = $results + $extra;
                } */
            } else {
                $exists = false;
            }
            
            $cache_cols_info = new ARedisHash(Common::REDIS_COMMON_COLUMNS_INFO);
            $cache_cols_info[$report_id] = serialize($results);
            Yii::app()->redis->getClient()->setTimeout(Common::REDIS_COMMON_COLUMNS_INFO, 12*Common::REDIS_DURATION);
        }
        
        return $results;
    }
    
    /**
     * 获取报表的城市归属字段名称及定义
     * @param string $report_id
     * @param boolean $cache    是否使用缓存，默认为使用
     * @return array
     */
    public static function getCityDivColumnsInfoByReport($report_id,$cache=true)
    {
        $results = array();
    
        if ($cache) {
            $cache_cols_info = new ARedisHash(Common::REDIS_COMMON_COLUMNS_INFO_CITYDIV);
            $results = Yii::app()->redis->getClient()->hExists(Common::REDIS_COMMON_COLUMNS_INFO_CITYDIV, $report_id) && !empty($cache_cols_info[$report_id]) ? unserialize($cache_cols_info[$report_id]) : array();
        }
    
        if (empty($results)) {
            $models = self::model()->findAll('report_id=:report_id and status=1 and function=2', array(':report_id'=>$report_id));
            if ($models) {
                foreach ($models as $model) {
                    $results[$model->column_name] = array(
                            'show_name' => $model->show_name,
                            'define' => $model->define,
                    );
                }
            }
    
            $cache_cols_info = new ARedisHash(Common::REDIS_COMMON_COLUMNS_INFO_CITYDIV);
            $cache_cols_info[$report_id] = serialize($results);
            Yii::app()->redis->getClient()->setTimeout(Common::REDIS_COMMON_COLUMNS_INFO_CITYDIV, 12*Common::REDIS_DURATION);
        }
    
        return $results;
    }
    
    /**
     * 清除报表的权限控制字段
     * @param string $report_id
     * @return integer
     */
    public static function emptyReportPrivilegeColumns($report_id)
    {
        $sql = "update column_define set privilege_type='' where report_id='$report_id'";
        return Yii::app()->db->createCommand($sql)->execute();
    }
    
    /**
     * 清除报表的城市归属展示目标字段
     * @param string $report_id
     * @return integer
     */
    public static function emptyCityDivisionColumns($report_id)
    {
        //清除关联城市归属的目标城市字段的附加功能
        $sql = "update column_define set function=0,function_detail='' where report_id='$report_id' and function=".ColumnDefine::FUNCTION_CITY_DIVISION_COLUMN;
        Yii::app()->db->createCommand($sql)->execute();
        
        //删除所有城市归属展示字段
        $sql = "delete from column_define where report_id='$report_id' and function=".ColumnDefine::FUNCTION_CITY_DIVISIONS;
        Yii::app()->db->createCommand($sql)->execute();
    }
}
