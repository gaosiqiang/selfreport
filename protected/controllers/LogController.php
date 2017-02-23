<?php
class LogController extends Controller
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
        if (isset($super) && ($super == 1)) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * 日志
     * @author sangxiaolong
     */
    public function actionIndex()
    {
        $model = new Log('search');
        $model->menu = '';
        $menus = Log::getAllMenus();
        if (isset($_GET['Log']))
            $model->attributes = $_GET['Log'];
        $this->render('index', array(
            'model' => $model,
            'menus' => $menus,
        ));
    }
}