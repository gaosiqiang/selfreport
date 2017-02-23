<?php
class AccessFilter extends CFilter
{
    protected function preFilter($filterChain)
    {
        //每次操作都重新设置session在客户端的cookie有效期，达到一定时间不操作session失效的效果
        if (array_key_exists(Yii::app()->session->sessionName, $_COOKIE)) {
            //延长cookie有效期
            setcookie(Yii::app()->session->sessionName, $_COOKIE[Yii::app()->session->sessionName], time()+3600, '/', Yii::app()->session->cookieParams['domain'], Yii::app()->session->cookieParams['secure'], Yii::app()->session->cookieParams['httponly']);
            //延长session有效期
            Yii::app()->redis->getClient()->setTimeout(Yii::app()->redis->prefix.Yii::app()->session->keyPrefix.Yii::app()->session->sessionId, Common::REDIS_DURATION);
        }
        
        // 动作被执行之前应用的逻辑
        $loginfo = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_ACCESS_LOGINFO);
        $ip = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_ACCESS_LOGINFO, Common::REDIS_ACCESS_IP) ? $loginfo[Common::REDIS_ACCESS_IP] : '';
        $user_agent = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_ACCESS_LOGINFO, Common::REDIS_ACCESS_USER_AGENT) ? $loginfo[Common::REDIS_ACCESS_USER_AGENT] : '';
        $login_status = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_ACCESS_LOGINFO, Common::REDIS_ACCESS_STATUS) ? $loginfo[Common::REDIS_ACCESS_STATUS] : '';
        
        $user = Admin::getUserInformation(Yii::app()->user->name);
        $privileges_obj = Privileges::getPrivileges(Yii::app()->user->name);
        if (!$privileges_obj) {
            Yii::app()->request->redirect('/site/failed/type/4');
            return false;
        }
        switch ($login_status) {
            case Common::REDIS_V_ACCESS_STATUS_PW:
                if ($ip !== Utility::getRealIP() || $user_agent !== Yii::app()->request->userAgent) {
                    Yii::app()->user->setFlash('error', '您的登录位置发生改变（IP或浏览器），请重新登录');
                    Yii::app()->runController('site/logout');
                } else {
                    $cookie=Yii::app()->getRequest()->getCookies()->itemAt('dc_vc');
                    if($cookie && !empty($cookie->value) && strpos($cookie->value, $user->username) !== FALSE)
                    {
                        if (($data=Yii::app()->getSecurityManager()->validateData($cookie->value, $user->validation_key))!==false)
                        {
                            $data=@unserialize($data);
                            if(is_array($data) && isset($data[0]))
                            {
                                list($name)=$data;
                                if ($user->validation_expire < time()) {
                                    Yii::app()->request->redirect(Yii::app()->params['dcplatform'].'/site/reverify');
                                    return false;
                                } else {
                                    if (!Yii::app()->user->hasstate(Common::SESSION_CITIES) || !Yii::app()->user->hasstate(Common::SESSION_CATEGORY) || 
                                        !Yii::app()->user->hasstate(Common::SESSION_SUPER) || !Yii::app()->user->hasstate(Common::SESSION_MEDIA)) {
                                        if ($privileges_obj) {
                                            Yii::app()->user->setstate(Common::SESSION_SUPER, $privileges_obj->is_super);
                                            if ($privileges_obj->is_super > 0) {
                                                Yii::app()->user->setstate(Common::SESSION_CITIES, 1);
                                                Yii::app()->user->setstate(Common::SESSION_BRANCHES, 1);
                                                Yii::app()->user->setstate(Common::SESSION_CATEGORY, 1);
                                                Yii::app()->user->setstate(Common::SESSION_MEDIA, 1);
                                            } else {
                                                $privileges = isset($privileges_obj->privileges) ? unserialize($privileges_obj->privileges) : array();
                                                if (array_key_exists(Common::PRIVILEGE_TYPE_CITY, $privileges) && in_array('all', $privileges[Common::PRIVILEGE_TYPE_CITY])) {
                                                    Yii::app()->user->setstate(Common::SESSION_CITIES, 1);
                                                } else {
                                                    Yii::app()->user->setstate(Common::SESSION_CITIES, 0);
                                                }
                                                if (array_key_exists(Common::PRIVILEGE_TYPE_BRANCH, $privileges) && in_array('all', $privileges[Common::PRIVILEGE_TYPE_BRANCH])) {
                                                    Yii::app()->user->setstate(Common::SESSION_BRANCHES, 1);
                                                } else {
                                                    Yii::app()->user->setstate(Common::SESSION_BRANCHES, 0);
                                                }
                                                if (array_key_exists(Common::PRIVILEGE_TYPE_CATEGORY, $privileges) && in_array('all', $privileges[Common::PRIVILEGE_TYPE_CATEGORY])) {
                                                    Yii::app()->user->setstate(Common::SESSION_CATEGORY, 1);
                                                } else {
                                                    Yii::app()->user->setstate(Common::SESSION_CATEGORY, 0);
                                                }
                                                if (array_key_exists(Common::PRIVILEGE_TYPE_MEDIA, $privileges) && in_array('all', $privileges[Common::PRIVILEGE_TYPE_MEDIA])) {
                                                    Yii::app()->user->setstate(Common::SESSION_MEDIA, 1);
                                                } else {
                                                    Yii::app()->user->setstate(Common::SESSION_MEDIA, 0);
                                                }
                                            }
                                        } else {
                                            Yii::app()->user->setstate(Common::SESSION_SUPER, 0);
                                            Yii::app()->user->setstate(Common::SESSION_CITIES, 0);
                                            Yii::app()->user->setstate(Common::SESSION_BRANCHES, 0);
                                            Yii::app()->user->setstate(Common::SESSION_CATEGORY, 0);
                                            Yii::app()->user->setstate(Common::SESSION_MEDIA, 0);
                                        }
                                    }
                                    
                                    $announcement = Announcement::getTodayAnnouncement();
                                    if(!empty($announcement))
                                    {
                                        Yii::app()->user->setFlash('info', '<strong>公告声明：</strong>' . CHtml::link($announcement->title, array('announcement/view', 'id' => $announcement->id), array('target'=>'_blank')));
                                    }
                                    
                                    $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT);
                                    if (!Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT, Common::REDIS_PRIVILEGES_FIELD) || empty($priv[Common::REDIS_PRIVILEGES_FIELD])) {
                                        //保存当前平台的权限
                                        $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT);
                                        $user_group = isset($privileges_obj->user_group_id)&&!empty($privileges_obj->user_group_id)&&preg_match('/^a:/', $privileges_obj->user_group_id) ? unserialize($privileges_obj->user_group_id) : array();
                                        $report_ids = ReportPrivileges::getReportsByUsergroup($user_group);
                                        $priv[Common::REDIS_PRIVILEGES_FIELD] = $privileges_obj->privileges;    //将用户行级权限保存到缓存中
                                        $priv[Common::REDIS_PRIVILEGES_REPORTS] = serialize($report_ids);    //将用户报表权限保存到缓存中
                                        Yii::app()->redis->getClient()->setTimeout(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT, 12*Common::REDIS_DURATION);
                                        
                                        Common::setPrivCityStruct();        //缓存权限城市及网店通分部结构
                                        //Common::setPrivWdtCityStruct();     //缓存权限网店通城市结构
                                        Common::setCityDivisions();           //缓存城市与大区-区域、战区分区-分档的对应关系
                                        $priv_menu = Common::getUserMenuTree();
                                        if ($privileges_obj->is_super == 1 || $privileges_obj->is_super == 2) {
                                            Yii::app()->request->redirect('/configure/index');
                                        } else {
                                            if (!empty($priv_menu)) {
                                                Yii::app()->request->redirect('/view/index');
                                            } else {
                                                //Yii::app()->request->redirect(Yii::app()->homeUrl);
                                                Yii::app()->request->redirect('/site/failed/type/4');
                                                return false;
                                            }
                                        }
                                    }
                                    return true;
                                }
                            } else {
                                Yii::app()->request->redirect(Yii::app()->params['dcplatform'].'/site/reverify');
                                return false;
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
                                Yii::app()->request->redirect('site/failed/type/1');
                                return false;
                            } else {
                                $user->updateByPk($user->id, array('cookie_failed' => $cookie_failed));
                                Yii::app()->request->redirect(Yii::app()->params['dcplatform'].'/site/reverify');
                                //$this->runController(array('site/logout'));
                                return false;
                            }
                        }
                    } else {
                        Yii::app()->request->redirect(Yii::app()->params['dcplatform'].'/site/reverify');
                        return false;
                    }
                }
                break;
            case Common::REDIS_V_ACCESS_STATUS_SMS:
                if ($ip !== Utility::getRealIP() || $user_agent !== Yii::app()->request->userAgent) {
                    Yii::app()->request->redirect('/site/failed/type/5');
                    return false;
                } else {
                    if (!Yii::app()->user->hasstate(Common::SESSION_CITIES) || !Yii::app()->user->hasstate(Common::SESSION_CATEGORY) || 
                        !Yii::app()->user->hasstate(Common::SESSION_SUPER) || !Yii::app()->user->hasstate(Common::SESSION_MEDIA)) {
                        if ($privileges_obj) {
                            Yii::app()->user->setstate(Common::SESSION_SUPER, $privileges_obj->is_super);
                            if ($privileges_obj->is_super > 0) {
                                Yii::app()->user->setstate(Common::SESSION_CITIES, 1);
                                Yii::app()->user->setstate(Common::SESSION_BRANCHES, 1);
                                Yii::app()->user->setstate(Common::SESSION_CATEGORY, 1);
                                Yii::app()->user->setstate(Common::SESSION_MEDIA, 1);
                            } else {
                                $privileges = isset($privileges_obj->privileges) ? unserialize($privileges_obj->privileges) : array();
                                if (array_key_exists(Common::PRIVILEGE_TYPE_CITY, $privileges) && in_array('all', $privileges[Common::PRIVILEGE_TYPE_CITY])) {
                                    Yii::app()->user->setstate(Common::SESSION_CITIES, 1);
                                } else {
                                    Yii::app()->user->setstate(Common::SESSION_CITIES, 0);
                                }
                                if (array_key_exists(Common::PRIVILEGE_TYPE_BRANCH, $privileges) && in_array('all', $privileges[Common::PRIVILEGE_TYPE_BRANCH])) {
                                    Yii::app()->user->setstate(Common::SESSION_BRANCHES, 1);
                                } else {
                                    Yii::app()->user->setstate(Common::SESSION_BRANCHES, 0);
                                }
                                if (array_key_exists(Common::PRIVILEGE_TYPE_CATEGORY, $privileges) && in_array('all', $privileges[Common::PRIVILEGE_TYPE_CATEGORY])) {
                                    Yii::app()->user->setstate(Common::SESSION_CATEGORY, 1);
                                } else {
                                    Yii::app()->user->setstate(Common::SESSION_CATEGORY, 0);
                                }
                                if (array_key_exists(Common::PRIVILEGE_TYPE_MEDIA, $privileges) && in_array('all', $privileges[Common::PRIVILEGE_TYPE_MEDIA])) {
                                    Yii::app()->user->setstate(Common::SESSION_MEDIA, 1);
                                } else {
                                    Yii::app()->user->setstate(Common::SESSION_MEDIA, 0);
                                }
                            }
                        } else {
                            Yii::app()->user->setstate(Common::SESSION_SUPER, 0);
                            Yii::app()->user->setstate(Common::SESSION_CITIES, 0);
                            Yii::app()->user->setstate(Common::SESSION_BRANCHES, 0);
                            Yii::app()->user->setstate(Common::SESSION_CATEGORY, 0);
                            Yii::app()->user->setstate(Common::SESSION_MEDIA, 0);
                        }
                    }
                    
                    $announcement = Announcement::getTodayAnnouncement();
                    if(!empty($announcement))
                    {
                        Yii::app()->user->setFlash('info', '<strong>公告声明：</strong>' . CHtml::link($announcement->title, array('announcement/view', 'id' => $announcement->id), array('target'=>'_blank')));
                    }
                    
                    $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT);
                    if (!Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT, Common::REDIS_PRIVILEGES_FIELD)) {
                        //保存当前平台的权限
                        $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT);
                        $user_group = isset($privileges_obj->user_group_id)&&!empty($privileges_obj->user_group_id)&&preg_match('/^a:/', $privileges_obj->user_group_id) ? unserialize($privileges_obj->user_group_id) : array();
                        $report_ids = ReportPrivileges::getReportsByUsergroup($user_group);
                        $priv[Common::REDIS_PRIVILEGES_FIELD] = $privileges_obj->privileges;    //将用户行级权限保存到缓存中
                        $priv[Common::REDIS_PRIVILEGES_REPORTS] = serialize($report_ids);    //将用户报表权限保存到缓存中
                        Yii::app()->redis->getClient()->setTimeout(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT, 12*Common::REDIS_DURATION);
                        
                        Common::setPrivCityStruct();        //缓存权限城市及网店通分部结构
                        //Common::setPrivWdtCityStruct();     //缓存权限网店通城市结构
                        Common::setCityDivisions();           //缓存城市与大区-区域、战区分区-分档的对应关系
                        $priv_menu = Common::getUserMenuTree();
                        
                        if (Yii::app()->controller->route == 'view/report') {
                            $report_id = Common::getStringParam('report_id');
                            if (in_array($report_id, $report_ids) || $privileges_obj->is_super == 1 || $privileges_obj->is_super == 2) {
                                return true;
                            }
                        } else {
                            if ($privileges_obj->is_super == 1 || $privileges_obj->is_super == 2) {
                                Yii::app()->request->redirect('/configure/index');
                            } else {
                                if (!empty($priv_menu)) {
                                    Yii::app()->request->redirect('/view/index');
                                } else {
                                    //Yii::app()->request->redirect(Yii::app()->homeUrl);
                                    Yii::app()->request->redirect('/site/failed/type/4');
                                    return false;
                                }
                            }
                        }
                    }
                    return true;
                }
                break;
            default:
                Yii::app()->user->setFlash('error', '您尚未登录该平台，请重新登录');
                Yii::app()->runController('site/logout');
                return false;
                break;
        }
    }

//     protected function postFilter($filterChain)
//     {
//         // 动作执行之后应用的逻辑
//     }
}
?>
