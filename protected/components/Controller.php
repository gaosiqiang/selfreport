<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController
{
    public $layout = '//layouts/main';
    public $menu=array();
    public $breadcrumbs=array();
    
    //便于各controller使用
    protected function beforeAction($action) {
        return true;
    }
    
    /**
     * 每个action执行后记录访问日志
     * @author sangxiaolong
     * @see CController::afterAction()
     */
    protected function afterAction($action)
    {
        $report_id = Common::getStringParam('report_id');
        Log::createLog($this->route, $report_id);
    }
}
