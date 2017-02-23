<?php

/**
 * This is the model class for table "menu".
 *
 * The followings are the available columns in table 'menu':
 * @property integer $id
 * @property integer $platform
 * @property integer $is_viewed_only_admin
 * @property integer $status
 * @property integer $menu_grade
 * @property integer $parent_id
 * @property integer $tab_state
 * @property string $menu_name
 * @property string $report_id
 */
class Menu extends CActiveRecord
{
    public static $status = array('1'=>'可见','0'=>'隐藏');
    public static $menu_grade = array('2'=>'二级');
    public static $platforms = array('9'=>'自助报表平台');//,'2'=>'决策系统'
    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'menu';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('platform, is_viewed_only_admin, status, menu_grade, parent_id, tab_state', 'numerical', 'integerOnly'=>true),
            array('menu_name', 'length', 'max'=>30),
            array('report_id', 'length', 'max'=>16),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, platform, is_viewed_only_admin, status, menu_grade, parent_id, tab_state, menu_name, report_id', 'safe', 'on'=>'search'),
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
            'platform' => '分发平台，1数据平台，2决策系统，9自助报表平台',
            'is_viewed_only_admin' => '1表示只有管理员可见，0表示全部可见',
            'status' => '1可见，0隐藏',
            'menu_grade' => '菜单层级',
            'parent_id' => '如果该值为0，说明其为顶层菜单',
            'tab_state' => '标签页状况:2本身为标签页',
            'menu_name' => '菜单名称',
            'report_id' => '自助报表ID',
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
        $criteria->compare('platform',$this->platform);
        $criteria->compare('is_viewed_only_admin',$this->is_viewed_only_admin);
        $criteria->compare('status',$this->status);
        $criteria->compare('menu_grade',$this->menu_grade);
        $criteria->compare('parent_id',$this->parent_id);
        $criteria->compare('tab_state',$this->tab_state);
        $criteria->compare('menu_name',$this->menu_name,true);
        $criteria->compare('report_id',$this->report_id,true);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Menu the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    /**
     * 获取菜单ID与名称对应关系
     * @param $grade 菜单级别
     */
    public static function getMenuListForSelect($grade=0)
    {
        //$result = array('0'=>'无');
        $result = array();
        if ($grade == 0)
            $menu_list = self::model()->findAll();
        else {
            $menu_list = self::model()->findAll('platform=9 and status=1 and menu_grade=:grade', array(':grade'=>$grade));
        }
        foreach ($menu_list as $menu) {
            $result[$menu->id] = $menu->menu_name;
        }
        return $result;
    }
    
    /**
     * 获取全部有效菜单
     * @param $cache    是否使用缓存，默认为使用
     */
    public static function getAllMenus($cache=true)
    {
        $results = array();
        if ($cache) {
            $menu = Yii::app()->redis->getClient()->get(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_SELFREPORT_ALLMENU);
            $results = unserialize($menu);
        }
        
        if (empty($results)) {
            $models = self::model()->findAll('platform=9 and status=1');
            if ($models) {
                foreach ($models as $model) {
                    $results[$model->id] = array(
                            'id' => $model->id,
                            'menu_name' => $model->menu_name,
                            'menu_grade' => $model->menu_grade,
                            'parent_id' => $model->parent_id,
                            'tab_state' => $model->tab_state,
                            'report_id' => $model->report_id,
                            'is_viewed_only_admin' => $model->is_viewed_only_admin,
                    );
                }
            }
            Yii::app()->redis->getClient()->setex(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_SELFREPORT_ALLMENU, 12*Common::REDIS_DURATION, serialize($results));
        }
        return $results;
    }
    
    /**
     * 获取子菜单
     * @param int $parent_id
     * @param int $level #2:二级菜单,3:三级菜单,4:末级菜单
     * @author chensm
     */
    public static function getSubMenu($parent_id, $menu_grade)
    {
        $menu = self::model()->findAll('parent_id=:parent_id and menu_grade=:menu_grade and platform=9 and status=1', array (
                ':parent_id' => $parent_id,
                ':menu_grade' => $menu_grade,
        ));
        return $menu;
    }
    
    /**
     * 装配分级菜单树
     * @param $menus    执行类似model->findAll得到的结果集
     * @param $cache    是否使用缓存，默认为使用
     */
    public static function constructMenuTree($menus,$cache=true)
    {
        $result = array();
        $all_menu = self::getAllMenus($cache);
        if (!empty($all_menu) && !empty($menus)) {
            foreach ($menus as $menu) {
                switch ($menu->menu_grade) {
                    case 1:
                        if (!array_key_exists($menu->id, $result)) {
                            //如果子菜单在父菜单前读取到，就会先保存到数组中，再次读到父菜单时就不可以将其置为空数组
                            $result[$menu->id] = array();
                        }
                        break;
                    case 2:
                        $parent = array_key_exists($menu->parent_id, $all_menu) ? $all_menu[$menu->parent_id] : array();
                        if (array_key_exists('menu_grade', $parent) && $parent['menu_grade']==1 && !isset($result[$menu->parent_id][$menu->id])) {
                            //如果子菜单在父菜单前读取到，就会先保存到数组中，再次读到父菜单时就不可以将其置为空数组
                            $result[$menu->parent_id][$menu->id] = array();
                        }
                        break;
                    case 3:
                        $parent2 = array_key_exists($menu->parent_id, $all_menu) ? $all_menu[$menu->parent_id] : array();
                        if (array_key_exists('parent_id', $parent2)) {
                            $parent1 = array_key_exists($parent2['parent_id'], $all_menu) ? $all_menu[$parent2['parent_id']] : array();
                            if (array_key_exists('menu_grade', $parent1) && $parent1['menu_grade']==1 && !isset($result[$parent1['id']][$parent2['id']][$menu->id])) {
                                //如果子菜单在父菜单前读取到，就会先保存到数组中，再次读到父菜单时就不可以将其置为空数组
                                $result[$parent1['id']][$parent2['id']][$menu->id] = array();
                            }
                        }
                        break;
                    case 4:
                        $parent3 = array_key_exists($menu->parent_id, $all_menu) ? $all_menu[$menu->parent_id] : array();
                        if (array_key_exists('parent_id', $parent3)) {
                            $parent2 = array_key_exists($parent3['parent_id'], $all_menu) ? $all_menu[$parent3['parent_id']] : array();
                            if (array_key_exists('parent_id', $parent2)) {
                                $parent1 = array_key_exists($parent2['parent_id'], $all_menu) ? $all_menu[$parent2['parent_id']] : array();
                                if (array_key_exists('menu_grade', $parent1) && $parent1['menu_grade']==1) {
                                    $result[$parent1['id']][$parent2['id']][$parent3['id']][$menu->id] = array();
                                }
                            }
                        }
                        break;
                }
            }
        }
        return $result;
    }
    
    /**
     * 根据菜单树获取所有菜单ID
     * @param array $menu_tree    由self::constructMenuTree()生成的菜单树
     */
    public static function getMenuIDByTree($menu_tree)
    {
        $result = array();
        if (!empty($menu_tree)) {
            foreach ($menu_tree as $first_id => $second_menu_arr) {
                $result[$first_id] = $first_id;
                if (!empty($second_menu_arr)) {
                    foreach ($second_menu_arr as $second_id => $third_menu_arr) {
                        $result[$second_id] = $second_id;
                        if (!empty($third_menu_arr)) {
                            foreach ($third_menu_arr as $third_id => $fourth_menu_arr) {
                                $result[$third_id] = $third_id;
                                if (!empty($fourth_menu_arr)) {
                                    foreach ($fourth_menu_arr as $fourth_id => $fourth_arr) {
                                        $result[$fourth_id] = $fourth_id;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }
    
    /**
     * 根据报表获取菜单(所有平台)
     * @param string $report_id
     * @author chensm
     */
    public static function getMenuByReportID($report_id)
    {
        $menu = self::model()->find('report_id=:report_id', array (
                ':report_id' => $report_id,
        ));
        return $menu;
    }
    
    /**
     * 取管理菜单或非管理菜单 非管理菜单可以包含 大区管理 和 战区管理
     * @param int $is_viewed_only_admin 0|1 默认为0
     * @return array
     */
    public static function getAdminMenuOrNot($is_viewed_only_admin=0)
    {
        $ids = array();
        $menus = self::model()->findAll('is_viewed_only_admin=:is_viewed_only_admin and platform=9 and status=1', array (
                ':is_viewed_only_admin' => $is_viewed_only_admin,
        ));
        return $menus;
    }
    
    /**
     * 获取全部有效报表菜单(所有平台)
     * @param $cache    是否使用缓存，默认为使用
     */
    public static function getAllReportMenus($cache=true)
    {
        $results = array();
        if ($cache) {
            $menu = Yii::app()->redis->getClient()->get(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_SELFREPORT_REPORT_MENU);
            $results = unserialize($menu);
        }
    
        //如果unserialize无法反序列化则会返回false
        if ($results === false) {
            $results = array();
        }
        
        if (empty($results)) {
            $models = self::model()->findAll('status=1');
            if ($models) {
                foreach ($models as $model) {
                    if (!empty($model->report_id)) {
                        $results[$model->report_id] = array(
                                'id' => $model->id,
                                'platform' => $model->platform,
                                'tab_state' => $model->tab_state,
                                'menu_name' => $model->menu_name,
                                'report_id' => $model->report_id,
                        );
                    }
                }
            }
            Yii::app()->redis->getClient()->setex(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_SELFREPORT_REPORT_MENU, 12*Common::REDIS_DURATION, serialize($results));
        }
        return $results;
    }
    
    /**
     * 获取全部为标签页菜单的报表ID
     * @param $cache    是否使用缓存，默认为使用
     */
    public static function getTabReports($cache=true)
    {
        $results = array();
        if ($cache) {
            $tab_reports = Yii::app()->redis->getClient()->get(Common::REDIS_COMMEN_TAB_REPORTS);
            $results = unserialize($tab_reports);
        }
        
        //如果unserialize无法反序列化则会返回false
        if ($results === false) {
            $results = array();
        }
        
        if (empty($results)) {
            $models = self::model()->findAll('platform=9 and status=1 and tab_state=2');
            if ($models) {
                foreach ($models as $model) {
                    if (!empty($model->report_id)) {
                        $results[$model->parent_id][$model->report_id] = array(
                                'id' => $model->id,
                                'menu_name' => $model->menu_name,
                                'report_id' => $model->report_id,
                        );
                    }
                }
            }
            Yii::app()->redis->getClient()->setex(Common::REDIS_COMMEN_TAB_REPORTS, 12*Common::REDIS_DURATION, serialize($results));
        }
        return $results;
    }
    
    /**
     * 对分发至其他平台的报表，获取其所在平台的菜单，以parent_id集合
     */
    public static function getReportsInDeliverPlat($platform)
    {
        $results = array();
        $models = self::model()->findAll('platform=:platform and status=1', array(':platform'=>$platform));
        if ($models) {
            foreach ($models as $model) {
                if (!empty($model->report_id)) {
                    $results[$model->parent_id][$model->id] = array(
                            'id' => $model->id,
                            'menu_name' => $model->menu_name,
                            'menu_grade' => $model->menu_grade,
                            'parent_id' => $model->parent_id,
                            'tab_state' => $model->tab_state,
                            'report_id' => $model->report_id,
                    );
                }
            }
        }
        return $results;
    }
    
    /**
     * 获取全部有效菜单，完整表包括所有平台
     */
    public static function getAllMenusTruely()
    {
        $results = array();
        $models = self::model()->findAll('status=1');
        if ($models) {
            foreach ($models as $model) {
                $results[$model->id] = array(
                        'id' => $model->id,
                        'menu_name' => $model->menu_name,
                        'menu_grade' => $model->menu_grade,
                        'parent_id' => $model->parent_id,
                        'tab_state' => $model->tab_state,
                        'report_id' => $model->report_id,
                        'is_viewed_only_admin' => $model->is_viewed_only_admin,
                );
            }
        }
        return $results;
    }
    
    /**
     * 检查报表是否平台自有，如果数组则含有就算无需全部
     * @param string|array $report_id
     * @return boolean
     */
    public static function checkSelfOwn($report_id)
    {
        if (is_array($report_id)) {
            $criteria=new CDbCriteria;
            $criteria->compare('platform', 9);
            $criteria->addInCondition('report_id',$report_id);
            $models = self::model()->findAll($criteria);
        } else {
            $models = self::model()->findAll('platform=9 and report_id=:report_id',array(':report_id'=>$report_id));
        }
        
        if (isset($models) && !empty($models)) {
            return true;
        } else {
            return false;
        }
    }


}
