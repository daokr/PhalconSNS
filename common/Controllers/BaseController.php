<?php
namespace IKPHP\Common\Controllers;

use Phalcon\Mvc\Controller;

use IKPHP\Common\Models\Setting as Setting;

class BaseController extends Controller {
	
	public $ik_setting; //网站配置
	
	protected function initialize() { 
		//定义网站全局常量
		$this->_init_var();
		//初始化网站配置
		$setting = new Setting ();
		$this->ik_setting = $setting->setting_cache ();
		$this->view->setVar ( 'ik_setting', $this->ik_setting );
		//初始化前端样式css和js
		$this->init_frontend();

	}
	/**
	 * 重新Phalcon的get方法
	 * @param string $name
	 * @param string|array $filters
	 * @param mixed $defaultValue
	 * @param boolean $notAllowEmpty
	 * @param boolean $noRecursive
	 * @return mixed
	 */
	protected function _get($name=null, $filters=null, $defaultValue=null){
		return $this->request->get($name , $filters, $defaultValue);
	}
	/**
	 * 重新Phalcon的post方法
	 * @param string $name
	 * @param string|array $filters
	 * @param mixed $defaultValue
	 * @param boolean $notAllowEmpty
	 * @param boolean $noRecursive
	 * @return mixed
	 */
	protected function _post($name=null, $filters=null, $defaultValue=null){
		return $this->request->getPost($name , $filters, $defaultValue);
	}
    /**
     * Ajax方式返回数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type AJAX返回数据格式
     * @return void
     */
    protected function ajaxReturn($data,$type='JSON') {
        switch (strtoupper($type)){
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data));
            case 'JSONP':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                $handler  =   isset($_GET['callback']) ? $_GET['callback'] : 'jsonpReturn';
                exit($handler.'('.json_encode($data).');');  
            case 'EVAL' :
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                exit($data);            
        }
    }	
    /**
     * 模板变量赋值
     * @access protected
     * @param mixed $name 要显示的模板变量
     * @param mixed $value 变量的值
     * @return Action
     */
    protected function assign($name,$value='') {
        $this->view->setVar($name,$value);
        return $this;
    }
    /**
     * 重写redirect
     * @param string $uri
     * @return redirect
     */
    protected function _redirect($uri){
    	return $this->response->redirect($uri);
    }
    /**
     * 重写forward
     * @param string $uri
     * @return forward
     */
    protected function _forward($uri){
    	$uriParts = explode('/', $uri);
    	return $this->dispatcher->forward(
    		array(
    			'controller' => $uriParts[0], 
    			'action' => $uriParts[1]
    		)
    	);
    }   
	//初始化前端样式
	private function init_frontend(){
		//网站地址 带 / 如：http://www.ikphp.com/
		$site_url = $this->ik_setting->ik_site_url;
		$site_theme = $this->ik_setting->ik_site_theme;
		$this->view->setVar ( 'SITE_URL',  $site_url);
		
		$moudelname = $this->dispatcher->getModuleName();
		$controll_name = $this->dispatcher->getcontrollerName();
		$action_name = $this->dispatcher->getActionName();
		
        //当前app名称
        $this->view->setVar('app_name',strtolower($moudelname));
        //当前model名称
        $this->view->setVar('module_name',strtolower($moudelname));
        //当前controll名称
        $this->view->setVar('controll_name',strtolower($controll_name)); 
        //当前action名称
        $this->view->setVar('action_name',strtolower($action_name)); 		

		//网站公共文件目录
		$this->view->setVar ( 'PUBLIC', $site_url.'static' );        
		//网站APP静态文件目录
		$this->view->setVar ( 'APP_STATIC', $site_url.'theme/'.$site_theme.'/'.$moudelname );
        //网站APP应用风格路径
        $this->view->setVar ( 'APP_STATIC_CSS', $site_url.'theme/'.$site_theme.'/'.$moudelname.'/css' );
        //网站APP应用风格图片路径
        $this->view->setVar ( 'APP_STATIC_IMG', $site_url.'theme/'.$site_theme.'/'.$moudelname.'/images' );
        //网站APP应用风格图片路径
        $this->view->setVar ( 'APP_STATIC_JS', $site_url.'theme/'.$site_theme.'/'.$moudelname.'/js' );
        
        //网站基本风格
        $basecss = 'theme/'.$site_theme.'/base.css';
        //APP风格默认样式
        $appcss = 'theme/'.$site_theme.'/'.$moudelname.'/css/style.css'; 
        //APP风格下的controll_css样式
        $app_controll_css = 'theme/'.$site_theme.'/'.$moudelname.'/css/'.strtolower($controll_name).'.css'; 
		
        //添加页面样式  
		$site_css = $this->assets->collection ( 'SITE_THEME_CSS' );
		if(is_file($basecss)){
			$site_css->addCss ( $basecss );
		}
		if(is_file($appcss)){
			$site_css->addCss ( $appcss );
		}
		if(is_file($app_controll_css)){
			$site_css->addCss ( $app_controll_css );
		}

		//网站扩展js
		$appextendjs = 'theme/'.$site_theme.'/'.$moudelname.'/js/extend.func.js';
		//APP下的MODULE_NAME 对应的js
        $appcontrolljs = 'theme/'.$site_theme.'/'.$moudelname.'/js/'.strtolower($controll_name).'.js';
        
		$site_js = $this->assets->collection ( 'EXTENDS_JS' );
		if(is_file($appextendjs)){
			$site_js->addJs ( $appextendjs );
		}
		if(is_file($appcontrolljs)){
			$site_js->addJs ( $appcontrolljs );
		}		
	}
	//定义全局常量
	private function _init_var(){
		defined('IS_AJAX') or define('IS_AJAX', $this->request->isAjax());
		defined('IS_GET')  or define('IS_GET',  $this->request->isGet());
        defined('IS_POST') or define('IS_POST', $this->request->isPost());

        //定义全局
        defined('MODULE_NAME') or define('MODULE_NAME', $this->dispatcher->getModuleName());	
        defined('CONTROLLER_NAME') or define('CONTROLLER_NAME', $this->dispatcher->getcontrollerName());	
        defined('ACTION_NAME') or define('ACTION_NAME', $this->dispatcher->getActionName());	

	}
    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param string $message 错误信息
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    protected function error($messages='',$jumpUrl='') {
		 $this->dispatcher->forward(array(
		    "action" 		=> "notice",
		 	"params" 		=> array('jumpurl'=>$jumpUrl)
		 ));
		 if(is_array($messages)){
		    foreach ($messages as $message) {
                $this->flash->error((string) $message.'<br/>');
            }
		 }else{
		 	$this->flash->error($messages);
		 }
    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param string $message 提示信息
     * @param string $jumpUrl 页面跳转地址
     * @return void
     */
    protected function success($message='',$jumpUrl='') {
		 $this->dispatcher->forward(array(
		    "action" 		=> "notice",
		 	"params" 		=> array('jumpurl'=>$jumpUrl)
		 ));
		 $this->flash->success($message);
    }
    /**
     * 全站统一分页方法
     * @param string $count 总的记录数
     * @param string $pagesize 每页显示记录数
     */
    protected function _pager($count, $pagesize, $pageurl = '') {
    	$pager = new \IKPHP\Org\Paginator($count, $pagesize, $pageurl);
    	$pager->rollPage = 5;
    	$pager->setConfig('prev', '<前页');
    	$pager->setConfig('next', '后页>');
    	$pager->setConfig('theme', '%UP_PAGE% %FIRST% %LINK_PAGE% %END% %DOWN_PAGE%');
    	return $pager;
    } 
}