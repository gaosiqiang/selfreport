<?php
/**
 * This is the model class for table "wdt_city".
 *
 * The followings are the available columns in table 'wdt_city':
 * @property integer $id
 * @property string $name
 * @property string $pinyin
 * @property string $initials
 * @property integer $city_level
 * @property integer $city_type
 * @property string $operation_mode
 * @property integer $parent_id
 * @property integer $area_id
 * @property integer $region_id
 * @property integer $status
 * @property integer $war_zone
 * @property integer $war_step
 * @property integer $yewu_id
 * @property string $relation_city
 * @property integer $is_delete
 */
class WdtCity extends CActiveRecord
{
    const TOS_WDT = 1;
    const TOS_WDT_TG = 2;
    
    /* (non-PHPdoc)
     * @see CActiveRecord::getDbConnection()
    */
    public function getDbConnection() {
        return Yii::app()->pdb;
    }
    
    /**
     * Returns the static model of the specified AR class.
     * @return City the static model class
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
        return 'wdt_city';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('id, pinyin, initials, parent_id', 'required'),
            array('id, city_level, city_type, parent_id, area_id, region_id, status, war_zone, war_step, yewu_id, is_delete', 'numerical', 'integerOnly'=>true),
            array('name, operation_mode', 'length', 'max'=>50),
            array('pinyin', 'length', 'max'=>32),
            array('initials', 'length', 'max'=>4),
            array('relation_city', 'length', 'max'=>30),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, name, pinyin, initials, city_level, city_type, operation_mode, parent_id, area_id, region_id, status, war_zone, war_step, yewu_id, relation_city, is_delete', 'safe', 'on'=>'search'),
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
                'city' => array(self::HAS_ONE, 'City', '', 'on'=>'city.name = t.name and city.city_type = 1 and city.status = 1', 'joinType' => 'INNER JOIN'),
                'relation_city' => array(self::HAS_ONE, 'City', '', 'on'=>'relation_city.name = t.relation_city and relation_city.city_type = 1 and relation_city.status = 1', 'joinType' => 'INNER JOIN'),
                'team' => array(self::HAS_MANY, 'MappingTeamCity', '', 'on'=>'team.city_id=t.id'),
                'region' => array(self::BELONGS_TO, 'Region', '', 'on' => 't.region_id = region.id and t.city_type = 1', 'joinType' => 'RIGHT JOIN'),
                'warzone' => array(self::BELONGS_TO, 'WarZone', '', 'on' => 't.war_zone = warzone.id and t.city_type = 1 and warzone.parent_id = 0', 'joinType' => 'RIGHT JOIN'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => '城市id',
            'name' => '城市名称',
            'pinyin' => '城市拼音',
            'initials' => '首字母缩写',
            'city_level' => '城市级别',
            'city_type' => '城市类型，1为城市，2为省份，3大区，4事业部',
            'operation_mode' => '运营模式',
            'parent_id' => '父级id',
            'area_id' => '大区ID',
            'region_id' => '区域ID',
            'status' => '城市是否开通，0为不开通，1为开通',
            'war_zone' => '战区分区',
            'war_step' => '战区分档',
            'yewu_id' => '业务标识,0-团购,1-网店通,2-团购+网店通',
            'relation_city' => '权限关联城市',
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
        $criteria->compare('name',$this->name,true);
        $criteria->compare('city_type',$this->city_type);
        $criteria->compare('area_id',$this->area_id);
        $criteria->compare('region_id',$this->region_id);
        $criteria->compare('status',$this->status);
        $criteria->compare('war_zone',$this->war_zone);
        $criteria->compare('war_step',$this->war_step);
        $criteria->compare('yewu_id',$this->yewu_id);
        $criteria->compare('relation_city',$this->relation_city,true);
        $criteria->compare('is_delete',$this->is_delete);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }
    
    /**
     * 根据业务类型取网店通城市
     * @param integer $tos 业务类型yewu_id
     */
    public static function getCitiesByToS($tos)
    {
        $result = self::model()->findAll('city_type=1 and status=1 and is_delete=0 and yewu_id=:yewu_id', array(':yewu_id'=>$tos));
        return $result;
    }
    
    /**
     * 根据业务类型取网店通专属城市，用于权限分配
     */
    public static function getWdtOnlyForAdmin()
    {
        $results = array();
        $models = self::getCitiesByToS(self::TOS_WDT);
        if (!empty($models)) {
            foreach ($models as $model) {
                $results[$model->id] = $model->name;
            }
        }
        return $results;
    }
    
    /**
     * 根据城市ID获取网店通城市
     * @param array $city_ids
     */
    public static function getWdtCitiesByTGID($city_ids)
    {
        $results = array();
        if (!is_array($city_ids))
            $city_ids = array();
        
        $criteria=new CDbCriteria;
        $criteria->with = 'city';
        $criteria->compare('t.is_delete', 0);
        $criteria->compare('t.yewu_id', self::TOS_WDT_TG);
        $criteria->addInCondition('city.id', $city_ids);
        $models = self::model()->findAll($criteria);
        
        if (!empty($models)) {
            foreach ($models as $model) {
                $results[$model->id] = $model->name;
            }
        }
        return $results;
    }
    
    /**
     * 根据全部城市获取网店通城市
     */
    public static function getWdtCitiesByAllCity()
    {
        $results = array();
    
        $criteria=new CDbCriteria;
        $criteria->with = 'city';
        $criteria->compare('t.is_delete', 0);
        $criteria->compare('t.yewu_id', self::TOS_WDT_TG);
        $models = self::model()->findAll($criteria);
    
        if (!empty($models)) {
            foreach ($models as $model) {
                $results[$model->id] = $model->name;
            }
        }
        return $results;
    }
    
    /**
     * 城市ID对应城市名列表
     * @author chensm
     * @return array
     */
    public static function getCityIDList()
    {
        $city_ids = Yii::app()->redis->getClient()->get(Common::REDIS_COMMON_WDT_CITYLIST);
        $city_list = unserialize($city_ids);
        if (!empty($city_list)) {
            return $city_list;
        } else {
            $cities = self::model()->findAll('is_delete=0');
            $result = array();
            foreach ($cities as $city)
            {
                $result[$city['id']] = $city['name'];
            }
            Yii::app()->redis->getClient()->setex(Common::REDIS_COMMON_WDT_CITYLIST, 12*Common::REDIS_DURATION, serialize($result));
            return $result;
        }
    }
    
    /**
     * 根据区域获取城市，用于联动列表
     * @param string|int $region    参数可能是区域ID或名称
     */
    public static function getLinkedCitiesByRegion($region)
    {
        if (is_numeric($region)) {
            $key = Common::REDIS_CITYSTRUCT_REGION_CITIES;
        } else {
            $key = Common::REDIS_CITYSTRUCT_REGION_NAME_CITIES;
        }
    
        $region_cities = Common::getOnePrivWdtCityStruct($key);
        return array_key_exists($region, $region_cities) ? $region_cities[$region] : array();
    }
    
    /**
     * 根据战区获取城市，用于联动列表
     * @param string|int $warzone    参数可能是战区ID或名称
     */
    public static function getLinkedCitiesByWarzone($warzone)
    {
        if (is_numeric($warzone)) {
            $key = Common::REDIS_CITYSTRUCT_WARZONE_CITIES;
        } else {
            $key = Common::REDIS_CITYSTRUCT_WARZONE_NAME_CITIES;
        }
        $warzone_cities = Common::getOnePrivWdtCityStruct($key);
        return array_key_exists($warzone, $warzone_cities) ? $warzone_cities[$warzone] : array();
    }
    
}
