<?php
/**
 * 包含主配置文件
 * 当有差异的数组或要删除的数组的时候，才需要unset，否则如果重写结构一样的数组可以不必unset
 */
$main_conf = require(dirname(__FILE__).'/main.php');
// 注销掉main中的配置
unset($main_conf['components']['log']);
unset($main_conf['components']['user']);
unset($main_conf['components']['session']);
unset($main_conf['components']['redis']);
unset($main_conf['components']['statePersister']);
return CMap::mergeArray(
    $main_conf,
    array(
        // "[*]"标识的位置为需要或可能修改的配置

        'components'=>array(
            //[*]自助报表 - 主库
            'db'=>array(
                'class'=>'CDbConnection',
                'connectionString' => 'mysql:host=10.8.210.177;port=3308;dbname=dc_self_report',
                'emulatePrepare' => true,
                'username' => 'root',
                'password' => '123456',
                'charset' => 'utf8',
                'enableProfiling'=>true,
                'enableParamLogging' => true,
                'schemaCachingDuration'=>3600,
            ),
            //[*]决策系统 - 用户基本信息库
            'udb'=>array(
                'class'=>'CDbConnection',
                'connectionString' => 'mysql:host=10.8.210.177;port=3308;dbname=datacenter_decision',
                'emulatePrepare' => true,
                'username' => 'root',
                'password' => '123456',
                'charset' => 'utf8',
                'enableProfiling'=>true,
                'enableParamLogging' => true,
                'schemaCachingDuration'=>3600,
            ),
            //[*]数据平台
            'pdb'=>array(
                'class'=>'CDbConnection',
                'connectionString' => 'mysql:host=123.56.106.97;dbname=dcdata',
                'emulatePrepare' => true,
                'username' => 'root',
                'password' => '123456',
                'charset' => 'utf8',
                'enableProfiling'=>true,
                'enableParamLogging' => true,
                'schemaCachingDuration'=>3600,
            ),
            'user'=>array(
                'class'=>'CWebUser',
                'identityCookie'=>array('domain' => '.dc.com','path' => '/', 'httponly' => true), //配置用户cookie作用域
                'stateKeyPrefix'=>'_dc_', //你的前缀,cookie的加密KEY,必须指定为一样的
                //[*]通过数据平台统一登录
                'loginUrl'=>'http://data.dc.com/login?return=selfreport',//array('site/login'),
            ),
            //session组件 多个二级域名访问时需要
            'session'=>array(
                'class' => 'ARedisSession',
                'keyPrefix' => '_dc_session:',
                'cookieParams' => array('domain' => '.dc.com', 'lifetime' => '3600', 'httponly' => true), //配置会话ID作用域 生命期和超时
                'sessionName' => '_dc_sid',
            ),
            'statePersister'=>array(    //配置单点登录//指定cookie加密的状态文件
                'class'=>'ARedisStatePersister',//指定类
                'key'=>'_dc_common:statepersister',
            ),
            //[*]Redis服务器配置
            'redis' => array(
                'class' => 'ARedisConnection',
                'hostname' => '127.0.0.1',
                'port' => 6379,
                'database' => 0,
                'prefix' => ''
            ),
            'log'=>array(
                'class'=>'CLogRouter',
                'routes'=>array(
                    array(
                        'class'=>'CFileLogRoute',
                        'levels'=>'error, warning', //, trace
                    ),
                ),
            ),
        ),
        
        //[*]参数配置
        'params'=>array(
            'dcplatform'=>'http://test_dataplatform.wowotuan.com:8000',
            'decision'=>'http://test_jcfx.wowotuan.com:8000',
            'analytics'=>'http://test_analytics.wowotuan.com:8000',
            'dsfhz'=>'http://test_jcfx.wowotuan.com:8001',
            'selfreport'=>'http://test_report.wowotuan.com:8000',
            'selfquery'=>'http://test_selfquery.wowotuan.com:3000',
            'login_url'=>'http://data.dc.com/login?return=selfreport',
            'uploadDir'=>dirname(dirname(dirname(__FILE__))).'/upload/',
            
            'export_page_size'=>'500000',    //导出分页
            'export_block_size'=>'50000',    //导出分块
            'export_time_limit'=>'180',      //导出超时，单位秒，0为永不超时
            'encryptKey'=>'598d1c37b0',      //数据源密码加密密钥
            'column_define_separator' => '|+|',
        ),
    )
);