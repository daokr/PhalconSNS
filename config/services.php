<?php
//设置网站基本url路径
$di->set('url', function() use ($config) {
	$url = new \Phalcon\Mvc\Url();
	$url->setBaseUri($config->application->baseUri);
	return $url;
}, true);
//注册路由
$di->set('router', function() {
	return include __DIR__ . "/routes.php";
}, true);

//注册配置
$di->set('config',$config);

//会话开始
$di->set('session', function() {
	$session = new \Phalcon\Session\Adapter\Files();
	if (!$session->isStarted())
	{
		$session->start();
	}	
	return $session;
}, true);

//安全组件
$di->set('security', function(){
    $security = new Phalcon\Security();
    //Set the password hashing factor to 12 rounds
    $security->setWorkFactor(12);
    return $security;
}, true);

//视图模板
$di->set('volt', function($view, $di) use ($config){
    $volt = new \Phalcon\Mvc\View\Engine\Volt($view, $di);
    $volt->setOptions(array(
        "compiledPath" => $config->application->voltDir,
        "compiledExtension" => ".php",
    	"compiledSeparator" => "_",
    	"compileAlways" => $config->application->debug,//功能：模板始终检查父模板是否有修改 上线时候可以去掉
    ));
    //添加自定义函数
	$volt->getCompiler()->addExtension(new IKPHP\Common\Functions\IkFunctionExtension());   
    return $volt;
});

//错误提示
$di->set('flash', function(){
	$flash = new Phalcon\Flash\Direct();
	$flash->setAutomaticHtml(false);
    return $flash;
});

$di->set('flashSession', function(){
	$flash = new Phalcon\Flash\Direct();
	$flash->setAutomaticHtml(false);
    return $flash;
});

//记录日志
$di->set('logger', function () use ($config){
      $logger = new \Phalcon\Logger\Adapter\File($config->logger->path .date('Y-m-d').'.log');
      $formatter = new Phalcon\Logger\Formatter\Line($config->logger->format);
      $logger->setFormatter($formatter);
      return $logger;
}, true);

//注册数据库链接
$di->set('db', function() use ($config) {

	$connection = new \Phalcon\Db\Adapter\Pdo\Mysql(array(
		"host" => $config->database->host,
		"username" => $config->database->username,
		"password" => $config->database->password,
		"dbname" => $config->database->name,
		"prefix" => $config->database->prefix,
		"options" => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
        )
	));

	if ($config->application->debug) {
		//开启日志记录
		$eventsManager = new Phalcon\Events\Manager();
		$logger = new \Phalcon\Logger\Adapter\File($config->logger->path .date('Y-m-d').'db.log');
		//监听数据库日志
		$eventsManager->attach('db', function($event, $connection) use ($logger) {
		    if ($event->getType() == 'beforeQuery') {
		        $logger->log($connection->getSQLStatement(), \Phalcon\Logger::INFO);
		    }
		});	
		$connection->setEventsManager($eventsManager);		
	}

	return $connection;
});

//初始化元数据适配器 使用内存
$di->set('modelsMetadata', function() use ($config) {
    if ($config->modelsMetadata->enable) { 
		$metaDataConfig = $config->modelsMetadata;
		$metadataAdapter = 'Phalcon\Mvc\Model\Metadata\\' . $metaDataConfig->adapter;
		return new $metadataAdapter($config->modelsMetadata->options->toArray());
     }else{
     	return new Phalcon\Mvc\Model\Metadata\Memory();
     }
}, true);

//公用组件
$di->set('elements', function(){
	return new IKPHP\Common\Library\Elements();
});

//定义分发器
$di->set('dispatcher',function() use ($application, $di){ 
        $dispatcher = new \Phalcon\Mvc\Dispatcher(); 
        $moudelname = $di->getShared('router')-> getModuleName ();
        
        $arrApp = $application->getModules();
        if(in_array($moudelname, array_keys($arrApp))){
        	 $appname = ucfirst($moudelname); 
        	 $dispatcher->setDefaultNamespace('IKPHP\Apps\\'.$appname.'\Controllers');
        }
        $eventsManager = new \Phalcon\Events\Manager();
        $eventsManager->attach("dispatch:beforeException", function($event, $dispatcher, $exception) use ($di) { 
        	switch ($exception->getCode()) {
		         case Phalcon\Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
		         case Phalcon\Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
				 $dispatcher->forward(array(
					"controller" => "index",
				    "action" => "show404"
				 ));				 
		         return FALSE;
		     }        						        
        });   
    
        //Bind the EventsManager to the dispatcher
        $dispatcher->setEventsManager($eventsManager);  
        return $dispatcher;
},true);

//静态文件缓存
$di->set('viewCache', function() use ($config) {
    //Cache data for one day by default
    $frontCache = new \Phalcon\Cache\Frontend\Output($config->cache->viewCache->frontend->options->toArray());
    //Memcached connection settings
    return new \Phalcon\Cache\Backend\File($frontCache, $config->cache->viewCache->backend->options->toArray());
});
//数据缓存
$di->set('modelCache', function() use ($config) {
    //Cache data for one day by default
    $frontCache = new \Phalcon\Cache\Frontend\Data($config->cache->modelCache->frontend->options->toArray());
    //Memcached connection settings
    return new \Phalcon\Cache\Backend\File($frontCache,$config->cache->modelCache->backend->options->toArray());
});

//注入配置

