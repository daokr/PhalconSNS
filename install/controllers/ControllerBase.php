<?php
/*
 * 爱客开源网站安装程序
* @copyright (c) 2012-3000 IKPHP All Rights Reserved
* @author 小麦
* @Email:810578553@qq.com
*/
class ControllerBase extends Phalcon\Mvc\Controller
{

    protected function initialize()
    {
      	$language = $this->request->getBestLanguage();
      	$langUri = __DIR__."/../lang/".$language.".php";
		if (file_exists($langUri)) {
	       require $langUri;
	    }     	
    	$newcon = new \Phalcon\Translate\Adapter\NativeArray(array(
       		"content" => $messages
    	));

    	$this->view->setVar('L',$newcon);
    }

    protected function forward($uri){
    	$uriParts = explode('/', $uri);
    	return $this->dispatcher->forward(
    		array(
    			'controller' => $uriParts[0], 
    			'action' => $uriParts[1]
    		)
    	);
    }
    public function assign($key, $val){
    	$this->view->setVar($key, $val);
    }
}
