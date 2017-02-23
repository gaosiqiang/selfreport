<?php
/**
 * 城市基础数据
 * @author sangxiaolong
 */

class City extends CActiveRecord
{
    const ALLCITY = 9999;        //所有城市销售额加和记录id
    const TYPE_CITY = 1;         //城市类型 - 城市
    const TYPE_PROVINCE = 2;     //城市类型 - 省份
    const TYPE_REGION = 3;       //城市类型 - 大区
    const TYPE_SYB = 4;          //城市类型 - 事业部
    const TYPE_DISTRICT = 5;     //城市类型 - 区县
    const TYPE_AREA = 6;         //城市类型 - 商圈
    
    // 当前城市
    private static $_currentCity;
    // 所有城市列表
    private static $_cityList = null;
    // 所有省份列表
    private static $_provinceList = null;
    
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
        return 'city';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array();
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
                'team' => array(self::HAS_MANY, 'MappingTeamCity', '', 'on'=>'team.city_id=t.id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'name' => 'Name',
            'pinyin' => 'Pinyin',
            'initials' => 'Initials',
            'level' => 'Level',
        );
    }
    
    /**
     * 省份列表缓存key
     * @author sangxiaolong
     * @return string
     */
    public static function getProvinceCacheKey()
    {
        return 'all_provinces';
    }
    
    /**
     * 获取所有城市列表，以城市id为键名，array('id'=>$city_id, 'name'=>$city_name)为键值的数组
     * @author sangxiaolong
     * @return array
     */
    public static function getAllCities($is_privileges=TRUE)
    {
        if(self::$_cityList === null)
        {
            $session_super = Yii::app()->user->getstate(Common::SESSION_SUPER);
            $session_cities = Yii::app()->user->getstate(Common::SESSION_CITIES);
            if (in_array($session_super, Privileges::$super_admin) || $session_cities == 1 || $is_privileges === FALSE) {        //用户具有所有城市的权限或用于分配城市权限
                $all_cities = Yii::app()->pdb->createCommand()
                ->select('id, name, city_level, city_type, initials, parent_id, region_id, status, war_zone, war_step')
                ->from('city')
                ->where(array('in', 'city_type', array(1,3,4,5)))// ->where(array('and', 'city_type=1'))  //, array('not in', 'id', array(7000,7001,7002,7003,7004))
                ->query();
                while (($row = $all_cities->read()) !== false) {
                    if ($row['id'] != 0 && $row['id'] != 9999) self::$_cityList[$row['id']] = $row;
                }
            } else {
                self::$_cityList = array();
                $privileges = !empty(Yii::app()->user->privileges) ? unserialize(Yii::app()->user->privileges) : array();
                if (!empty($privileges)) {
                    $cities = isset($privileges['operation']['cities']) ? $privileges['operation']['cities'] : array();
                    if (!empty($cities)) {
                        $city_ids = array_values($cities);
                        $all_cities = Yii::app()->pdb->createCommand()
                        ->select('id, name, city_level, city_type, initials, parent_id, region_id, status, war_zone, war_step')
                        ->from('city')
                        ->where(array('and', array('in', 'city_type', array(1,3,4)), array('in', 'id', $city_ids)))// ->where(array('and', 'city_type=1', array('in', 'id', $city_ids))) //, array('not in', 'id', array(7000,7001,7002,7003,7004))
                        ->query();
                        while (($row = $all_cities->read()) !== false) {
                            if ($row['id'] != 0 && $row['id'] != 9999) self::$_cityList[$row['id']] = $row;
                        }
                    }
                }
            }
        }
        return self::$_cityList;
    }
    
    /**
     * 城市ID对应城市名列表
     * @author chensm
     * @return array
     */
    public static function getCityIDList()
    {
        $city_ids = Yii::app()->redis->getClient()->get(Common::REDIS_COMMON_CITYLIST);
        $city_list = unserialize($city_ids);
        if (!empty($city_list)) {
            return $city_list;
        } else {
            $cities = self::getAllCities(FALSE);
            $result = array();
            foreach ($cities as $city)
            {
                $result[$city['id']] = $city['name'];
            }
            Yii::app()->redis->getClient()->setex(Common::REDIS_COMMON_CITYLIST, 12*Common::REDIS_DURATION, serialize($result));
            return $result;
        }
    }
    
    /**
     * 获取以城市名为键名的城市列表
     * @author sangxiaolong
     * @return array
     */
    public static function getCityNameList()
    {
        $cityname = Yii::app()->redis->getClient()->get(Common::REDIS_COMMON_CITYNAME);
        $city_list = unserialize($cityname);
        if (!empty($city_list) && count($city_list) > 300) {
            return $city_list;
        } else {
            $cities = self::getAllCities(FALSE);
            $result = array();
            foreach ($cities as $city)
            {
                $result[$city['name']] = $city['id'];
            }
            Yii::app()->redis->getClient()->setex(Common::REDIS_COMMON_CITYNAME, 12*Common::REDIS_DURATION, serialize($result));
            return $result;
        }
    }
    
    /**
     * 获取以城市拼音为键名的城市列表
     * @author sangxiaolong
     * @return array
     */
    public static function getCitySlugList()
    {
        $cities = self::getAllCities();
        $result = array();
        foreach ($cities as $city)
        {
            if ($city['city_type'] == 1)
                $result[$city['pinyin']] = $city;
        }
        return $result;
    }
    
    /**
     * 获取以城市类别为键值，城市id为值的列表
     * @author sangxiaolong
     * @return array
     */
    public static function getCityListByLevel()
    {
        $cities = self::getAllCities();
        $result = array();
        foreach ($cities as $city)
        {
            if ($city['city_type'] == 1 || $city['city_type'] == 4)
                $result[$city['city_level']][] = $city['id'];
        }
        return $result;
    }
    
    /**
     * 根据城市id获取城市名
     * @author sangxiaolong
     * @param $city_id
     * @return string
     */
    public static function getCityNameById($city_id)
    {
        if ($city_id == 0)
            return '未知城市';
        if ($city_id == City::ALLCITY)
            return '整站';
        $cityNameList = array_flip(self::getCityNameList());
        return isset($cityNameList[$city_id]) ? $cityNameList[$city_id] : '';
    }
    
    /**
     * 根据省份id获取城市列表
     * @author sangxiaolong
     * @return array
     */
    public static function getCitiesByProvince($province_id)
    {
        $allCities = array();
        if ($province_id !== null)
        {
            $allCities = self::getProvinceAndCities();
            return isset($allCities[$province_id]) ? $allCities[$province_id]['cities'] : array();
        }
    
        return $allCities;
    }
    
    /**
     * 获取所有在线城市
     * @author sangxiaolong
     * @return array
     */
    public static function getOnlineCities()
    {
        $result = array();
        $all_cities = self::getAllCities();
        foreach ($all_cities as $k=>$city)
        {
            //if ($city['status'] == 1 && ($city['id']<7000 || $city['id']>7004))
            if ($city['status'] == 1 && $city['city_type'] == 1)
            {
                $initials = substr($city['initials'], 0, 1);
                $result[$initials][] = $city;
            }
        }
        ksort($result);
        return $result;
    }
    
    /**
     * 获取所有省份
     * @author sangxiaolong
     * @return array
     */
    public static function getProvinces()
    {
        $cachekey = self::getProvinceCacheKey();
        if(self::$_provinceList === null)
            self::$_provinceList = Yii::app()->cache->get($cachekey);
        if(self::$_provinceList === false)
        {
            $provinceList = array();
            $all_provinces = Yii::app()->db->createCommand()
                ->select('id, name')
                ->from('city')
                ->where('status=1 and city_type=2')
                ->query();
            $provinceList[0] = array('id'=>0,'name'=>'全国');
            while (($row = $all_provinces->read())!==false)
            {
                $provinceList[$row['id']] = $row;
            }
            
            self::$_provinceList = $provinceList;
            Yii::app()->cache->set($cachekey, $provinceList, 24*60*60);
        }
        return self::$_provinceList;
    }
    
    /**
     * 获取省份和城市
     * @author sangxiaolong
     * @reuturn array
     */
    public static function getProvinceAndCities()
    {
        $cities = self::getOnlineCities();
        $provinces = self::getProvinces();
        $result = array();
        foreach($provinces as $k=>$province)
        {
            $result[$k]['id'] = $k;
            $result[$k]['name'] = $province['name'];
        }
        foreach($cities as $k=>$v)
        {
            foreach ($v as $city)
            {
                $result[$city['parent_id']]['cities'][$city['id']] = array('id'=>$city['id'], 'name'=>$city['name']);
            }
        }
        //unset($result[0]);// 去掉全国和海外
        return $result;
    }
    
    /**
     * 获取以拼音缩写为键，城市列表为值的数组
     * @param $cache    是否使用缓存，默认为使用
     * @return array
     */
    public static function getCitiesInitials($cache=true)
    {
        //@param array $_cities 要返回的城市列表，如果传入了这个参数就只返回这个数组中的城市列表。如果这个参数为空，则返回全部城市列表
        //@author sangxiaolong
        //getCitiesInitials($_cities = array())
        
        $cities = array();
        
        if ($cache) {
            $cache_cities = Yii::app()->redis->getClient()->get(Common::REDIS_COMMON_CITY_INITIALS);
            $cities = unserialize($cache_cities);
        }
        
        if ($cities === false || empty($cities)) {
            $cityList = self::getAllCities(FALSE);
            foreach ($cityList as $city)
            {
                //if ($city['status'] == 1)
                if ($city['status'] == 1 && $city['city_type'] == 1)
                {
                    $cities[] = array(
                        'id' => $city['id'],
                        'name' => $city['name'],
                        'initials' => $city['initials']
                    );
                }
            }
            
            Yii::app()->redis->getClient()->setex(Common::REDIS_COMMON_CITY_INITIALS, 12*Common::REDIS_DURATION, serialize($cities));
        }
        return $cities;
    }
    
    public static function getCitiesByInitials()
    {
        $array = array(
            'A' => 1,
            'B' => 1,
            'C' => 1,
            'D' => 1,
            'E' => 1,
            'F' => 1,
            'G' => 2,
            'H' => 2,
            'I' => 2,
            'J' => 2,
            'K' => 2,
            'L' => 2,
            'M' => 3,
            'N' => 3,
            'P' => 3,
            'Q' => 3,
            'R' => 3,
            'S' => 3,
            'T' => 4,
            'W' => 4,
            'X' => 4,
            'Y' => 4,
            'Z' => 4
        );
        $result = $data = array();
        $all_cities = self::getAllCities();
        foreach ($all_cities as $k=>$city)
        {
            //if ($city['status'] == 1 && ($city['id']<7000 || $city['id']>7004))
            if ($city['status'] == 1 && $city['city_type'] == 1)
            {
                $initials = substr($city['initials'], 0, 1);
                $result[$array[$initials]][$initials][$city['id']] = array(
                    'id' => $city['id'],
                    'name' => $city['name']
                );
            }
        }
        foreach ($result as $k=>$v)
        {
            ksort($v);
            $data[$k] = $v;
        }
        return $data;
    }
    
    /**
     * 返回多个大区下的城市
     * @param array $areaIds 大区id列表
     * @return array    城市ID
     */
    public static function getCitiesByAreas($areaIds)
    {
        $cities = array();
        if(is_array($areaIds) && !empty($areaIds)) {
            $criteria=new CDbCriteria;
            $criteria->addInCondition('area_id', $areaIds);
            $criteria->compare('status', 1);
            $criteria->compare('city_type', 1);
            $models = self::model()->findAll($criteria);
            foreach($models as $model){
                $cities[] = $model->id;
            }
        }
        return $cities;
    }
    
    /**
     * 返回多个区域下的城市
     * @param array $regionIds 区域id列表
     * @return array    城市ID
     */
    public static function getCitiesByRegins($regionIds)
    {
        $cities = array();
        if(is_array($regionIds) && !empty($regionIds)) {
            $criteria=new CDbCriteria;
            $criteria->addInCondition('region_id', $regionIds);
            $criteria->compare('status', 1);
            $criteria->compare('city_type', 1);
            $models = self::model()->findAll($criteria);
            foreach($models as $model){
                $cities[] = $model->id;
            }
        }
        return $cities;
    }
    
    /**
     * 根据城市ID获取名称，用于将权限中保存的ID转换为名称
     * @param array $city_ids
     */
    public static function getCityNameByIDs($city_ids)
    {
        $result = array();
        if(is_array($city_ids) && !empty($city_ids)) {
            if (in_array(0, $city_ids))
                $result[] = 0;
            if (in_array(City::ALLCITY, $city_ids))
                $result[] = City::ALLCITY;
            $criteria=new CDbCriteria;
            $criteria->addInCondition('id', $city_ids);
            $criteria->compare('status', 1);
            $criteria->compare('city_type', 1);
            $models = self::model()->findAll($criteria);
            foreach($models as $model){
                $result[] = $model->name;
            }
        }
        return $result;
    }
    
    /**
     * 根据大区获取城市，用于联动列表
     * @param string|int $area    参数可能是大区ID或名称
     */
    public static function getLinkedCitiesByArea($area)
    {
        if (is_numeric($area)) {
            $key = Common::REDIS_CITYSTRUCT_AREA_CITIES;
            /* $models = self::model()->findAll('city_type=1 and area_id=:area_id',array(':area_id'=>$area));
            if (!empty($models)) {
                foreach ($models as $model) {
                    $result[$model->id] = $model->name;
                }
            } */
        } else {
            $key = Common::REDIS_CITYSTRUCT_AREA_NAME_CITIES;
            /* $area_ids = array();
            $models = Region::model()->findAll('parent_id=0 and name=:name',array(':name'=>$area));
            if (!empty($models)) {
                foreach ($models as $model) {
                    $area_ids[] = $model->id;
                }
            }
            unset($models);
            $criteria=new CDbCriteria;
            $criteria->addInCondition('area_id', $area_ids);
            $models = self::model()->findAll($criteria);
            if (!empty($models)) {
                foreach($models as $model){
                    $result[$model->id] = $model->name;
                }
            } */
        }
        $area_cities = Common::getOnePrivCityStruct($key);
        return array_key_exists($area, $area_cities) ? $area_cities[$area] : array();
    }
    
    /**
     * 根据区域获取城市，用于联动列表
     * @param string|int $region    参数可能是区域ID或名称
     */
    public static function getLinkedCitiesByRegion($region)
    {
        if (is_numeric($region)) {
            $key = Common::REDIS_CITYSTRUCT_REGION_CITIES;
            /* $models = self::model()->findAll('city_type=1 and region_id=:region_id',array(':region_id'=>$region));
            if (!empty($models)) {
                foreach ($models as $model) {
                    $result[$model->id] = $model->name;
                }
            } */
        } else {
            $key = Common::REDIS_CITYSTRUCT_REGION_NAME_CITIES;
            /* $region_ids = array();
            $models = Region::model()->findAll('parent_id!=0 and name=:name',array(':name'=>$region));
            if (!empty($models)) {
                foreach ($models as $model) {
                    $region_ids[] = $model->id;
                }
            }
            unset($models);
            $criteria=new CDbCriteria;
            $criteria->addInCondition('region_id', $region_ids);
            $models = self::model()->findAll($criteria);
            if (!empty($models)) {
                foreach($models as $model){
                    $result[$model->id] = $model->name;
                }
            } */
        }
        
        $region_cities = Common::getOnePrivCityStruct($key);
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
            /* $models = self::model()->findAll('city_type=1 and war_zone=:warzone',array(':warzone'=>$warzone));
            if (!empty($models)) {
                foreach ($models as $model) {
                    $result[$model->id] = $model->name;
                }
            } */
        } else {
            $key = Common::REDIS_CITYSTRUCT_WARZONE_NAME_CITIES;
            /* $warzone_ids = array();
            $models = WarZone::model()->findAll('parent_id=0 and name=:name',array(':name'=>$warzone));
            if (!empty($models)) {
                foreach ($models as $model) {
                    $warzone_ids[] = $model->id;
                }
            }
            unset($models);
            $criteria=new CDbCriteria;
            $criteria->addInCondition('war_zone', $warzone_ids);
            $models = self::model()->findAll($criteria);
            if (!empty($models)) {
                foreach($models as $model){
                    $result[$model->id] = $model->name;
                }
            } */
        }
        $warzone_cities = Common::getOnePrivCityStruct($key);
        return array_key_exists($warzone, $warzone_cities) ? $warzone_cities[$warzone] : array();
    }
    
    /**
     * 根据城市id获取城市拼音缩写
     * @author sangxiaolong
     * @param $city_id
     * @return string
     */
    public static function getInitialsById($city_id)
    {
        if ($city_id == 0 || $city_id == City::ALLCITY)
            return '';

        $criteria = new CDbCriteria();
        $criteria->select = 'initials';
        $criteria->compare('id', $city_id);
        $criteria->addCondition('city_type=1 and status=1');
        
        $result = self::model()->find($criteria);
        return $result['initials'];
    }
    
    /**
     * 根据city id获取城市信息，不涉及关联操作
     * @param integer $id
     * @return array
     * @author chensm
     */
    public static function getCityInfoByID($id)
    {
        $cities = self::getAllCities(false);
        return $cities[$id];
    }
    
}
