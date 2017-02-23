<?php
/**
 * 通用类
 * @author sangxiaolong
 */
class Common
{
    //////////////////////////////////////////////////// Redis相关 ////////////////////////////////////////////////////
    
    const REDIS_DURATION = 3600;        //有效单位时长
    
    //身份验证
    const REDIS_ACCESS = '_dc_access:';
    const REDIS_ACCESS_LOGINFO = ':loginfo';
    const REDIS_ACCESS_IP = 'ip';
    const REDIS_ACCESS_USER_AGENT = 'useragent';
    const REDIS_ACCESS_STATUS = 'status';        //0未登录，1密码验证通过，2短信验证通过
    const REDIS_ACCESS_SYSTEM = ':system';                    //有权限的平台名
    const REDIS_ACCESS_SYSTEM_DCPLAT = 'dcplat';              //数据平台
    const REDIS_ACCESS_SYSTEM_DECISION = 'decision';          //决策系统
    const REDIS_ACCESS_SYSTEM_ANALYTICS = 'analytics';        //流量分析系统
    const REDIS_ACCESS_SYSTEM_DSFHZ = 'dsfhz';                //第三方平台
    const REDIS_ACCESS_SYSTEM_SELFREPORT = 'selfreport';      //自助报表系统
    const REDIS_ACCESS_SYSTEM_SELFQUERY = 'selfquery';        //即席查询
    const REDIS_VERIFY_CODE = '_dc_verifycode:';
    
    const REDIS_V_ACCESS_STATUS_NONE = '0';        //0未登录
    const REDIS_V_ACCESS_STATUS_PW = '1';        //1密码验证通过
    const REDIS_V_ACCESS_STATUS_SMS = '2';        //2短信验证通过
    
    //用户权限保存
    const REDIS_PRIVILEGES_SELFREPORT = ':privileges:selfreport';
    const REDIS_PRIVILEGES_FIELD = 'privileges';
    const REDIS_PRIVILEGES_REPORTS = 'reports';
    const REDIS_PRIVILEGES_SELFREPORT_MENU = 'menu';
    const REDIS_PRIVILEGES_SELFREPORT_CATEGORY = 'category';    //品类权限
    
    //根据用户权限构造数据结构缓存
    const REDIS_SELFREPORT_ALLMENU = ':selfreport:allmenu';    //菜单缓存
    const REDIS_SELFREPORT_REPORT_MENU = ':selfreport:reportmenu';    //菜单缓存
    
    const REDIS_SELFREPORT_CATEGORY = ':selfreport:category';    //品类缓存
    const REDIS_SELFREPORT_GOODS_CATE = 'goods_cate';    //权限品类分级(ID为键)
    const REDIS_SELFREPORT_GOODS_CATE_MAP = 'goods_cate_map';    //品类对应关系(ID为键)
    const REDIS_SELFREPORT_GOODS_CATE_NAME = 'goods_cate_name';    //权限品类分级(名称为键)
    const REDIS_SELFREPORT_GOODS_CATE_NAME_MAP = 'goods_cate_name_map';    //品类对应关系(名称为键)
    
    const REDIS_SELFREPORT_MEDIA = ':selfreport:media';    //媒体缓存
    const REDIS_SELFREPORT_MEDIA_CATE = 'media_cate';    //媒体级别缓存
    const REDIS_SELFREPORT_MEDIA_NO_LINKAGE = 'media_no_linkage';    //媒体级别缓存
    const REDIS_SELFREPORT_MEDIA_CATE_MAP = 'media_cate_map';    //媒体级别映射缓存
    
    const REDIS_CITYSTRUCT = ':selfreport:group_city_struct';      //用于团购业务的城市相关结构
    const REDIS_CITYSTRUCT_WDT = ':selfreport:wdt_branch_struct';  //用于网店通业务的分部相关结构
    const REDIS_CITYSTRUCT_AREAS = 'areas';                        //大区列表
    const REDIS_CITYSTRUCT_REGIONS = 'regions';                    //区域列表
    const REDIS_CITYSTRUCT_WARZONES = 'warzones';                  //战区列表
    const REDIS_CITYSTRUCT_CITIES = 'cities';                      //城市(分部)列表
    const REDIS_CITYSTRUCT_AREA_REGIONS = 'area_regions';          //大区-区域
    const REDIS_CITYSTRUCT_AREA_NAME_REGIONS = 'area_name_regions';          //大区-区域-名称为键
    const REDIS_CITYSTRUCT_AREA_CITIES = 'area_cities';            //大区-城市(分部)-ID为键
    const REDIS_CITYSTRUCT_AREA_NAME_CITIES = 'area_name_cities';            //大区-城市(分部)-名称为键
    const REDIS_CITYSTRUCT_REGION_CITIES = 'region_cities';        //区域-城市(分部)-ID为键
    const REDIS_CITYSTRUCT_REGION_NAME_CITIES = 'region_name_cities';        //区域-城市(分部)-名称为键
    const REDIS_CITYSTRUCT_WARZONE_CITIES = 'warzone_cities';      //战区-城市(分部)-ID为键
    const REDIS_CITYSTRUCT_WARZONE_NAME_CITIES = 'warzone_name_cities';      //战区-城市(分部)-名称为键
    const REDIS_CITYSTRUCT_CITY_TEAMS = 'city_teams';      //城市(分部)-团队-ID为键
    const REDIS_CITYSTRUCT_CITY_NAME_TEAMS = 'city_name_teams';      //城市(分部)-团队-名称为键
    
    //缓存报表配置中Step2.查询配置用到的项
    const REDIS_REPORT_CONFIG = ':selfreport:configure';
    const REDIS_REPORT_CONFIG_TABLES = 'tables';                        //已选择的表
    const REDIS_REPORT_CONFIG_ALIAS_TABLE = 'alias_table';              //表别名与表对应
    const REDIS_REPORT_CONFIG_JOIN_MODAL_ALIAS = 'join_modal_alias';    //已添加表关联的表别名
    const REDIS_REPORT_CONFIG_COLUMNS = 'columns';                      //已选择的字段
    const REDIS_REPORT_CONFIG_ALIAS_COLUMN = 'alias_column';            //使用过的字段别名
    const REDIS_REPORT_CONFIG_COLUMN_COMMENT = 'column_comment';        //字段注释
    
    //平台用户通用数据缓存
    const REDIS_COMMON_CITYNAME = '_dc_common:selfreport:cityname';      //所有城市名(只包含城市)对应ID
    const REDIS_COMMON_CITYLIST = '_dc_common:selfreport:citylist';      //所有城市ID对应城市名
    const REDIS_COMMON_CITY_INITIALS = '_dc_common:selfreport:city_initials';    //所有城市缩写，并包括ID和名称，用于城市提示
    const REDIS_COMMON_WDT_CITYLIST = '_dc_common:selfreport:wdt_citylist';      //所有用于网店通业务的分部ID对应分部名
    const REDIS_COMMON_UNIT = '_dc_common:selfreport:unit';              //所有事业部，来自来源城市表dim_source_city
    const REDIS_COMMON_REGION = '_dc_common:selfreport:region';          //所有大区名
    const REDIS_COMMON_WARZONE = '_dc_common:selfreport:warzone';        //战区表缓存
    const REDIS_COMMON_WARZONE_ALL = 'all';                   //所有战区
    const REDIS_COMMON_WARZONE_ZONE = 'warzone';              //分区
    const REDIS_COMMON_WARZONE_STEP = 'warstep';              //分档
    
    const REDIS_COMMON_COLUMNS_INFO = '_dc_common:selfreport:columns_info';                    //数据项定义
    const REDIS_COMMON_COLUMNS_INFO_CITYDIV = '_dc_common:selfreport:columns_info_citydiv';    //城市分区数据项定义
    const REDIS_COMMON_PRIVILEGE_COLUMNS = '_dc_common:selfreport:privilege_columns';          //权限控制字段 
    const REDIS_COMMON_CITY_DIVISIONS = '_dc_common:selfreport:city_divisions';                //城市分区对应关系
    
    const REDIS_COMMEN_TAB_REPORTS = '_dc_common:selfreport:tab_reports';    //标签页报表，父子结构
    
    //////////////////////////////////////////////////// Redis相关 END ////////////////////////////////////////////////////
    
    //////////////////////////////////////////////////// SESSIONS相关 ////////////////////////////////////////////////////
    
    const SESSION_SUPER = 'sr_super';
    const SESSION_CITIES = 'sr_cities';
    const SESSION_BRANCHES = 'sr_branches';
    const SESSION_CATEGORY = 'sr_categories';
    const SESSION_MEDIA = 'sr_medias';
    
    const SESSION_STEP2_TABLE_CHANGED = 'step2_table_changed';  //报表配置中Step2.查询配置，表操作，0为无，1为增，2为删
    
    //////////////////////////////////////////////////// SESSIONS相关 END ////////////////////////////////////////////////////
    
    const EXPORT_PAGE_SIZE = 500000;    //导出分页
    const EXPORT_BLOCK_SIZE = 50000;    //导出分块
    const EXPORT_TIME_LIMIT = 180;      //导出超时，单位秒，0为永不超时
    
    const UNKNOWN_AREA_ID = -1;         //未知大区ID
    const UNKNOWN_REGION_ID = -1;       //未知区域ID
    const UNKNOWN_WARZONE_ID = -1;      //未知战区ID
    
    const SUMMARY_IDENTIFICATION = 9999;    //汇总值标识，用于预置联动列表，明细汇总混合列表
    const LINK_LIST_ALL_AREAS = 'all_areas';                    //全部大区，用于预置联动列表，明细汇总混合列表,下同
    const LINK_LIST_ALL_REGIONS = 'all_regions';                //全部区域
    const LINK_LIST_ALL_WARZONES = 'all_warzones';              //全部战区分区
    const LINK_LIST_ALL_WARSTEPS = 'all_warsteps';              //全部战区分档
    const LINK_LIST_ALL_CITIES = 'all_cities';                  //全部城市
    const LINK_LIST_ALL_BRANCHES = 'all_branches';              //全部分部
    const LINK_LIST_ALL_TEAMS = 'all_teams';                    //全部团队
    const LINK_LIST_SUMMARY_WHOLESITE = 'summary_wholesite';    //整站汇总
    const LINK_LIST_SUMMARY_AREA = 'summary_area';              //大区汇总
    const LINK_LIST_SUMMARY_REGION = 'summary_region';          //区域汇总
    const LINK_LIST_SUMMARY_CITY = 'summary_city';              //城市汇总
    const LINK_LIST_SUMMARY_BRANCH = 'summary_branch';          //分部汇总
    
    //////////////////////////////////////////////////// 其他平台读取 ////////////////////////////////////////////////////
    
    //数据平台
    const SESSION_DCPLAT_SUPER = 'super';
    const REDIS_PRIVILEGES_DCPLAT = ':privileges:dcplat';
    const REDIS_PRIVILEGES_DCPLAT_SELFREPORT = 'selfreport';       //自助报表权限
    const REDIS_PRIVILEGES_DCPLAT_MENU = 'menu';                  //整体菜单ID结构树
    const REDIS_PRIVILEGES_DCPLAT_SELFREPORT_PARENTS_TREE = 'selfreport_parents_tree';       //自助报表父级菜单树
    const REDIS_PRIVILEGES_DCPLAT_SELFREPORT_PARENTS_IDS = 'selfreport_parents_ids';         //自助报表所有父级菜单ID
    const REDIS_COMMEN_MENU = '_dc_common:menu:menu';    //菜单缓存
    const REDIS_COMMEN_MENU_GRADE = '_dc_common:menu:menugrade';    //菜单层级缓存
    const REDIS_COMMEN_MENU_MAP = '_dc_common:menu:menumap';    //菜单层级对应缓存
    const REDIS_COMMEN_MENU_DEPT = '_dc_common:menu:menudept';    //菜单部门缓存
    const REDIS_COMMEN_MENU_TOID = '_dc_common:menu:menu2id';    //菜单URL与ID对应缓存
    const REDIS_COMMEN_MENU_TABS = '_dc_common:menu:menutabs';    //标签页父子菜单
    const REDIS_COMMEN_DCPLAT_SELFREPORT_WITH_PARENT = '_dc_common:dcplatform:selfreport_with_parent';    //自助报表父子结构缓存
    
    //////////////////////////////////////////////////// 其他平台读取 END ////////////////////////////////////////////////////
    
    //////////////////////////////////////////////////// 其他公用常量 ////////////////////////////////////////////////////
    
    const PRIVILEGE_TYPE_CITY = 'cities';
    const PRIVILEGE_TYPE_BRANCH = 'branches';
    const PRIVILEGE_TYPE_AREA = 'areas';
    const PRIVILEGE_TYPE_REGION = 'regions';
    const PRIVILEGE_TYPE_CATEGORY = 'categories';
    const PRIVILEGE_TYPE_MEDIA = 'medias';
    
    //////////////////////////////////////////////////// 其他公用常量 END ////////////////////////////////////////////////////
    
    //////////////////////////////////////////////////// 静态变量 ////////////////////////////////////////////////////
    
    //数据库中的数值类型
    public static $db_int_type = array('int','tinyint','bigint','smallint','mediumint','integer');
    public static $db_double_type = array('decimal','double','float','real','numeric');
    public static $db_date_time_type = array('datetime', 'timestamp');
    
    //////////////////////////////////////////////////// 静态变量 END ////////////////////////////////////////////////////
    
    /**
     * 校验日期格式参数值
     * @param string $date
     * @return boolean
     */
    public static function checkDateParam($date)
    {
        return preg_match('/^\d{4}(\-|\/|\.)\d{1,2}\1\d{1,2}$/', $date) || preg_match('/^\d{4}[\-|\/|\.]{0,1}\d{2}$/', $date);
    }
    
    /**
     * 获取输入数字型参数
     */
    public static function getNumParam($param,$default=0)
    {
        $value = Yii::app()->request->getParam($param);
        return ctype_digit(trim($value)) ? trim($value) : $default;
    }
    
    /**
     * 校验数字型参数值
     * @param string $param
     * @return boolean
     */
    public static function checkNumParam($param)
    {
        return ctype_digit(trim($param));
    }
    
    /**
     * 获取输入字符串型参数
     */
    public static function getStringParam($param,$default='')
    {
        $value = Yii::app()->request->getParam($param);
        $value = trim($value);
        return preg_match('/^[\x80-\xff_a-zA-Z0-9@\-\.\(\)]+$/', $value) ? $value : $default;//\x80-\xff表示中文
    }
    
    /**
     * 校验字符串型参数值
     * @param string $param
     * @return boolean
     */
    public static function checkStringParam($param)
    {
        return preg_match('/^[\x80-\xff_a-zA-Z0-9@\-]+$/', trim($param));
    }
    
    /**
     * 获取输入数组型参数
     */
    public static function getArrayParam($param,$default=array())
    {
        $value = Yii::app()->request->getParam($param);
        if ($value && is_array($value) && !empty($value)) {
            return $value;
        } else {
            return $default;
        }
    }
    
    /**
     * 获取输入邮箱参数
     */
    public static function getEMailParam($param,$default='')
    {
        $value = Yii::app()->request->getParam($param);
        return preg_match('/^[a-z0-9]+([._\\-]*[a-z0-9])*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+.){1,63}[a-z0-9]+$/', trim($value)) ? trim($value) : $default;
    }
    
    /**
     * 获取输入JSON型参数
     */
    public static function getJsonParam($param,$default=array())
    {
        $value = Yii::app()->request->getParam($param);
        if ($value && is_string($value) && !empty($value)) {
            return CJSON::decode($value);
        } else {
            return $default;
        }
    }
    
    /**
     * 获取输入数字型参数，保存到session中，当请求中取不到参数时，使用之前session中的
     */
    public static function getNumParamBySession($param,$default=0)
    {
        $value = Yii::app()->request->getParam($param);
        if (ctype_digit(trim($value))) {
            $result = trim($value);
            Yii::app()->user->setstate($param, $result);
        } else {
            $session_value = Yii::app()->user->getstate($param);
            $result = ctype_digit($session_value) ? $session_value : $default;
        }
        return $result;
    }
    
    /**
     * 获取验证码
     * @author chensm
     * @return string
     */
    public static function getToken()
    {
        $token = Yii::app()->request->getParam('token');
        return ctype_digit($token) ? $token : '';
    }
    
    /**
     * 根据数据源ID获取数据库连接
     * @param int $data_source_id
     */
    public static function getDBConnection($data_source)
    {
        $model = DataSource::getDataSourceByID($data_source);
        if ($model) {
            $error = false;
//            $dsn = 'mysql:host='.$model['server_ip'].';dbname='.$model['database'];
            $dsn = 'mysql:host='.$model['server_ip'].';port='.$model['port'].';dbname='.$model['database'];

            try {
                $conn = new CDbConnection($dsn,$model['username'],$model['password']);//建立链接，可能会出现异常
                $conn->emulatePrepare = true;
                $conn->charset = $model['charset'];
                $conn->enableProfiling = true;
                $conn->enableParamLogging = true;
                $conn->schemaCachingDuration = 3600;
                $conn->active = true;
            } catch (Exception $e) {
                $error = true;
            }
            
            if (!$error) {
                return array($conn, $model);
            } else {
                return array(null, null);
            }
        } else {
            return array(null, null);
        }
    }
    
    /**
     * 获取当前用户具有权限的菜单树
     * @return array
     */
    public static function getUserMenuTree()
    {
        $result = array();
        $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT);
        $menu_tree = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT, Common::REDIS_PRIVILEGES_SELFREPORT_MENU) ? $priv[Common::REDIS_PRIVILEGES_SELFREPORT_MENU] : '';
    
        if (!empty($menu_tree)) {
            $result_array = unserialize($menu_tree);
            $result = $result_array !== false ? $result_array : array();
        }
    
        if (empty($result)) {
            $result = Utility::assemblePrivilegeMenu();
            $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT);
            $priv[Common::REDIS_PRIVILEGES_SELFREPORT_MENU] = serialize($result);
        }
        return $result;
    }
    
    /**
     * 取所有用户组
     * @author chensm
     */
    public static function getUserGroups()
    {
        /* $usergroups = array(''=>'请选择用户组');
        $all = UserGroup::getAllGroups();
        $usergroups = $usergroups + $all;
        $usergroups[UserGroup::SUPER_GROUP] = '超级权限';
        return $usergroups; */
        return UserGroup::getAllGroups();
    }
    
    /**
     * 取所有媒体
     * @author chensm
     */
    public static function getAllMedias()
    {
        $medias = array(''=>'不设置媒体','all'=>'全部');
        $mediaArr = MediaList::getAllMedias();
        foreach ($mediaArr as $media)
        {
            $medias[$media->id] = $media->media_name;
        }
        return $medias;
    }
    
    /**
     * 为列表框数组添加头array(''=>'请选择')
     * @param array $list    列表
     */
    public static function addTitleToList($list)
    {
        $title = array(''=>'请选择');
        return $title+$list;
    }
    
    /**
     * 设置团购业务权限城市及网店通业务权限分部相关结构，包括大区、区域、战区等
     */
    public static function setPrivCityStruct()
    {
        //团购业务
        $area_ids = array();
        $areas = array();
        $area_regions = array();
        $area_name_regions = array();
        $area_cities = array();
        $area_name_cities = array();
        
        $region_ids = array();
        $regions = array();
        $region_cities = array();
        $region_name_cities = array();
        
        $warzone_ids = array();
        $warzones = array();
        $warzone_cities = array();
        $warzone_name_cities = array();
        
        $cities = array();
        
        $city_teams = array();
        $city_name_teams = array();
        
        //网店通业务
        $wdt_area_ids = array();
        $wdt_areas = array();
        $wdt_area_regions = array();
        $wdt_area_name_regions = array();
        $wdt_area_branches = array();
        $wdt_area_name_branches = array();
        
        $wdt_region_ids = array();
        $wdt_regions = array();
        $wdt_region_branches = array();
        $wdt_region_name_branches = array();
        
        $wdt_warzone_ids = array();
        $wdt_warzones = array();
        $wdt_warzone_branches = array();
        $wdt_warzone_name_branches = array();
        
        $wdt_branches = array();
        
        $wdt_branch_teams = array();
        $wdt_branch_name_teams = array();
        
        $session_super = Yii::app()->user->getstate(Common::SESSION_SUPER);
        $session_cities = Yii::app()->user->getstate(Common::SESSION_CITIES);
        $session_branches = Yii::app()->user->getstate(Common::SESSION_BRANCHES);
        
        if (in_array($session_super, Privileges::$super_admin)) {
            //正向构造全权限结构
            $allcity = City::getCityIDList();
            $city_ids = array_keys($allcity);
            list($areas,$regions,$warzones,$cities,$area_regions,$area_name_regions,$area_cities,$area_name_cities,$region_cities,$region_name_cities,$warzone_cities,$warzone_name_cities,$city_teams,$city_name_teams) = Utility::assembleStructByCities($city_ids);
            
            $all_branch = Branch::getBranchIDList();
            $branch_ids = array_keys($all_branch);
            list($wdt_areas,$wdt_regions,$wdt_warzones,$wdt_branches,$wdt_area_regions,$wdt_area_name_regions,$wdt_area_branches,$wdt_area_name_branches,$wdt_region_branches,$wdt_region_name_branches,$wdt_warzone_branches,$wdt_warzone_name_branches,$wdt_branch_teams,$wdt_branch_name_teams) = Utility::assembleStructByWdtBranches($branch_ids);
            
            /* 
            $areas = Region::getAreas();
            $regions = Region::getRegions();
            $warzones = WarZone::getWarZone();
            $cities = City::getCityIDList();
            
            $area_ids = array_keys($areas);
            $region_ids = array_keys($regions);
            $warzone_ids = array_keys($warzones);
            
            $criteria=new CDbCriteria;
            $criteria->addInCondition('parent_id', $area_ids);
            $models = Region::model()->findAll($criteria);
            foreach($models as $model){
                $area_regions[$model->parent_id][$model->id] = $model->name;
                $area_name_regions[$areas[$model->parent_id]][$model->id] = $model->name;
            }
            unset($models);
            unset($criteria);
            
            $criteria=new CDbCriteria;
            $criteria->addInCondition('area_id', $area_ids);
            $criteria->compare('status', 1);
            $criteria->compare('city_type', 1);
            $models = City::model()->findAll($criteria);
            foreach($models as $model){
                $area_cities[$model->area_id][$model->id] = $model->name;
                $area_name_cities[$areas[$model->area_id]][$model->id] = $model->name;
            }
            unset($models);
            unset($criteria);
            
            $criteria=new CDbCriteria;
            $criteria->addInCondition('region_id', $region_ids);
            $criteria->compare('status', 1);
            $criteria->compare('city_type', 1);
            $models = City::model()->findAll($criteria);
            foreach($models as $model){
                $region_cities[$model->region_id][$model->id] = $model->name;
                $region_name_cities[$regions[$model->region_id]][$model->id] = $model->name;
            }
            unset($models);
            unset($criteria);
            
            $criteria=new CDbCriteria;
            $criteria->addInCondition('war_zone', $warzone_ids);
            $criteria->compare('status', 1);
            $criteria->compare('city_type', 1);
            $models = City::model()->findAll($criteria);
            foreach($models as $model){
                $warzone_cities[$model->war_zone][$model->id] = $model->name;
                $warzone_name_cities[$warzones[$model->war_zone]][$model->id] = $model->name;
            }
            unset($models);
            unset($criteria);
             */
        } else {
            //取用户权限数据
            $privileges_obj = Privileges::getPrivileges(Yii::app()->user->name);
            $privileges_array = unserialize($privileges_obj->privileges);
            $privileges = $privileges_array !== false ? $privileges_array : array();
            $priv_areas = array_key_exists(Common::PRIVILEGE_TYPE_AREA, $privileges)&&!empty($privileges[Common::PRIVILEGE_TYPE_AREA]) ? $privileges[Common::PRIVILEGE_TYPE_AREA] : array();
            $priv_regions = array_key_exists(Common::PRIVILEGE_TYPE_REGION, $privileges)&&!empty($privileges[Common::PRIVILEGE_TYPE_REGION]) ? $privileges[Common::PRIVILEGE_TYPE_REGION] : array();
            $priv_cities = array_key_exists(Common::PRIVILEGE_TYPE_CITY, $privileges)&&!empty($privileges[Common::PRIVILEGE_TYPE_CITY]) ? $privileges[Common::PRIVILEGE_TYPE_CITY] : array();
            $priv_branches = array_key_exists(Common::PRIVILEGE_TYPE_BRANCH, $privileges)&&!empty($privileges[Common::PRIVILEGE_TYPE_BRANCH]) ? $privileges[Common::PRIVILEGE_TYPE_BRANCH] : array();
            
            //构造团购城市结构
            if ($session_cities == 1) {
                $allcity = City::getCityIDList();
                $city_ids = array_keys($allcity);
                list($areas,$regions,$warzones,$cities,$area_regions,$area_name_regions,$area_cities,$area_name_cities,$region_cities,$region_name_cities,$warzone_cities,$warzone_name_cities,$city_teams,$city_name_teams) = Utility::assembleStructByCities($city_ids);
            } else {
                if (!empty($priv_cities)) {        //以城市权限构造大区、区域权限
                    list($areas,$regions,$warzones,$cities,$area_regions,$area_name_regions,$area_cities,$area_name_cities,$region_cities,$region_name_cities,$warzone_cities,$warzone_name_cities,$city_teams,$city_name_teams) = Utility::assembleStructByCities($priv_cities);
                
                } elseif (!empty($priv_regions)) {        //以区域权限构造大区、城市权限
                    $criteria=new CDbCriteria;
                    $criteria->addInCondition('region_id', $priv_regions);
                    $criteria->compare('status', 1);
                    $criteria->compare('city_type', 1);
                    $models = City::model()->findAll($criteria);
                    
                    $city_ids = array();
                    foreach($models as $model){
                        $city_ids[] = $model->id;
                    }
                    
                    list($areas,$regions,$warzones,$cities,$area_regions,$area_name_regions,$area_cities,$area_name_cities,$region_cities,$region_name_cities,$warzone_cities,$warzone_name_cities,$city_teams,$city_name_teams) = Utility::assembleStructByCities($city_ids);
                    
                } elseif (!empty($priv_areas)) {        //以大区权限构造区域、城市权限
                    $criteria=new CDbCriteria;
                    $criteria->addInCondition('area_id', $priv_areas);
                    $criteria->compare('status', 1);
                    $criteria->compare('city_type', 1);
                    $models = City::model()->findAll($criteria);
                    
                    $city_ids = array();
                    foreach($models as $model){
                        $city_ids[] = $model->id;
                    }
                    list($areas,$regions,$warzones,$cities,$area_regions,$area_name_regions,$area_cities,$area_name_cities,$region_cities,$region_name_cities,$warzone_cities,$warzone_name_cities,$city_teams,$city_name_teams) = Utility::assembleStructByCities($city_ids);
                }
            }
            
            //构造网店通分部结构
            if ($session_branches == 1) {
                $all_branch = Branch::getBranchIDList();
                $branch_ids = array_keys($all_branch);
                list($wdt_areas,$wdt_regions,$wdt_warzones,$wdt_branches,$wdt_area_regions,$wdt_area_name_regions,$wdt_area_branches,$wdt_area_name_branches,$wdt_region_branches,$wdt_region_name_branches,$wdt_warzone_branches,$wdt_warzone_name_branches,$wdt_branch_teams,$wdt_branch_name_teams) = Utility::assembleStructByWdtBranches($branch_ids);
            } else {
                if (!empty($priv_branches)) {        //以城市权限构造大区、区域权限
                    list($wdt_areas,$wdt_regions,$wdt_warzones,$wdt_branches,$wdt_area_regions,$wdt_area_name_regions,$wdt_area_branches,$wdt_area_name_branches,$wdt_region_branches,$wdt_region_name_branches,$wdt_warzone_branches,$wdt_warzone_name_branches,$wdt_branch_teams,$wdt_branch_name_teams) = Utility::assembleStructByWdtBranches($priv_branches);
            
                } elseif (!empty($priv_regions)) {        //以区域权限构造大区、城市权限
                    $criteria=new CDbCriteria;
                    $criteria->addInCondition('region_id', $priv_regions);
                    $criteria->compare('is_delete', '0');
                    $models = Branch::model()->findAll($criteria);
            
                    $branch_ids = array();
                    foreach($models as $model){
                        $branch_ids[] = $model->id;
                    }
                    list($wdt_areas,$wdt_regions,$wdt_warzones,$wdt_branches,$wdt_area_regions,$wdt_area_name_regions,$wdt_area_branches,$wdt_area_name_branches,$wdt_region_branches,$wdt_region_name_branches,$wdt_warzone_branches,$wdt_warzone_name_branches,$wdt_branch_teams,$wdt_branch_name_teams) = Utility::assembleStructByWdtBranches($branch_ids);
            
                } elseif (!empty($priv_areas)) {        //以大区权限构造区域、城市权限
                    $criteria=new CDbCriteria;
                    $criteria->addInCondition('area_id', $priv_areas);
                    $criteria->compare('is_delete', '0');
                    $models = Branch::model()->findAll($criteria);
            
                    $branch_ids = array();
                    foreach($models as $model){
                        $branch_ids[] = $model->id;
                    }
                    list($wdt_areas,$wdt_regions,$wdt_warzones,$wdt_branches,$wdt_area_regions,$wdt_area_name_regions,$wdt_area_branches,$wdt_area_name_branches,$wdt_region_branches,$wdt_region_name_branches,$wdt_warzone_branches,$wdt_warzone_name_branches,$wdt_branch_teams,$wdt_branch_name_teams) = Utility::assembleStructByWdtBranches($branch_ids);
                }
            }
            
            //对于原来就拥有大区或区域权限的用户，按照当前最新对应关系重置大区、区域、城市权限，便于权限控制操作使用
            //注意：此构造规则是基于权限配置时大区、区域、城市保存其一的原则制定的
            if (!empty($priv_areas)) {
                //$tmp_areas = $areas;
                //if (array_key_exists(Common::UNKNOWN_AREA_ID, $tmp_areas)) unset($tmp_areas[Common::UNKNOWN_AREA_ID]);
                //$privileges[Common::PRIVILEGE_TYPE_AREA] = array_keys($tmp_areas);    //自身权限从逻辑上不需要重新赋值，从业务上包含团购和网店通双线业务，重新算的列表会仅包含团购业务
                $tmp_regions = $regions + $wdt_regions;
                if (array_key_exists(Common::UNKNOWN_REGION_ID, $tmp_regions)) unset($tmp_regions[Common::UNKNOWN_REGION_ID]);
                $privileges[Common::PRIVILEGE_TYPE_REGION] = array_keys($tmp_regions);
                $privileges[Common::PRIVILEGE_TYPE_CITY] = array_keys($cities);
                $privileges[Common::PRIVILEGE_TYPE_BRANCH] = array_keys($wdt_branches);
            } elseif (!empty($priv_regions)) {
                //$tmp_regions = $regions;
                //if (array_key_exists(Common::UNKNOWN_REGION_ID, $tmp_regions)) unset($tmp_regions[Common::UNKNOWN_REGION_ID]);
                //$privileges[Common::PRIVILEGE_TYPE_REGION] = array_keys($tmp_regions);    //自身权限从逻辑上不需要重新赋值，从业务上包含团购和网店通双线业务，重新算的列表会仅包含团购业务
                $privileges[Common::PRIVILEGE_TYPE_CITY] = array_keys($cities);
                $privileges[Common::PRIVILEGE_TYPE_BRANCH] = array_keys($wdt_branches);
            }
            //权限写到缓存中
            $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT);
            $priv[Common::REDIS_PRIVILEGES_FIELD] = serialize($privileges);
            Yii::app()->redis->getClient()->setTimeout(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT, 12*Common::REDIS_DURATION);
        }
        
        $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_CITYSTRUCT);
        $priv[Common::REDIS_CITYSTRUCT_AREAS] = serialize($areas);
        $priv[Common::REDIS_CITYSTRUCT_REGIONS] = serialize($regions);
        $priv[Common::REDIS_CITYSTRUCT_WARZONES] = serialize($warzones);
        $priv[Common::REDIS_CITYSTRUCT_CITIES] = serialize($cities);
        $priv[Common::REDIS_CITYSTRUCT_AREA_REGIONS] = serialize($area_regions);
        $priv[Common::REDIS_CITYSTRUCT_AREA_NAME_REGIONS] = serialize($area_name_regions);
        $priv[Common::REDIS_CITYSTRUCT_AREA_CITIES] = serialize($area_cities);
        $priv[Common::REDIS_CITYSTRUCT_AREA_NAME_CITIES] = serialize($area_name_cities);
        $priv[Common::REDIS_CITYSTRUCT_REGION_CITIES] = serialize($region_cities);
        $priv[Common::REDIS_CITYSTRUCT_REGION_NAME_CITIES] = serialize($region_name_cities);
        $priv[Common::REDIS_CITYSTRUCT_WARZONE_CITIES] = serialize($warzone_cities);
        $priv[Common::REDIS_CITYSTRUCT_WARZONE_NAME_CITIES] = serialize($warzone_name_cities);
        $priv[Common::REDIS_CITYSTRUCT_CITY_TEAMS] = serialize($city_teams);
        $priv[Common::REDIS_CITYSTRUCT_CITY_NAME_TEAMS] = serialize($city_name_teams);
        Yii::app()->redis->getClient()->setTimeout(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_CITYSTRUCT, 12*Common::REDIS_DURATION);
        
        $priv_wdt = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_CITYSTRUCT_WDT);
        $priv_wdt[Common::REDIS_CITYSTRUCT_AREAS] = serialize($wdt_areas);
        $priv_wdt[Common::REDIS_CITYSTRUCT_REGIONS] = serialize($wdt_regions);
        $priv_wdt[Common::REDIS_CITYSTRUCT_WARZONES] = serialize($wdt_warzones);
        $priv_wdt[Common::REDIS_CITYSTRUCT_CITIES] = serialize($wdt_branches);
        $priv_wdt[Common::REDIS_CITYSTRUCT_AREA_REGIONS] = serialize($wdt_area_regions);
        $priv_wdt[Common::REDIS_CITYSTRUCT_AREA_NAME_REGIONS] = serialize($wdt_area_name_regions);
        $priv_wdt[Common::REDIS_CITYSTRUCT_AREA_CITIES] = serialize($wdt_area_branches);
        $priv_wdt[Common::REDIS_CITYSTRUCT_AREA_NAME_CITIES] = serialize($wdt_area_name_branches);
        $priv_wdt[Common::REDIS_CITYSTRUCT_REGION_CITIES] = serialize($wdt_region_branches);
        $priv_wdt[Common::REDIS_CITYSTRUCT_REGION_NAME_CITIES] = serialize($wdt_region_name_branches);
        $priv_wdt[Common::REDIS_CITYSTRUCT_WARZONE_CITIES] = serialize($wdt_warzone_branches);
        $priv_wdt[Common::REDIS_CITYSTRUCT_WARZONE_NAME_CITIES] = serialize($wdt_warzone_name_branches);
        $priv_wdt[Common::REDIS_CITYSTRUCT_CITY_TEAMS] = serialize($wdt_branch_teams);
        $priv_wdt[Common::REDIS_CITYSTRUCT_CITY_NAME_TEAMS] = serialize($wdt_branch_name_teams);
        Yii::app()->redis->getClient()->setTimeout(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_CITYSTRUCT_WDT, 12*Common::REDIS_DURATION);
        
    }
    
    /**
     * 获取权限城市相关结构的其中一个，包括大区、区域、战区等
     * @param $key 缓存键
     */
    public static function getOnePrivCityStruct($key)
    {
        $result = array();
        $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_CITYSTRUCT);
        $structs = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_CITYSTRUCT, $key) ? $priv[$key] : '';
        
        if (!empty($structs)) {
            $result_array= unserialize($structs);
            $result = $result_array !== false ? $result_array : array();
        }
        
        if (empty($result)) {
            Common::setPrivCityStruct();
            unset($priv);
            unset($structs);
            $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_CITYSTRUCT);
            $structs = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_CITYSTRUCT, $key) ? $priv[$key] : '';
            if (!empty($structs)) {
                $result = unserialize($structs);
            }
        }
        
        return $result;
    }
    
    /**
     * 获取权限网店通分部相关结构的其中一个，包括大区、区域、战区等
     * @param $key 缓存键
     */
    public static function getOnePrivWdtCityStruct($key)
    {
        $result = array();
        $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_CITYSTRUCT_WDT);
        $structs = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_CITYSTRUCT_WDT, $key) ? $priv[$key] : '';
    
        if (!empty($structs)) {
            $result_array= unserialize($structs);
            $result = $result_array !== false ? $result_array : array();
        }
    
        if (empty($result)) {
            Common::setPrivCityStruct();
            unset($priv);
            unset($structs);
            $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_CITYSTRUCT_WDT);
            $structs = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_CITYSTRUCT_WDT, $key) ? $priv[$key] : '';
            if (!empty($structs)) {
                $result = unserialize($structs);
            }
        }
    
        return $result;
    }
    
    /**
     * 设置城市的分区对应关系
     * @return array
     */
    public static function setCityDivisions()
    {
        $results = array();
        $sql = "SELECT c.`name` AS city, r1.`name` AS area, r2.`name` AS region, w1.`name` AS warzone, w2.`name` AS warstep
                FROM `city` c
                LEFT JOIN region r1 ON c.area_id = r1.id and r1.is_delete=0
                LEFT JOIN region r2 ON c.region_id = r2.id and r2.is_delete=0
                LEFT JOIN warzone w1 ON c.war_zone = w1.id and w1.is_delete=0
                LEFT JOIN warzone w2 ON c.war_step = w2.id and w2.is_delete=0
                WHERE c.`status` = 1 AND c.city_type = 1
                UNION
                SELECT c.`name` AS city, r1.`name` AS area, r2.`name` AS region, w1.`name` AS warzone, w2.`name` AS warstep
                FROM `branch` c
                LEFT JOIN region r1 ON c.area_id = r1.id and r1.is_delete=0
                LEFT JOIN region r2 ON c.region_id = r2.id and r2.is_delete=0
                LEFT JOIN warzone w1 ON c.war_zone = w1.id and w1.is_delete=0
                LEFT JOIN warzone w2 ON c.war_step = w2.id and w2.is_delete=0
                WHERE c.is_delete = 0
                AND c.business_type = 1";
    
        $reports = Yii::app()->pdb->createCommand($sql)->queryAll();
        foreach ($reports as $data) {
            $results[$data['city']] = array(
                    'city' => Utility::turnNull($data['city'], '未知'),
                    'area' => Utility::turnNull($data['area'], '未知'),
                    'region' => Utility::turnNull($data['region'], '未知'),
                    'warzone' => Utility::turnNull($data['warzone'], '未知'),
                    'warstep' => Utility::turnNull($data['warstep'], '未知'),
            );
        }
    
        Yii::app()->redis->getClient()->setex(Common::REDIS_COMMON_CITY_DIVISIONS, 12*Common::REDIS_DURATION, serialize($results));
    }
    
    /**
     * 获取城市的分区对应关系
     * @param $cache    是否使用缓存，默认为使用
     * @return array
     */
    public static function getCityDivisions($cache=true)
    {
        $results = array();
    
        if ($cache) {
            $cache_city_div = Yii::app()->redis->getClient()->get(Common::REDIS_COMMON_CITY_DIVISIONS);
            $results = !empty($cache_city_div) ? unserialize($cache_city_div) : array();
        }
    
        if (empty($results)) {
            Common::setCityDivisions();
            $cache_city_div = Yii::app()->redis->getClient()->get(Common::REDIS_COMMON_CITY_DIVISIONS);
            $results = !empty($cache_city_div) ? unserialize($cache_city_div) : array();
        }
    
        return $results;
    }
    
    /**
     * 校验通过其他平台展示自助报表的原平台菜单的权限
     * @param int $platform
     * @param int $menu_id
     */
    public static function checkPlatMenuPriv($platform, $menu_id)
    {
        $result = false;
        
        switch ($platform) {
            case 1:    //数据平台
                $super = Yii::app()->user->getstate(Common::SESSION_DCPLAT_SUPER);
                if ($super > 0) {
                    $result = true;
                } else {
                    $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_DCPLAT);
                    $privileges = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_DCPLAT, Common::REDIS_PRIVILEGES_FIELD) ? $priv[Common::REDIS_PRIVILEGES_FIELD] : '';
                    $privileges = unserialize($privileges);
                    $menus = !empty($privileges['menu']) ? $privileges['menu'] : array();
                    if (is_numeric($menu_id)) {
                        $result = in_array($menu_id, $menus);
                    }
                }
                break;
            case 2:    //决策系统
                break;
        }
        
        return $result;
    }
    
    /**
     * 根据平台和父ID获取所有父级菜单ID
     * @param int $platform
     * @param int $parent_id
     */
    public static function getParentsByPlatAndID($platform, $parent_id)
    {
        $first_grade = '';
        $second_grade = '';
        $third_grade = '';
    
        $first_menus = array(''=>'请选择一级菜单');
        $second_menus = array(''=>'请选择二级菜单');
        $third_menus = array(''=>'请选择三级菜单');
    
        switch ($platform) {
            case 1:    //数据平台
                $menu_list = MenuDataPlatform::getMenuListForSelect(1);
                if (!empty($menu_list)) {
                    foreach ($menu_list as $id=>$menu)
                    {
                        if (!($id==8 && $menu=='平台管理')) {
                            $first_menus[$id] = $menu;
                        }
                    }
                }
    
                $model = MenuDataPlatform::model()->find('status=1 and id=:id', array(':id'=>$parent_id));
                if ($model) {
                    switch ($model->menu_grade) {
                        case 1:
                            $first_grade = $model->id;
                            break;
                        case 2:
                            $second_grade = $model->id;
                            $first = MenuDataPlatform::model()->find('status=1 and id=:id', array(':id'=>$model->parent_id));
                            if ($first) {
                                $first_grade = $first->id;
                            }
                            break;
                        case 3:
                            $third_grade = $model->id;
                            $second = MenuDataPlatform::model()->find('status=1 and id=:id', array(':id'=>$model->parent_id));
                            if ($second) {
                                $second_grade = $second->id;
                                $first = MenuDataPlatform::model()->find('status=1 and id=:id', array(':id'=>$second->parent_id));
                                if ($first) {
                                    $first_grade = $first->id;
                                }
                            }
                            break;
                    }
                    $menu_list = MenuDataPlatform::getSubMenu($first_grade, 2);
                    if (!empty($menu_list)) {
                        foreach ($menu_list as $menu)
                        {
                            if (strpos($menu->url, '/') === false && $menu->tab_state < 2) {
                                $second_menus[$menu->id] = $menu->menu_name;
                            }
                        }
                    }
                    $menu_list = MenuDataPlatform::getSubMenu($second_grade, 3);
                    if (!empty($menu_list)) {
                        foreach ($menu_list as $menu)
                        {
                            if (strpos($menu->url, '/') === false && $menu->tab_state < 2) {
                                $third_menus[$menu->id] = $menu->menu_name;
                            }
                        }
                    }
                }
                break;
            case 2:    //决策系统
                break;
            case 9:    //自助报表平台
                $menu_list = Menu::getMenuListForSelect(1);
                if (!empty($menu_list)) {
                    foreach ($menu_list as $id=>$menu)
                    {
                        $first_menus[$id] = $menu;
                    }
                }
    
                $model = Menu::model()->find('platform=9 and status=1 and id=:id', array(':id'=>$parent_id));
                if ($model) {
                    switch ($model->menu_grade) {
                        case 1:
                            $first_grade = $parent_id;
                            break;
                        case 2:
                            $second_grade = $parent_id;
                            $first = Menu::model()->find('platform=9 and status=1 and id=:id', array(':id'=>$model->parent_id));
                            if ($first) {
                                $first_grade = $first->id;
                            }
                            break;
                        case 3:
                            $third_grade = $parent_id;
                            $second = Menu::model()->find('platform=9 and status=1 and id=:id', array(':id'=>$model->parent_id));
                            if ($second) {
                                $second_grade = $second->id;
                                $first = Menu::model()->find('platform=9 and status=1 and id=:id', array(':id'=>$second->parent_id));
                                if ($first) {
                                    $first_grade = $first->id;
                                }
                            }
                            break;
                    }
                    $menu_list = Menu::getSubMenu($first_grade, 2);
                    if (!empty($menu_list)) {
                        foreach ($menu_list as $menu)
                        {
                            if (empty($menu->report_id) && $menu->tab_state < 2) {
                                $second_menus[$menu->id] = $menu->menu_name;
                            }
                        }
                    }
                    $menu_list = Menu::getSubMenu($second_grade, 3);
                    if (!empty($menu_list)) {
                        foreach ($menu_list as $menu)
                        {
                            if (empty($menu->report_id) && $menu->tab_state < 2) {
                                $third_menus[$menu->id] = $menu->menu_name;
                            }
                        }
                    }
                }
                break;
        }
    
        return array($first_grade, $second_grade, $third_grade, $first_menus, $second_menus, $third_menus);
    }
    
    /**
     * 处理报表父菜单的标签页属性
     * 检查父菜单是否还有其他标签页报表，如果没有则将其标签页属性置为0，否则置1
     * @param int $platform
     * @param int $parent_id
     */
    public static function dealParentTabState($platform, $parent_id)
    {
        switch ($platform) {
            case 1:    //数据平台
                $parent = MenuDataPlatform::model()->find('status=1 and id=:id', array(':id'=>$parent_id));
                $tab_reports = MenuDataPlatform::model()->findAll('status=1 and tab_state=2 and parent_id=:parent_id', array(':parent_id'=>$parent_id));
                $tab_reports_sr = Menu::model()->findAll('platform=1 and status=1 and tab_state=2 and parent_id=:parent_id', array(':parent_id'=>$parent_id));
                if ($parent) {
                    $parent_tab_state = isset($parent->tab_state) ? $parent->tab_state : 0;
                    if (empty($tab_reports) && empty($tab_reports_sr)) {
                        if ($parent_tab_state != 0) {
                            $parent->tab_state = 0;
                            $parent->save();
                        }
                    } else {
                        if ($parent_tab_state != 1) {
                            $parent->tab_state = 1;
                            $parent->save();
                        }
                    }
                }
                break;
            case 2:    //决策系统
                break;
            case 9:    //自助报表平台
                $parent = Menu::model()->find('status=1 and id=:id', array(':id'=>$parent_id));
                $tab_reports = Menu::model()->findAll('status=1 and tab_state=2 and parent_id=:parent_id', array(':parent_id'=>$parent_id));
                if ($parent) {
                    $parent_tab_state = isset($parent->tab_state) ? $parent->tab_state : 0;
                    if (empty($tab_reports)) {
                        if ($parent_tab_state != 0) {
                            $parent->tab_state = 0;
                            $parent->save();
                        }
                    } else {
                        if ($parent_tab_state != 1) {
                            $parent->tab_state = 1;
                            $parent->save();
                        }
                    }
                }
                break;
        }
    }
    
    /**
     * 根据平台获取外发自助报表父菜单树
     * @param int $platform
     */
    public static function getParentsTree($platform)
    {
        $parents_tree = array();
        $parents_ids = array();
        switch ($platform) {
            case 1:    //数据平台
                $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_DCPLAT);
                $parents_tree_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_DCPLAT, Common::REDIS_PRIVILEGES_DCPLAT_SELFREPORT_PARENTS_TREE) ? $priv[Common::REDIS_PRIVILEGES_DCPLAT_SELFREPORT_PARENTS_TREE] : '';
                $parents_ids_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_DCPLAT, Common::REDIS_PRIVILEGES_DCPLAT_SELFREPORT_PARENTS_IDS) ? $priv[Common::REDIS_PRIVILEGES_DCPLAT_SELFREPORT_PARENTS_IDS] : '';
                
                if (!empty($parents_tree_cache) && !empty($parents_ids_cache)) {
                    $parents_tree_array = unserialize($parents_tree_cache);
                    $parents_ids_array = unserialize($parents_ids_cache);
                    $parents_tree = $parents_tree_array !== false ? $parents_tree_array : array();
                    $parents_ids = $parents_ids_array !== false ? $parents_ids_array : array();
                }
                break;
            case 2:    //决策系统
                break;
        }
        return array($parents_tree, $parents_ids);
    }
    
    /**
     * 根据平台父级菜单ID获取外发自助报表
     * @param int $platform
     * @param int $parent_id
     */
    public static function getReportsByParentID($platform, $parent_id)
    {
        $results = array();
        $menus = array();
        switch ($platform) {
            case 1:    //数据平台
                $selfreport_with_parent = Yii::app()->redis->getClient()->get(Common::REDIS_COMMEN_DCPLAT_SELFREPORT_WITH_PARENT);
                $selfreports = unserialize($selfreport_with_parent);
                $menus = $selfreports !== false ? $selfreports : array();
                break;
            case 2:    //决策系统
                break;
        }
        $results = array_key_exists($parent_id, $menus) ? $menus[$parent_id] : array();
        return $results;
    }
    
    /**
     * 根据平台获取当前用户拥有权限的自助报表ID
     * @param int $platform
     */
    public static function getPrivSelfReports($platform)
    {
        $result = array();
        switch ($platform) {
            case 1:    //数据平台
                $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_DCPLAT);
                $report_ids = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_DCPLAT, Common::REDIS_PRIVILEGES_DCPLAT_SELFREPORT) ? $priv[Common::REDIS_PRIVILEGES_DCPLAT_SELFREPORT] : '';
                if (!empty($report_ids)) {
                    $result_array = unserialize($report_ids);
                    $result = $result_array !== false ? $result_array : array();
                }
                break;
            case 2:    //决策系统
                break;
        }
        return $result;
    }
    
    /**
     * 指定菜单下的第一个子菜单
     * @param int $platform
     * @param int $parent
     */
    public static function get1stSubMenu($platform, $parent)
    {
        $return_url = array();
        $session_super = Yii::app()->user->getstate(Common::SESSION_SUPER);
        
        switch ($platform) {
            case 1:    //数据平台
                $priv_self_reports = Common::getPrivSelfReports($platform);
                list($parents_tree, $parents_ids) = Common::getParentsTree($platform);    //自助报表涉及数据平台权限菜单
                if (in_array($parent, $parents_ids)) {    //如果该菜单是自助报表的父菜单之一
                    $break_2nd = false;
                    $break_3rd = false;
                    foreach ($parents_tree as $first_id => $menus_2nd) {
                        if (!array_key_exists($parent, $parents_tree) || $parent == $first_id) {
                            $match_1st = $parent == $first_id;
                            if (!empty($menus_2nd)) {
                                foreach ($menus_2nd as $second_id => $menus_3rd) {
                                    if (!array_key_exists($parent, $menus_2nd) || $parent == $second_id) {
                                        $match_2nd = $parent == $second_id;
                                        if (!empty($menus_3rd)) {
                                            foreach ($menus_3rd as $third_id => $menus_4th) {
                                                if (!array_key_exists($parent, $menus_3rd) || $parent == $third_id) {
                                                    $match_3rd = $parent == $third_id;
                                                    $reports_4th = Common::getReportsByParentID($platform, $third_id);
                                                    if (($match_1st||$match_2nd||$match_3rd) && !empty($reports_4th)) {
                                                        foreach ($reports_4th as $report) {
                                                            $report_id = isset($report['report_id']) ? $report['report_id'] : '';
                                                            if ($session_super>0 || in_array($report_id, $priv_self_reports)) {
                                                                if (!empty($report_id)) {
                                                                    $return_url = array('view/report','platform'=>1,'module'=>$first_id,'mp'=>$third_id,'report_id'=>$report_id);
                                                                    break;
                                                                }
                                                            }
                                                        }
                                                        if (!empty($return_url)) {
                                                            $break_3rd = true;
                                                            break;
                                                        }
                                                    }
                                                    if ($match_3rd) {
                                                        $break_3rd = true;
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                        if ($break_3rd === true) {
                                            $break_2nd = true;
                                            break;
                                        } else {
                                            $reports_3rd = Common::getReportsByParentID($platform, $second_id);
                                            if (($match_1st||$match_2nd||$match_3rd) && !empty($reports_3rd)) {
                                                foreach ($reports_3rd as $report) {
                                                    $report_id = isset($report['report_id']) ? $report['report_id'] : '';
                                                    if ($session_super>0 || in_array($report_id, $priv_self_reports)) {
                                                        if (!empty($report_id)) {
                                                            $return_url = array('view/report','platform'=>1,'module'=>$first_id,'mp'=>$second_id,'report_id'=>$report_id);
                                                            break;
                                                        }
                                                    }
                                                }
                                                if (!empty($return_url)) {
                                                    $break_2nd = true;
                                                    break;
                                                }
                                            }
                                        }
                                        if ($match_2nd) {
                                            $break_2nd = true;
                                            break;
                                        }
                                    }
                                }
                            }
                            if ($break_2nd === true) {
                                break;
                            } else {
                                $reports_2nd = Common::getReportsByParentID($platform, $first_id);
                                if (($match_1st||$match_2nd||$match_3rd) && !empty($reports_2nd)) {
                                    foreach ($reports_2nd as $report) {
                                        $report_id = isset($report['report_id']) ? $report['report_id'] : '';
                                        if ($session_super>0 || in_array($report_id, $priv_self_reports)) {
                                            if (!empty($report_id)) {
                                                $return_url = array('view/report','platform'=>1,'module'=>$first_id,'mp'=>$first_id,'report_id'=>$report_id);
                                                break;
                                            }
                                        }
                                    }
                                    if (!empty($return_url)) {
                                        break;
                                    }
                                }
                            }
                            if ($match_1st) {
                                break;
                            }
                        }
                    }
                }
                break;
            case 2:    //决策系统
                break;
            case 9:    //自助报表平台
                break;
        }
        return $return_url;
    }
    
    /**
     * 清除报表配置step2.查询配置使用的缓存
     */
    public static function clearCacheForConfig()
    {
        $del_keys = Yii::app()->redis->getClient()->keys(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG);
        if (!empty($del_keys)) {
            Yii::app()->redis->getClient()->delete($del_keys);
        }
    }
    
    /**
     * 取数据表字段注释
     */
    public static function getCommentByTableColumn($column)
    {
        $config = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG);
        $comment_cache = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_REPORT_CONFIG, Common::REDIS_REPORT_CONFIG_COLUMN_COMMENT) ? $config[Common::REDIS_REPORT_CONFIG_COLUMN_COMMENT] : '';
        $comment_array= unserialize($comment_cache);
        $column_comment = $comment_array !== false && !empty($comment_array) ? $comment_array : array();
        
        $key = str_replace('.', '-', $column);
        return array_key_exists($key, $column_comment) ? $column_comment[$key] : '';
    }
    
    /**
     * 装配汇总明细联动列表的相应查询条件
     */
    private static function _assembleSumDetailConditions($level,$list_value,$column,&$query_condition,&$command_params,&$title_columns)
    {
        if (array_key_exists($column['column'], $title_columns)) {
            unset($title_columns[$column['column']]);
        }
        switch ($level) {
            case 1:
                switch ($list_value) {
                    case Common::LINK_LIST_SUMMARY_WHOLESITE:
                        $query_condition .= ' and '.$column['expression'].'=:'.$column['column'];
                        $command_params[] = array(
                            'name' => ':'.$column['column'],
                            'value' => 9999,
                            'data_type' => PDO::PARAM_STR,
                        );
                        if (array_key_exists('extra_column', $column) && array_key_exists($column['extra_column'], $title_columns)) {
                            unset($title_columns[$column['extra_column']]);
                        }
                        break;
                    case Common::LINK_LIST_ALL_AREAS:
                        $query_condition .= ' and '.$column['expression'].' not in (:'.$column['column'].')';
                        $command_params[] = array(
                            'name' => ':'.$column['column'],
                            'value' => '9999,0',
                            'data_type' => PDO::PARAM_STR,
                        );
                        break;
                    case Common::UNKNOWN_AREA_ID:
                        $query_condition .= ' and ('.$column['expression']."='' or lower(".$column['expression'].")='null' or ".$column['expression'].' is null )';
                        break;
                    default:
                        $query_condition .= ' and '.$column['expression'].'=:'.$column['column'];
                        $command_params[] = array(
                            'name' => ':'.$column['column'],
                            'value' => $list_value,
                            'data_type' => PDO::PARAM_STR,
                        );
                }
                break;
            case 2:
                switch ($list_value) {
                    case Common::LINK_LIST_SUMMARY_WHOLESITE:
                        $query_condition .= ' and '.$column['expression'].'=:'.$column['column'];
                        $command_params[] = array(
                            'name' => ':'.$column['column'],
                            'value' => 9999,
                            'data_type' => PDO::PARAM_STR,
                        );
                        if (array_key_exists('extra_column', $column) && array_key_exists($column['extra_column'], $title_columns)) {
                            unset($title_columns[$column['extra_column']]);
                        }
                        break;
                    case Common::LINK_LIST_ALL_AREAS:
                        $query_condition .= ' and '.$column['expression'].'=:'.$column['column'];
                        $command_params[] = array(
                            'name' => ':'.$column['column'],
                            'value' => '9999',
                            'data_type' => PDO::PARAM_STR,
                        );
                        if (array_key_exists('extra_column', $column) && array_key_exists($column['extra_column'], $title_columns)) {
                            unset($title_columns[$column['extra_column']]);
                        }
                        break;
                    case Common::LINK_LIST_ALL_REGIONS:
                        $query_condition .= ' and '.$column['expression'].' not in (:'.$column['column'].')';
                        $command_params[] = array(
                            'name' => ':'.$column['column'],
                            'value' => '9999,0',
                            'data_type' => PDO::PARAM_STR,
                        );
                        break;
                    case Common::LINK_LIST_SUMMARY_AREA:
                        $query_condition .= ' and '.$column['expression'].'=:'.$column['column'];
                        $command_params[] = array(
                            'name' => ':'.$column['column'],
                            'value' => 9999,
                            'data_type' => PDO::PARAM_STR,
                        );
                        if (array_key_exists('extra_column', $column) && array_key_exists($column['extra_column'], $title_columns)) {
                            unset($title_columns[$column['extra_column']]);
                        }
                        break;
                    case Common::UNKNOWN_REGION_ID:
                        $query_condition .= ' and ('.$column['expression']."='' or lower(".$column['expression'].")='null' or ".$column['expression'].' is null )';
                        break;
                    default:
                        $query_condition .= ' and '.$column['expression'].'=:'.$column['column'];
                        $command_params[] = array(
                            'name' => ':'.$column['column'],
                            'value' => $list_value,
                            'data_type' => PDO::PARAM_STR,
                        );
                }
                break;
            case 3:
                switch ($list_value) {
                    case Common::LINK_LIST_SUMMARY_WHOLESITE:
                        $query_condition .= ' and '.$column['expression'].'=:'.$column['column'];
                        $command_params[] = array(
                            'name' => ':'.$column['column'],
                            'value' => 9999,
                            'data_type' => PDO::PARAM_STR,
                        );
                        if (array_key_exists('extra_column', $column) && array_key_exists($column['extra_column'], $title_columns)) {
                            unset($title_columns[$column['extra_column']]);
                        }
                        break;
                    case Common::LINK_LIST_ALL_AREAS:
                        $query_condition .= ' and '.$column['expression'].'=:'.$column['column'];
                        $command_params[] = array(
                            'name' => ':'.$column['column'],
                            'value' => 9999,
                            'data_type' => PDO::PARAM_STR,
                        );
                        if (array_key_exists('extra_column', $column) && array_key_exists($column['extra_column'], $title_columns)) {
                            unset($title_columns[$column['extra_column']]);
                        }
                        break;
                    case Common::LINK_LIST_ALL_REGIONS:
                        $query_condition .= ' and '.$column['expression'].'=:'.$column['column'];
                        $command_params[] = array(
                            'name' => ':'.$column['column'],
                            'value' => 9999,
                            'data_type' => PDO::PARAM_STR,
                        );
                        if (array_key_exists('extra_column', $column) && array_key_exists($column['extra_column'], $title_columns)) {
                            unset($title_columns[$column['extra_column']]);
                        }
                        break;
                    case Common::LINK_LIST_ALL_CITIES:
                        $query_condition .= ' and '.$column['expression'].' != :'.$column['column'];
                        $command_params[] = array(
                            'name' => ':'.$column['column'],
                            'value' => 9999,
                            'data_type' => PDO::PARAM_STR,
                        );
                        break;
                    case Common::LINK_LIST_SUMMARY_AREA:
                        $query_condition .= ' and '.$column['expression'].'=:'.$column['column'];
                        $command_params[] = array(
                            'name' => ':'.$column['column'],
                            'value' => 9999,
                            'data_type' => PDO::PARAM_STR,
                        );
                        if (array_key_exists('extra_column', $column) && array_key_exists($column['extra_column'], $title_columns)) {
                            unset($title_columns[$column['extra_column']]);
                        }
                        break;
                    case Common::LINK_LIST_SUMMARY_REGION:
                        $query_condition .= ' and '.$column['expression'].'=:'.$column['column'];
                        $command_params[] = array(
                            'name' => ':'.$column['column'],
                            'value' => 9999,
                            'data_type' => PDO::PARAM_STR,
                        );
                        if (array_key_exists('extra_column', $column) && array_key_exists($column['extra_column'], $title_columns)) {
                            unset($title_columns[$column['extra_column']]);
                        }
                        break;
                    default:
                        $query_condition .= ' and '.$column['expression'].'=:'.$column['column'];
                        $command_params[] = array(
                            'name' => ':'.$column['column'],
                            'value' => $list_value,
                            'data_type' => PDO::PARAM_STR,
                        );
                }
                break;
            case 4:
                switch ($list_value) {
                    case Common::LINK_LIST_SUMMARY_WHOLESITE:
                        $query_condition .= ' and '.$column['expression'].'=:'.$column['column'];
                        $command_params[] = array(
                            'name' => ':'.$column['column'],
                            'value' => 9999,
                            'data_type' => PDO::PARAM_STR,
                        );
                        if (array_key_exists('extra_column', $column) && array_key_exists($column['extra_column'], $title_columns)) {
                            unset($title_columns[$column['extra_column']]);
                        }
                        break;
                    case Common::LINK_LIST_ALL_AREAS:
                        $query_condition .= ' and '.$column['expression'].'=:'.$column['column'];
                        $command_params[] = array(
                            'name' => ':'.$column['column'],
                            'value' => 9999,
                            'data_type' => PDO::PARAM_STR,
                        );
                        if (array_key_exists('extra_column', $column) && array_key_exists($column['extra_column'], $title_columns)) {
                            unset($title_columns[$column['extra_column']]);
                        }
                        break;
                    case Common::LINK_LIST_ALL_REGIONS:
                        $query_condition .= ' and '.$column['expression'].'=:'.$column['column'];
                        $command_params[] = array(
                            'name' => ':'.$column['column'],
                            'value' => 9999,
                            'data_type' => PDO::PARAM_STR,
                        );
                        if (array_key_exists('extra_column', $column) && array_key_exists($column['extra_column'], $title_columns)) {
                            unset($title_columns[$column['extra_column']]);
                        }
                        break;
                    case Common::LINK_LIST_ALL_CITIES:
                        $query_condition .= ' and '.$column['expression'].'=:'.$column['column'];
                        $command_params[] = array(
                            'name' => ':'.$column['column'],
                            'value' => 9999,
                            'data_type' => PDO::PARAM_STR,
                        );
                        if (array_key_exists('extra_column', $column) && array_key_exists($column['extra_column'], $title_columns)) {
                            unset($title_columns[$column['extra_column']]);
                        }
                        break;
                    case Common::LINK_LIST_ALL_TEAMS:
                        $query_condition .= ' and '.$column['expression'].' != :'.$column['column'];
                        $command_params[] = array(
                            'name' => ':'.$column['column'],
                            'value' => 9999,
                            'data_type' => PDO::PARAM_STR,
                        );
                        break;
                    case Common::LINK_LIST_SUMMARY_AREA:
                        $query_condition .= ' and '.$column['expression'].'=:'.$column['column'];
                        $command_params[] = array(
                            'name' => ':'.$column['column'],
                            'value' => 9999,
                            'data_type' => PDO::PARAM_STR,
                        );
                        if (array_key_exists('extra_column', $column) && array_key_exists($column['extra_column'], $title_columns)) {
                            unset($title_columns[$column['extra_column']]);
                        }
                        break;
                    case Common::LINK_LIST_SUMMARY_REGION:
                        $query_condition .= ' and '.$column['expression'].'=:'.$column['column'];
                        $command_params[] = array(
                            'name' => ':'.$column['column'],
                            'value' => 9999,
                            'data_type' => PDO::PARAM_STR,
                        );
                        if (array_key_exists('extra_column', $column) && array_key_exists($column['extra_column'], $title_columns)) {
                            unset($title_columns[$column['extra_column']]);
                        }
                        break;
                    case Common::LINK_LIST_SUMMARY_CITY:
                        $query_condition .= ' and '.$column['expression'].'=:'.$column['column'];
                        $command_params[] = array(
                            'name' => ':'.$column['column'],
                            'value' => 9999,
                            'data_type' => PDO::PARAM_STR,
                        );
                        if (array_key_exists('extra_column', $column) && array_key_exists($column['extra_column'], $title_columns)) {
                            unset($title_columns[$column['extra_column']]);
                        }
                        break;
                    default:
                        $query_condition .= ' and '.$column['expression'].'=:'.$column['column'];
                        $command_params[] = array(
                            'name' => ':'.$column['column'],
                            'value' => $list_value,
                            'data_type' => PDO::PARAM_STR,
                        );
                }
                break;
        }
        //return array($query_condition, $command_params, $title_columns);
    }
    
    /**
     * 根据查询条件及用户权限拼装SQL
     * @param string $report_id     报表ID
     * @param array $conditions     输入查询条件
     * @param array $group          分组
     * @param array $order          排序
     * @return array                查询语句、绑定参数、访问参数、是否执行
     */
    public static function assembleQueryConditions($report_id, $conditions, $group, $order, &$title_columns, $period)
    {
        //参数校验
        $report_id = is_string($report_id) || is_numeric($report_id) ? $report_id : '';
        $conditions = is_array($conditions) ? $conditions : array();
        $group = is_array($group) ? $group : array();
        $order = is_array($order) ? $order : array();
        
        /////////////////////////////////////////////////根据查询条件拼装SQL/////////////////////////////////////////////////
        $query_condition = '';          //查询语句
        $command_params = array();      //绑定到查询语句的参数配置
        $condition_params = array();    //记录访问参数
        if (!empty($conditions)) {
            foreach ($conditions as $key => $condition) {
                switch ($key) {
                    case 'date':
                        foreach ($condition as $col) {
                            if (array_key_exists('data_type', $col)) {
                                $date_data_type = $col['data_type'];
                            } else {
                                $date_data_type = '';
                            }
                            $date_is_time = in_array($date_data_type, Common::$db_date_time_type);
                            $date_is_int = in_array($date_data_type, Common::$db_int_type);
                            
                            if ($col['attr'] == 'alone') {
                                //由于分页参数以GET方式传递，所以不能只判断POST
                                //$date = array_key_exists($col['column'], $_POST) && !empty($_POST[$col['column']]) ? $_POST[$col['column']] : '';
                                if ($col['type'] == 'dwysum' && $period == 'specified_time') {
                                    $start = Common::getStringParam($col['column'].'_start');
                                    $end = Common::getStringParam($col['column'].'_end');
                                    if ((!isset($start)&&!isset($end)) || (!Common::checkDateParam($start) && !Common::checkDateParam($end))) {
                                        $current_day = date('Y-m-d', time());
                                        $start = date('Y-m-01', time());
                                        $start = $current_day == $start ? date('Y-m-01', strtotime('-1 month', time())) : $start;
                                        $end = date('Y-m-d', strtotime('-1 day', time()));
                                    }
                                    if (!empty($start) && empty($end)) {
                                        if (!Common::checkDateParam($start)) {
                                            $current_day = date('Y-m-d', time());
                                            $start = date('Y-m-01', time());
                                            $start = $current_day == $start ? date('Y-m-01', strtotime('-1 month', time())) : $start;
                                        }
                                        $query_condition .= ' and '.$col['expression'].">=:start";
                                        if ($date_is_int) {
                                            $command_params[] = array(
                                                'name' => ':start',
                                                'value' => strtotime($start),
                                                'data_type' => PDO::PARAM_INT,
                                            );
                                        } else {
                                            $command_params[] = array(
                                                'name' => ':start',
                                                'value' => $start,
                                                'data_type' => PDO::PARAM_STR,
                                            );
                                        }
                                    } elseif (empty($start) && !empty($end)) {
                                        if (!Common::checkDateParam($end)) {
                                            $end = date('Y-m-d', strtotime('-1 day', time()));
                                        }
                                        if ($date_is_time || $date_is_int) {
                                            $query_condition .= ' and '.$col['expression']."<:end";
                                            $end_time = strtotime('+1 day', strtotime($end));
                                            $end_next = date('Y-m-d', $end_time);
                                    
                                            if ($date_is_time) {
                                                $command_params[] = array(
                                                    'name' => ':end',
                                                    'value' => $end_next,
                                                    'data_type' => PDO::PARAM_STR,
                                                );
                                            } elseif ($date_is_int) {
                                                $command_params[] = array(
                                                    'name' => ':end',
                                                    'value' => $end_time,
                                                    'data_type' => PDO::PARAM_INT,
                                                );
                                            }
                                        } else {
                                            $query_condition .= ' and '.$col['expression']."<=:end";
                                            $command_params[] = array(
                                                'name' => ':end',
                                                'value' => $end,
                                                'data_type' => PDO::PARAM_STR,
                                            );
                                        }
                                    } elseif (!empty($start) && !empty($end)) {
                                        if ($date_is_time || $date_is_int) {
                                            $query_condition .= ' and '.$col['expression'].">=:start and ".$col['expression']."<:end";
                                            $start_time = strtotime($start);
                                            $end_time = strtotime('+1 day', strtotime($end));
                                            $end_next = date('Y-m-d', $end_time);
                                    
                                            if ($date_is_time) {
                                                $command_params[] = array(
                                                    'name' => ':start',
                                                    'value' => $start,
                                                    'data_type' => PDO::PARAM_STR,
                                                );
                                                $command_params[] = array(
                                                    'name' => ':end',
                                                    'value' => $end_next,
                                                    'data_type' => PDO::PARAM_STR,
                                                );
                                            } elseif ($date_is_int) {
                                                $command_params[] = array(
                                                    'name' => ':start',
                                                    'value' => $start_time,
                                                    'data_type' => PDO::PARAM_INT,
                                                );
                                                $command_params[] = array(
                                                    'name' => ':end',
                                                    'value' => $end_time,
                                                    'data_type' => PDO::PARAM_INT,
                                                );
                                            }
                                    
                                        } else {
                                            $query_condition .= ' and '.$col['expression']." between :start and :end";
                                            $command_params[] = array(
                                                'name' => ':start',
                                                'value' => $start,
                                                'data_type' => PDO::PARAM_STR,
                                            );
                                            $command_params[] = array(
                                                'name' => ':end',
                                                'value' => $end,
                                                'data_type' => PDO::PARAM_STR,
                                            );
                                        }
                                    }
                                    
                                    $condition_params[$col['column'].'_start'] = $start;
                                    $condition_params[$col['column'].'_end'] = $end;
                                } else {
                                    $date = Common::getStringParam($col['column']);
                                    if (!isset($date) || !Common::checkDateParam($date)) {
                                        if ($col['type'] == 'day') {
                                            $date = date('Y-m-d', strtotime('-1 day', time()));
                                        } elseif ($col['type'] == 'week') {
                                            $date = date('oW', strtotime('-1 week', time()));
                                        } elseif ($col['type'] == 'month') {
                                            $date = date('Y-m', strtotime('-1 month', time()));
                                        } elseif ($col['type'] == 'dwysum') {
                                            if($period == 'day' || $period == 'all'){
                                                $date = date('Y-m-d', strtotime('-1 day', time()));
                                            }elseif($period=='week'){
                                                $date = date('oW', time());
                                            }elseif($period=='month'){
                                                $date = date('Y-m', time());
                                            }
                                        }
                                    }
                                    if (!empty($date)) {
                                        if ($col['type'] == 'day' && ($date_is_time || $date_is_int)) {
                                            $query_condition .= ' and '.$col['expression'].">=:start and ".$col['expression']."<:end";
                                            $start_time = strtotime($date);
                                            $end_time = strtotime('+1 day', $start_time);
                                            $end = date('Y-m-d', $end_time);
                                            
                                            if ($date_is_time) {
                                                $command_params[] = array(
                                                    'name' => ':start',
                                                    'value' => $date,
                                                    'data_type' => PDO::PARAM_STR,
                                                );
                                                $command_params[] = array(
                                                    'name' => ':end',
                                                    'value' => $end,
                                                    'data_type' => PDO::PARAM_STR,
                                                );
                                            } elseif ($date_is_int) {
                                                $command_params[] = array(
                                                    'name' => ':start',
                                                    'value' => $start_time,
                                                    'data_type' => PDO::PARAM_INT,
                                                );
                                                $command_params[] = array(
                                                    'name' => ':end',
                                                    'value' => $end_time,
                                                    'data_type' => PDO::PARAM_INT,
                                                );
                                            }
                                            
                                        } else {
                                            $query_condition .= ' and '.$col['expression']."=:date";
                                            $command_params[] = array(
                                                'name' => ':date',
                                                'value' => $date,
                                                'data_type' => PDO::PARAM_STR,
                                            );
                                        }
                                    }
                                    $condition_params[$col['column']] = $date;
                                }
                            } elseif ($col['attr'] == 'range') {
                                //由于分页参数以GET方式传递，所以不能只判断POST
                                //$start = array_key_exists($col['column'].'_start', $_POST) && !empty($_POST[$col['column'].'_start']) ? $_POST[$col['column'].'_start'] : '';
                                //$end = array_key_exists($col['column'].'_end', $_POST) && !empty($_POST[$col['column'].'_end']) ? $_POST[$col['column'].'_end'] : '';
                                $start = Common::getStringParam($col['column'].'_start');
                                $end = Common::getStringParam($col['column'].'_end');
                                if ((!isset($start)&&!isset($end)) || (!Common::checkDateParam($start) && !Common::checkDateParam($end))) {
                                    if ($col['type'] == 'day') {
                                        $current_day = date('Y-m-d', time());
                                        $start = date('Y-m-01', time());
                                        $start = $current_day == $start ? date('Y-m-01', strtotime('-1 month', time())) : $start;
                                        $end = date('Y-m-d', strtotime('-1 day', time()));
                                    } elseif ($col['type'] == 'week') {
                                        $start = date('oW', strtotime('-30 week', time()));
                                        $end = date('oW', strtotime('-1 week', time()));
                                    } elseif ($col['type'] == 'month') {
                                        $start = date('Y-m', strtotime('-12 month', time()));
                                        $end = date('Y-m', strtotime('-1 month', time()));
                                    } elseif ($col['type'] == 'dwysum') {
                                        if($period == 'day' || $period == 'all' || $period == 'specified_time'){
                                            $current_day = date('Y-m-d', time());
                                            $start = date('Y-m-01', time());
                                            $start = $current_day == $start ? date('Y-m-01', strtotime('-1 month', time())) : $start;
                                            $end = date('Y-m-d', strtotime('-1 day', time()));
                                        }elseif($period=='week'){
                                            $start = date('oW', strtotime('-29 week', time()));
                                            $end = date('oW', time());
                                        }elseif($period=='month'){
                                            $start = date('Y-m', strtotime('-11 month', time()));
                                            $end = date('Y-m', time());
                                        }
                                    }
                                }
                                if (!empty($start) && empty($end)) {
                                    if (!Common::checkDateParam($start)) {
                                        if ($col['type'] == 'day') {
                                            $current_day = date('Y-m-d', time());
                                            $start = date('Y-m-01', time());
                                            $start = $current_day == $start ? date('Y-m-01', strtotime('-1 month', time())) : $start;
                                        } elseif ($col['type'] == 'week') {
                                            $start = date('oW', strtotime('-30 week', time()));
                                        } elseif ($col['type'] == 'month') {
                                            $start = date('Y-m', strtotime('-12 month', time()));
                                        }
                                    }
                                    $query_condition .= ' and '.$col['expression'].">=:start";
                                    if ($date_is_int) {
                                        $command_params[] = array(
                                            'name' => ':start',
                                            'value' => strtotime($start),
                                            'data_type' => PDO::PARAM_INT,
                                        );
                                    } else {
                                        $command_params[] = array(
                                            'name' => ':start',
                                            'value' => $start,
                                            'data_type' => PDO::PARAM_STR,
                                        );
                                    }
                                } elseif (empty($start) && !empty($end)) {
                                    if (!Common::checkDateParam($end)) {
                                        if ($col['type'] == 'day') {
                                            $end = date('Y-m-d', strtotime('-1 day', time()));
                                        } elseif ($col['type'] == 'week') {
                                            $end = date('oW', strtotime('-1 week', time()));
                                        } elseif ($col['type'] == 'month') {
                                            $end = date('Y-m', strtotime('-1 month', time()));
                                        }
                                    }
                                    if ($col['type'] == 'day' && ($date_is_time || $date_is_int)) {
                                        $query_condition .= ' and '.$col['expression']."<:end";
                                        $end_time = strtotime('+1 day', strtotime($end));
                                        $end_next = date('Y-m-d', $end_time);
                                        
                                        if ($date_is_time) {
                                            $command_params[] = array(
                                                'name' => ':end',
                                                'value' => $end_next,
                                                'data_type' => PDO::PARAM_STR,
                                            );
                                        } elseif ($date_is_int) {
                                            $command_params[] = array(
                                                'name' => ':end',
                                                'value' => $end_time,
                                                'data_type' => PDO::PARAM_INT,
                                            );
                                        }
                                    } else {
                                        $query_condition .= ' and '.$col['expression']."<=:end";
                                        $command_params[] = array(
                                            'name' => ':end',
                                            'value' => $end,
                                            'data_type' => PDO::PARAM_STR,
                                        );
                                    }
                                } elseif (!empty($start) && !empty($end)) {
                                    if ($col['type'] == 'day' && ($date_is_time || $date_is_int)) {
                                        $query_condition .= ' and '.$col['expression'].">=:start and ".$col['expression']."<:end";
                                        $start_time = strtotime($start);
                                        $end_time = strtotime('+1 day', strtotime($end));
                                        $end_next = date('Y-m-d', $end_time);
                                        
                                        if ($date_is_time) {
                                            $command_params[] = array(
                                                'name' => ':start',
                                                'value' => $start,
                                                'data_type' => PDO::PARAM_STR,
                                            );
                                            $command_params[] = array(
                                                'name' => ':end',
                                                'value' => $end_next,
                                                'data_type' => PDO::PARAM_STR,
                                            );
                                        } elseif ($date_is_int) {
                                            $command_params[] = array(
                                                'name' => ':start',
                                                'value' => $start_time,
                                                'data_type' => PDO::PARAM_INT,
                                            );
                                            $command_params[] = array(
                                                'name' => ':end',
                                                'value' => $end_time,
                                                'data_type' => PDO::PARAM_INT,
                                            );
                                        }
                                        
                                    } else {
                                        $query_condition .= ' and '.$col['expression']." between :start and :end";
                                        $command_params[] = array(
                                            'name' => ':start',
                                            'value' => $start,
                                            'data_type' => PDO::PARAM_STR,
                                        );
                                        $command_params[] = array(
                                            'name' => ':end',
                                            'value' => $end,
                                            'data_type' => PDO::PARAM_STR,
                                        );
                                    }
                                }
        
                                $condition_params[$col['column'].'_start'] = $start;
                                $condition_params[$col['column'].'_end'] = $end;
                            }
                        }
                        break;
                    case 'text':
                        foreach ($condition as $col) {
                            //由于分页参数以GET方式传递，所以不能只判断POST
                            //$text = array_key_exists($col['column'], $_POST) && !empty($_POST[$col['column']]) ? $_POST[$col['column']] : '';
                            $text = Common::getStringParam($col['column']);
                            if (!empty($text)) {
                                if ($col['attr'] == 'exact') {
                                    //$query_condition .= ' and '.$col['expression']."='$text'";
                                    $query_condition .= ' and '.$col['expression'].'=:'.$col['column'];
                                    $command_params[] = array(
                                        'name' => ':'.$col['column'],
                                        'value' => $text,
                                        'data_type' => PDO::PARAM_STR,
                                    );
                                } elseif ($col['attr'] == 'partial') {
                                    //$query_condition .= ' and '.$col['expression']." like '%$text%'";
                                    $query_condition .= ' and '.$col['expression']." like :text";
                                    $command_params[] = array(
                                        'name' => ':text',
                                        'value' => "%$text%",
                                        'data_type' => PDO::PARAM_STR,
                                    );
                                }
                            }
                            $condition_params[$col['column']] = $text;
                        }
                        break;
                    case 'list':
                        foreach ($condition as $col) {
                            $list_value = Common::getStringParam($col['column']);
                            if (!empty($list_value)) {
                                //$query_condition .= ' and '.$col['expression']."='$list_value'";
                                $query_condition .= ' and '.$col['expression'].'=:'.$col['column'];
                                $command_params[] = array(
                                    'name' => ':'.$col['column'],
                                    'value' => $list_value,
                                    'data_type' => PDO::PARAM_STR,
                                );
                            }
                            $condition_params[$col['column']] = $list_value;
                        }
                        break;
                    case 'linked':
                        foreach ($condition as $col) {
                            $first = array_key_exists('first', $col) ? $col['first'] : array();
                            $second = array_key_exists('second', $col) ? $col['second'] : array();
                            $third = array_key_exists('third', $col) ? $col['third'] : array();
                            $fourth = array_key_exists('fourth', $col) ? $col['fourth'] : array();
                            $dict = array_key_exists('dict', $col) ? $col['dict'] : '';
        
                            $first_value = $second_value = $third_value = '';    //当一、二、三级列表为默认列表时，列表参数值
                            $second_param_value = $third_param_value = '';    //二、三级列表参数值
                            if (!empty($first)) {
                                $list_value = Common::getStringParam($first['column']);
                                if (in_array($dict, DictList::$sum_detail_list)) {
                                    if ($list_value == '') {
                                        $default_list = DictList::getLinkedPresetByID($dict);
                                        if (!empty($default_list)) {
                                            foreach ($default_list as $k => $v) {
                                                $list_value = $k;
                                                $first_value = $list_value;
                                                break;
                                            }
                                        }
                                    }
                                    //list($query_condition, $command_params, $title_columns) = 
                                    self::_assembleSumDetailConditions(1, $list_value, $first, $query_condition, $command_params, $title_columns);
                                } else {
                                    if ($first['is_default']==0) {
                                        if (!empty($list_value)) {
                                            //$query_condition .= ' and '.$first['expression']."='$list_value'";
                                            $query_condition .= ' and '.$first['expression'].'=:'.$first['column'];
                                            $command_params[] = array(
                                                'name' => ':'.$first['column'],
                                                'value' => $list_value,
                                                'data_type' => PDO::PARAM_STR,
                                            );
                                        }
                                    } elseif ($first['is_default']==1) {
                                        if (!empty($list_value)) {
                                            $first_value = $list_value;
                                        }
                                    }
                                }
                                $condition_params[$first['column']] = $list_value;
                            }
                            if (!empty($second)) {
                                $list_value = Common::getStringParam($second['column']);
                                if (in_array($dict, DictList::$sum_detail_list)) {
                                    if ($list_value == '') {
                                        $default_list = DictList::getLinkedPresetByID($dict, 2, $first_value);
                                        if (!empty($default_list)) {
                                            foreach ($default_list as $k => $v) {
                                                $list_value = $k;
                                                $second_value = $list_value;
                                                break;
                                            }
                                        }
                                    }
                                    //list($query_condition, $command_params, $title_columns) = 
                                    self::_assembleSumDetailConditions(2, $list_value, $second, $query_condition, $command_params, $title_columns);
                                } else {
                                    if ($second['is_default']==0) {
                                        if (!empty($list_value)) {
                                            //$query_condition .= ' and '.$second['expression']."='$list_value'";
                                            $query_condition .= ' and '.$second['expression'].'=:'.$second['column'];
                                            $command_params[] = array(
                                                'name' => ':'.$second['column'],
                                                'value' => $list_value,
                                                'data_type' => PDO::PARAM_STR,
                                            );
                                        } elseif (!empty($first_value)) {
                                            $list = DictList::getLinkedPresetByID($dict,2,$first_value);
                                            if (!empty($list) && $second['value_type'] == 1 && $dict != 'media-type-list') {
                                                $name_list = array_combine($list, $list);
                                                unset($list);
                                                $list = $name_list;
                                                unset($name_list);
                                            }
                                            $value_list = array_keys($list);
                                            $query_condition .= ' and '.$second['expression']." in ('".implode("','", $value_list)."')";
                                        }
                                    } elseif ($second['is_default']==1) {
                                        if (!empty($list_value)) {
                                            $second_value = $list_value;
                                        }
                                    }
                                }
                                $second_param_value = $list_value;
                                $condition_params[$second['column']] = $list_value;
                            }
                            if (!empty($third)) {
                                $list_value = Common::getStringParam($third['column']);
                                if (in_array($dict, DictList::$sum_detail_list)) {
                                    if ($list_value == '') {
                                        $default_list = DictList::getLinkedPresetByID($dict, 3, $second_value);
                                        if (!empty($default_list)) {
                                            foreach ($default_list as $k => $v) {
                                                $list_value = $k;
                                                $third_value = $list_value;
                                                break;
                                            }
                                        }
                                    }
                                    //list($query_condition, $command_params, $title_columns) = 
                                    self::_assembleSumDetailConditions(3, $list_value, $third, $query_condition, $command_params, $title_columns);
                                    /* if ($condition_params[$second['column']] == Common::UNKNOWN_REGION_ID) {
                                        $list = DictList::getLinkedPresetByID($dict,3,Common::UNKNOWN_REGION_ID);
                                        $value_list = array_keys($list);
                                        $query_condition .= ' and '.$third['expression']." in ('".implode("','", $value_list)."')";
                                    } */
                                } else {
                                    if ($third['is_default']==0) {
                                        if (!empty($list_value)) {
                                            //$query_condition .= ' and '.$third['expression']."='$list_value'";
                                            $query_condition .= ' and '.$third['expression'].'=:'.$third['column'];
                                            $command_params[] = array(
                                                'name' => ':'.$third['column'],
                                                'value' => $list_value,
                                                'data_type' => PDO::PARAM_STR,
                                            );
                                        } elseif (!empty($first_value)) {
                                            if (!empty($second_value)) {
                                                $list = DictList::getLinkedPresetByID($dict,3,$second_value);
                                                if (!empty($list) && $third['value_type'] == 1 && $dict != 'media-type-list') {
                                                    $name_list = array_combine($list, $list);
                                                    unset($list);
                                                    $list = $name_list;
                                                    unset($name_list);
                                                }
                                                $value_list = array_keys($list);
                                                $query_condition .= ' and '.$third['expression']." in ('".implode("','", $value_list)."')";
                                            } elseif (empty($second_param_value)) {
                                                //暂只提供大区->城市(分部)
                                                if ($dict == 'area-region-city-team') {
                                                    $list = City::getLinkedCitiesByArea($first_value);
                                                    if (!empty($list) && $third['value_type'] == 1) {
                                                        $name_list = array_combine($list, $list);
                                                        unset($list);
                                                        $list = $name_list;
                                                        unset($name_list);
                                                    }
                                                    $value_list = array_keys($list);
                                                    $query_condition .= ' and '.$third['expression']." in ('".implode("','", $value_list)."')";
                                                }
                                                if ($dict == 'area-region-branch-team') {
                                                    $list = Branch::getLinkedBranchesByArea($first_value);
                                                    if (!empty($list) && $third['value_type'] == 1) {
                                                        $name_list = array_combine($list, $list);
                                                        unset($list);
                                                        $list = $name_list;
                                                        unset($name_list);
                                                    }
                                                    $value_list = array_keys($list);
                                                    $query_condition .= ' and '.$third['expression']." in ('".implode("','", $value_list)."')";
                                                }
                                            }
                                        }
                                    } elseif ($third['is_default']==1) {
                                        if (!empty($list_value)) {
                                            $third_value = $list_value;
                                        }
                                    }
                                }
                                $condition_params[$third['column']] = $list_value;
                            }
                            if (!empty($fourth)) {
                                $list_value = Common::getStringParam($fourth['column']);
                                if (in_array($dict, DictList::$sum_detail_list)) {
                                    if ($list_value == '') {
                                        $default_list = DictList::getLinkedPresetByID($dict, 4, $third_value);
                                        if (!empty($default_list)) {
                                            foreach ($default_list as $k => $v) {
                                                $list_value = $k;
                                                break;
                                            }
                                        }
                                    }
                                    //list($query_condition, $command_params, $title_columns) = 
                                    self::_assembleSumDetailConditions(4, $list_value, $fourth, $query_condition, $command_params, $title_columns);
                                } else {
                                    if ($fourth['is_default']==0) {
                                        if (!empty($list_value)) {
                                            //$query_condition .= ' and '.$fourth['expression']."='$list_value'";
                                            $query_condition .= ' and '.$fourth['expression'].'=:'.$fourth['column'];
                                            $command_params[] = array(
                                                'name' => ':'.$fourth['column'],
                                                'value' => $list_value,
                                                'data_type' => PDO::PARAM_STR,
                                            );
                                        } elseif (!empty($first_value) && !empty($second_value)) {
                                            if (!empty($third_value)) {
                                                $list = DictList::getLinkedPresetByID($dict,4,$third_value);
                                                if (!empty($list) && $fourth['value_type'] == 1) {
                                                    $name_list = array_combine($list, $list);
                                                    unset($list);
                                                    $list = $name_list;
                                                    unset($name_list);
                                                }
                                                $value_list = array_keys($list);
                                                $query_condition .= ' and '.$fourth['expression']." in ('".implode("','", $value_list)."')";
                                            } elseif (empty($third_param_value)) {
                                                //暂不提供四级列表
                                            }
                                        }
                                    }
                                }
                                $condition_params[$fourth['column']] = $list_value;
                            }
                        }
                        break;
                }
            }
        }
        /*  else {
         Yii::app()->user->setFlash('error', '报表未配置筛选条件，请联系管理员核查');
         } */
        
        /////////////////////////////////////////////////根据用户权限拼装SQL/////////////////////////////////////////////////
        $does_query = true;    //是否执行查询
        $super = Yii::app()->user->getstate(Common::SESSION_SUPER);
        $cities = Yii::app()->user->getstate(Common::SESSION_CITIES);
        $branches = Yii::app()->user->getstate(Common::SESSION_BRANCHES);
        $categories = Yii::app()->user->getstate(Common::SESSION_CATEGORY);
        $medias = Yii::app()->user->getstate(Common::SESSION_MEDIA);
        
        $privilege_columns = ColumnForPrivilege::getPrivilegeColumnsByReport($report_id);
        if (!empty($privilege_columns) && empty($super)) {        //empty($super)涵盖了!isset($super)和$super==0，即$super丢失或普通用户
            $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT);
            $privileges = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT, Common::REDIS_PRIVILEGES_FIELD) && !empty($priv[Common::REDIS_PRIVILEGES_FIELD]) ? unserialize($priv[Common::REDIS_PRIVILEGES_FIELD]) : array();
        
            foreach ($privilege_columns as $col => $pri_type) {
                if (($pri_type == Common::PRIVILEGE_TYPE_AREA || $pri_type == Common::PRIVILEGE_TYPE_REGION ||
                    $pri_type == Common::PRIVILEGE_TYPE_CITY) && $cities == 1)
                    continue;
                if (($pri_type == Common::PRIVILEGE_TYPE_AREA || $pri_type == Common::PRIVILEGE_TYPE_REGION ||
                    $pri_type == Common::PRIVILEGE_TYPE_BRANCH) && $branches == 1)
                    continue;
                if ($pri_type == Common::PRIVILEGE_TYPE_CATEGORY && $categories == 1)
                    continue;
                if ($pri_type == Common::PRIVILEGE_TYPE_MEDIA && $medias == 1)
                    continue;
    
                //城市权限缓存已在过滤器中结构化，只要具有城市权限即构造出相应的大区和区域，故不会出现有城市权限无区域、大区权限而又存在大区或区域权限控制字段时报错提示无权限，区域之于大区同理
                $pri_type_values = array_key_exists($pri_type, $privileges)&&!empty($privileges[$pri_type]) ? $privileges[$pri_type] : array();
                if (!empty($pri_type_values)) {
                    if ($pri_type == Common::PRIVILEGE_TYPE_CITY) {
                        $pri_type_values = City::getCityNameByIDs($pri_type_values);
                    }
                    if ($pri_type == Common::PRIVILEGE_TYPE_BRANCH) {
                        $pri_type_values = Branch::getNameByIDs($pri_type_values);
                    }
                    if ($pri_type == Common::PRIVILEGE_TYPE_MEDIA) {
                        $pri_type_values = MediaList::getMediaNameByIDs($pri_type_values);
                    }
                    
                    if ($pri_type == Common::PRIVILEGE_TYPE_CITY || $pri_type == Common::PRIVILEGE_TYPE_BRANCH) {
                        if (!empty($conditions) && array_key_exists('linked', $conditions) && !empty($conditions['linked'])) {
                            foreach ($conditions['linked'] as $link) {
                                $first = array_key_exists('first', $link) ? $link['first'] : array();
                                $second = array_key_exists('second', $link) ? $link['second'] : array();
                                $dict = array_key_exists('dict', $link) ? $link['dict'] : '';
                                
                                if (in_array($dict, DictList::$sum_detail_list)) {
                                    if (!empty($first)) {
                                        $areas = array_key_exists(Common::PRIVILEGE_TYPE_AREA, $privileges)&&!empty($privileges[Common::PRIVILEGE_TYPE_AREA]) ? $privileges[Common::PRIVILEGE_TYPE_AREA] : array();
                                        if (!empty($areas)) {
                                            $query_condition .= " and ".$first['expression']." in ('".implode("','", $areas)."')";
                                            if (!in_array('9999', $pri_type_values)) {
                                                $pri_type_values[] = '9999';
                                            }
                                        }
                                    }
                                    if (!empty($second)) {
                                        if (!isset($areas) || empty($areas)) {
                                            $regions = array_key_exists(Common::PRIVILEGE_TYPE_REGION, $privileges)&&!empty($privileges[Common::PRIVILEGE_TYPE_REGION]) ? $privileges[Common::PRIVILEGE_TYPE_REGION] : array();
                                            if (!empty($regions)) {
                                                $query_condition .= " and ".$second['expression']." in ('".implode("','", $regions)."')";
                                                if (!in_array('9999', $pri_type_values)) {
                                                    $pri_type_values[] = '9999';
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    
                    $query_condition .= " and $col in ('".implode("','", $pri_type_values)."')";
                } else {
                    $does_query = false;
                    Yii::app()->user->setFlash('error', '您没有数据查看权限，请重新登录或联系管理员');
                }
            }
        }
        
        if (!empty($group)) {
            $query_condition .= ' GROUP BY '.implode(',', $group);
        }
        
        if (!empty($order)) {
            $query_condition .= ' ORDER BY ';
            $order_str = '';
            foreach ($order as $col => $or) {
                $order_str .= ', '.$col.' '.$or;
            }
            $order_str = substr($order_str, 1);
            $query_condition .= $order_str;
        }
        
        return array($query_condition, $command_params, $condition_params, $does_query);//, $title_columns
    }
    
    /**
     * 构造查询条件
     */
    public static function assembleConditions($configuration, $report_id, &$params, $period, $conditions, &$select, &$columns, $distinct, $from, $where, $group, $order, $columns_info, $columns_info_citydiv)
    {
        ///////////////////////日周月累计///////////////////////
        $original_table = '';
        $new_table = '';
        switch ($period) {
            case 'week':
                $from_arr = explode(' ', $from);
                $original_table = $from_arr[0];
                $new_table = $from_arr[0].'_week';
                break;
            case 'month':
                $from_arr = explode(' ', $from);
                $original_table = $from_arr[0];
                $new_table = $from_arr[0].'_month';
                break;
            case 'all':
                $from_arr = explode(' ', $from);
                $original_table = $from_arr[0];
                $new_table = $from_arr[0].'_summary';
                break;
        }
    
        $table_exists = true;
        list($db,$model) = Common::getDBConnection($configuration->data_source);
        if ($db && $model) {
            if ($period == 'week' || $period == 'month' || $period == 'all') {
                if (!empty($new_table)) {
                    $table_sql = "SELECT COUNT(*) FROM information_schema.tables WHERE TABLE_SCHEMA='".$model['database']."' AND TABLE_NAME='".$new_table."'";
                    $table_count = $db->createCommand($table_sql)->queryScalar();
                    if (empty($table_count)) {
                        $table_exists = false;
                    }
                } else {
                    $table_exists = false;
                }
            }
        } else {
            Yii::app()->user->setFlash('error', '无法连接数据源，请与管理员联系。');
        }
        
        //指定时间累计
        if ($period == 'specified_time') {
            if (!empty($conditions) && array_key_exists('date', $conditions)) {
                foreach ($conditions['date'] as $col) {
                    if (array_key_exists('sumcols', $col) && !empty($col['sumcols'])) {
                        if (array_key_exists('select', $col['sumcols']) && !empty($col['sumcols']['select'])) {
                            $select = $col['sumcols']['select'];
                        }
                        if (array_key_exists('columns', $col['sumcols']) && !empty($col['sumcols']['columns'])) {
                            $columns = $col['sumcols']['columns'];
                        }
                    }
                }
            }
        }
    
        ///////////////////////日周月累计 END///////////////////////
    
        //选择数据项
        $show_columns = Common::getArrayParam('show_columns');
        $title_columns = array();
        $city_divisions = array();
        $city_div_cols = array();
        foreach ($columns as $column) {
            //$title_columns[$column] = array_key_exists($column, $columns_info)&&!empty($columns_info[$column]['show_name']) ? $columns_info[$column]['show_name'] : $column;
            if (array_key_exists($column, $columns_info)) {
                if ($columns_info[$column]['function'] == ColumnDefine::FUNCTION_CITY_DIVISION_COLUMN) {
                    $function_detail = !empty($columns_info[$column]['function_detail']) ? unserialize($columns_info[$column]['function_detail']) : array();
                    if (in_array(ColumnDefine::FUNCTION_DETAIL_CITY_DIV_AREA_REGION, $function_detail)) {
                        $column_area = $column."_area_".$report_id;
                        $column_region = $column."_region_".$report_id;
                        $city_divisions[$column_area] = $column;
                        $city_divisions[$column_region] = $column;
                        $city_div_cols[$column]['area'] = $column_area;
                        $city_div_cols[$column]['region'] = $column_region;
                        $title_columns[$column_area] = !empty($columns_info_citydiv[$column_area]['show_name']) ? $columns_info_citydiv[$column_area]['show_name'] : $column_area;
                        $title_columns[$column_region] = !empty($columns_info_citydiv[$column_region]['show_name']) ? $columns_info_citydiv[$column_region]['show_name'] : $column_region;
                    }
                    if (in_array(ColumnDefine::FUNCTION_DETAIL_CITY_DIV_WARZONE, $function_detail)) {
                        $column_warzone = $column."_warzone_".$report_id;
                        $city_divisions[$column_warzone] = $column;
                        $city_div_cols[$column]['warzone'] = $column_warzone;
                        $title_columns[$column_warzone] = !empty($columns_info_citydiv[$column_warzone]['show_name']) ? $columns_info_citydiv[$column_warzone]['show_name'] : $column_warzone;
                    }
                }
                $title_columns[$column] = !empty($columns_info[$column]['show_name']) ? $columns_info[$column]['show_name'] : $column;
            } else {
                $title_columns[$column] = $column;
            }
        }
    
        //根据查询条件及用户权限拼装SQL
        //对于汇总明细联动列表，不同的查询条件展示不同字段，校验查询前的字段名是否与查询后的字段名一致，如果不一致则以后者展示（该控件没有单独标识，所以查询都要统一执行校验）
        //Yii::app()->request->getParam('prev_title_columns');
        //$params['prev_title_columns'] = $prev_title_columns_param;
        $prev_title_columns_param = Yii::app()->user->getstate('selfreport_prev_title_columns');
        $prev_title_columns = !empty($prev_title_columns_param) ? unserialize($prev_title_columns_param) : array();
        list($query_condition, $command_params, $condition_params, $does_query) = Common::assembleQueryConditions($report_id, $conditions, $group, $order, $title_columns, $period);
        if ($prev_title_columns !== false) {
            $title_columns_diff1 = array_diff_key($title_columns, $prev_title_columns);
            $title_columns_diff2 = array_diff_key($prev_title_columns, $title_columns);
            if (!empty($title_columns_diff1) || !empty($title_columns_diff2)) {
                $show_columns = array_keys($title_columns);
            }
        }
    
        if (empty($show_columns)) {
            $show_columns = array_keys($title_columns);
        }
        $params['show_columns'] = $show_columns;
    
        //组装查询sql
        $select_cols = array();
        foreach ($show_columns as $show_column) {
            if (array_key_exists($show_column, $select)) {
                $select_cols[] = $select[$show_column];
            }
        }
        if (empty($select_cols)) {
            $select_cols = $select;
        }
    
        $sql = 'SELECT ';
        if ($distinct == 1) {
            $sql .= 'distinct ';
        }
    
        $sql .= implode(',', $select_cols).' FROM '.$from.' WHERE '.$where.' '.$query_condition;
    
        if (!empty($original_table) && !empty($new_table)) {
            $sql = str_replace($original_table, $new_table, $sql);
        }
        
        return array($does_query, $table_exists, $show_columns, $title_columns, $sql, $command_params, $condition_params, $city_divisions, $city_div_cols);
    }
    
    
     /**
     * 取所有图表类型
     * @author pangaofeng
     */
    public static function getChartTypeList()
    {
        $chart_types = array('' => '请选择图表类型','line'=>'折线图','bar'=>'柱状图','pie'=>'饼图');
        
        return $chart_types;
    }
}
