<?php
return new \Phalcon\Config(array(
	'database' => require __DIR__.'/db.php',
	'application' => array(
		'debug' => true,//是否是开发调试；如果是 那么记录数据库日志

		'commmonDir'     => __DIR__ . '/../common/',
		'vendorDir'     => __DIR__ . '/../common/Library/Vendor/', //第三方库
		'orgDir'     => __DIR__ . '/../common/Library/Org/', //公用库
		'apiDir'     => __DIR__ . '/../common/Library/Api/', //api类
		'pluginsDir'     => __DIR__ . '/../common/Plugins/', //插件库

		'voltDir'   => __DIR__ . '/../runtime/cache/volt/',//编译模板cache
		'cacheviewDir'   => __DIR__ . '/../runtime/cache/views/',//静态化cache	
		'baseUri'        => '/phalconcms/'
	),
    //元数据配置
    'modelsMetadata' => array(
        'enable' => true,
        'adapter' => 'Files',
        'options' => array(
            'metaDataDir' => __DIR__ . '/../runtime/cache/schema/',
	        "lifetime" => 86400,
        	"prefix"   => "meta-"
        ),
    ),
	//缓存配置
	'cache' => array(
        'viewCache' => array(
            'enable' => true,
            'frontend' => array(
                'adapter' => 'Output',
                'options' => array(
    				'lifetime' => 86400 ,
    			),
            ),
            'backend' => array(
                'adapter' => 'File',
                'options' => array(
                    'cacheDir' => __DIR__ . '/../runtime/cache/views/',
            		'prefix' => "ik-cache-view-",
                ),
            ),
        ),
        'modelCache' => array(
            'enable' => true,
            'frontend' => array(
                'adapter' => 'Data',
                'options' => array('lifetime' => 86400),
            ),
            'backend' => array(
                'adapter' => 'File',
                'options' => array(
                    'cacheDir' => __DIR__ . '/../runtime/cache/model/',
            		"prefix"   => "ik-data-"
                ),
            ),
        )
    ),    
  	
    'logger' => array (
      'enabled' => true,
      'path' => ROOT_PATH . '/runtime/logs/',
      'format' => '[%date%][%type%] %message%',
    ),

    'datetime' => array(
        'defaultTimezone' => 8,
        'defaultFormat' => 'Y年m月d日 H:i:s',
    ),

    'filesystem' => array(
        'default' => array(
            'adapter' => 'local',
            'baseUrlForLocal' => '/uploads',
            'uploadPath' => __DIR__ . '/../public/uploads/',
        ),
    ),    

       
	
));