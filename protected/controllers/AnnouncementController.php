<?php
class AnnouncementController extends Controller
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
                'actions'=>array('admin','index', 'create', 'update', 'delete', 'view'),
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
     * 公告列表
     * @author sangxiaolong
     */
    public function actionIndex()
    {
        $criteria = new CDbCriteria(array(
            'condition' => 'is_delete=0',
            'order' => 'create_time DESC',
        ));
        $dataProvider = new CActiveDataProvider('Announcement', array(
            'pagination' => array(
                'pageSize' => 15
            ),
            'criteria' => $criteria
        ));
        $this->render('index', array(
            'dataProvider' => $dataProvider
        ));
    }
    
    /**
     * 管理公告
     * @author sangxiaolong
     */
    public function actionAdmin()
    {
        $model = new Announcement('search');
        if (isset($_GET['Announcement']))
            $model->attributes = $_GET['Announcement'];
        $model->is_delete = 0;
        $this->render('admin', array(
            'model' => $model
        ));
    }
    
    /**
     * 添加公告
     * @author sangxiaolong
     */
    public function actionCreate()
    {
        $model = new Announcement();
        if (isset($_POST['Announcement']))
        {
            $model->attributes = $_POST['Announcement'];
            $model->start_time = isset($_POST['Announcement']['start_time']) ? strtotime($_POST['Announcement']['start_time']) : '';
            $model->end_time = isset($_POST['Announcement']['end_time']) ? strtotime($_POST['Announcement']['end_time']) : '';
            if ($model->save())
            {
                $this->redirect(array('announcement/index'));
            }
            else 
            {
                $model->start_time = isset($_POST['Announcement']['start_time']) ? $_POST['Announcement']['start_time'] : '';
                $model->end_time = isset($_POST['Announcement']['end_time']) ? $_POST['Announcement']['end_time'] : '';
            }
        }
        $this->render('create', array('model' => $model));
    }
    
    /**
     * 更新公告
     * @author sangxiaolong
     */
    public function actionUpdate($id)
    {
        $model = Announcement::model()->findByPk($id);
        if (empty($model) || $model->is_delete == 1)
        {
            throw new CHttpException('404', '未找到指定的公告');
        }
        else
        {
            if (isset($_POST['Announcement']))
            {
                $model->attributes = $_POST['Announcement'];
                $model->start_time = isset($_POST['Announcement']['start_time']) ? strtotime($_POST['Announcement']['start_time']) : '';
                $model->end_time = isset($_POST['Announcement']['end_time']) ? strtotime($_POST['Announcement']['end_time']) : '';
                if ($model->save())
                {
                    $this->redirect(array('announcement/index'));
                }
            }
            $model->start_time = isset($_POST['Announcement']['start_time']) ? $_POST['Announcement']['start_time'] : date('Y-m-d', $model->start_time);
            $model->end_time = isset($_POST['Announcement']['end_time']) ? $_POST['Announcement']['end_time'] : date('Y-m-d', $model->end_time);
        }
        $this->render('update',array('model'=>$model));
    }
    
    /**
     * 删除公告
     * @author sangxiaolong
     */
    public function actionDelete($id)
    {
        $announcement = Announcement::model()->findByPk($id);
        if (empty($announcement) || $announcement->is_delete == 1)
        {
            throw new CHttpException('404','未找到指定的公告');
        }
        else
        {
            if($announcement->updateByPk($id, array('is_delete'=>1)))
            {
                $this->redirect(array('announcement/index'));
            }
        }
    }
    
    /**
     * 查看公告
     * @author sangxiaolong
     */
    public function actionView($id)
    {
        $model = Announcement::model()->findByPk($id);
        if (empty($model) || $model->is_delete == 1)
        {
            throw new CHttpException('404','未找到指定的公告');
        }
        else
        {
            $author = Admin::model()->findByPk($model->author_id);
            $this->render('view', array('model'=>$model, 'author'=>$author));
        }
    }
}