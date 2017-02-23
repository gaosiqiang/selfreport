<?php 
    $plat_access = true;    //是否具有原平台权限
    $system = new ARedisSet(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_ACCESS_SYSTEM);
    switch ($platform) {
        case 1:
            $brand_name = '窝窝数据平台';
            $brand_url = Yii::app()->params['dcplatform'];
            //if ($system->count()>0 && !$system->contains(Common::REDIS_ACCESS_SYSTEM_DCPLAT)) {
            if (Yii::app()->user->hasstate('dcplat_only_selfreport')) {
                $plat_access = false;
            }
            break;
        case 2:
            $brand_name = '决策系统';
            $brand_url = Yii::app()->params['decision'];
            if ($system->count()>0 && !$system->contains(Common::REDIS_ACCESS_SYSTEM_DECISION)) {
                $plat_access = false;
            }
            break;
        default:
            $brand_name = Yii::app()->name;
            $brand_url = Yii::app()->homeUrl;
    }
?>
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
        <title><?php echo $brand_name; ?></title>
    </head>
    <body>
        <div class="navbar navbar-inverse navbar-static-top" style="min-height: 0;">
            <div class="container-fluid" style="padding-left: 45px; padding-right: 45px;">
                <div class="navbar-collapse collapse">
                    <ul class="nav navbar-nav">
                        <li>
                        <?php 
                            if ($system->count==1) {
                                echo CHtml::link($brand_name, '', array('style'=>'margin-left:0;font-size:15px;color:#1E90FF;'));
                            } else { ?>
                                <li class="dropdown">
                                    <a class="dropdown-toggle" data-toggle="dropdown" href="#" style="margin-left:0;font-size:15px;color:#1E90FF;"><?php echo $brand_name;?><b class="caret"></b></a>
                                    <ul class="dropdown-menu" style="border-radius: 4px;">
                                        <?php 
                                            if ($system->contains(Common::REDIS_ACCESS_SYSTEM_DCPLAT) && $platform!=1)
                                                echo '<li>'.CHtml::link('窝窝数据平台', Yii::app()->params['dcplatform'], array('target'=>'_blank')).'</li>';
                                            if ($system->contains(Common::REDIS_ACCESS_SYSTEM_DECISION) && $platform!=2)
                                                echo '<li>'.CHtml::link('决策系统', Yii::app()->params['decision'], array('target'=>'_blank')).'</li>';
                                            if ($system->contains(Common::REDIS_ACCESS_SYSTEM_ANALYTICS))
                                                echo '<li>'.CHtml::link('流量分析系统', Yii::app()->params['analytics'], array('target'=>'_blank')).'</li>';
                                            if ($system->contains(Common::REDIS_ACCESS_SYSTEM_DSFHZ))
                                                echo '<li>'.CHtml::link('第三方平台', Yii::app()->params['dsfhz'], array('target'=>'_blank')).'</li>';
                                            if ($system->contains(Common::REDIS_ACCESS_SYSTEM_SELFREPORT))
                                                echo '<li>'.CHtml::link('自助报表平台', Yii::app()->params['selfreport'], array('target'=>'_blank')).'</li>';
                                            if ($system->contains(Common::REDIS_ACCESS_SYSTEM_SELFQUERY))
                                                echo '<li>'.CHtml::link('即席查询', Yii::app()->params['selfquery'], array('target'=>'_blank')).'</li>';
                                        ?>
                                    </ul>
                                </li>
                        <?php } ?>
                        </li>
                        <?php 
                            if (!$plat_access) {
                                foreach ($parents_tree as $menu_id => $tree) {
                                    $menu = array_key_exists($menu_id, $all_menu) ? $all_menu[$menu_id] : array();
                                    if (!empty($menu)) {
                                        $menu_name = isset($menu['menu_name']) ? $menu['menu_name'] : '';
                                        $url = Common::get1stSubMenu($platform, $menu_id);
                        ?>
                        <li <?php if ($menu_id==$module) echo 'class="active"';?>><?php echo CHtml::link($menu_name, $url);?></li>
                        <?php 
                                    }
                                }
                            } else {
                        ?>
                        <?php 
                                $menu = array_key_exists($module, $all_menu) ? $all_menu[$module] : array();
                                if (!empty($menu)) {
                                    $menu_name = isset($menu['menu_name']) ? $menu['menu_name'] : '';
                                    $url = Common::get1stSubMenu($platform, $module);
                        ?>
                        <li class="active"><?php echo CHtml::link($menu_name, $url);?></li>
                        <?php 
                                }
                            }
                        ?>
                    </ul>
                    <ul class="nav navbar-nav navbar-right" style="font-weight: bold;">
                        <?php if (Yii::app()->user->isGuest){?>
                            <li><?php echo CHtml::link('登录',array('/site/login')); ?></li>
                        <?php }else{?>
                            <li><?php echo CHtml::link('<i class="glyphicon glyphicon-user icon-white"></i>&nbsp;'.Yii::app()->user->name, ''); ?></li>
                            <li><?php echo CHtml::link('退出',$brand_url.'/site/logout'); ?></li>
                        <?php }?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="container-fluid" style="padding-left: 30px; padding-right: 30px;">
            <div class="content">
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
                    <p>Copyright &copy; SqTech.com&nbsp;DataCenter</p>
                </footer>
            </div>
        </div>
    </body>
</html>