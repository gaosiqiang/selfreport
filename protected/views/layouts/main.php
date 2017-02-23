<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="language" content="zh" />
        <meta name="author" content="sangxiaolong" />
        <meta name="description" content="55tuan_dc_self_report" />
        <link href="<?php echo Yii::app()->request->baseUrl; ?>/static/img/favicon.ico" rel="shortcut icon">
        <link href="<?php echo Yii::app()->request->baseUrl; ?>/static/img/animated_favicon.gif" type="image/gif" rel="icon">
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/static/css/bootstrap.css"/>
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/static/css/datepicker.css"/>
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/static/css/query-builder.css"/>
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/static/css/style.css?v=20121101"/>
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/static/css/typeahead.bundle.css" />
        <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/static/js/jquery.js"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/static/js/jquery.qrcode.js"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/static/js/jquery.cookie.js"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/static/js/moment-with-locales.js"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/static/js/query-builder.js"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/static/js/query-builder-sql-support.js"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/static/js/qrcode.js"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/static/js/bootstrap.js"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/static/js/bootstrap-datepicker.js"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/static/js/bootstrap-datepicker.zh-CN.js"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/static/js/common.js?v=20121101"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/static/js/highcharts/highcharts.js"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/static/js/typeahead.bundle.js"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/static/js/handlebars.js"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/static/js/echarts/echarts.js"></script>
        <!--[if lt IE 9]><script type="text/javascript" src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
        <title><?php echo $this->pageTitle; ?></title>
    </head>
    <body>
        <div class="navbar navbar-inverse navbar-static-top" style="min-height: 0;">
            <div class="container-fluid" style="padding-left: 45px; padding-right: 45px;">
                <div class="navbar-collapse collapse">
                    <ul class="nav navbar-nav">
                        <?php if(Yii::app()->user->isGuest){?>
                        <?php echo '<li>'.CHtml::link(Yii::app()->name, Yii::app()->homeUrl, array('class'=>'brand')).'</li>';?>
                        <?php } else {

//                                $currController = Yii::app()->controller->id; #获取当前controller
//                                $currAction = Yii::app()->controller->action->id; #action
//                                $hostInfo = Yii::app()->request->hostInfo;
//                                preg_match('/^\w+:\/\/(\w+)?\..+/', Yii::app()->params['decision'], $md);
//                                preg_match('/^\w+:\/\/(\w+)?\..+/', Yii::app()->params['dcplatform'], $mp);
//                                preg_match('/^\w+:\/\/(\w+)?\..+/', Yii::app()->params['analytics'], $as);
//                                preg_match('/^\w+:\/\/(\w+)?\..+/', Yii::app()->params['dsfhz'], $dz);
//                                preg_match('/^\w+:\/\/(\w+)?\..+/', Yii::app()->params['selfreport'], $sr);
//                                preg_match('/^\w+:\/\/(\w+)?\..+/', Yii::app()->params['selfquery'], $sq);
//                                if(strpos($hostInfo, $md[1]) !== false){
//                                    $platformName = '决策系统';
//                                }elseif(strpos($hostInfo, $mp[1]) !== false){
//                                    $platformName = '窝窝数据平台';
//                                }elseif(strpos($hostInfo, $as[1]) !== false){
//                                    $platformName = '流量分析系统';
//                                }elseif(strpos($hostInfo, $dz[1]) !== false){
//                                    $platformName = '第三方平台';
//                                }elseif(strpos($hostInfo, $sr[1]) !== false){
//                                    $platformName = '自助报表平台';
//                                }elseif(strpos($hostInfo, $sq[1]) !== false){
//                                    $platformName = '即席查询';
//                                }else{
//                                    $platformName = '选择平台';
//                                }
//
//                                $system = new ARedisSet(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_ACCESS_SYSTEM);

                        ?>

<!--                        --><?php //if ($system->count == 1) {
//                                echo '<li>'.CHtml::link(Yii::app()->name, Yii::app()->homeUrl, array('class'=>'brand')).'</li>';
//                              } else {?>
<!--                                <li class="dropdown">-->
<!--                                    <a class="dropdown-toggle" data-toggle="dropdown" href="#" style="margin-left:0;font-size:15px;color:#1E90FF;">--><?php //echo $platformName;?><!--<b class="caret"></b></a>-->
<!--                                    <ul class="dropdown-menu" style="border-radius: 4px;">-->
<!--                                        --><?php
//                                            if ($system->contains(Common::REDIS_ACCESS_SYSTEM_DCPLAT))
//                                                echo '<li>'.CHtml::link('窝窝数据平台', Yii::app()->params['dcplatform'], array('target'=>'_blank')).'</li>';
//                                            if ($system->contains(Common::REDIS_ACCESS_SYSTEM_DECISION))
//                                                 echo '<li>'.CHtml::link('决策系统', Yii::app()->params['decision'], array('target'=>'_blank')).'</li>';
//                                            if ($system->contains(Common::REDIS_ACCESS_SYSTEM_ANALYTICS))
//                                                 echo '<li>'.CHtml::link('流量分析系统', Yii::app()->params['analytics'], array('target'=>'_blank')).'</li>';
//                                            if ($system->contains(Common::REDIS_ACCESS_SYSTEM_DSFHZ))
//                                                echo '<li>'.CHtml::link('第三方平台', Yii::app()->params['dsfhz'], array('target'=>'_blank')).'</li>';
//                                            if ($system->contains(Common::REDIS_ACCESS_SYSTEM_SELFREPORT))
//                                                echo '<li>'.CHtml::link('自助报表平台', Yii::app()->params['selfreport'], array('target'=>'_blank')).'</li>';
//                                            if ($system->contains(Common::REDIS_ACCESS_SYSTEM_SELFQUERY))
//                                                echo '<li>'.CHtml::link('即席查询', Yii::app()->params['selfquery'], array('target'=>'_blank')).'</li>';
//                                        ?>
<!--                                    </ul>-->
<!--                                </li>-->
<!--                        --><?php //} ?>

                        <?php
                            $all_menu = Menu::getAllMenus();
                            $priv_menu = Common::getUserMenuTree();
                            $super = Yii::app()->user->getstate(Common::SESSION_SUPER);
                            $module_param = Common::getNumParam('module');
                        ?>
                        <?php if ($super==1 || $super==2){?>
                        <li <?php if(strstr($this->route, 'configure/') || strstr($this->route, 'datasource/') || strstr($this->route, 'usergroup/')) echo 'class="active"'?>><?php echo CHtml::link('报表配置', array('configure/index'));?></li>
                        <?php }?>
                        <?php
                            if (!empty($priv_menu)) {
                                $modules = array_keys($priv_menu);
                                foreach ($modules as $module) {
                                    if (!empty($priv_menu[$module])) {    //包含二级菜单的一级菜单才会显示
                                    $module_name = array_key_exists($module, $all_menu) ? $all_menu[$module]['menu_name'] : '';
                        ?>
                        <li <?php if($module_param == $module) echo 'class="active"'?>><?php echo CHtml::link($module_name, array('view/index','module'=>$module));?></li>
                        <?php }}}?>
                    <?php } ?>
                    </ul>
                    <ul class="nav navbar-nav navbar-right" style="font-weight: bold;">
                        <?php if (Yii::app()->user->isGuest){?>
                            <li><?php echo CHtml::link('登录',array('/site/login')); ?></li>
                        <?php }else{?>
                            <!-- <li <?php //if($this->route == 'feedback/index') echo 'class="active"'?>><?php //echo CHtml::link('意见反馈', array('feedback/index'));?></li> -->
                            <li <?php $ac = Announcement::getTodayAnnouncementCount(); if($this->route == 'announcement/index' || $this->route == 'announcement/view') echo 'class="active"'?>><?php echo CHtml::link('公告('.$ac.')', array('/announcement/index'));?></li>
                            <?php if ($super==1){?>
                            <li <?php if($this->route == 'admin/index') echo 'class="active"'?>><?php echo CHtml::link('平台管理', array('admin/index'));?></li>
                            <?php }?>
                            <li><?php echo CHtml::link('<i class="glyphicon glyphicon-user icon-white"></i>&nbsp;'.Yii::app()->user->name, array('/admin/setting')); ?></li>
                            <li><?php echo CHtml::link('退出',array('/site/logout')); ?></li>
                        <?php }?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="container-fluid" style="padding-left: 30px; padding-right: 30px;">
            <div class="content" id="main-body">
                <div id="alert-area">
                    <?php if(Yii::app()->user->hasFlash('success')){?>
                    <div class="alert alert-success">
                        <a class="close" data-dismiss="alert">×</a>
                        <?php  echo Yii::app()->user->getFlash('success');?>
                    </div>
                    <?php }?>
                    <?php if(Yii::app()->user->hasFlash('error')){?>
                    <div class="alert alert-danger">
                        <a class="close" data-dismiss="alert">×</a>
                        <?php  echo Yii::app()->user->getFlash('error');?>
                    </div>
                    <?php }?>
                    <?php if(Yii::app()->user->hasFlash('info')){?>
                    <div class="alert alert-info">
                        <a class="close" data-dismiss="alert">×</a>
                        <?php  echo Yii::app()->user->getFlash('info');?>
                    </div>
                    <?php }?>
                    <?php if(isset(Yii::app()->session['expire_remind']) && Yii::app()->session['expire_remind'] == 1){?>
                    <div class="alert alert-info">
                        <a class="close" data-dismiss="alert">×</a>
                        <?php  echo '您的账号即将到期，请通过OA重新申请';?>
                    </div>
                    <?php unset(Yii::app()->session['expire_remind']); }?>
                </div>
                <?php echo $content;?>
            </div>
        </div>
        <div style="position: fixed; bottom: 20%; right: 0.75%; cursor: pointer;">
            <div><a id="go-top" class="btn btn-lg" href="javascript:void(0)" title="返回顶部"><i class="glyphicon glyphicon-circle-arrow-up"></i></a></div>
            <div><a id="go-bottom" class="btn btn-lg" href="javascript:void(0)" title="前往底部"><i class="glyphicon glyphicon-circle-arrow-down"></i></a></div>
        </div>
        
        <div class="container">
            <div class="content">
                <hr>
                <footer class="footer">
                    <p>Copyright &copy; SqTech&nbsp;DataCenter</p>
                </footer>
            </div>
        </div>
    </body>
</html>