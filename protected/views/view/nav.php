<?php 
    $report_id_param = Common::getStringParam('report_id');
    $module = Common::getNumParam('module');
    $parent_id = Common::getNumParam('mp');
    $tab_reports = Menu::getTabReports();
    
    $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT);
    $reports = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT, Common::REDIS_PRIVILEGES_REPORTS) ? $priv[Common::REDIS_PRIVILEGES_REPORTS] : '';
    $priv_reports = !empty($reports) ? unserialize($reports) : array();
    
    $session_super = Yii::app()->user->getstate(Common::SESSION_SUPER);
?>
<ul class="nav nav-tabs">
    <?php 
        $platform = Common::getNumParam('platform');
        if (!empty($platform) && $platform != 9) {    //展示其他平台标签页
            $all_reports = Menu::getAllReportMenus();
            $report = array_key_exists($report_id_param, $all_reports) ? $all_reports[$report_id_param] : array();
            $tab_state = !empty($report['tab_state']) ? $report['tab_state'] : '';
            if ($tab_state == 2) {
                $priv_self_reports = Common::getPrivSelfReports($platform);
                $reports_4th = Common::getReportsByParentID($platform, $parent_id);
                foreach ($reports_4th as $report) {
                    $report_id = isset($report['report_id']) ? $report['report_id'] : '';
                    $tab_report = array_key_exists($report_id, $all_reports) ? $all_reports[$report_id] : array();
                    $tab_report_name = isset($tab_report['menu_name']) ? $tab_report['menu_name'] : '';
                    if (strpos($tab_report_name, '-') !== false) { //对于标签页菜单，剔除父菜单名称
                        $pos = strpos($tab_report_name, '-') + 1;
                        $tab_report_name = substr($tab_report_name, $pos);
                    }
                    if ($session_super>0 || in_array($report_id, $priv_self_reports)) {
    ?>
    <li <?php if($report_id_param === $report_id) echo 'class="active"'?>><?php echo CHtml::link($tab_report_name,array('view/report','platform'=>1,'module'=>$module,'mp'=>$parent_id,'report_id'=>$report_id));?></li>
    <?php 
                    }
                }
            }
        } else {
            if (array_key_exists($parent_id, $tab_reports) && !empty($tab_reports[$parent_id])) {
                foreach ($tab_reports[$parent_id] as $report_id => $report) {
                    if ($session_super>0 ||in_array($report_id, $priv_reports)) {
                        $tab_report = !empty($report['report_id']) ? $report['report_id'] : '';
                        $tab_name = !empty($report['menu_name']) ? $report['menu_name'] : '';
                        if (strpos($tab_name, '-') !== false) { //对于标签页菜单，剔除父菜单名称
                            $pos = strpos($tab_name, '-') + 1;
                            $tab_name = substr($tab_name, $pos);
                        }
    ?>
    <li <?php if ($report_id_param === $report_id) echo 'class="active"'?>>
        <?php echo CHtml::link($tab_name,array('view/show', 'module'=>$module, 'mp'=>$parent_id, 'report_id'=>$tab_report));?>
    </li>
    <?php
                    }
                }
            }
        }
    ?>
</ul>
