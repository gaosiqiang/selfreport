<?php

class UsergroupController extends Controller
{
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
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
                array('allow',
                        'users'=>array('@'),
                ),
                array('deny',
                        'users'=>array('*'),
                ),
        );
    }
    
    //校验权限
    protected function beforeAction($action) {
        $super = Yii::app()->user->getstate(Common::SESSION_SUPER);
        if (isset($super) && ($super == 1 || $super == 2)) {
            return true;
        } else {
            return false;
        }
    }
    
    public function actionIndex()
    {
        $model=new UserGroup('search');
        if(isset($_POST['UserGroup']))
            $model->attributes=$_POST['UserGroup'];
        $this->render('index',array(
            'model'=>$model,
        ));
    }
    
    /**
     * 创建用户组
     * @author chensm
     */
    public function actionCreate()
    {
        $model = new UserGroup('create');
        $all_menu = Menu::getAllMenus(false);
        $menus = Menu::getAdminMenuOrNot();    //获取非管理菜单，即报表菜单
        $menu_map = Menu::constructMenuTree($menus,false);
        $all_menu_truely = Menu::getAllMenusTruely();    //获取全部有效菜单，完整表包括所有平台
        
        //组装数据平台报表菜单结构
        $menu_map_dcplatform = array();
        $all_menu_dcplatform = MenuDataPlatform::getAllMenus(false);
        $reports_dcplatform = Menu::getReportsInDeliverPlat(1);    //取分发至数据平台的报表（以parent_id集合）
        $menu_ids_dcplatform = !empty($reports_dcplatform) ? array_keys($reports_dcplatform) : array();
        if (!empty($menu_ids_dcplatform)) {
            $criteria=new CDbCriteria;
            $criteria->compare('status', 1);
            $criteria->addInCondition('id',$menu_ids_dcplatform);
            $menus_dcplatform = MenuDataPlatform::model()->findAll($criteria);
            $menu_map_dcplatform = MenuDataPlatform::constructMenuTree($menus_dcplatform);
        }
        
        if (isset($_POST['UserGroup']))
        {
            $model->attributes = $_POST['UserGroup'];
            if ($model->save()) {
                $privileges=array();
                if (isset($_POST['menu']) && is_array($_POST['menu']) && !empty($_POST['menu']))
                {
                    foreach ($_POST['menu'] as $k=>$v) {
                        if ($v && array_key_exists($v, $all_menu_truely) && !empty($all_menu_truely[$v]['report_id'])) {
                            $privileges[] = $all_menu_truely[$v]['report_id'];
                        }
                    }
                }
        
                if (!empty($privileges)) {
                    foreach ($privileges as $report_id) {
                        $pk = array('report_id'=>$report_id,'user_group_id'=>$model->id);
                        $rp = ReportPrivileges::model()->findByPk($pk);
                        if (!$rp) {
                            $rp = new ReportPrivileges();
                            $rp->attributes = $pk;
                            $rp->save();
                        }
                    }
                }
                Yii::app()->user->setFlash('success', '用户组创建成功');
                $this->redirect(array('usergroup/index'));
            } else {
                //var_dump($model->getErrors());exit;
                Yii::app()->user->setFlash('error', '用户组创建失败');
            }
        }
    
        $this->render('create', array(
                'model'=>$model,
                'all_menu'=>$all_menu,
                'menu_map'=>$menu_map,
                'all_menu_dcplatform'=>$all_menu_dcplatform,
                'menu_map_dcplatform'=>$menu_map_dcplatform,
                'reports_dcplatform'=>$reports_dcplatform,
        ));
    }
    
    /**
     * 更新用户组资料
     * @author chensm
     */
    public function actionUpdate($id)
    {
        $all_menu = Menu::getAllMenus(false);
        $menus = Menu::getAdminMenuOrNot();    //获取非管理菜单，即报表菜单
        $menu_map = Menu::constructMenuTree($menus,false);
        $all_menu_truely = Menu::getAllMenusTruely();    //获取全部有效菜单，完整表包括所有平台
    
        //组装数据平台报表菜单结构
        $menu_map_dcplatform = array();
        $all_menu_dcplatform = MenuDataPlatform::getAllMenus(false);
        $reports_dcplatform = Menu::getReportsInDeliverPlat(1);    //取分发至数据平台的报表（以parent_id集合）
        $menu_ids_dcplatform = !empty($reports_dcplatform) ? array_keys($reports_dcplatform) : array();
        if (!empty($menu_ids_dcplatform)) {
            $criteria=new CDbCriteria;
            $criteria->compare('status', 1);
            $criteria->addInCondition('id',$menu_ids_dcplatform);
            $menus_dcplatform = MenuDataPlatform::model()->findAll($criteria);
            $menu_map_dcplatform = MenuDataPlatform::constructMenuTree($menus_dcplatform);
        }
        
        $model = UserGroup::model()->findByPk($id);
        if (isset($_POST['UserGroup']))
        {
            $model->attributes = $_POST['UserGroup'];
            if ($model->save()) {
                $privileges=array();
                if (isset($_POST['menu']) && is_array($_POST['menu']) && !empty($_POST['menu']))
                {
                    foreach ($_POST['menu'] as $k=>$v) {
                        if ($v && array_key_exists($v, $all_menu_truely) && !empty($all_menu_truely[$v]['report_id'])) {
                            $privileges[] = $all_menu_truely[$v]['report_id'];
                        }
                    }
                }
                
                ReportPrivileges::model()->deleteAll('user_group_id=:user_group_id',array(':user_group_id'=>$id));
                if (!empty($privileges)) {
                    foreach ($privileges as $report_id) {
                        $pk = array('report_id'=>$report_id,'user_group_id'=>$model->id);
                        $rp = new ReportPrivileges();    //由于上面已将所有报表权限清除，所以此处重新添加
                        $rp->attributes = $pk;
                        $rp->save();
                    }
                }
                Yii::app()->user->setFlash('success', '用户组修改成功');
                $this->redirect(array('usergroup/index'));
            } else {
                //var_dump($model->getErrors());exit;
                Yii::app()->user->setFlash('error', '用户组修改失败');
            }
        }
    
        //取用户组拥有权限的报表菜单及其所属菜单的ID
        $menu_ids = array();
        $report_ids = ReportPrivileges::getReportsByUsergroup($id);
        if (!empty($report_ids)) {
            //自助报表平台
            $criteria=new CDbCriteria;
            $criteria->compare('status', 1);
            $criteria->compare('platform', 9);
            $criteria->addInCondition('report_id',$report_ids);
            $menus_9 = Menu::model()->findAll($criteria);
            $privilege_menus = Menu::constructMenuTree($menus_9,false);
            $menu_ids = Menu::getMenuIDByTree($privilege_menus);
        
            //数据平台
//            $criteria=new CDbCriteria;
//            $criteria->compare('status', 1);
//            $criteria->compare('platform', 1);
//            $criteria->addInCondition('report_id',$report_ids);
//            $menus_1 = Menu::model()->findAll($criteria);
//            $menus_1_parents = array();
//
//            foreach ($menus_1 as $menu) {    //分发报表的菜单ID
//                $menu_ids[$menu->id] = $menu->id;
//                $menus_1_parents[$menu->parent_id] = $menu->parent_id;
//            }
//
//            $privilege_menus_dcplatform = array();
//            if (!empty($menus_1_parents)) {
//                $criteria=new CDbCriteria;
//                $criteria->compare('status', 1);
//                $criteria->addInCondition('id',$menus_1_parents);
//                $menus_dcplatform = MenuDataPlatform::model()->findAll($criteria);
//                $privilege_menus_dcplatform = MenuDataPlatform::constructMenuTree($menus_dcplatform);
//            }
//
//            if (!empty($privilege_menus_dcplatform)) {    //数据平台菜单ID
//                $menu_ids_dcplatform = Menu::getMenuIDByTree($privilege_menus_dcplatform);
//                foreach ($menu_ids_dcplatform as $menu_id) {
//                    $menu_ids[$menu_id.'-dcplatform'] = $menu_id.'-dcplatform';
//                }
//            }
        }
        
        $this->render('update',array(
                'model'=>$model,
                'id' => $id,
                'all_menu'=>$all_menu,
                'menu_map'=>$menu_map,
                'all_menu_dcplatform'=>$all_menu_dcplatform,
                'menu_map_dcplatform'=>$menu_map_dcplatform,
                'reports_dcplatform'=>$reports_dcplatform,
                'menu_ids'=>$menu_ids,
        ));
    }
    
    /**
     * 删除用户组
     * @author chensm
     */
    public function actionDelete($id)
    {
        $user_group = UserGroup::model()->findByPk($id);
        if(empty($user_group))
        {
            throw new CHttpException('404','未找到指定的用户组');
        }
        else
        {
            if($user_group->deleteByPk($id))
            {
                ReportPrivileges::model()->deleteAll('user_group_id=:user_group_id',array(':user_group_id'=>$id));
                $this->redirect(array('usergroup/index'));
            }
        }
    }
}