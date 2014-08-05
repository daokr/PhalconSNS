<?php

namespace IKPHP\Common\Plugins;
use Phalcon\Mvc\User\Plugin;

class MyPlugin extends Plugin{
	public function getName($name){
		
		return $this->tag->setTitle($name);
	}
}

?>