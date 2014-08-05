<?php
// +----------------------------------------------------------------------
// | IKPHP.COM [ I can do all the things that you can imagine ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2050 http://www.ikphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 小麦 <ikphp@sina.cn> <http://www.ikphp.cn>
// +----------------------------------------------------------------------

// 爱客开源多应用入口文件

// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('要求 PHP > 5.3.0 !');
define ( 'IN_IK', true );
//载入版本号 删除后将无法升级系统
$arr_ikversion = require_once('version.php');
//定义全局版本序列
foreach ($arr_ikversion as $key => $val){
	define($key, $val);
}
if (!is_file('./../data/install.lock')) {
	header('Location: ./install.php');
	exit;
}
//报告所有错误
error_reporting(E_ALL);
//设置默认时区
date_default_timezone_set("Asia/Hong_Kong");

//定义基本常量必须
define('ROOT_PATH', dirname(dirname(__FILE__)));
define('RUN_PATH', ROOT_PATH.'/runtime/');
define('DATA_PATH', RUN_PATH.'/data/');
define('APP_PATH', ROOT_PATH.'/apps/');
define('COM_PATH', ROOT_PATH.'/common/');

try{

	$di = new \Phalcon\DI\FactoryDefault();
	
	//加载配置
	$config = include ROOT_PATH . "/config/config.php";
	//加载公用函数库
	require ROOT_PATH . "/common/Functions/Common.php";
	//加载组件目录和类
	require ROOT_PATH . "/config/loader.php";
	
	
    //处理请求
	$application = new Phalcon\Mvc\Application($di);
		
	//DI注入服务
	require ROOT_PATH . "/config/services.php";
	
	//注入模块
	$application->registerModules(require ROOT_PATH . '/config/modules.php');	
	
	//输出
	echo $application->handle()->getContent();
	
}catch (Phalcon\Exception $e) {
	$moudelname = $di->get('router')-> getModuleName ();
	$arrApp = $application->getModules();
	if(!in_array($moudelname, array_keys($arrApp))){
		if(!$di->get('config')->application->debug){
			$di->get('logger')->log($moudelname.' App Module Not Find:'.$e->getMessage());
			header ( "Location: " . 'http://www.ikphp.com');
		}else{			
			echo $e->getMessage();
		}
		exit;
	}
}catch (PDOException $e){
	echo $e->getMessage();
}