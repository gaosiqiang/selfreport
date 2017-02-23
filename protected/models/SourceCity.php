<?php

/**
 * This is the model class for table "dim_source_city".
 *
 * The followings are the available columns in table 'dim_source_city':
 * @property integer $city_id
 * @property string $city_name
 * @property string $city_type
 */
class SourceCity extends CActiveRecord
{
    /* (non-PHPdoc)
     * @see CActiveRecord::getDbConnection()
    */
    public function getDbConnection() {
        return Yii::app()->pdb;
    }
    
    /**
     * Returns the static model of the specified AR class.
     * @return SourceCity the static model class
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
        return 'dim_source_city';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('city_id', 'required'),
            array('city_id', 'numerical', 'integerOnly'=>true),
            array('city_name', 'length', 'max'=>50),
            array('city_type', 'length', 'max'=>10),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('city_id, city_name, city_type', 'safe', 'on'=>'search'),
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
            'city_id' => 'City',
            'city_name' => 'City Name',
            'city_type' => 'City Type',
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

        $criteria->compare('city_id',$this->city_id);
        $criteria->compare('city_name',$this->city_name,true);
        $criteria->compare('city_type',$this->city_type,true);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }
    
    /**
     * 获取事业部数据
     * @return array
     */
    public static function getUnitData()
    {
        $result = array();
        $unit = Yii::app()->redis->getClient()->get(Common::REDIS_COMMON_UNIT);
        $result = unserialize($unit);
        if (!empty($result)) {
            return $result;
        } else {
            $unit = array();
            $ret = self::model()->findAll('city_type =:city_type', array(
                    'city_type' => 'unit'
            ));
            if(!empty($ret)){
                foreach($ret as $v){
                    $unit[$v->city_id] = $v->city_name;
                }
            }
            Yii::app()->redis->getClient()->setex(Common::REDIS_COMMON_UNIT, 12*Common::REDIS_DURATION, serialize($unit));
            return $unit;
        }
    }
}