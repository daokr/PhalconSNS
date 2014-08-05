<?php
namespace IKPHP\Common\Controllers;

class BackendController extends BaseController
{
	//全站初始化设置
    protected function initialize(){
    	
          $this->tag->appendTitle(' - 爱客网');
          
    }
}