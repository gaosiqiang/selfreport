<?php
/**
 * This is the model class for table "branch".
 *
 * The followings are the available columns in table 'branch':
 * @property integer $id
 * @property string $name
 * @property string $initials
 * @property string $operation_mode
 * @property integer $area_id
 * @property integer $region_id
 * @property integer $war_zone
 * @property integer $war_step
 * @property integer $business_type
 * @property string $relation_city
 * @property string $type_sign
 * @property integer $is_delete
 * @property string $update_time
 */
class Branch extends CActiveRecord
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
        return 'branch';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array();
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
                'team' => array(self::HAS_MANY, 'MappingTeamCity', '', 'on'=>'team.branch_id=t.id'),
                'region' => array(self::BELONGS_TO, 'Region', '', 'on' => 't.region_id = region.id', 'joinType' => 'RIGHT JOIN'),
                'warzone' => array(self::BELONGS_TO, 'WarZone', '', 'on' => 't.war_zone = warzone.id and warzone.parent_id = 0', 'joinType' => 'RIGHT JOIN'),
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
            'initials' => '首字母缩写',
            'operation_mode' => '运营模式',
            'area_id' => '大区ID',
            'region_id' => '区域ID',
            'war_zone' => '战区分区',
            'war_step' => '战区分档',
            'business_type' => '业务标识,0-团购,1-网店通,2-团购+网店通',
            'relation_city' => '权限关联城市',
            'type_sign' => '类型标识,空字符串:自然城市,a:部,b:事业部,c:渠道部',
            'is_delete' => '是否被删除,0-否,1-是',
            'update_time' => '更新时间',
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
        $criteria->compare('area_id',$this->area_id);
        $criteria->compare('region_id',$this->region_id);
        $criteria->compare('war_zone',$this->war_zone);
        $criteria->compare('war_step',$this->war_step);
        $criteria->compare('business_type',$this->business_type);
        $criteria->compare('relation_city',$this->relation_city,true);
        $criteria->compare('is_delete',$this->is_delete);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }
    
        /**
     * 根据业务类型取网店通城市
     * @param integer $tos 业务类型business_type
     */
    public static function getCitiesByToS($tos)
    {
        $result = self::model()->findAll('is_delete=0 and business_type=:business_type', array(':business_type'=>$tos));
        return $result;
    }
    
    /**
     * 根据业务类型取网店通专属分部，用于权限分配
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
     * 根据城市ID获取网店通分部
     * @param array $branch_ids
     */
    public static function getWdtBranchesByTGID($branch_ids)
    {
        $results = array();
        if (!is_array($branch_ids))
            $branch_ids = array();
        
        $criteria=new CDbCriteria;
        $criteria->with = 'city';
        $criteria->compare('is_delete', 0);
        $criteria->compare('business_type', self::TOS_WDT_TG);
        $criteria->addInCondition('city.id', $branch_ids);
        $models = self::model()->findAll($criteria);
        
        if (!empty($models)) {
            foreach ($models as $model) {
                $results[$model->id] = $model->name;
            }
        }
        return $results;
    }
    
    /**
     * 根据全部城市获取网店通分部
     */
    public static function getWdtBranchesByAllCity()
    {
        $results = array();
    
        $criteria=new CDbCriteria;
        $criteria->with = 'city';
        $criteria->compare('is_delete', 0);
        $criteria->compare('business_type', self::TOS_WDT_TG);
        $models = self::model()->findAll($criteria);
    
        if (!empty($models)) {
            foreach ($models as $model) {
                $results[$model->id] = $model->name;
            }
        }
        return $results;
    }
    

    /**
     * 分部ID对应分部名列表
     * @author chensm
     * @return array
     */
    public static function getBranchIDList()
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
     * 根据大区获取分部，用于联动列表
     * @param string|int $area    参数可能是大区ID或名称
     */
    public static function getLinkedBranchesByArea($area)
    {
        if (is_numeric($area)) {
            $key = Common::REDIS_CITYSTRUCT_AREA_CITIES;
        } else {
            $key = Common::REDIS_CITYSTRUCT_AREA_NAME_CITIES;
        }
    
        $area_cities = Common::getOnePrivWdtCityStruct($key);
        return array_key_exists($area, $area_cities) ? $area_cities[$area] : array();
    }
    
    /**
     * 根据区域获取分部，用于联动列表
     * @param string|int $region    参数可能是区域ID或名称
     */
    public static function getLinkedBranchesByRegion($region)
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
     * 根据战区获取分部，用于联动列表
     * @param string|int $warzone    参数可能是战区ID或名称
     */
    public static function getLinkedBranchesByWarzone($warzone)
    {
        if (is_numeric($warzone)) {
            $key = Common::REDIS_CITYSTRUCT_WARZONE_CITIES;
        } else {
            $key = Common::REDIS_CITYSTRUCT_WARZONE_NAME_CITIES;
        }
        $warzone_cities = Common::getOnePrivWdtCityStruct($key);
        return array_key_exists($warzone, $warzone_cities) ? $warzone_cities[$warzone] : array();
    }
    
    /**
     * 根据分部ID获取名称，用于将权限中保存的ID转换为名称
     * @param array $branch_ids
     */
    public static function getNameByIDs($branch_ids)
    {
        $result = array();
        if(is_array($branch_ids) && !empty($branch_ids)) {
            if (in_array(0, $branch_ids))
                $result[] = 0;
            if (in_array(City::ALLCITY, $branch_ids))
                $result[] = City::ALLCITY;
            $criteria=new CDbCriteria;
            $criteria->addInCondition('id', $branch_ids);
            $criteria->compare('is_delete', 0);
            $models = self::model()->findAll($criteria);
            foreach($models as $model){
                $result[] = $model->name;
            }
        }
        return $result;
    }
    
}
