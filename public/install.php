<?php

error_reporting(E_ALL);
//判断是否安装过了
if (is_file('./../data/install.lock')) {
	header('Location: ./index.php');
	exit;
}
//定义基础路径
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__FILE__)));
}
//定义安装常量
define('DATA_PATH', ROOT_PATH.'/install/data/');
define('APP_PATH', ROOT_PATH.'/install/');
try {

	$config = new Phalcon\Config\Adapter\Ini(__DIR__ . '/../install/config/config.ini');
	//载入版本号
	require_once('version.php');
	//加载公用函数库
	require ROOT_PATH . "/common/Function/common.php";	
	$loader = new \Phalcon\Loader();

	$loader->registerDirs(
		array(
			__DIR__ . $config->application->controllersDir,
		)
	)->register();

	$di = new \Phalcon\DI\FactoryDefault();

	$di->set('url', function() use ($config){
		$url = new \Phalcon\Mvc\Url();
		$url->setBaseUri($config->application->baseUri);
		return $url;
	});
	
	$di->set('view', function() use ($config) {
		$view = new \Phalcon\Mvc\View();
		$view->setViewsDir(__DIR__ . $config->application->viewsDir);
		$view->registerEngines(array(
			".html" => 'volt'
		));
		return $view;
	});
	
	$di->set('volt', function($view, $di) use ($config) {
		$volt = new \Phalcon\Mvc\View\Engine\Volt($view, $di);
	    $volt->setOptions(array(
	        "compiledPath" => __DIR__ . $config->application->cacheDir,
	        "compiledExtension" => ".php",
	    	"compiledSeparator" => "_",
	    	"compileAlways" => false,//功能：是否开启编译 上线时候可以去掉
	    ));		
	    return $volt;
	}, true);

	
	$application = new \Phalcon\Mvc\Application();
	$application->setDI($di);
	echo $application->handle()->getContent();

} catch (Phalcon\Exception $e) {
	echo $e->getMessage();
} catch (PDOException $e){
	echo $e->getMessage();
}