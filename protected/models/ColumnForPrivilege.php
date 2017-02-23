<?php

/**
 * This is the model class for table "column_for_privilege".
 *
 * The followings are the available columns in table 'column_for_privilege':
 * @property string $report_id
 * @property string $expression
 * @property string $privilege_type
 * @property integer $status
 */
class ColumnForPrivilege extends CActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'column_for_privilege';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('report_id, expression', 'required'),
            array('status', 'numerical', 'integerOnly'=>true),
            array('report_id', 'length', 'max'=>16),
            array('expression', 'length', 'max'=>255),
            array('privilege_type', 'length', 'max'=>18),
            // The following rule is used by search().
            array('report_id, expression, privilege_type, status', 'safe', 'on'=>'search'),
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
            'expression' => '结果字段表达式',
            'privilege_type' => '行级权限类型，如city,category,media等',
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
        $criteria=new CDbCriteria;

        $criteria->compare('report_id',$this->report_id,true);
        $criteria->compare('expression',$this->expression,true);
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
     * @return ColumnForPrivilege the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
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
                    $results[$model->expression] = $model->privilege_type;
                }
            }
    
            $cache_priv_cols = new ARedisHash(Common::REDIS_COMMON_PRIVILEGE_COLUMNS);
            $cache_priv_cols[$report_id] = serialize($results);
            Yii::app()->redis->getClient()->setTimeout(Common::REDIS_COMMON_PRIVILEGE_COLUMNS, 12*Common::REDIS_DURATION);
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
        //$sql = "update column_for_privilege set privilege_type='' where report_id='$report_id'";
        //return Yii::app()->db->createCommand($sql)->execute();
        
        return ColumnForPrivilege::model()->deleteAll('report_id=:report_id',array(':report_id'=>$report_id));
    }
}
