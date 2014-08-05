<?php
namespace IKPHP\Apps\Space\Controllers;

use IKPHP\Common\Models\User;
use IKPHP\Common\Controllers\FrontendController;

class IndexController extends FrontendController
{
    public function initialize()
    {
        parent::initialize();
    }
    
    public function indexAction($id = '')
    {
    	if(empty($id)) return $this->error ( '呃...你想要的东西不在这儿' );
    	
    	echo $id;
 		$this->_config_seo ();
    }
    public function testAction(){
    	echo 123;
    }

}