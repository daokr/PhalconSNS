<?php
namespace IKPHP\Apps\Home\Controllers;

use IKPHP\Common\Models\User as User;
use IKPHP\Common\Models\Setting;
use IKPHP\Common\Controllers\FrontendController;

class IndexController extends FrontendController
{
    public function initialize()
    {
        parent::initialize();
    }
	public function testAction(){
		echo 'hello word!:';
		$this->view->disable();
	}
    public function indexAction(){

		$user = new User();
		$abc = $user->getOneUser(2);
		
		$this->view->abc = $abc;
		
		$this->_config_seo (); 
    }
	
}