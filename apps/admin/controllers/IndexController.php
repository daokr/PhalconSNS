<?php
namespace IKPHP\Admin\Controllers;

use IKPHP\Common\Controllers\BackendController;

class IndexController extends BackendController
{
    public function initialize()
    {
    	$this->tag->setTitle("后台管理");
        parent::initialize();
    }
    
    public function indexAction()
    {
		
    }


}