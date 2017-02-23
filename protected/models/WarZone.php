<?php

/**
 * This is the model class for table "warzone".
 *
 * The followings are the available columns in table 'warzone':
 * @property integer $id
 * @property integer $parent_id
 * @property string $name
 */
class WarZone extends CActiveRecord
{
    /* (non-PHPdoc)
     * @see CActiveRecord::getDbConnection()
    */
    public function getDbConnection() {
        return Yii::app()->pdb;
    }
    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'warzone';
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
            array('parent_id', 'numerical', 'integerOnly'=>true),
            array('name', 'length', 'max'=>30),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, parent_id, name', 'safe', 'on'=>'search'),
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
            'id' => '战区ID',
            'parent_id' => '如果该值为0，说明其为顶层战区',
            'name' => '战区名称',
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
        $criteria->compare('parent_id',$this->parent_id);
        $criteria->compare('name',$this->name,true);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return WarZone the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    /**
     * 设置缓存
     */
    public static function setWarZone()
    {
        $all = $warzone = $warstep = array();
        $cache = new ARedisHash(Common::REDIS_COMMON_WARZONE);
        Yii::app()->redis->getClient()->setTimeout(Common::REDIS_COMMON_WARZONE, 12*Common::REDIS_DURATION);
        
        $warzone_obj = self::model()->findAll(array('order' => 'id asc'));
        foreach ($warzone_obj as $obj) {
            $all[$obj->id] = $obj->name;
            if ($obj->parent_id == 0) {
                $warzone[$obj->id] = $obj->name;
            } else {
                $warstep[$obj->id] = $obj->name;
            }
        }
        
        $cache[Common::REDIS_COMMON_WARZONE_ALL] = serialize($all);
        $cache[Common::REDIS_COMMON_WARZONE_ZONE] = serialize($warzone);
        $cache[Common::REDIS_COMMON_WARZONE_STEP] = serialize($warstep);
        
        return array($all, $warzone, $warstep);
    }
    
    /**
     * 获取缓存 - 全部
     */
    public static function getALLWarZone()
    {
        $cache = new ARedisHash(Common::REDIS_COMMON_WARZONE);
        $warzone = Yii::app()->redis->getClient()->hExists(Common::REDIS_COMMON_WARZONE, Common::REDIS_COMMON_WARZONE_ALL) ? $cache[Common::REDIS_COMMON_WARZONE_ALL] : '';
        $warzones = !empty($warzone) ? unserialize($warzone) : array();
        if (!empty($warzones))
            return $warzones;
        else {
            list($all, $zone, $step) = self::setWarZone();
            return $all;
        }
    }
    
    /**
     * 获取缓存 - 分区
     */
    public static function getWarZone()
    {
        $cache = new ARedisHash(Common::REDIS_COMMON_WARZONE);
        $warzone = Yii::app()->redis->getClient()->hExists(Common::REDIS_COMMON_WARZONE, Common::REDIS_COMMON_WARZONE_ZONE) ? $cache[Common::REDIS_COMMON_WARZONE_ZONE] : '';
        $warzones = !empty($warzone) ? unserialize($warzone) : array();
        if (!empty($warzones))
            return $warzones;
        else {
            list($all, $zone, $step) = self::setWarZone();
            return $zone;
        }
    }
    
    /**
     * 获取缓存 - 分档
     */
    public static function getWarSteps()
    {
        $cache = new ARedisHash(Common::REDIS_COMMON_WARZONE);
        $warzone = Yii::app()->redis->getClient()->hExists(Common::REDIS_COMMON_WARZONE, Common::REDIS_COMMON_WARZONE_STEP) ? $cache[Common::REDIS_COMMON_WARZONE_STEP] : '';
        $warzones = !empty($warzone) ? unserialize($warzone) : array();
        if (!empty($warzones))
            return $warzones;
        else {
            list($all, $zone, $step) = self::setWarZone();
            return $step;
        }
    }
    
    /**
     * 获取战区分档
     * @param int $parent_id
     * @author chensm
     */
    public static function getWarStep($parent_id)
    {
        $warstep = array();
        $warstep_obj = self::model()->findAll('parent_id=:parent_id', array (
                ':parent_id' => $parent_id,
        ));
        $c = 0;
        foreach ($warstep_obj as $obj) {
            $warstep[$c]['id'] = $obj->id;
            $warstep[$c]['name'] = $obj->name;
            $c++;
        }
        return $warstep;
    }
    
}
