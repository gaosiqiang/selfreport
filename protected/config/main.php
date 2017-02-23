<?php
//if (isset($_SERVER['HTTP_HOST'])) {
//    $domain = implode('.',array_slice(explode(".",strpos($_SERVER['HTTP_HOST'],':') ? substr($_SERVER['HTTP_HOST'],0,strpos($_SERVER['HTTP_HOST'],':')) : $_SERVER['HTTP_HOST']),-2,2));
//} else {
//    $domain = '';
//}
return array(
    'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
    'name'=>'自助报表平台',
    'defaultController'=>'view',
    'timeZone'=>'Asia/Shanghai',
    'language'=>'zh_cn',
    'preload'=>array('log'),

    // autoloading model and component classes
    'import'=>array(
        'application.models.*',
        'application.components.*',
        'application.components.widgets.*',
        'application.widgets.*',
        'application.extensions.*',
        'application.extensions.redis.*',
    ),

    'components'=>array(
        // 主库
        'db'=>array(
            'class'=>'CDbConnection',
            'connectionString' => 'mysql:host=123.56.106.97;dbname=dc_self_report',
            'emulatePrepare' => true,
            'username' => 'root',
            'password' => 'gao1990',
            'charset' => 'utf8',
            'enableProfiling'=>true,
        ),
        'udb'=>array(
            'class'=>'CDbConnection',
            'connectionString' => 'mysql:host=123.56.106.97;dbname=dc_self_report',
            'emulatePrepare' => true,
            'username' => 'root',
            'password' => 'gao1990',
            'charset' => 'utf8',
            'enableProfiling'=>true,
            'schemaCachingDuration'=>3600,
        ),
        //数据平台权限库
        'pdb'=>array(
            'class'=>'CDbConnection',
            'connectionString' => 'mysql:host=123.56.106.97;dbname=dc_self_report',
            'emulatePrepare' => true,
            'username' => 'root',
            'password' => 'gao1990',
            'charset' => 'utf8',
            'enableProfiling'=>true,
            'schemaCachingDuration'=>3600,
        ),
        'user'=>array(
            'class'=>'CWebUser',
            'identityCookie'=>array('domain' => 'table.com','path' => '/', 'httponly' => true), //配置用户cookie作用域
            //'allowAutoLogin'=>true,
            'stateKeyPrefix'=>'_dc_', //你的前缀,cookie的加密KEY,必须指定为一样的
//            'loginUrl'=>'https://data.wowotuan.com/login?return=selfreport',//array('site/login'),
            'loginUrl' => 'http://table.com/login',
        ),
        
        'request'=>array(
            'enableCookieValidation'=>true,
        ),
        //session组件 多个二级域名访问时需要
        'session'=>array(
            //'class' => 'CacheHttpSession',
            //'cacheID' => 'rediscache',
            'class' => 'ARedisSession',
            'keyPrefix' => '_dc_session:',
            'cookieParams' => array('domain' => 'table.com', 'lifetime' => '3600', 'httponly' => true), //配置会话ID作用域 生命期和超时
            'sessionName' => '_dc_',    //配置cookie的前缀
            //'cookieParams' => array('domain' => '.'. $domain),
            //'cookieParams' => array('lifetime' => '3600', 'path' => '/', 'domain' => '.test.com', 'httponly' => '1'),
            //'cookieMode' => 'only',
            //'timeout'=>3600,
        ),
        'statePersister'=>array(    //配置单点登录//指定cookie加密的状态文件
            'class'=>'ARedisStatePersister',//指定类
            'key'=>'_dc_common:statepersister',
            //'class'=>'StatePersister',//指定类
            //'cacheID'=>'rediscache',
        ),
        'clientScript'=>array(
            'packages'=>array(
                'jquery'=>false,
            ),
        ),
        'cache' => array(
            'class' => 'CFileCache',
            //'keyPrefix' => '_dc', //cache key 前缀
        ),
        'rediscache' => array(
            'class' => 'CRedisCache',
            'hostname'=>'10.9.210.197',
            'port'=>6379,
            'database'=>0,
            'keyPrefix' => '', //cache key 前缀
        ),
        'redis' => array(
            'class' => 'ARedisConnection',
            'hostname' => '10.9.210.197',
            'port' => 6379,
            'database' => 0,
            'prefix' => ''    //配置此项后，会导致通过 keys() -> delete() 删除键值对时因找不到键而无法删除。ps. keys()取出的键为完整键名，而delete()时会对参数里键名加上该前缀，使得反而找不到要删除的键
        ),
        'errorHandler'=>array(
            // use 'site/error' action to display errors
            'errorAction'=>'site/error',
        ),
        
        'log'=>array(
            'class'=>'CLogRouter',
            'routes'=>array(
                array(
                    'class'=>'CFileLogRoute',
                    'levels'=>'error, warning',
                ),
            ),
        ),
        'mailer' => array(
            'class' => 'application.extensions.mailer.EMailer',
            'pathViews' => 'application.views.email',
            'pathLayouts' => 'application.views.email.layouts'
        ),
        //路由处理组件
        'urlManager'=>array(
            'urlFormat'=>'path',
            'caseSensitive'=>false,// 是否区分大小写
            'showScriptName'=>false,// 显示脚本文件名
            'useStrictParsing'=>false,// 404时显示完整路径，默认为false
            'appendParams'=>true,
            'rules'=>array(
                'admin' => 'admin/index',
                'login' => 'site/login',
                'logout' => 'site/logout',
                '<controller:\w+>/<action:\w+>/<id:\d+>'=>array('<controller>/<action>'),
                '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
            ),
        ),
    ),
    'params'=>array(
        'dcplatform'=>'https://data.wowotuan.com',
        'decision'=>'https://jcfx.wowotuan.com',
        'analytics'=>'https://analytics.wowotuan.com',
        'dsfhz'=>'https://dsfhz.wowotuan.com',
        'selfreport'=>'https://report.wowotuan.com',
        'selfquery'=>'https://query.wowotuan.com',
        'login_url'=>array('site/login'),
        'uploadDir'=>dirname(dirname(dirname(__FILE__))).'/upload/',
        
        'export_page_size'=>'500000',
        'export_block_size'=>'50000',
        'export_time_limit'=>'180', //导出超时，单位秒，0为永不超时
        
        'admin' => array('1'),
        'smtp' => array(
//            'host' => 'mx.55tuan.com',
//            'port' => '587',
//            'username' => 'dcmonitor@55tuan.com',
//            'password' => '1qaz2wsx#EDC',
//            'from' => 'dcmonitor@55tuan.com',
//            'fromName' => 'dcmonitor',
//            'replyTo' => 'datacenter@55tuan.com',
//            'to' => array(
//                'datacenter@55tuan.com',
//                'wujianguang@55tuan.com'
//            ),
//            'charset' => 'utf-8',
//            'contentType' => 'text/html'
            'host' => 'mx.fengchaotx.com',
            'port' => '25',
            'username' => 'wuchunhui@fengchaotx.com',
            'password' => 'flofei@163.com',
            'from' => 'wuchunhui@fengchaotx.com',
            'fromName' => 'wuchunhui',
            'replyTo' => 'wuchunhui@fengchaotx.com',
            'to' => array(
                'wuchunhui@fengchaotx.com'
            ),
            'charset' => 'utf-8',
            'contentType' => 'textml'
        ),
        
        'encryptKey'=>'598d1c37b0',
        'column_define_separator' => '|+|',
    ),
);
