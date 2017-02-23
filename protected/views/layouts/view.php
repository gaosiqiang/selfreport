<?php 
    $report_id_param = Common::getStringParam('report_id');
    $module = Common::getNumParam('module');
    $priv_menu = Common::getUserMenuTree();
    $menu_tree_2nd = array_key_exists($module, $priv_menu) ? array_keys($priv_menu[$module]) : array();    //二级菜单
    $all_menu = Menu::getAllMenus();
?>
<?php $this->beginContent('/layouts/main'); ?>
<div class="row">
    <div class="col-md-2" style="padding-left: 5px">
        <div class="well sidebar-nav" id="layout" style="position: relative;padding:10px">
            <ul class="nav nav-pills nav-stacked">
                <?php foreach ($menu_tree_2nd as $menu_id_2nd) {
                    $menu_2nd = array_key_exists($menu_id_2nd, $all_menu) ? $all_menu[$menu_id_2nd] : array();
                    $tab_state_2nd = array_key_exists('tab_state', $menu_2nd) ? $menu_2nd['tab_state'] : '0';
                    $menu_name_2nd = array_key_exists('menu_name', $menu_2nd) ? $menu_2nd['menu_name'] : '';
                    $report_id_2nd = array_key_exists('report_id', $menu_2nd) ? $menu_2nd['report_id'] : '';
                    if (!empty($report_id_2nd)) {
                ?>
                <li <?php if($report_id_param === $report_id_2nd) echo 'class="active"'?>><?php echo CHtml::link($menu_name_2nd,array('view/show', 'module'=>$module, 'report_id'=>$report_id_2nd));?></li>
                <?php } elseif (!empty($priv_menu[$module][$menu_id_2nd])) {
                        if ($tab_state_2nd == 1) {    //三级菜单为标签页的情况
                            $is_active_3rd = false;
                            $loop = 0;
                            $report_id_3rd_url = '';
                            $menu_tree_3rd = array_keys($priv_menu[$module][$menu_id_2nd]);
                            foreach ($menu_tree_3rd as $menu_id_3rd) {
                                ++$loop;
                                $menu_3rd = array_key_exists($menu_id_3rd, $all_menu) ? $all_menu[$menu_id_3rd] : array();
                                $report_id_3rd = array_key_exists('report_id', $menu_3rd) ? $menu_3rd['report_id'] : '';
                                if (!empty($report_id_3rd)) {
                                    if ($loop == 1) {
                                        $report_id_3rd_url = $report_id_3rd;
                                    }
                                    if ($report_id_param === $report_id_3rd) {
                                        $is_active_3rd = true;
                                        break;
                                    }
                                }
                            }
                ?>
                <li <?php if ($is_active_3rd) echo 'class="active"'?>><?php echo CHtml::link($menu_name_2nd,array('view/show', 'module'=>$module, 'mp'=>$menu_id_2nd, 'report_id'=>$report_id_3rd_url));?></li>
                <?php } else {?>
                <li>
                    <a class="sidebar-nav-header"><?php echo $menu_name_2nd;?></a>
                    <ul class="nav nav-pills nav-stacked">
                    <?php 
                        $menu_tree_3rd = array_keys($priv_menu[$module][$menu_id_2nd]);
                        foreach ($menu_tree_3rd as $menu_id_3rd) {
                            $menu_3rd = array_key_exists($menu_id_3rd, $all_menu) ? $all_menu[$menu_id_3rd] : array();
                            $menu_name_3rd = array_key_exists('menu_name', $menu_3rd) ? $menu_3rd['menu_name'] : '';
                            $report_id_3rd = array_key_exists('report_id', $menu_3rd) ? $menu_3rd['report_id'] : '';
                            if (!empty($report_id_3rd)) {
                    ?>
                        <li <?php if($report_id_param === $report_id_3rd) echo 'class="active"'?>><?php echo CHtml::link($menu_name_3rd,array('view/show', 'module'=>$module, 'report_id'=>$report_id_3rd));?></li>
                    <?php } elseif (!empty($priv_menu[$module][$menu_id_2nd][$menu_id_3rd])) {
                            $is_active_4th = false;
                            $loop = 0;
                            $report_id_4th_url = '';
                            $menu_tree_4th = array_keys($priv_menu[$module][$menu_id_2nd][$menu_id_3rd]);
                            foreach ($menu_tree_4th as $menu_id_4th) {
                                ++$loop;
                                $menu_4th = array_key_exists($menu_id_4th, $all_menu) ? $all_menu[$menu_id_4th] : array();
                                $report_id_4th = array_key_exists('report_id', $menu_4th) ? $menu_4th['report_id'] : '';
                                if (!empty($report_id_4th)) {
                                    if ($loop == 1) {
                                        $report_id_4th_url = $report_id_4th;
                                    }
                                    if ($report_id_param === $report_id_4th) {
                                        $is_active_4th = true;
                                        break;
                                    }
                                }
                            }
                            ?>
                        <li <?php if ($is_active_4th) echo 'class="active"'?>><?php echo CHtml::link($menu_name_3rd,array('view/show', 'module'=>$module, 'mp'=>$menu_id_3rd, 'report_id'=>$report_id_4th_url));?></li>
                    <?php }}?>
                    </ul>
                </li>
                <?php }}}?>
            </ul>
        </div><!-- sidebar -->
    </div>
    <div class="col-md-10" style="padding-left: 5px">
            <?php echo $content; ?>
    </div>
</div>
<?php $this->endContent(); ?>