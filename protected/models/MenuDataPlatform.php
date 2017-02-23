<?php

/**
 * This is the model class for table "menu".
 *
 * The followings are the available columns in table 'menu':
 * @property integer $id
 * @property integer $data_resource_id
 * @property integer $department_id
 * @property integer $is_viewed_only_admin
 * @property integer $status
 * @property integer $menu_grade
 * @property integer $parent_id
 * @property integer $tab_state
 * @property string $menu_name
 * @property string $url
 */
class MenuDataPlatform extends CActiveRecord
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
        return 'data_menu';//原来链接的数据平台的menu表
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('data_resource_id, department_id, is_viewed_only_admin, status, menu_grade, parent_id, tab_state', 'numerical', 'integerOnly'=>true),
            array('menu_name', 'length', 'max'=>30),
            array('url', 'length', 'max'=>100),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, data_resource_id, department_id, is_viewed_only_admin, status, menu_grade, parent_id, tab_state, menu_name, url', 'safe', 'on'=>'search'),
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
            'data_resource_id' => 'Data Resource',
            'department_id' => 'Department',
            'is_viewed_only_admin' => '1表示只有管理员可见，0表示全部可见',
            'status' => '1可见，0隐藏',
            'menu_grade' => '数字1表示一级菜单，2表示二级菜单，二级菜单隶属于一级菜单，3同理，4为子菜单，隶属于二级或三级菜单',
            'parent_id' => '如果该值为0，说明其为顶层菜单',
            'tab_state' => '标签页状况:0无,1子菜单为标签页,2本身为标签页',
            'menu_name' => 'Menu Name',
            'url' => 'Url',
        );
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
        $result = array();
        if ($grade == 0)
            $menu_list = self::model()->findAll('url!=:competitor and url not like :cia',array(':competitor'=>'competitor', ':cia'=>'cia%'));
        else {
            $menu_list = self::model()->findAll('menu_grade=:grade and status=1 and url!=:competitor and url not like :cia', array(':grade'=>$grade, ':competitor'=>'competitor', ':cia'=>'cia%'));
        }
        foreach ($menu_list as $menu) {
            $result[$menu->id] = $menu->menu_name;
        }
        return $result;
    }
    
    /**
     * 获取子菜单
     * @param int $parent_id
     * @param int $level #2:二级菜单,3:三级菜单,4:末级菜单
     * @author chensm
     */
    public static function getSubMenu($parent_id, $menu_grade)
    {
        $menu = self::model()->findAll('parent_id=:parent_id and menu_grade=:menu_grade and status=1', array (
                ':parent_id' => $parent_id,
                ':menu_grade' => $menu_grade,
        ));
        return $menu;
    }
    
    /**
     * 装配分级菜单树
     * @param $menu    有效参数，执行类似model->find得到的结果集
     * @param $menu_range  指定的目录范围
     * @author chensm
     */
    private static function _assembleMenuTree($menu,$menu_range=array())
    {
        $data = array();     //全部菜单
        $grade = array();    //分级菜单
        $map = array();      //父子菜单
        $url2id = array();   //菜单URL对应ID
        $tabs = array();     //标签页父子菜单
    
        if(count($menu)){
            #分类数据
            foreach($menu as $v){
                $grade[$v->menu_grade][$v->id] = array('name'=>$v->menu_name,'url'=>$v->url);
                if ($v->tab_state == 2 && strpos($v->menu_name, '-') !== false) { //对于标签页菜单，剔除父菜单名称
                    $pos = strpos($v->menu_name, '-') + 1;
                    $v_name = substr($v->menu_name, $pos);
                } else {
                    $v_name = $v->menu_name;
                }
                $data[$v->id] = array(
                        'id' => $v->id,
                        'is_viewed_only_admin' => $v->is_viewed_only_admin,
                        'status' => $v->status,
                        'menu_grade' => $v->menu_grade,
                        'parent_id' => $v->parent_id,
                        'tab_state' => $v->tab_state,
                        'menu_name' => $v_name,
                        'url' => $v->url,
                );
                if ($v->url)    $url2id[$v->url] = $v->id;
                switch ($v->menu_grade) {
                    case 1 :
                        $level2 = self::getSubMenu($v->id, 2);
                        if(count($level2) > 0){
                            foreach ($level2 as $s) {
                                if (!empty($menu_range) && !in_array($s->id, $menu_range))
                                    continue;
                                $map[$v->id][$s->id] = array('name'=>$s->menu_name,'url'=>$s->url);
                            }
                        }else{
                            $map[$v->id] = array();
                        }
                        break;
                    case 2 :
                        $level3 = self::getSubMenu($v->id, 3);
                        if(count($level3) > 0){
                            foreach ($level3 as $t) {
                                if (!empty($menu_range) && !in_array($t->id, $menu_range))
                                    continue;
                                $map[$v->id][$t->id] = array('name'=>$t->menu_name,'url'=>$t->url);
                            }
                        }else{
                            $map[$v->id] = array();
                        }
    
                        if (empty($map[$v->id])) {
                            $level4 = self::getSubMenu($v->id, 4);
                            if(count($level4) > 0){
                                foreach ($level4 as $q) {
                                    if (!empty($menu_range) && !in_array($q->id, $menu_range))
                                        continue;
                                    $map[$v->id][$q->id] = array('name'=>$q->menu_name,'url'=>$q->url);
                                }
                            }else{
                                $map[$v->id] = array();
                            }
                        }
                        break;
                    case 3 :
                        $level4 = self::getSubMenu($v->id, 4);
                        if(count($level4) > 0){
                            foreach ($level4 as $q) {
                                if (!empty($menu_range) && !in_array($q->id, $menu_range))
                                    continue;
                                $map[$v->id][$q->id] = array('name'=>$q->menu_name,'url'=>$q->url);
                            }
                        }else{
                            $map[$v->id] = array();
                        }
                        if ($v->tab_state==2 && !strpos($v->menu_name, '导出')) {
                            if (!array_key_exists($v->parent_id, $tabs)) {
                                $tabs[$v->parent_id][] = $v->id;
                            } elseif (!in_array($v->id, $tabs[$v->parent_id])) {
                                $tabs[$v->parent_id][] = $v->id;
                            }
                        }
                        break;
                    case 4 :    //数据平台将标签页的菜单等级都标识为4
                        if (!strpos($v->menu_name, '导出')) {
                            if (!array_key_exists($v->parent_id, $tabs)) {
                                $tabs[$v->parent_id][] = $v->id;
                            } elseif (!in_array($v->id, $tabs[$v->parent_id])) {
                                $tabs[$v->parent_id][] = $v->id;
                            }
                        }
                        break;
                }
            }
        }
    
        return array($data, $grade, $map, $url2id, $tabs);
    }
    
    
    /**
     * 菜单数据放到缓存中，有效期1天
     */
    public static function setMenu()
    {
        $data = array();
        $grade = array();
        $map = array();
        $url2id = array();
        $tabs = array();
        $ret = self::model()->findAll('status=1');
    
        list($data,$grade,$map,$url2id,$tabs) = self::_assembleMenuTree($ret);
        Yii::app()->redis->getClient()->setex(Common::REDIS_COMMEN_MENU, 12*Common::REDIS_DURATION, serialize($data));
        Yii::app()->redis->getClient()->setex(Common::REDIS_COMMEN_MENU_GRADE, 12*Common::REDIS_DURATION, serialize($grade));
        Yii::app()->redis->getClient()->setex(Common::REDIS_COMMEN_MENU_MAP, 12*Common::REDIS_DURATION, serialize($map));
        Yii::app()->redis->getClient()->setex(Common::REDIS_COMMEN_MENU_TOID, 12*Common::REDIS_DURATION, serialize($url2id));
        Yii::app()->redis->getClient()->setex(Common::REDIS_COMMEN_MENU_TABS, 12*Common::REDIS_DURATION, serialize($tabs));
    }
    
    /**
     * 获取全部有效菜单
     * @param $cache    是否使用缓存，默认为使用
     */
    public static function getAllMenus($cache=true)
    {
        $results = array();
        if ($cache) {
            $menu = Yii::app()->redis->getClient()->get(Common::REDIS_COMMEN_MENU);
            $results = unserialize($menu);
        }
    
        if (empty($results)) {
            self::setMenu();
            $results = unserialize(Yii::app()->redis->getClient()->get(Common::REDIS_COMMEN_MENU));
        }
        return $results;
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
                if (!strpos($menu->menu_name, '导出')) {
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
                                    if ($parent2['parent_id'] === '0') {
                                        $result[$parent2['id']][$parent3['id']][$menu->id] = array();
                                    } else {
                                        $parent1 = array_key_exists($parent2['parent_id'], $all_menu) ? $all_menu[$parent2['parent_id']] : array();
                                        if (array_key_exists('menu_grade', $parent1) && $parent1['menu_grade']==1) {
                                            $result[$parent1['id']][$parent2['id']][$parent3['id']][$menu->id] = array();
                                        }
                                    }
                                }
                            }
                            break;
                    }
                }
            }
        }
        return $result;
    }
}
