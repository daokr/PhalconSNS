<?php
$router = new \Phalcon\Mvc\Router(false);

//默认首页 public
$router->setDefaultModule("home");
$router->setDefaultController("index");
$router->setDefaultAction("index");
$router->removeExtraSlashes(true); //加 斜杠 /

	

$router->add('/:module/:controller/:action/:params', array(
	    'module' => 1,
	    'controller' => 2,
	    'action' => 3,
	    'params' => 4
));

$router->add('/:module/:controller/:action', array(
	    'module' => 1,
	    'controller' => 2,
	    'action' => 3
));	
$router->add('/:module', array(
	    'module' => 1,
	    'controller' => 'index',
	    'action' => 'index'
));			
//默认http://www.ikphp.com/group/topic/	直接到index
$router->add('/:module/:controller', array(
	    'module' => 1,
	    'controller' => 2,
	    'action' => "index",
));	

//http://www.ikphp.com/group/topic/show/123
//http://www.ikphp.com/group/index/index/123
$router->add(
    "/:module/:controller/:action/:int",
    array(
     	'module' => 1,
        "controller" => 2,
        "action"     => 3,
        "id"         => 4,
    )
);

$router->add(
    "/:module/:controller/:action/id/:int/p/:int",
    array(
     	'module' => 1,
        "controller" => 2,
        "action"     => 3,
        "id"         => 4,
    	"p"         => 5,
    )
);
//个人空间
$router->add(
    "/space/{id:[a-zA-Z0-9\-_]{0,14}}",
    array(
     	'module' => 'space',
        "controller" => 'index',
        "action"     => 'index',
    )
);
   
return $router;