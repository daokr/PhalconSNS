<?php
namespace IKPHP\Apps\Home\Controllers;

use IKPHP\Common\Models\User as User;
use IKPHP\Common\Controllers\FrontendController;


class OauthController extends FrontendController
{
    public function initialize()
    {
        parent::initialize();
    }

    public function indexAction($mod){
		echo $mod;
		$this->_config_seo (); 
    }
	
}