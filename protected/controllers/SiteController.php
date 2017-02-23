<?php
class SiteController extends Controller
{
    public function filters()
    {
        return array('accessControl');
    }
    
    /**
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow', 'users'=>array('@')),
            array('allow',
                'actions' => array(
                    'captcha',    //由CCaptcha生成的图像元素显示一张由当前控制器中的CCaptchaAction动作 生成的验证码图片。 默认是，动作ID应该为‘captcha’，可以通过修改captchaAction设置。 
                    'login',
                    'logout',
                    'error',
                    'failed',
                    'checksys',
                ),
                'users'=>array('*'),
            ),
            array('deny',
                'users'=>array('*'),
            ),
        );
    }

    /**
     * Declares class-based actions.
     */
    public function actions()
    {
        return array(
            // captcha action renders the CAPTCHA image displayed on the contact page
            'captcha' => array(
                'class' => 'CCaptchaAction',
                'backColor' => 0xf5f5f5,
                'minLength' => 4,
                'maxLength' => 4,
            ),
        );
    }

    /**
     * 错误处理
     * @author sangxiaolong
     */
    public function actionError()
    {
        if($error = Yii::app()->errorHandler->error)
        {
            if(Yii::app()->request->isAjaxRequest)
                echo $error['message'];
            else
                $this->render('error', $error);
        }
    }

    /**
     * 登录
     * @author sangxiaolong
     */
    public function actionLogin()
    {
        if (!Yii::app()->user->isGuest)
        {
            $this->redirect(Yii::app()->homeUrl);
        }

        $model = new LoginForm('login');

        if (isset($_POST['LoginForm']))
        {
            $model->attributes = $_POST['LoginForm'];
            if ($model->validate() && $model->login())
            {
                //清除redis中的登录信息
                $del_keys = Yii::app()->redis->getClient()->keys(Common::REDIS_ACCESS.Yii::app()->user->name.'*');
                if (!empty($del_keys))
                    Yii::app()->redis->getClient()->delete($del_keys);

                $user = Admin::model()->findByPk(Yii::app()->user->id);

                $cookie=Yii::app()->getRequest()->getCookies()->itemAt('_dc_vc');
                if($cookie && !empty($cookie->value) && strpos($cookie->value, $user->username) !== FALSE)
                {
                    if (($data=Yii::app()->getSecurityManager()->validateData($cookie->value, $user->validation_key))!==false)
                    {
                        $data=@unserialize($data);
                        if(is_array($data) && isset($data[0]))
                        {
                            $name = '';
                            list($name)=$data;
                            if ($user->validation_expire < time()) {
//                                $this->loginToken($user);
//                                return;
                            } else {
                                //Yii::app()->session['user_verify'] = 'pass';    //应用于controller的filter
                                Admin::model()->updateByPk(Yii::app()->user->id, array('lock_time' => 0, 'login_failed' => 0, 'sms_times' => 0));    //验证成功后部分条件需要置0

                                //将登录信息存入redis
                                $loginfo = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_ACCESS_LOGINFO);
                                $loginfo[Common::REDIS_ACCESS_IP] = Utility::getRealIP();
                                $loginfo[Common::REDIS_ACCESS_USER_AGENT] = Yii::app()->request->userAgent;
                                $loginfo[Common::REDIS_ACCESS_STATUS] = Common::REDIS_V_ACCESS_STATUS_SMS;
                                Yii::app()->redis->getClient()->setTimeout(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_ACCESS_LOGINFO, 12*Common::REDIS_DURATION);

                                //检查是否具体其他平台权限
                                $system = new ARedisSet(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_ACCESS_SYSTEM);
                                $privileges_obj = Privileges::getPrivileges(Yii::app()->user->name);
                                if ($privileges_obj) {
                                    $privileges = unserialize($privileges_obj->privileges);
                                    if (in_array($privileges_obj->is_super, Common::$super_admin) || (!empty($privileges) && array_key_exists('menu', $privileges) && !empty($privileges['menu']))) {
                                        $system->add(Common::REDIS_ACCESS_SYSTEM_DCPLAT);
                                    } else {
                                        $privilege = SFRTPrivileges::getPrivileges(Yii::app()->user->name);
                                        if ($privilege) {
                                            $user_group = isset($privilege->user_group_id)&&!empty($privilege->user_group_id)&&preg_match('/^a:/', $privilege->user_group_id) ? unserialize($privilege->user_group_id) : array();
                                            $report_ids = SFRTReportPrivileges::getReportsByUsergroup($user_group);
                                            if (SFRTMenu::checkSelfReports($report_ids)) {
                                                $system->add(Common::REDIS_ACCESS_SYSTEM_DCPLAT);
                                                Yii::app()->user->setstate('dcplat_only_selfreport', 1);
                                            }
                                        }
                                    }
                                }

                                Yii::app()->redis->getClient()->setTimeout(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_ACCESS_SYSTEM, 12*Common::REDIS_DURATION);
                                PhpClient::get(Yii::app()->params['checksys_decision'].Yii::app()->user->name);    //取是否具有决策系统权限
                                PhpClient::get(Yii::app()->params['checksys_analytics'].Yii::app()->user->id);     //取是否具有流量分析系统权限
                                PhpClient::get(Yii::app()->params['checksys_dsfhz'].Yii::app()->user->id);         //取是否具有第三方平台权限
                                PhpClient::get(Yii::app()->params['checksys_selfreport'].Yii::app()->user->id);    //取是否具有自助报表系统权限
                                PhpClient::get(Yii::app()->params['checksys_selfquery'].Yii::app()->user->name);    //取是否具有即席查询系统权限

                                $this->redirect('index');
                                return;
                            }
                        }
                    } else {
                        //cookie验证错误次数
                        $cookie_failed = $user->cookie_failed + 1;
                        if ($cookie_failed > Admin::COOKIE_VALIDATE_LIMIT) {
                            $locktime = $user->lockUser(Admin::ACCOUNT_LOCK_DAY);
                            //清除redis中的登录信息
                            $del_keys = Yii::app()->redis->getClient()->keys(Common::REDIS_ACCESS.Yii::app()->user->name.'*');
                            if (!empty($del_keys))
                                Yii::app()->redis->getClient()->delete($del_keys);

                            Yii::app()->user->logout();
                            $this->redirect(array('site/failed/type/1'));
                            return;
                        } else {
                            $user->updateByPk($user->id, array('cookie_failed' => $cookie_failed));
                            //$this->redirect(array('site/logout'));
                            $this->loginToken($user);
                            return;
                        }
                    }
                } else {
                    $this->loginToken($user);
                    return;
                }

            }
        }

        $this->render('login', array(
            'model' => $model
        ));
    }

    /**
     * 退出
     * @author sangxiaolong
     */
    public function actionLogout()
    {
        setcookie('time_limit', '', time()-3600, '/', '.dc.com');    //以防verify时cookie清除不成功
        //清除redis中的登录信息
        $del_keys = Yii::app()->redis->getClient()->keys(Common::REDIS_ACCESS.Yii::app()->user->name.'*');
        if (!empty($del_keys))
            Yii::app()->redis->getClient()->delete($del_keys);
        
        Yii::app()->user->logout();
        $this->redirect(Yii::app()->params['login_url']);//array('site/login')
    }
    
    /**
     * 验证超过限制退出
     * @author chensm
     */
    public function actionFailed()
    {
        $type = Yii::app()->request->getParam('type', '');
        $type = ctype_digit($type) ? $type : '';
        //清除redis中的登录信息
        $del_keys = Yii::app()->redis->getClient()->keys(Common::REDIS_ACCESS.Yii::app()->user->name.'*');
        if (!empty($del_keys))
            Yii::app()->redis->getClient()->delete($del_keys);
        
        Yii::app()->user->logout();
        $this->render('failed',array('type'=>$type));
    }

    /**
     * 验证用户是否具有权限
     */
    public function actionChecksys() {
        $id = Common::getNumParam('id');
        $privilege = Privileges::model()->findByPk($id);
        if ($privilege) {
            if (in_array($privilege->is_super, Privileges::$super_admin)) {
                $system = new ARedisSet(Common::REDIS_ACCESS.$privilege->username.Common::REDIS_ACCESS_SYSTEM);
                $system->add(Common::REDIS_ACCESS_SYSTEM_SELFREPORT);
                echo 'exists';
                return;
            } else {
                $user_group = isset($privilege->user_group_id)&&!empty($privilege->user_group_id)&&preg_match('/^a:/', $privilege->user_group_id) ? unserialize($privilege->user_group_id) : array();
                $report_ids = ReportPrivileges::getReportsByUsergroup($user_group);
                if (Menu::checkSelfOwn($report_ids)) {
                    $system = new ARedisSet(Common::REDIS_ACCESS.$privilege->username.Common::REDIS_ACCESS_SYSTEM);
                    $system->add(Common::REDIS_ACCESS_SYSTEM_SELFREPORT);
                    echo 'exists';
                    return;
                }
            }
        }
        echo 'not exists';
        return;
    }

    /**
     * loginToken
     * */
    protected function loginToken()
    {
                setcookie('time_limit', '', time()-3600, '/', 'report.com');        //删除jquery赋值的倒计时cookie
                //Yii::app()->session['user_verify'] = 'pass';    //应用于controller的filter
                Admin::model()->updateByPk(Yii::app()->user->id, array('lock_time' => 0, 'login_failed' => 0, 'sms_times' => 0, 'cookie_failed' => 0));    //验证成功后部分条件需要置0
                $this->saveVerifyStatus(Admin::COOKIE_VALIDATE_TIME);    //保存验证状态有效期到cookie，只在验证时保存一次

                //将登录信息存入redis
                $loginfo = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_ACCESS_LOGINFO);
                $loginfo[Common::REDIS_ACCESS_IP] = Utility::getRealIP();
                $loginfo[Common::REDIS_ACCESS_USER_AGENT] = Yii::app()->request->userAgent;
                $loginfo[Common::REDIS_ACCESS_STATUS] = Common::REDIS_V_ACCESS_STATUS_SMS;
                Yii::app()->redis->getClient()->setTimeout(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_ACCESS_LOGINFO, 12*Common::REDIS_DURATION);

                //检查是否具体其他平台权限
//                $system = new ARedisSet(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_ACCESS_SYSTEM);
//                $privileges_obj = Privileges::getPrivileges(Yii::app()->user->name);
//                if ($privileges_obj) {
//                    $privileges = unserialize($privileges_obj->privileges);
//                    if (in_array($privileges_obj->is_super, Common::$super_admin) || (!empty($privileges) && array_key_exists('menu', $privileges) && !empty($privileges['menu']))) {
//                        $system->add(Common::REDIS_ACCESS_SYSTEM_DCPLAT);
//                    } else {
//                        $privilege = SFRTPrivileges::getPrivileges(Yii::app()->user->name);
//                        if ($privilege) {
//                            $user_group = isset($privilege->user_group_id)&&!empty($privilege->user_group_id)&&preg_match('/^a:/', $privilege->user_group_id) ? unserialize($privilege->user_group_id) : array();
//                            $report_ids = SFRTReportPrivileges::getReportsByUsergroup($user_group);
//                            if (SFRTMenu::checkSelfReports($report_ids)) {
//                                $system->add(Common::REDIS_ACCESS_SYSTEM_DCPLAT);
//                                Yii::app()->user->setstate('dcplat_only_selfreport', 1);
//                            }
//                        }
//                    }
//                }

                Yii::app()->redis->getClient()->setTimeout(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_ACCESS_SYSTEM, 12*Common::REDIS_DURATION);
                PhpClient::get(Yii::app()->params['checksys_decision'].Yii::app()->user->name);    //取是否具有决策系统权限
                PhpClient::get(Yii::app()->params['checksys_analytics'].Yii::app()->user->id);    //取是否具有流量分析系统权限
                PhpClient::get(Yii::app()->params['checksys_dsfhz'].Yii::app()->user->id);    //取是否具有第三方平台权限
                PhpClient::get(Yii::app()->params['checksys_selfreport'].Yii::app()->user->id);    //取是否具有自助报表系统权限
                PhpClient::get(Yii::app()->params['checksys_selfquery'].Yii::app()->user->name);    //取是否具有即席查询系统权限

//                $this->redirect(Yii::app()->user->return_url);
        $this->redirect(Yii::app()->homeUrl);

    }

    /**
     * saveVerifyStatus
     * */
    protected function saveVerifyStatus($duration=43200)
    {
        $app=Yii::app();
        $cookie=new CHttpCookie('_do_','');
        $cookie->domain='report.com';
        $cookie->expire=time()+$duration;
        $cookie->httpOnly=true;
        $validation_key = Admin::model()->getValidationKey();
        Admin::model()->updateByPk(Yii::app()->user->id, array('validation_key' => $validation_key, 'validation_expire' => $cookie->expire));    //保存免验证密钥和到期时间

        $data=array(
            $app->user->name,
        );
        $cookie->value=$app->getSecurityManager()->hashData(serialize($data), $validation_key);
        $app->getRequest()->getCookies()->add($cookie->name,$cookie);
    }


}