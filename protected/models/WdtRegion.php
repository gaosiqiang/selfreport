<?php
/**
 * This is the model class for table "wdt_region".
 *
 * The followings are the available columns in table 'wdt_region':
 * @property integer $id
 * @property integer $parent_id
 * @property string $name
 * @property integer $show_order
 * @property string $update_time
 * @property integer $is_delete
 */
class WdtRegion extends CActiveRecord
{
    /* (non-PHPdoc)
     * @see CActiveRecord::getDbConnection()
    */
    public function getDbConnection() {
        return Yii::app()->pdb;
    }
    
    /**
     * Returns the static model of the specified AR class.
     * @return Region the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'wdt_region';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name, show_order, update_time', 'required'),
            array('parent_id, show_order, is_delete', 'numerical', 'integerOnly'=>true),
            array('name', 'length', 'max'=>30),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, parent_id, name, show_order, update_time, is_delete', 'safe', 'on'=>'search'),
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
                'region' => array(self::HAS_ONE, 'Region', '', 'on'=>'region.name = t.name', 'joinType' => 'INNER JOIN'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'parent_id' => '0-大区,其他-区域',
            'name' => '区域名称',
            'show_order' => '显示顺序',
            'update_time' => '更新时间',
            'is_delete' => '是否被删除,0-否,1-是',
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
        // Please modify the following code to remove attributes that should not be searched.
        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('parent_id',$this->parent_id);
        $criteria->compare('name',$this->name,true);
        $criteria->compare('show_order',$this->show_order);
        $criteria->compare('update_time',$this->update_time,true);
        $criteria->compare('is_delete',$this->is_delete);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    /**
     * 获取所有大区
     */
    public static function getAreas()
    {
        $result = array();
        $models = self::model()->findAll('parent_id=0 and is_delete=0');
        if (!empty($models)) {
            foreach ($models as $model) {
                $result[$model->id] = $model->name;
            }
        }
        return $result;
    }
    
    /**
     * 获取所有区域
     */
    public static function getRegions()
    {
        $result = array();
        $models = self::model()->findAll('parent_id!=0 and is_delete=0');
        if (!empty($models)) {
            foreach ($models as $model) {
                $result[$model->id] = $model->name;
            }
        }
        return $result;
    }
    
    /**
     * 根据大区/区域ID获取网店通大区/区域
     * @param array $region_ids
     */
    public static function getRegionsByID($region_ids)
    {
        $results = array();
        if (!is_array($region_ids))
            $region_ids = array();
    
        $criteria=new CDbCriteria;
        $criteria->with = 'region';
        $criteria->compare('t.is_delete', 0);
        $criteria->addInCondition('region.id', $region_ids);
        $models = self::model()->findAll($criteria);
    
        if (!empty($models)) {
            foreach ($models as $model) {
                $results[$model->id] = $model->name;
            }
        }
        return $results;
    }
    
    /**
     * 根据大区获取区域，用于联动列表
     * @param string|int $area    参数可能是大区ID或名称
     */
    public static function getLinkedRegionsByArea($area)
    {
        if (is_numeric($area)) {
            $key = Common::REDIS_CITYSTRUCT_AREA_REGIONS;
        } else {
            $key = Common::REDIS_CITYSTRUCT_AREA_NAME_REGIONS;
        }
    
        $area_regions = Common::getOnePrivWdtCityStruct($key);
        return array_key_exists($area, $area_regions) ? $area_regions[$area] : array();
    }
}
