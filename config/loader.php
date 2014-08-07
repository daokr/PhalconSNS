<?php
$loader = new \Phalcon\Loader();

//注册公用组件目录
$loader->registerNamespaces(array(

'IKPHP\Common'    => $config->application->commmonDir,
'IKPHP\Vendor'    => $config->application->vendorDir,
'IKPHP\Org'       => $config->application->orgDir,
'IKPHP\Api'       => $config->application->apiDir,
'IKPHP\Plugins'       => $config->application->pluginsDir,

));

$eventsManager = new \Phalcon\Events\Manager();
//接听装置了哪些目录
$eventsManager->attach('loader', function($event, $loader) use ($di){
    if ($event->getType() == 'beforeCheckPath') {
        $di->get('logger')->log('加载文件：'.$loader->getCheckedPath());
    }
});
$loader->setEventsManager($eventsManager);

$loader->register();