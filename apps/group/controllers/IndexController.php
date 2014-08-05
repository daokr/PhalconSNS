<?php
namespace IKPHP\Apps\Group\Controllers;

use IKPHP\Common\Models\User;
use IKPHP\Common\Controllers\FrontendController;

class IndexController extends FrontendController
{
    public function initialize()
    {
    	$this->tag->setTitle("小组");
        parent::initialize();
        
        $this->session->set('identity','my is session set');
    }
    
    public function indexAction()
    {
    	//$c = new Companies();
    	//$res = $c->findFirst(1);
    	//echo $res->name;
    	
    	$this->view->hello = "hey, 小组!";
    	
 		$this->flash->notice('小组首页');
 		
 		
 		if($this->request->isPost()){
			var_dump($this->security->checkToken());
 		}
 		
    }

    public function listAction(){

		$this->error('我是小组的list', 'home/index/index/dd/ss');
    }

}