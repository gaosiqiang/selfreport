<?php

/**
 * This is the model class for table "dim_category".
 *
 * The followings are the available columns in table 'dim_category':
 * @property integer $id
 * @property string $name
 * @property integer $parent_id
 * @property integer $level
 */
class Category extends CActiveRecord
{
    /* (non-PHPdoc)
     * @see CActiveRecord::getDbConnection()
    */
    public function getDbConnection() {
        return Yii::app()->pdb;
    }
    
    /**
     * Returns the static model of the specified AR class.
     * @return Category the static model class
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
        return 'dim_category';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name, parent_id, level', 'required'),
            array('parent_id, level', 'numerical', 'integerOnly'=>true),
            array('name', 'length', 'max'=>100),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, name, parent_id, level', 'safe', 'on'=>'search'),
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
            'name' => 'Name',
            'parent_id' => 'Parent',
            'level' => 'Level',
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
        $criteria->compare('parent_id',$this->parent_id);
        $criteria->compare('level',$this->level);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }
    
    /**
     * 获取子分类
     * @param int $parent_id
     * @param int $level #2:二级分类,3:三级分类
     * @author chensm
     */
    public static function getSubCates($parent_id, $level)
    {
        $cate = self::model()->findAll('parent_id=:parent_id and level=:level', array (
                ':parent_id' => $parent_id,
                ':level' => $level,
        ));
        return $cate;
    }

    /**
     * 获取当前用户具有权限的品类(仅限于一级品类)
     * @return array
     */
    public static function getUserCategories()
    {
        $result = array();
        $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT);
        $categories = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT, Common::REDIS_PRIVILEGES_SELFREPORT_CATEGORY) ? $priv[Common::REDIS_PRIVILEGES_SELFREPORT_CATEGORY] : '';
    
        if (!empty($categories)) {
            $result = unserialize($categories);
        }
    
        if (empty($result)) {
            $super = Yii::app()->user->getstate(Common::SESSION_SUPER);
            $categories = Yii::app()->user->getstate(Common::SESSION_CATEGORY);
            if (in_array($super, Privileges::$super_admin) || $categories == 1) {
                $models = self::model()->findAll('level=1');
            } else {
                $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT);
                $privileges = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT, Common::REDIS_PRIVILEGES_FIELD) && !empty($priv[Common::REDIS_PRIVILEGES_FIELD]) ? unserialize($priv[Common::REDIS_PRIVILEGES_FIELD]) : array();
                
                $priv_categories = array_key_exists(Common::PRIVILEGE_TYPE_CATEGORY, $privileges)&&!empty($privileges[Common::PRIVILEGE_TYPE_CATEGORY]) ? $privileges[Common::PRIVILEGE_TYPE_CATEGORY] : array();
                if (!empty($priv_categories)) {
                    $criteria=new CDbCriteria;
                    $criteria->addInCondition('id', $priv_categories);
                    $models = self::model()->findAll($criteria);
                }
            }
            
            if(!empty($models)){
                foreach($models as $model){
                    $result[$model->id] = $model->name;
                }
                $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT);
                $priv[Common::REDIS_PRIVILEGES_SELFREPORT_CATEGORY] = serialize($result);
            }
        }
        return $result;
    }
    
    /**
     * 根据父级分类获取子级分类，用于联动列表
     * @param string|int $parent    参数可能是分类ID或名称
     */
    public static function getLinkedByParent($parent)
    {
        $result = array();
        if (is_numeric($parent)) {
            list($first_cates, $second_cates, $third_cates, $goods_cate_map) = self::getCateData();
            $result = array_key_exists($parent, $goods_cate_map) ? $goods_cate_map[$parent] : array();
        } else {
            list($first_cates, $second_cates, $third_cates, $goods_cate_map) = self::getCateDataByName();
            $result = array_key_exists($parent, $goods_cate_map) ? $goods_cate_map[$parent] : array();
        }
        return $result;
    }
    
    /**
     * 每个商品分类下的数据放到缓存中，有效期1天
     * @author fuyp
     */
    public static function setCateData()
    {
        $data = array();
        $map = array();
        
        //取品类权限(一级品类)
        $super = Yii::app()->user->getstate(Common::SESSION_SUPER);
        $categories = Yii::app()->user->getstate(Common::SESSION_CATEGORY);
        if (in_array($super, Privileges::$super_admin) || $categories == 1) {
            $models = self::model()->findAll('level=1');
        } else {
            $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT);
            $privileges = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT, Common::REDIS_PRIVILEGES_FIELD) && !empty($priv[Common::REDIS_PRIVILEGES_FIELD]) ? unserialize($priv[Common::REDIS_PRIVILEGES_FIELD]) : array();
            
            $priv_categories = array_key_exists(Common::PRIVILEGE_TYPE_CATEGORY, $privileges)&&!empty($privileges[Common::PRIVILEGE_TYPE_CATEGORY]) ? $privileges[Common::PRIVILEGE_TYPE_CATEGORY] : array();
            if (!empty($priv_categories)) {
                $criteria=new CDbCriteria;
                $criteria->addInCondition('id', $priv_categories);
                $models = self::model()->findAll($criteria);
            }
        }
        
        if (!empty($models)) {
            foreach ($models as $model) {
                $data[$model->level][$model->id] = $model->name;
                
                $level2 = self::getSubCates($model->id, 2);
                if(!empty($level2)){
                    foreach ($level2 as $s) {
                        $data[$s->level][$s->id] = $s->name;
                        $map[$model->id][$s->id] = $s->name;
                        
                        $level3 = self::getSubCates($s->id, 3);
                        if(!empty($level3)){
                            foreach ($level3 as $t) {
                                $data[$t->level][$t->id] = $t->name;
                                $map[$s->id][$t->id] = $t->name;
                            }
                        }else{
                            $map[$s->id] = array();
                        }
                    }
                }else{
                    $map[$model->id] = array();
                }
            }
        }
        
        $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_SELFREPORT_CATEGORY);
        $priv[Common::REDIS_SELFREPORT_GOODS_CATE] = serialize($data);
        $priv[Common::REDIS_SELFREPORT_GOODS_CATE_MAP] = serialize($map);
        Yii::app()->redis->getClient()->setTimeout(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_SELFREPORT_CATEGORY, 12*Common::REDIS_DURATION);
        
        return array($data, $map);
    }
    
    /**
     * 每个商品分类下的数据放到缓存中，有效期1天(通过name传值)
     */
    public static function setCateDataByName()
    {
        $data = array();
        $map = array();
        
        $super = Yii::app()->user->getstate(Common::SESSION_SUPER);
        $categories = Yii::app()->user->getstate(Common::SESSION_CATEGORY);
        if (in_array($super, Privileges::$super_admin) || $categories == 1) {
            $models = self::model()->findAll();
        } else {
            $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT);
            $privileges = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT, Common::REDIS_PRIVILEGES_FIELD) && !empty($priv[Common::REDIS_PRIVILEGES_FIELD]) ? unserialize($priv[Common::REDIS_PRIVILEGES_FIELD]) : array();
        
            $priv_categories = array_key_exists(Common::PRIVILEGE_TYPE_CATEGORY, $privileges)&&!empty($privileges[Common::PRIVILEGE_TYPE_CATEGORY]) ? $privileges[Common::PRIVILEGE_TYPE_CATEGORY] : array();
            if (!empty($priv_categories)) {
                $criteria=new CDbCriteria;
                $criteria->addInCondition('id', $priv_categories);
                $models = self::model()->findAll($criteria);
            }
        }
        
        if (!empty($models)) {
            foreach ($models as $model) {
                $data[$model->level][$model->name] = $model->name;
        
                $level2 = self::getSubCates($model->id, 2);
                if(!empty($level2)){
                    foreach ($level2 as $s) {
                        $data[$s->level][$s->name] = $s->name;
                        $map[$model->name][$s->name] = $s->name;
        
                        $level3 = self::getSubCates($s->id, 3);
                        if(!empty($level3)){
                            foreach ($level3 as $t) {
                                $data[$t->level][$t->name] = $t->name;
                                $map[$s->name][$t->name] = $t->name;
                            }
                        }else{
                            $map[$s->name] = array();
                        }
                    }
                }else{
                    $map[$model->name] = array();
                }
            }
        }
        
        $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_SELFREPORT_CATEGORY);
        $priv[Common::REDIS_SELFREPORT_GOODS_CATE_NAME] = serialize($data);
        $priv[Common::REDIS_SELFREPORT_GOODS_CATE_NAME_MAP] = serialize($map);
        Yii::app()->redis->getClient()->setTimeout(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_SELFREPORT_CATEGORY, 12*Common::REDIS_DURATION);
        
        return array($data, $map);
    }
    
    /**
     * 获取商品分类数据
     */
    public static function getCateData()
    {
        $result = array();
        $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_SELFREPORT_CATEGORY);
        $goods_cate_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_SELFREPORT_CATEGORY, Common::REDIS_SELFREPORT_GOODS_CATE) ? $priv[Common::REDIS_SELFREPORT_GOODS_CATE] : '';
        $goods_cate_map_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_SELFREPORT_CATEGORY, Common::REDIS_SELFREPORT_GOODS_CATE_MAP) ? $priv[Common::REDIS_SELFREPORT_GOODS_CATE_MAP] : '';
        
        if (!empty($goods_cate_cache) && !empty($goods_cate_map_cache)) {
            $goods_cate = unserialize($goods_cate_cache);
            $goods_cate_map = unserialize($goods_cate_map_cache);
        }
        
        if (empty($goods_cate) || empty($goods_cate_map)) {
            list($goods_cate, $goods_cate_map) = self::setCateData();
        }
        
        $first_cates = array_key_exists(1, $goods_cate) ? $goods_cate[1] : array();
        $second_cates = array_key_exists(2, $goods_cate) ? $goods_cate[2] : array();
        $third_cates = array_key_exists(3, $goods_cate) ? $goods_cate[3] : array();
        return array($first_cates, $second_cates, $third_cates, $goods_cate_map);
    }
    
    /**
     * 获取商品分类数据
     */
    public static function getCateDataByName()
    {   
        $result = array();
        $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_SELFREPORT_CATEGORY);
        $goods_cate_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_SELFREPORT_CATEGORY, Common::REDIS_SELFREPORT_GOODS_CATE_NAME) ? $priv[Common::REDIS_SELFREPORT_GOODS_CATE_NAME] : '';
        $goods_cate_map_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_SELFREPORT_CATEGORY, Common::REDIS_SELFREPORT_GOODS_CATE_NAME_MAP) ? $priv[Common::REDIS_SELFREPORT_GOODS_CATE_NAME_MAP] : '';
        
        if (!empty($goods_cate_cache) && !empty($goods_cate_map_cache)) {
            $goods_cate = unserialize($goods_cate_cache);
            $goods_cate_map = unserialize($goods_cate_map_cache);
        }
        
        if (empty($goods_cate) || empty($goods_cate_map)) {
            list($goods_cate, $goods_cate_map) = self::setCateDataByName();
        }
        
        $first_cates = array_key_exists(1, $goods_cate) ? $goods_cate[1] : array();
        $second_cates = array_key_exists(2, $goods_cate) ? $goods_cate[2] : array();
        $third_cates = array_key_exists(3, $goods_cate) ? $goods_cate[3] : array();
        return array($first_cates, $second_cates, $third_cates, $goods_cate_map);
    }
    
    /**
     * 根据一级分类ID获取名称
     * $param  int $firstCate 一级分类ID
     * $return string 一级分类名称
     */
    public static function getNameById($firstCate)
    {
        $cateData = self::getCateData();
        $firstData = $cateData[0];
        return isset($firstData[$firstCate]) ? $firstData[$firstCate] : '未知';
        
    }
    
    /**
     * 根据一级分类名称获取ID
     * $param string $firstCate 一级分类名称
     * $return int 一级分类名称
     */
    public static function getFirstCateIdByName($firstCate)
    {
        $model = self::model()->find('name=:name and parent_id=0 and level=1', array(':name'=>$firstCate));
        return isset($model) ? $model->id : 0;
    }
    
}