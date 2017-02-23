<?php 
$platform = Common::getNumParam('platform');
$report_id_param = Common::getStringParam('report_id');
$module = Common::getNumParam('module');
$mp = Common::getNumParam('mp');
$session_super = Yii::app()->user->getstate(Common::SESSION_SUPER);
switch ($platform) {
    case 1:
        $all_menu = MenuDataPlatform::getAllMenus();
        list($parents_tree, $parents_ids) = Common::getParentsTree($platform);
        $menu_tree_2nd = array_key_exists($module, $parents_tree) ? array_keys($parents_tree[$module]) : array();
        $reports_2nd = Common::getReportsByParentID($platform, $module);
        $priv_self_reports = Common::getPrivSelfReports($platform);
        break;
    case 2:
        break;
    default:
        $all_menu = array();
        $parents_tree = array();
        $menu_tree_2nd = array();
        $reports_2nd = array();
        $priv_self_reports = array();
}
?>
<?php 
$this->beginContent('/layouts/report_main', array(
        'platform'=>$platform,
        'module'=>$module,
        'all_menu'=>$all_menu,
        'parents_tree'=>$parents_tree,
));
?>
<div class="row">
    <div class="col-md-2" style="padding-left: 5px">
        <div class="well sidebar-nav" id="layout" style="position: relative;padding:10px">
            <ul class="nav nav-pills nav-stacked">
                <?php 
                if (!empty($menu_tree_2nd)) {
                    foreach ($menu_tree_2nd as $menu_id_2nd) {
                        $menu_2nd = array_key_exists($menu_id_2nd, $all_menu) ? $all_menu[$menu_id_2nd] : array();
                        $tab_state_2nd = array_key_exists('tab_state', $menu_2nd) ? $menu_2nd['tab_state'] : '0';
                        $menu_name_2nd = array_key_exists('menu_name', $menu_2nd) ? $menu_2nd['menu_name'] : '';
                        $menu_tree_3rd = array_keys($parents_tree[$module][$menu_id_2nd]);
                        $reports_3rd = Common::getReportsByParentID($platform, $menu_id_2nd);
                        if ($tab_state_2nd == 1) {    //二级菜单下的三级菜单为标签页的情况
                            if (!empty($reports_3rd)) {
                                foreach ($reports_3rd as $report) {
                                    $report_id = isset($report['report_id']) ? $report['report_id'] : '';
                                    if ($report['tab_state']==2 && ($session_super>0 || in_array($report_id, $priv_self_reports))) {
                ?>
                <li <?php if($mp == $menu_id_2nd) echo 'class="active"'?>><?php echo CHtml::link($menu_name_2nd,array('view/report','platform'=>1,'module'=>$module,'mp'=>$menu_id_2nd,'report_id'=>$report_id));?></li>
                <?php 
                                        break;
                                    }
                                }
                            }
                        } else {        //二级菜单下的三级菜单为侧栏菜单的情况
                ?>
                <li>
                    <a class="sidebar-nav-header"><?php echo $menu_name_2nd;?></a>
                    <ul class="nav nav-pills nav-stacked">
                    <?php 
                                if (!empty($menu_tree_3rd)) {
                                    foreach ($menu_tree_3rd as $menu_id_3rd) {
                                        $menu_3rd = array_key_exists($menu_id_3rd, $all_menu) ? $all_menu[$menu_id_3rd] : array();
                                        $tab_state_3rd = array_key_exists('tab_state', $menu_3rd) ? $menu_3rd['tab_state'] : '0';
                                        $menu_name_3rd = array_key_exists('menu_name', $menu_3rd) ? $menu_3rd['menu_name'] : '';
                                        $url_3rd = array_key_exists('url', $menu_3rd) ? $menu_3rd['url'] : '';
                                        $menu_tree_4th = array_keys($parents_tree[$module][$menu_id_2nd][$menu_id_3rd]);
                                        $reports_4th = Common::getReportsByParentID($platform, $menu_id_3rd);
                                        if (!empty($reports_4th)) {
                                                foreach ($reports_4th as $report) {
                                                    $report_id = isset($report['report_id']) ? $report['report_id'] : '';
                                                    if ($session_super>0 || in_array($report_id, $priv_self_reports)) {
                    ?>
                    <li <?php if($report_id_param === $report_id) echo 'class="active"'?>><?php echo CHtml::link($menu_name_3rd,array('view/report','platform'=>1,'module'=>$module,'mp'=>$menu_id_3rd,'report_id'=>$report_id));?></li>
                    <?php 
                                                        break;
                                                    }
                                                }
                                            }
                                    }
                                }
                                if (!empty($reports_3rd)) { 
                                    foreach ($reports_3rd as $report) {
                                        $report_id = isset($report['report_id']) ? $report['report_id'] : '';
                                        if ($report['tab_state']!=2 && ($session_super>0 || in_array($report_id, $priv_self_reports))) {
                    ?>
                    <li <?php if($report_id_param === $report_id) echo 'class="active"'?>><?php echo CHtml::link($report['menu_name'],array('view/report','platform'=>1,'module'=>$module,'mp'=>$menu_id_2nd,'report_id'=>$report_id));?></li>
                    <?php 
                                        }
                                    }
                                }
                    ?>
                    </ul>
                </li>
                <?php 
                            }
                        }
                    }
                ?>
                <?php 
                if (!empty($reports_2nd)) {
                    foreach ($reports_2nd as $report) {
                        $report_id = isset($report['report_id']) ? $report['report_id'] : '';
                        if ($report['tab_state']!=2 && ($session_super>0 || in_array($report_id, $priv_self_reports))) {
                ?>
                <li <?php if($report_id_param === $report_id) echo 'class="active"'?>><?php echo CHtml::link($report['menu_name'],array('view/report','platform'=>1,'module'=>$module,'mp'=>$module,'report_id'=>$report_id));?></li>
                <?php 
                        }
                    }
                }?>
            </ul>
        </div><!-- sidebar -->
    </div>
    <div class="col-md-10" style="padding-left: 5px">
            <?php echo $content; ?>
    </div>
</div>
<?php $this->endContent(); ?>