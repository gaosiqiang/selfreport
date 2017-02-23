<?php

/**
 * This is the model class for table "region".
 *
 * The followings are the available columns in table 'region':
 * @property integer $id
 * @property integer $parent_id
 * @property string $name
 * @property integer $show_order
 */
class Region extends CActiveRecord
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
        return 'region';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name', 'required'),
            array('name', 'length', 'max'=>30),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, parent_id, name, show_order', 'safe', 'on'=>'search'),
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
            'id' => '区域ID',
            'parent_id' => '0-大区,其他-区域',
            'name' => '区域名称',
            'show_order' => '显示顺序'
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('name',$this->name,true);

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
        $models = self::model()->findAll('parent_id>0 and is_delete=0');
        if (!empty($models)) {
            foreach ($models as $model) {
                $result[$model->id] = $model->name;
            }
        }
        return $result;
    }
    
    /**
     * 返回多个大区下的区域
     * @param array $areaIds 大区id列表
     * @return array    区域ID
     */
    public static function getRegionsByAreas($areaIds)
    {
        $regions = array();
        if(is_array($areaIds) && !empty($areaIds)) {
            $criteria=new CDbCriteria;
            $criteria->addInCondition('parent_id', $areaIds);
            $models = self::model()->findAll($criteria);
            foreach($models as $model){
                $regions[] = $model->id;
            }
        }
        return $regions;
    }
    
    /**
     * 根据大区获取区域，用于联动列表
     * @param string|int $area    参数可能是大区ID或名称
     */
    public static function getLinkedRegionsByArea($area)
    {
        if (is_numeric($area)) {
            $key = Common::REDIS_CITYSTRUCT_AREA_REGIONS;
            /* $models = self::model()->findAll('parent_id=:area_id',array(':area_id'=>$area));
            if (!empty($models)) {
                foreach ($models as $model) {
                    $result[$model->id] = $model->name;
                }
            } */
        } else {
            $key = Common::REDIS_CITYSTRUCT_AREA_NAME_REGIONS;
            /* $area_ids = array();
            $models = self::model()->findAll('parent_id=0 and name=:name',array(':name'=>$area));
            if (!empty($models)) {
                foreach ($models as $model) {
                    $area_ids[] = $model->id;
                }
            }
            unset($models);
            $criteria=new CDbCriteria;
            $criteria->addInCondition('parent_id', $area_ids);
            $models = self::model()->findAll($criteria);
            if (!empty($models)) {
                foreach($models as $model){
                    $result[$model->id] = $model->name;
                }
            } */
        }
        
        $area_regions = Common::getOnePrivCityStruct($key);
        return array_key_exists($area, $area_regions) ? $area_regions[$area] : array();
    }
    
    /**
     * 根据大区获取区域，用于联动列表，网店通业务用
     * @param string|int $area    参数可能是大区ID或名称
     */
    public static function getLinkedRegionsByAreaForWdt($area)
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