<?php

/**
 * This is the model class for table "dict_list".
 *
 * The followings are the available columns in table 'dict_list':
 * @property integer $id
 * @property string $name
 * @property integer $type
 * @property integer $value_type
 * @property string $linked_default
 */
class DictList extends CActiveRecord
{
    const TYPE_NORMAL = '0';    //普通列表
    const TYPE_LINKED = '1';    //联动列表
    
    public static $preset_list_id = array(
        '一级品类'=>'first_cate',
        '媒体'=>'media',
        '事业部'=>'unit',
        '(团购)大区-区域-城市-团队'=>'area-region-city-team',
        '(团购)大区体系各级汇总明细'=>'area-city-sum-detail',
        '(网店通)大区-区域-分部-团队'=>'area-region-branch-team',
        '(网店通)大区体系各级汇总明细'=>'area-branch-sum-detail',
        '(团购)战区-城市'=>'warzone-city',
        '一级品类-二级品类'=>'first-second-cate',
        '媒体类型-媒体'=>'media-type-list',
    );
    
    public static $preset_linked_list_default = array(
        'area-region-city-team' => array(
                'first'=>array('column'=>'area','name'=>'大区'),
                'second'=>array('column'=>'region','name'=>'区域'),
                'third'=>array('column'=>'city','name'=>'城市'),
                'fourth'=>array('column'=>'team','name'=>'团队'),
        ),
        'area-city-sum-detail' => array(
            'first'=>array('column'=>'area','name'=>'大区'),
            'second'=>array('column'=>'region','name'=>'区域'),
            'third'=>array('column'=>'city','name'=>'城市'),
            'fourth'=>array('column'=>'team','name'=>'团队'),
        ),
        'area-region-branch-team' => array(
                'first'=>array('column'=>'area','name'=>'大区'),
                'second'=>array('column'=>'region','name'=>'区域'),
                'third'=>array('column'=>'city','name'=>'分部'),
                'fourth'=>array('column'=>'team','name'=>'团队'),
        ),
        'area-branch-sum-detail' => array(
            'first'=>array('column'=>'area','name'=>'大区'),
            'second'=>array('column'=>'region','name'=>'区域'),
            'third'=>array('column'=>'city','name'=>'分部'),
            'fourth'=>array('column'=>'team','name'=>'团队'),
        ),
        'warzone-city' => array(
                'first'=>array('column'=>'warzone','name'=>'战区'),
                'second'=>array('column'=>'city','name'=>'城市'),
        ),
        'first-second-cate' => array(
                'first'=>array('column'=>'first_cate','name'=>'一级品类'),
                'second'=>array('column'=>'second_cate','name'=>'二级品类'),
        ),
        'media-type-list' => array(
                'first'=>array('column'=>'media_type','name'=>'媒体类型'),
                'second'=>array('column'=>'media','name'=>'媒体'),
        ),
    );
    
    public static $sum_detail_list = array(
        'area-city-sum-detail',
        'area-branch-sum-detail',
    );
    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'dict_list';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('linked_default', 'required'),
            array('type, value_type', 'numerical', 'integerOnly'=>true),
            array('name', 'length', 'max'=>18),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, name, type, value_type, linked_default', 'safe', 'on'=>'search'),
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
            'id' => 'ID',
            'name' => '名称',
            'type' => '类型，0普通列表，1联动列表',
            'value_type' => '值类型，0ID，1名称',
            'linked_default' => '联动列表默认项，如region=>大区，city=>城市',
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
        $criteria->compare('name',$this->name,true);
        $criteria->compare('type',$this->type);
        $criteria->compare('value_type',$this->value_type);
        $criteria->compare('linked_default',$this->linked_default,true);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return DictList the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    /**
     * 根据列表类型获取列表信息
     * @param int $type
     */
    public static function getListByType($type)
    {
        return self::model()->findAll('type=:type',array(':type'=>$type));
    }
    
    /**
     * 根据预置普通列表ID获取列表内容
     * @param string $id
     */
    public static function getNormalPresetByID($id)
    {
        $result = array();
        switch ($id) {
            case 'first_cate':
                $result = Category::getUserCategories();
                break;
            case 'media':
                $result = MediaList::getCateData(false);
                break;
            case 'unit':
                $result = SourceCity::getUnitData();
                break;
        }
        return $result;
    }
    
    /**
     * 根据预置联动列表ID获取列表内容
     * @param string $id            列表ID
     * @param int $level            列表级别
     * @param string $parent        父列表值
     */
    public static function getLinkedPresetByID($id, $level=1, $parent=null)
    {
        $result = array();
        switch ($id) {
            case 'area-region-city-team':
                switch ($level) {
                    case 1:
                        $result = Common::getOnePrivCityStruct(Common::REDIS_CITYSTRUCT_AREAS);
                        break;
                    case 2:
                        $result = Region::getLinkedRegionsByArea($parent);
                        break;
                    case 3:
                        $result = City::getLinkedCitiesByRegion($parent);
                        break;
                    case 4:
                        $result = MappingTeamCity::getLinkedTeamsByCity($parent);
                        break;
                }
                break;
            case 'area-city-sum-detail':
                $session_super = Yii::app()->user->getstate(Common::SESSION_SUPER);
                $session_cities = Yii::app()->user->getstate(Common::SESSION_CITIES);
                $has_all_data = in_array($session_super, Privileges::$super_admin) || $session_cities == 1;
                
                $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT);
                $privileges = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT, Common::REDIS_PRIVILEGES_FIELD) && !empty($priv[Common::REDIS_PRIVILEGES_FIELD]) ? unserialize($priv[Common::REDIS_PRIVILEGES_FIELD]) : array();
                $priv_areas = $privileges!==false && array_key_exists(Common::PRIVILEGE_TYPE_AREA, $privileges)&&!empty($privileges[Common::PRIVILEGE_TYPE_AREA]) ? $privileges[Common::PRIVILEGE_TYPE_AREA] : array();
                $priv_regions = $privileges!==false && array_key_exists(Common::PRIVILEGE_TYPE_REGION, $privileges)&&!empty($privileges[Common::PRIVILEGE_TYPE_REGION]) ? $privileges[Common::PRIVILEGE_TYPE_REGION] : array();
                $priv_cities = $privileges!==false && array_key_exists(Common::PRIVILEGE_TYPE_CITY, $privileges)&&!empty($privileges[Common::PRIVILEGE_TYPE_CITY]) ? $privileges[Common::PRIVILEGE_TYPE_CITY] : array();
                
                switch ($level) {
                    case 1:
                        $result = Common::getOnePrivCityStruct(Common::REDIS_CITYSTRUCT_AREAS);
                        if ($has_all_data) {
                            $options = array(Common::LINK_LIST_SUMMARY_WHOLESITE => '整站汇总', Common::LINK_LIST_ALL_AREAS => '全部大区');
                            $result = $options + $result;
                        }
                        /* 只给予全城市权限用户全部大区
                         elseif (count($priv_areas) > 1) {
                            $options = array(Common::LINK_LIST_ALL_AREAS => '全部大区');
                            $result = $options + $result;
                        } */
                        break;
                    case 2:
                        if ($parent == Common::LINK_LIST_SUMMARY_WHOLESITE && $has_all_data) {
                            $result = array(Common::LINK_LIST_SUMMARY_WHOLESITE => '整站汇总');
                        } elseif ($parent == Common::LINK_LIST_ALL_AREAS && ($has_all_data || in_array($parent, $priv_areas))) {
                            $options = array(Common::LINK_LIST_ALL_AREAS => '全部大区', Common::LINK_LIST_ALL_REGIONS => '全部区域');
                            $result = $options + $result;
                        } else {
                            $result = Region::getLinkedRegionsByArea($parent);
                            if (($has_all_data || in_array($parent, $priv_areas)) && $parent != Common::UNKNOWN_AREA_ID) {
                                $options = array(Common::LINK_LIST_SUMMARY_AREA => '大区汇总', Common::LINK_LIST_ALL_REGIONS => '全部区域');
                                $result = $options + $result;
                            }
                        }
                        break;
                    case 3:
                        if ($parent == Common::LINK_LIST_SUMMARY_WHOLESITE) {
                            $result = array(Common::LINK_LIST_SUMMARY_WHOLESITE => '整站汇总');
                        } elseif ($parent == Common::LINK_LIST_ALL_AREAS) {
                            $options = array(Common::LINK_LIST_ALL_AREAS => '全部大区');
                            $result = $options + $result;
                        } elseif ($parent == Common::LINK_LIST_ALL_REGIONS) {
                            $options = array(Common::LINK_LIST_ALL_REGIONS => '全部区域', Common::LINK_LIST_ALL_CITIES => '全部城市');
                            $result = $options + $result;
                        } elseif ($parent == Common::LINK_LIST_SUMMARY_AREA) {
                            $options = array(Common::LINK_LIST_SUMMARY_AREA => '大区汇总');
                            $result = $options + $result;
                        } else {
                            $result = City::getLinkedCitiesByRegion($parent);
                            if ($has_all_data || in_array($parent, $priv_regions)) {
                                $options = array(Common::LINK_LIST_SUMMARY_REGION => '区域汇总', Common::LINK_LIST_ALL_CITIES => '全部城市');
                                $result = $options + $result;
                            }
                        }
                        break;
                    case 4:
                        if ($parent == Common::LINK_LIST_SUMMARY_WHOLESITE) {
                            $result = array(Common::LINK_LIST_SUMMARY_WHOLESITE => '整站汇总');
                        } elseif ($parent == Common::LINK_LIST_ALL_AREAS) {
                            $options = array(Common::LINK_LIST_ALL_AREAS => '全部大区');
                            $result = $options + $result;
                        } elseif ($parent == Common::LINK_LIST_ALL_REGIONS) {
                            $options = array(Common::LINK_LIST_ALL_REGIONS => '全部区域');
                            $result = $options + $result;
                        } elseif ($parent == Common::LINK_LIST_ALL_CITIES) {
                            $options = array(Common::LINK_LIST_ALL_CITIES => '全部城市', Common::LINK_LIST_ALL_TEAMS => '全部团队');
                            $result = $options + $result;
                        } elseif ($parent == Common::LINK_LIST_SUMMARY_AREA) {
                            $options = array(Common::LINK_LIST_SUMMARY_AREA => '大区汇总');
                            $result = $options + $result;
                        } elseif ($parent == Common::LINK_LIST_SUMMARY_REGION) {
                            $options = array(Common::LINK_LIST_SUMMARY_REGION => '区域汇总');
                            $result = $options + $result;
                        } else {
                            $result = MappingTeamCity::getLinkedTeamsByCity($parent);
                            if ($has_all_data || in_array($parent, $priv_cities)) {
                                $options = array(Common::LINK_LIST_SUMMARY_CITY => '城市汇总', Common::LINK_LIST_ALL_TEAMS => '全部团队');
                                $result = $options + $result;
                            }
                        }
                        break;
                }
                break;
            case 'area-region-branch-team':
                switch ($level) {
                    case 1:
                        $result = Common::getOnePrivWdtCityStruct(Common::REDIS_CITYSTRUCT_AREAS);
                        break;
                    case 2:
                        $result = Region::getLinkedRegionsByAreaForWdt($parent);
                        break;
                    case 3:
                        $result = Branch::getLinkedBranchesByRegion($parent);
                        break;
                    case 4:
                        $result = MappingTeamCity::getLinkedTeamsByBranch($parent);
                        break;
                }
                break;
            case 'area-branch-sum-detail':
                $session_super = Yii::app()->user->getstate(Common::SESSION_SUPER);
                $session_branches = Yii::app()->user->getstate(Common::SESSION_BRANCHES);
                $has_all_data = in_array($session_super, Privileges::$super_admin) || $session_branches == 1;
            
                $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT);
                $privileges = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT, Common::REDIS_PRIVILEGES_FIELD) && !empty($priv[Common::REDIS_PRIVILEGES_FIELD]) ? unserialize($priv[Common::REDIS_PRIVILEGES_FIELD]) : array();
                $priv_areas = $privileges!==false && array_key_exists(Common::PRIVILEGE_TYPE_AREA, $privileges)&&!empty($privileges[Common::PRIVILEGE_TYPE_AREA]) ? $privileges[Common::PRIVILEGE_TYPE_AREA] : array();
                $priv_regions = $privileges!==false && array_key_exists(Common::PRIVILEGE_TYPE_REGION, $privileges)&&!empty($privileges[Common::PRIVILEGE_TYPE_REGION]) ? $privileges[Common::PRIVILEGE_TYPE_REGION] : array();
                $priv_branches = $privileges!==false && array_key_exists(Common::PRIVILEGE_TYPE_BRANCH, $privileges)&&!empty($privileges[Common::PRIVILEGE_TYPE_BRANCH]) ? $privileges[Common::PRIVILEGE_TYPE_BRANCH] : array();
            
                switch ($level) {
                    case 1:
                        $result = Common::getOnePrivWdtCityStruct(Common::REDIS_CITYSTRUCT_AREAS);
                        if ($has_all_data) {
                            $options = array(Common::LINK_LIST_SUMMARY_WHOLESITE => '整站汇总', Common::LINK_LIST_ALL_AREAS => '全部大区');
                            $result = $options + $result;
                            if (!array_key_exists(Common::UNKNOWN_AREA_ID, $result)) {
                                $unknown = array(Common::UNKNOWN_AREA_ID => '未知大区');
                                $result = $result + $unknown;
                            }
                        }
                        /* 只给予全分部权限用户全部大区
                         elseif (count($priv_areas) > 1) {
                            $options = array(Common::LINK_LIST_ALL_AREAS => '全部大区');
                            $result = $options + $result;
                        } */
                        break;
                    case 2:
                        if ($parent == Common::LINK_LIST_SUMMARY_WHOLESITE && $has_all_data) {
                            $result = array(Common::LINK_LIST_SUMMARY_WHOLESITE => '整站汇总');
                        } elseif ($parent == Common::LINK_LIST_ALL_AREAS && ($has_all_data || in_array($parent, $priv_areas))) {
                            $options = array(Common::LINK_LIST_ALL_AREAS => '全部大区', Common::LINK_LIST_ALL_REGIONS => '全部区域');
                            $result = $options + $result;
                        } else {
                            $result = Region::getLinkedRegionsByAreaForWdt($parent);
                            if (($has_all_data || in_array($parent, $priv_areas)) && $parent != Common::UNKNOWN_AREA_ID) {
                                $options = array(Common::LINK_LIST_SUMMARY_AREA => '大区汇总', Common::LINK_LIST_ALL_REGIONS => '全部区域');
                                $result = $options + $result;
                            }
                            if ($parent == Common::UNKNOWN_AREA_ID && !array_key_exists(Common::UNKNOWN_REGION_ID, $result)) {
                                $unknown = array(Common::UNKNOWN_REGION_ID => '未知区域');
                                $result = $result + $unknown;
                            }
                        }
                        break;
                    case 3:
                        if ($parent == Common::LINK_LIST_SUMMARY_WHOLESITE) {
                            $result = array(Common::LINK_LIST_SUMMARY_WHOLESITE => '整站汇总');
                        } elseif ($parent == Common::LINK_LIST_ALL_AREAS) {
                            $options = array(Common::LINK_LIST_ALL_AREAS => '全部大区');
                            $result = $options + $result;
                        } elseif ($parent == Common::LINK_LIST_ALL_REGIONS) {
                            $options = array(Common::LINK_LIST_ALL_REGIONS => '全部区域', Common::LINK_LIST_ALL_CITIES => '全部城市');
                            $result = $options + $result;
                        } elseif ($parent == Common::LINK_LIST_SUMMARY_AREA) {
                            $options = array(Common::LINK_LIST_SUMMARY_AREA => '大区汇总');
                            $result = $options + $result;
                        } else {
                            $result = Branch::getLinkedBranchesByRegion($parent);
                            if ($has_all_data || in_array($parent, $priv_regions)) {
                                $options = array(Common::LINK_LIST_SUMMARY_REGION => '区域汇总', Common::LINK_LIST_ALL_CITIES => '全部城市');
                                $result = $options + $result;
                            }
                        }
                        break;
                    case 4:
                        if ($parent == Common::LINK_LIST_SUMMARY_WHOLESITE) {
                            $result = array(Common::LINK_LIST_SUMMARY_WHOLESITE => '整站汇总');
                        } elseif ($parent == Common::LINK_LIST_ALL_AREAS) {
                            $options = array(Common::LINK_LIST_ALL_AREAS => '全部大区');
                            $result = $options + $result;
                        } elseif ($parent == Common::LINK_LIST_ALL_REGIONS) {
                            $options = array(Common::LINK_LIST_ALL_REGIONS => '全部区域');
                            $result = $options + $result;
                        } elseif ($parent == Common::LINK_LIST_ALL_CITIES) {
                            $options = array(Common::LINK_LIST_ALL_CITIES => '全部城市', Common::LINK_LIST_ALL_TEAMS => '全部团队');
                            $result = $options + $result;
                        } elseif ($parent == Common::LINK_LIST_SUMMARY_AREA) {
                            $options = array(Common::LINK_LIST_SUMMARY_AREA => '大区汇总');
                            $result = $options + $result;
                        } elseif ($parent == Common::LINK_LIST_SUMMARY_REGION) {
                            $options = array(Common::LINK_LIST_SUMMARY_REGION => '区域汇总');
                            $result = $options + $result;
                        } else {
                            $result = MappingTeamCity::getLinkedTeamsByBranch($parent);
                            if ($has_all_data || in_array($parent, $priv_branches)) {
                                $options = array(Common::LINK_LIST_SUMMARY_CITY => '城市汇总', Common::LINK_LIST_ALL_TEAMS => '全部团队');
                                $result = $options + $result;
                            }
                        }
                        break;
                }
                break;
            case 'warzone-city':
                switch ($level) {
                    case 1:
                        $result = Common::getOnePrivCityStruct(Common::REDIS_CITYSTRUCT_WARZONES);
                        break;
                    case 2:
                        $result = City::getLinkedCitiesByWarzone($parent);
                        break;
                }
                break;
            case 'first-second-cate':
                list($first_cates, $second_cates, $third_cates, $goods_cate_map) = Category::getCateData();
                switch ($level) {
                    case 1:
                        $result = $first_cates;        //用户具有权限的一级品类
                        break;
                    case 2:
                        $result = Category::getLinkedByParent($parent);
                        break;
                }
                break;
            case 'media-type-list':
                list($firstCateArr, $secondCateArr, $mediaCateMap) = MediaList::getCateData();        //获取用户具有权限的媒体
                switch ($level) {
                    case 1:
                        $result = $firstCateArr;
                        break;
                    case 2:
                        $result = array_key_exists($parent, $mediaCateMap) ? $mediaCateMap[$parent] : array();
                        break;
                }
                break;
        }
        return $result;
    }
}
