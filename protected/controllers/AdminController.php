<?php

class AdminController extends Controller
{
    public static $privtypes = array('0'=>'普通权限', UserGroup::SUPER_GROUP=>'超级权限');
    public static $privlevels = array('0'=>'请选择权限级别', '3'=>'全报表查看用户', '2'=>'报表配置员', '1'=>'超级管理员');
    
    public function filters()
    {
        return array(
                'accessControl',
                array(
                        'application.filters.AccessFilter',
                ),
            
        );
    }
    
    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow',
                'users'=>array('@'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }
    
    //校验权限
    protected function beforeAction($action) {
        $super = Yii::app()->user->getstate(Common::SESSION_SUPER);
        if (isset($super) && $super == 1) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * 个人设置
     * @author sangxiaolong
     */
    public function actionSetting()
    {
        $model = new PasswordForm();
        if (isset($_POST['PasswordForm']))
        {
            $model->attributes = $_POST['PasswordForm'];
            if ($model->password || $model->newPassword || $model->verifyPassword)
            {
                $model->scenario = 'update';
                if ($model->validate() && $model->updatePassword())
                {
                    $model = new PasswordForm();
                    Yii::app()->user->setFlash('success', '密码修改成功!');
                }
            }
        }
        $this->render('setting',array('model'=>$model));
    }
    
    /**
     * 管理用户
     * @author sangxiaolong
     */
    public function actionIndex()
    {
        $email = Common::getEMailParam('email');
        $username = Common::getStringParam('username');
        $mobile = Common::getStringParam('mobile');
        $city_id = Common::getNumParam('city_id');
        $city_name = Common::getStringParam('city_name');
        $user_group_id = Common::getNumParam('user_group');
        
        $cityname = City::getCityNameById($city_id, false);
        if (!empty($city_name) && $cityname!=$city_name) {
            Yii::app()->user->setFlash('error', '根据“城市”查询请务必点选提示框中的城市，如，北京[BJ]');
        }
        
        $user_group_select = array(''=>'请选择用户组');
        $user_group = UserGroup::getAllGroups();
        $user_group_select = $user_group_select + $user_group; 
        
        if (!empty($city_id) || !empty($user_group_id)) {
            if (!empty($city_id))
                $user_name = Privileges::getUserByCity($city_id);
            if (!empty($user_group_id))
                $user_id = Privileges::getUserIDByGroup($user_group_id);
            
            $criteria = new CDbCriteria();
            if (isset($user_name))
                $criteria->addInCondition('username', $user_name);
            if (isset($user_id))
                $criteria->addInCondition('id', $user_id);
            $criteria->distinct = true;
            
            $dataProvider = new CActiveDataProvider('Admin', array(
                    'pagination'=>array(
                            'pageSize'=>10,
                            'params'=>array(
                                    'city_id' => $city_id,
                                    'city_name' => $city_name,
                                    'user_group' => $user_group_id,
                            ),
                    ),
                    'criteria' => $criteria
            ));
        } else {
            $criteria = new CDbCriteria();
            if (!empty($email))
                $criteria->compare('email', $email, true);
            if (!empty($username))
                $criteria->compare('username', $username, true);
            if (!empty($mobile))
                $criteria->compare('mobile', $mobile, true);
            
            $dataProvider = new CActiveDataProvider('Admin', array(
                    'pagination'=>array(
                            'pageSize'=>10,
                            'params'=>array(
                                    'email' => $email,
                                    'username' => $username,
                                    'mobile' => $mobile,
                            ),
                    ),
                    'criteria' => $criteria
            ));
        }
        
        $this->render('index',array(
                'dataProvider' => $dataProvider,
                'email' => $email,
                'username' => $username,
                'mobile' => $mobile,
                'city_id' => $city_id,
                'city_name' => $city_name,
                'user_group_id' => $user_group_id,
                'user_group' => $user_group_select,
        ));
    }
    
    /**
     * 更新用户资料
     * @author chensm
     */
    public function actionUpdate($id)
    {
        $model = Admin::model()->findByPk($id);
        if (!empty($_POST))
        {
            $isPassChange = 2;    //无密码输入
            $model->email = $_POST['Admin']['email'];
            $model->mobile = $_POST['Admin']['mobile'];
            if (!empty($_POST['password']))
            {
                if (Utility::checkPassRules($_POST['password']))
                {
                    $model->password = $_POST['password'];
                    $isPassChange = 1;    //新密码符合规则
                } else {
                    $isPassChange = 0;    //新密码不符合规则
                }
            }
            if ($model->validate())
            {
                if ($isPassChange)
                {
                    if ($isPassChange == 1) {
                        $to = $model->email;
                        $subject = "您的自助报表平台登录密码已被重置，请使用新密码登录";
                        $content = "您好！您的登录密码已被重置为".htmlentities($model->password, ENT_QUOTES, 'utf-8')."，请使用新密码进行登录";
                        Mail::send($to, $subject, $content, MailLog::TYPE_CHANGE_PASSWORD);
                        $model->password = $model->hashPassword($model->password, $model->salt);
                    }
                    if ($model->save()) {
                        Yii::app()->user->setFlash('success', '修改成功!');
                    }
                } else {
                    Yii::app()->user->setFlash('error', '修改失败! 密码必须8-32位且含有字母、数字和符号!');
                }
            }
        }
        
        $this->render('update',array('model'=>$model));
    }
    
    /**
     * 删除用户
     * @author sangxiaolong
     */
    public function actionDelete($id)
    {
        $user = Admin::model()->findByPk($id);
        if(empty($user))
        {
            throw new CHttpException('404','未找到指定的用户');
        }
        else
        {
            if(Yii::app()->user->id == $id)
            {
                throw new CHttpException('403','您不可以删除自己');
            }
            else
            {
                //if($user->updateByPk($id, array('status'=>-1)))
                if ($user->deleteByPk($id))
                {
                    Privileges::model()->deleteByPk($id);
                    $this->redirect(array('admin/index'));
                }
            }
        }
    }

    /**
     * 启用或禁用账户
     * @author sangxiaolong
     */
    public function actionToggle($id)
    {
        $email = Common::getEMailParam('email');
        $username = Common::getStringParam('username');
        $mobile = Common::getStringParam('mobile');
        $city_id = Common::getNumParam('city_id');
        $city_name = Common::getStringParam('city_name');
        $user_group_id = Common::getNumParam('user_group');
        $page = Common::getNumParam('Admin_page');
        
        $user = Admin::model()->findByPk($id);
        if (empty($user))
        {
            throw new CHttpException('404', '未找到指定的用户');
        }
        else
        {
            if (Yii::app()->user->id == $id)
            {
                throw new CHttpException('403', '您不可以禁用自己');
            }
            else
            {
                if ($user->updateByPk($id, array(
                        'status' => $user->status == 0 ? 1 : 0,
                        'create_time' => time(),
                        'lock_time' => 0,
                        'login_failed' => 0,
                        'sms_times' => 0,
                        'cookie_failed' => 0)))
                {
                    $redirect = array('admin/index', 
                            'email' => $email,
                            'username' => $username,
                            'mobile' => $mobile,
                            'city_id' => $city_id,
                            'city_name' => $city_name,
                            'user_group' => $user_group_id,
                    );
                    if (!empty($page))
                        $redirect['Admin_page'] = $page;
                    $this->redirect($redirect);
                }
            }
        }
    }
    
    /**
     * 账户延期
     */
    public function actionProlong($id)
    {
        $email = Common::getEMailParam('email');
        $username = Common::getStringParam('username');
        $mobile = Common::getStringParam('mobile');
        $city_id = Common::getNumParam('city_id');
        $city_name = Common::getStringParam('city_name');
        $user_group_id = Common::getNumParam('user_group');
        $page = Common::getNumParam('Admin_page');
        
        $user = Admin::model()->findByPk($id);
        if (empty($user))
        {
            throw new CHttpException('404', '未找到指定的用户');
        }
        else
        {
            if ($user->updateByPk($id, array('create_time' => time())))
            {
                    $redirect = array('admin/index', 
                            'email' => $email,
                            'username' => $username,
                            'mobile' => $mobile,
                            'city_id' => $city_id,
                            'city_name' => $city_name,
                            'user_group' => $user_group_id,
                    );
                    if (!empty($page))
                        $redirect['Admin_page'] = $page;
                    $this->redirect($redirect);
            }
        }
    }
    
    /**
     * Create an new user.
     */
    public function actionCreate()
    {
        $model = new Admin('create');
        $usergroups = Common::getUserGroups();
        $medias = Common::getAllMedias();
        $cities = $privileges = array();
        
//        $areas = Region::getAreas();    //获取所有大区 Region
        $regions = Region::getRegions();    //获取所有区域 Region
    
        list($first_cates, $second_cates, $third_cates, $goods_cate_map) = Category::getCateData();
        $cities_wdt_only = Branch::getWdtOnlyForAdmin();
        
        if (isset($_POST['Admin']))
        {
//            if (empty($_POST['category'])) {
//                Yii::app()->user->setFlash('error', '未选择品类，无法提交!');
//            } else {
                $model->attributes = $_POST['Admin'];
                $password = $model->password;
                $is_super = isset($_POST['privlevel'])?$_POST['privlevel']:0;
                if((!empty($_POST['allcity']) && Utility::inputFilter($_POST['allcity'], 'alpha'))||in_array($is_super, Privileges::$super_admin))
                {
                    $cities[] = 'all';
                }
                else
                {
                    if (!empty($_POST['selected-ids']) && preg_match('/^[0-9,]+$/', $_POST['selected-ids']))
                    {
                        $cities = array_filter(explode(',', preg_match('/^,/', $_POST['selected-ids']) ? substr($_POST['selected-ids'], 1) : $_POST['selected-ids']));
                    }
                    if(!empty($_POST['allsite']) && Utility::inputFilter($_POST['allsite'], 'alpha'))
                    {
                        $cities[] = 9999;
                    }
                    if(!empty($_POST['quanguo']) && Utility::inputFilter($_POST['quanguo'], 'alpha'))
                    {
                        $cities[] = 0;
                    }
                    /* if(!empty($_POST['allcity']) && Utility::inputFilter($_POST['allcity'], 'alpha'))
                    {
                        $cities = array('0','9999');
                    } */
                    $area = !empty($_POST['area']) ? array_filter($_POST['area']) : array();
                    if(!empty($area)){
                        $privileges[Common::PRIVILEGE_TYPE_AREA] = $area;
                    }
                    $region = !empty($_POST['region']) ? array_filter($_POST['region']) : array();
                    if(!empty($region)){
                        $privileges[Common::PRIVILEGE_TYPE_REGION] = $region;
                    }
                }
                if(!empty($cities))
                {
                    asort($cities);
                    $city_ids = array_unique($cities);
                    $privileges[Common::PRIVILEGE_TYPE_CITY] = $city_ids;
                    $wdt_cities = array_keys(Branch::getWdtBranchesByTGID($city_ids));
                    if (!empty($wdt_cities)) {
                        $privileges[Common::PRIVILEGE_TYPE_BRANCH] = Utility::changeArrayValueType($wdt_cities, 'string');
                    }
                }

                /////////////////////////配置网店通全城市及专属城市/////////////////////////
                if (in_array($is_super, Privileges::$super_admin)) {
                    if (isset($privileges[Common::PRIVILEGE_TYPE_BRANCH])) unset($privileges[Common::PRIVILEGE_TYPE_BRANCH]);
                    $privileges[Common::PRIVILEGE_TYPE_BRANCH][] = 'all';
                } elseif (in_array('all', $cities)) {
                    if (isset($privileges[Common::PRIVILEGE_TYPE_BRANCH])) unset($privileges[Common::PRIVILEGE_TYPE_BRANCH]);
                    if (!empty($_POST['priv_wdt_only']) && count($_POST['priv_wdt_only']) == count($cities_wdt_only)) {
                        $privileges[Common::PRIVILEGE_TYPE_BRANCH][] = 'all';
                    } else {
                        $wdt_cities = array_keys(Branch::getWdtBranchesByAllCity());
                        if (!empty($wdt_cities)) {
                            $privileges[Common::PRIVILEGE_TYPE_BRANCH] = Utility::changeArrayValueType($wdt_cities, 'string');;
                        }
                    }
                }
                if (!empty($_POST['priv_wdt_only'])) {
                    $wdt_cities = !empty($privileges[Common::PRIVILEGE_TYPE_BRANCH]) ? $privileges[Common::PRIVILEGE_TYPE_BRANCH] : array();
                    if (!in_array('all', $wdt_cities)) {
                        $wdt_cities = array_merge($wdt_cities, $_POST['priv_wdt_only']);
                        asort($wdt_cities);
                        $privileges[Common::PRIVILEGE_TYPE_BRANCH] = array_unique($wdt_cities);
                    }
                }
                if(!empty($cities) && in_array(9999, $cities))
                {
                    $privileges[Common::PRIVILEGE_TYPE_BRANCH][] = '9999';
                }
                //////////////////////////////////////////////////
                
                if (!empty($_POST['category'])) {
                    $privileges[Common::PRIVILEGE_TYPE_CATEGORY] = $_POST['category'];
                    if (count($privileges[Common::PRIVILEGE_TYPE_CATEGORY]) == count($first_cates) || in_array($is_super, Privileges::$super_admin)) {
                        $privileges[Common::PRIVILEGE_TYPE_CATEGORY][] = 'all';
                    }
                }

                if (!empty($_POST['medias'])) {
                    $media_arr = $_POST['medias'];
                    if (count($_POST['medias']) == 1 && in_array('', $_POST['medias'])) {
                        $media_arr = array();
                    }
                    if (!empty($media_arr)) {
                        if (in_array('all', $media_arr)) {
                            $privileges[Common::PRIVILEGE_TYPE_MEDIA][] = 'all';
                        } else {
                            foreach ($media_arr as $media) {
                                if ($media != '-1') {
                                    $privileges[Common::PRIVILEGE_TYPE_MEDIA][] = $media;
                                }
                            }
                        }
                    }
                }

                if (Utility::checkPassRules($password))
                {
                    if ($model->save())
                    {
                        $new_user = Admin::getUserInformation($model->attributes['username']);
                        Yii::app()->user->setFlash('success', '成功添加用户"'.$new_user->username.'"!');
    
                        $privtype = isset($_POST['privtype'])?$_POST['privtype']:0;
                        if ($privtype==UserGroup::SUPER_GROUP) {
                            $user_group = array(strval(UserGroup::SUPER_GROUP));
                        } else {
                            $user_group = array_key_exists('usergroup', $_POST) ? $_POST['usergroup'] : '';
                        }
                        $privileges_obj = new Privileges();
                        $privileges_obj_attr = array(
                                'user_id' => $new_user->id,
                                'username' => $new_user->username,
                                'is_super' => $is_super,
                                'user_group_id' => empty($user_group) ? '' : serialize($user_group),
                                'privileges' => serialize($privileges),
                                /* 'user_group_id' => intval($user_group),
                                'cities' => !empty($all_city) ? $all_city : 0,
                                'category' => !empty($all_cate) ? $all_cate : 0, */
                        );
                        $privileges_obj->attributes = $privileges_obj_attr;
                        if($privileges_obj->save())
                            Yii::app()->user->setFlash('success', '成功添加用户"'.$new_user->username.'"!');
                        else
                            Yii::app()->user->setFlash('error', '成功添加用户"'.$new_user->username.'"，但菜单权限添加失败!');
    
                        $to = $model->email;
                        $subject = "您的自助报表平台账号已开通";
                        $content = "您好！现在已经为您开通了自助报表平台账号：<br><br>用户名：".$model->username."<br><br>密码：".htmlentities($password, ENT_QUOTES, 'utf-8')."<br><br>网址：https://report.fengchaotx.com";
                        Mail::send($to, $subject, $content, MailLog::TYPE_CREATE_USER);
                        $this->redirect(array('admin/index'));
                    } else {
                        Yii::app()->user->setFlash('error', '添加用户失败!');
                    }
                } else {
                    Yii::app()->user->setFlash('error', '密码必须8-32位且含有字母、数字和符号!');
                }
//            }//上面的Yii::app()->user->setFlash('error', '未选择品类，无法提交!');逻辑end点
        } else {
            $password = Utility::generatePassword();
            $model->password = $password;
        }
        $this->render('create', array(
                'model' => $model,
                'usergroups' => $usergroups,
                'medias' => $medias,
//                'area_arr' => $areas,
                'region_arr' => $regions,
                'first_cates' => $first_cates,
                'privtypes' => self::$privtypes,
                'privlevels' => self::$privlevels,
                'cities_wdt_only' => $cities_wdt_only,
        ));
    }
    /**
     * 查看用户权限
     * @author chensm
     */
    public function actionAuthority($id)
    {
        $model = Admin::model()->findByPk($id);
        $usergroups = Common::getUserGroups();
        $medias = Common::getAllMedias();
        
        $areas = Region::getAreas();    //获取所有大区 Region
        $regions = Region::getRegions();    //获取所有区域 Region
        
        list($first_cates, $second_cates, $third_cates, $goods_cate_map) = Category::getCateData();
        $cities_wdt_only = Branch::getWdtOnlyForAdmin();
        
        $privileges_obj = Privileges::getPrivileges($model->username);
        $privileges_obj = isset($privileges_obj) ? $privileges_obj : new Privileges();
        $user_group = isset($privileges_obj->user_group_id)&&!empty($privileges_obj->user_group_id)&&preg_match('/^a:/', $privileges_obj->user_group_id) ? unserialize($privileges_obj->user_group_id) : array();
        $privileges = !empty($privileges_obj->privileges) ? unserialize($privileges_obj->privileges) : array();
        
        $cities = array_key_exists(Common::PRIVILEGE_TYPE_CITY, $privileges)&&!empty($privileges[Common::PRIVILEGE_TYPE_CITY]) ? $privileges[Common::PRIVILEGE_TYPE_CITY] : array();
        
        $region = array_key_exists(Common::PRIVILEGE_TYPE_REGION, $privileges)&&!empty($privileges[Common::PRIVILEGE_TYPE_REGION]) ? $privileges[Common::PRIVILEGE_TYPE_REGION] : array();
        $area = array_key_exists(Common::PRIVILEGE_TYPE_AREA, $privileges)&&!empty($privileges[Common::PRIVILEGE_TYPE_AREA]) ? $privileges[Common::PRIVILEGE_TYPE_AREA] : array();
        
        $privilege_medias = array_key_exists(Common::PRIVILEGE_TYPE_MEDIA, $privileges)&&!empty($privileges[Common::PRIVILEGE_TYPE_MEDIA]) ? $privileges[Common::PRIVILEGE_TYPE_MEDIA] : array();
        $category = array_key_exists(Common::PRIVILEGE_TYPE_CATEGORY, $privileges)&&!empty($privileges[Common::PRIVILEGE_TYPE_CATEGORY]) ? $privileges[Common::PRIVILEGE_TYPE_CATEGORY] : array();
    
        #所有权限
        if(!empty($_POST))
        {
//            if (empty($_POST['category'])) {
//                Yii::app()->user->setFlash('error', '未选择品类，无法提交!');
//            } else {
                $privileges=array();
                $cities = array();
    
                $is_super = isset($_POST['privlevel'])?$_POST['privlevel']:0;
                if((!empty($_POST['allcity']) && Utility::inputFilter($_POST['allcity'], 'alpha'))||in_array($is_super, Privileges::$super_admin))
                {
                    $cities[] = 'all';
                }
                else
                {
                    if (!empty($_POST['selected-ids']) && preg_match('/^[0-9,]+$/', $_POST['selected-ids']))
                    {
                        $cities = array_filter(explode(',', preg_match('/^,/', $_POST['selected-ids']) ? substr($_POST['selected-ids'], 1) : $_POST['selected-ids']));
                    }
                    if(!empty($_POST['allsite']) && Utility::inputFilter($_POST['allsite'], 'alpha'))
                    {
                        $cities[] = 9999;
                    }
                    if(!empty($_POST['quanguo']) && Utility::inputFilter($_POST['quanguo'], 'alpha'))
                    {
                        $cities[] = 0;
                    }
                    /* if(!empty($_POST['allcity']) && Utility::inputFilter($_POST['allcity'], 'alpha'))
                    {
                        $cities = array('0','9999');
                    } */
            
                    $area = !empty($_POST['area']) ? array_filter($_POST['area']) : array();
                    if(!empty($area)){
                        $privileges[Common::PRIVILEGE_TYPE_AREA] = $area;
                    }
                    $region = !empty($_POST['region']) ? array_filter($_POST['region']) : array();
                    if(!empty($region)){
                        $privileges[Common::PRIVILEGE_TYPE_REGION] = $region;
                    }
                }
                if(!empty($cities))
                {
                    asort($cities);
                    $city_ids = array_unique($cities);
                    $privileges[Common::PRIVILEGE_TYPE_CITY] = $city_ids;
                    $wdt_cities = array_keys(Branch::getWdtBranchesByTGID($city_ids));
                    if (!empty($wdt_cities)) {
                        $privileges[Common::PRIVILEGE_TYPE_BRANCH] = Utility::changeArrayValueType($wdt_cities, 'string');
                    }
                }
        
                /////////////////////////配置网店通全城市及专属城市/////////////////////////
                if (in_array($is_super, Privileges::$super_admin)) {
                    if (isset($privileges[Common::PRIVILEGE_TYPE_BRANCH])) unset($privileges[Common::PRIVILEGE_TYPE_BRANCH]);
                    $privileges[Common::PRIVILEGE_TYPE_BRANCH][] = 'all';
                } elseif (in_array('all', $cities)) {
                    if (isset($privileges[Common::PRIVILEGE_TYPE_BRANCH])) unset($privileges[Common::PRIVILEGE_TYPE_BRANCH]);
                    if (!empty($_POST['priv_wdt_only']) && count($_POST['priv_wdt_only']) == count($cities_wdt_only)) {
                        $privileges[Common::PRIVILEGE_TYPE_BRANCH][] = 'all';
                    } else {
                        $wdt_cities = array_keys(Branch::getWdtBranchesByAllCity());
                        if (!empty($wdt_cities)) {
                            $privileges[Common::PRIVILEGE_TYPE_BRANCH] = Utility::changeArrayValueType($wdt_cities, 'string');;
                        }
                    }
                }
                if (!empty($_POST['priv_wdt_only'])) {
                    $wdt_cities = !empty($privileges[Common::PRIVILEGE_TYPE_BRANCH]) ? $privileges[Common::PRIVILEGE_TYPE_BRANCH] : array();
                    if (!in_array('all', $wdt_cities)) {
                        $wdt_cities = array_merge($wdt_cities, $_POST['priv_wdt_only']);
                        asort($wdt_cities);
                        $privileges[Common::PRIVILEGE_TYPE_BRANCH] = array_unique($wdt_cities);
                    }
                }
                if(!empty($cities) && in_array(9999, $cities))
                {
                    $privileges[Common::PRIVILEGE_TYPE_BRANCH][] = '9999';
                }
                //////////////////////////////////////////////////
                
                unset($category);
                $category = array();
                if (!empty($_POST['category'])) {
                    $category = $_POST['category'];
                    $privileges[Common::PRIVILEGE_TYPE_CATEGORY] = $_POST['category'];
                    if (count($privileges[Common::PRIVILEGE_TYPE_CATEGORY]) == count($first_cates) || in_array($is_super, Privileges::$super_admin)) {
                        $privileges[Common::PRIVILEGE_TYPE_CATEGORY][] = 'all';
                    }
                }
        
                unset($privilege_medias);
                $privilege_medias = array();
                if (!empty($_POST['medias'])) {
                    $privilege_medias = $_POST['medias'];
                    if (count($_POST['medias']) == 1 && in_array('', $_POST['medias'])) {
                        $privilege_medias = array();
                    }
                    if (!empty($privilege_medias)) {
                        if (in_array('all', $privilege_medias)) {
                            $privileges[Common::PRIVILEGE_TYPE_MEDIA][] = 'all';
                        } else {
                            foreach ($privilege_medias as $media) {
                                if ($media != '-1') {
                                    $privileges[Common::PRIVILEGE_TYPE_MEDIA][] = $media;
                                }
                            }
                        }
                    }
                }
                
                $privtype = isset($_POST['privtype'])?$_POST['privtype']:0;
                if ($privtype==UserGroup::SUPER_GROUP) {
                    $user_group = array(strval(UserGroup::SUPER_GROUP));
                } else {
                    $user_group = array_key_exists('usergroup', $_POST) ? $_POST['usergroup'] : '';
                }
                $privileges_obj_attr = array(
                        'user_id' => $model->id,
                        'username' => $model->username,
                        'is_super' => $is_super,
                        'user_group_id' => empty($user_group) ? '' : serialize($user_group),
                        'privileges' => !empty($privileges) ? serialize($privileges) : '',
                        /*'user_group_id' => intval($user_group),
                         'cities' => !empty($all_city) ? $all_city : 0,
                         'category' => !empty($all_cate) ? $all_cate : 0, */
                );
        
                $privileges_obj->attributes = $privileges_obj_attr;
                if($privileges_obj->save()) {
                    Yii::app()->user->setFlash('success', '用户权限修改成功!');
                } else {
                    Yii::app()->user->setFlash('error', '用户权限修改失败!');
                }
//            }上面的Yii::app()->user->setFlash('error', '未选择品类，无法提交!');逻辑end点
        }
    
        $priv_wdt_cities = array_key_exists(Common::PRIVILEGE_TYPE_BRANCH, $privileges)&&!empty($privileges[Common::PRIVILEGE_TYPE_BRANCH]) ? $privileges[Common::PRIVILEGE_TYPE_BRANCH] : array();
        $cities_wdt_only_keys = Utility::changeArrayValueType(array_keys($cities_wdt_only), 'string');
        if (in_array('all', $priv_wdt_cities)) {
            $priv_wdt_only = $cities_wdt_only_keys;
        } else {
            $priv_wdt_only = array_intersect($priv_wdt_cities, $cities_wdt_only_keys);
        }
        
        $this->render('authority',array(
                'model' => $model,
                'privileges_obj' => $privileges_obj,
                'usergroups' => $usergroups,
                'usergroup' => $user_group===false ? array() : $user_group,
                'medias' => $medias,
                'privilege_medias' => $privilege_medias,
                'area_arr' => $areas,
                'area' => $area,
                'region_arr' => $regions,
                'region' => $region,
                'cities' => $cities,
                'first_cates' => $first_cates,
                'category' => $category,
                'privtypes' => self::$privtypes,
                'privtype' => $user_group!==false && in_array(UserGroup::SUPER_GROUP, $user_group) ? UserGroup::SUPER_GROUP : 0,
                'privlevels' => self::$privlevels,
                'privlevel' => isset($privileges_obj)?$privileges_obj->is_super:0,
                'cities_wdt_only' => $cities_wdt_only,
                'priv_wdt_only' => $priv_wdt_only,
        ));
    }
}
