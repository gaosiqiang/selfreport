<?php

/**
 * This is the model class for table "log".
 *
 * The followings are the available columns in table 'log':
 * @property integer $id
 * @property string $username
 * @property integer $menu
 * @property string $menu_name
 * @property integer $ip
 * @property integer $time
 */
class Log extends CActiveRecord
{
    public static $maps = array(
            'configure/index' => '10001',
            'configure/create' => '10002',
            'configure/toggle' => '10003',
            'configure/update' => '10004',
            'configure/delete' => '10005',
            
            'usergroup/index' => '11001',
            'usergroup/create' => '11002',
            'usergroup/update' => '11003',
            'usergroup/delete' => '11004',
            
            'admin/index' => '12001',
            'admin/create' => '12002',
            'admin/toggle' => '12003',
            'admin/prolong' => '12004',
            'admin/update' => '12005',
            'admin/authority' => '12006',
            'admin/delete' => '12007',
            'admin/setting' => '12008',
            
            'announcement/admin' => '13001',
            'announcement/create' => '13002',
            'announcement/update' => '13003',
            'announcement/delete' => '13004',
            'announcement/view' => '13005',
            'announcement/index' => '13006',
            
            'log/index' => '14001',
            
            'datasource/index' => '15001',
            'datasource/create' => '15002',
            'datasource/update' => '15003',
    );
    
    public static $menus = array(
            '0' => '',
            '10001' => '报表配置',
            '10002' => '报表配置-添加',
            '10003' => '报表配置-可见隐藏',
            '10004' => '报表配置-编辑',
            '10005' => '报表配置-删除',
            
            '11001' => '用户组管理',
            '11002' => '用户组管理-添加',
            '11003' => '用户组管理-编辑',
            '11004' => '用户组管理-删除',
            
            '12001' => '用户管理',
            '12002' => '用户管理-添加',
            '12003' => '用户管理-禁用启用',
            '12004' => '用户管理-延期',
            '12005' => '用户管理-编辑',
            '12006' => '用户管理-权限',
            '12007' => '用户管理-删除',
            '12008' => '用户管理-设置',
            
            '13001' => '公告管理',
            '13002' => '公告管理-添加',
            '13003' => '公告管理-编辑',
            '13004' => '公告管理-删除',
            '13005' => '公告管理-查看',
            '13006' => '公告',
            
            '14001' => '访问日志',
            
            '15001' => '数据源管理',
            '15002' => '数据源管理-添加',
            '15003' => '数据源管理-编辑',
    );
    
    /**
     * Returns the static model of the specified AR class.
     * @return Log the static model class
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
        return 'log';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('username, menu, ip, time', 'required'),
            array('menu, ip, time', 'numerical', 'integerOnly'=>true),
            array('username', 'length', 'max'=>32),
            array('menu_name', 'length', 'max'=>30),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, username, menu, menu_name, ip, time', 'safe', 'on'=>'search'),
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
            'username' => 'Username',
            'menu' => 'Menu',
            'menu_name' => 'Menu Name',
            'ip' => 'Ip',
            'url' => 'Url',
            'time' => 'Time',
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

        $criteria = new CDbCriteria();
        
        $criteria->compare('username', $this->username);
        $criteria->compare('menu', $this->menu);
        $criteria->compare('url', $this->url, true);
        $criteria->order = 'time DESC';
        return new CActiveDataProvider($this, array(
            'criteria' => $criteria
        ));
    }
    
    /**
     * 添加新日志
     * @param string $route 路由
     * @param string $report_id 报表ID
     */
    public static function createLog($route, $report_id)
    {
        if (!empty($route))
        {
            $menu_id = 0;
            $menu_name = '';
            if (isset(self::$maps[$route])) {
                $menu_id = self::$maps[$route];
                $menu_name = array_key_exists($menu_id, self::$menus) ? self::$menus[$menu_id] : '';
            } elseif (!empty($report_id)) {
                $report_menus = Menu::getAllReportMenus();
                $menu_id = array_key_exists($report_id, $report_menus) ? $report_menus[$report_id]['id'] : 0;
                $menu_name = array_key_exists($report_id, $report_menus) ? $report_menus[$report_id]['menu_name'] : '';
            }
            $log = new Log();
            $log->username = Yii::app()->user->name;
            $log->menu = $menu_id;
            $log->menu_name = $menu_name;
            $log->ip = ip2long(Yii::app()->request->getUserHostAddress());
            $log->time = time();
            $log->url = Yii::app()->request->requestUri;
            if ($log->save())
                return true;
        }
    }
    
    /**
     * 获取所有菜单
     */
    public static function getAllMenus()
    {
        $results = array();
        $models = Menu::model()->findAll('status=1');
        if ($models) {
            foreach ($models as $model) {
                if (!empty($model->report_id)) {
                    $results[$model->id] = $model->menu_name;
                }
            }
        }
        return self::$menus + $results;
    }
}