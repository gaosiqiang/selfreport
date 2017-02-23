<?php

class DatasourceController extends Controller
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
        $model=new DataSource('search');
        if(isset($_POST['DataSource'])) {
            $model->attributes=$_POST['DataSource'];
        }
        $this->render('index',array(
            'model'=>$model,
        ));
    }
    
    /**
     * 创建数据源
     * @author chensm
     */
    public function actionCreate()
    {
        $model = new DataSource('create');
    
        if (isset($_POST['DataSource']))
        {
            $model->attributes = $_POST['DataSource'];
            if (preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $model->server_ip)) {
                $ip = ip2long($model->server_ip);
                if ($ip !== false) {
                    $model->server_ip = $ip;
                    $model->password = Phpaes::AesEncrypt($model->password);
                    if ($model->save()) {
                        Yii::app()->user->setFlash('success', '数据源创建成功');
                        $this->redirect(array('datasource/index'));
                    } else {
                        //var_dump($model->getErrors());exit;
                        Yii::app()->user->setFlash('error', '用户组创建失败');
                    }
                } else {
                    Yii::app()->user->setFlash('error', '服务器IP格式不正确');
                }
            } else {
                Yii::app()->user->setFlash('error', '服务器IP格式不正确');
            }
        }
    
        $this->render('create', array(
                'model'=>$model,
        ));
    }
    
    /**
     * 更新数据源
     * @author chensm
     */
    public function actionUpdate($id)
    {
        $model = DataSource::model()->findByPk($id);
        if (isset($_POST['DataSource']))
        {
            $password = $model->password;
            $model->attributes = $_POST['DataSource'];
            if (preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $model->server_ip)) {
                $ip = ip2long($model->server_ip);
                if ($ip !== false) {
                    $model->server_ip = $ip;
                    if (empty($model->password)) {
                        $model->password = $password;
                    } else {
                        $model->password = Phpaes::AesEncrypt($model->password);
                    }
                    if ($model->save()) {
                        Yii::app()->user->setFlash('success', '数据源修改成功');
                        $this->redirect(array('datasource/index'));
                    } else {
                        //var_dump($model->getErrors());exit;
                        Yii::app()->user->setFlash('error', '用户组修改失败');
                    }
                } else {
                    Yii::app()->user->setFlash('error', '服务器IP格式不正确');
                }
            } else {
                Yii::app()->user->setFlash('error', '服务器IP格式不正确');
            }
        }
    
        $this->render('update',array(
                'model'=>$model,
                'id' => $id,
        ));
    }
    
    /**
     * 删除数据源
     * @author chensm
     */
    /* public function actionDelete($id)
    {
        $data_source = DataSource::model()->findByPk($id);
        if(empty($data_source))
        {
            throw new CHttpException('404','未找到指定的数据源');
        }
        else
        {
            if($data_source->deleteByPk($id))
            {
                $this->redirect(array('datasource/index'));
            }
        }
    } */
}