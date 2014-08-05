<?php
namespace IKPHP\Common\Controllers;


use \Phalcon\Config as Config;
use IKPHP\Common\Models\App as AppMod;

class FrontendController extends BaseController {
	
	//全站前端 visitor
	public $visitor = null;
	
	//全站初始化设置
	protected function initialize() {
		parent::initialize();
		$app_name = strtolower($this->dispatcher->getModuleName());
		//初始化访问者
        $this->_init_visitor();
        //网站导航
        $this->assign('ik_top_nav',$this->_topnav());
        $this->assign('ik_app_nav',$this->_appnav($app_name));
        $this->assign('ik_app_logo',$this->_applogo($app_name));        
	}
    /**
     * 初始化访问者
     */
    private function _init_visitor() {
    	if($this->is_login()){
    		$this->visitor = $user_auth = $this->session->get("user_auth");
    		//$count_msg_unread = D('Common/Message')->where(array('touserid'=>$user_auth['userid'],'isread'=>0,'isinbox'=>0))->count();
    		//$count_new_msg = $count_msg_unread>0 ? $count_msg_unread : 0;
    		//$this->assign('count_new_msg', $count_new_msg);
    	}
    	$this->assign('visitor', $this->visitor);
    	//$this->assign('count_online_user', $this->visitor->getOnlineUserCount());
    	//$this->assign('count_online_user', rand(1000,9999));
    }
	/**
	 * 检测用户是否登录
	 * @return integer 0-未登录，大于0-当前登录用户ID
	 * @author 小麦 <ikphp@sina.cn>
	 */    
    private function is_login(){
	    $user = $this->session->get('user_auth'); 
	    if (empty($user)) {
	        return 0;
	    } else {
	        return $this->session->get('user_auth_sign') == data_auth_sign($user) ? $user['userid'] : 0;
	    }    	
    }
    /**
     * SEO设置
     * @param array $seo_info
     * @param array $data
     */
    protected function _config_seo($seo_info = array(), $data = array()) {
    	$page_seo = array(
    			'title' => $this->ik_setting->ik_site_title,
    			'subtitle' => $this->ik_setting->ik_site_subtitle,
    			'keywords' => $this->ik_setting->ik_site_keywords,
    			'description' => $this->ik_setting->ik_site_desc
    	);
    	$page_seo = array_merge($page_seo, $seo_info);
    	//开始替换
    	$searchs = array('{site_name}', '{site_title}', '{site_keywords}', '{site_desc');
    	$replaces = array($this->ik_setting->ik_site_title, $this->ik_setting->ik_site_subtitle, $this->ik_setting->ik_site_keywords, $this->ik_setting->ik_site_desc);
    	preg_match_all("/\{([a-z0-9_-]+?)\}/", implode(' ', array_values($page_seo)), $pageparams);
    	if ($pageparams) {
    		foreach ($pageparams[1] as $var) {
    			$searchs[] = '{' . $var . '}';
    			$replaces[] = $data[$var] ? strip_tags($data[$var]) : '';
    		}
    		//符号
    		$searchspace = array('((\s*\-\s*)+)', '((\s*\,\s*)+)', '((\s*\|\s*)+)', '((\s*\t\s*)+)', '((\s*_\s*)+)');
    		$replacespace = array('-', ',', '|', ' ', '_');
    		foreach ($page_seo as $key => $val) {
    			$page_seo[$key] = trim(preg_replace($searchspace, $replacespace, str_replace($searchs, $replaces, $val)), ' ,-|_');
    		}
    	}
    	//设置配置
        $conf = new Config($page_seo);
    	$this->view->setVar('seo', $conf);
    }
    // 顶部次导航
    protected function _topnav(){
    	$app_mod = new AppMod();
    	$arrNav = array ();
		$arrNav['index'] = array('name'=>'首页', 'url'=>$this->ik_setting->ik_site_url);
		$arrApp = $app_mod->find(array(
			'columns' => 'app_name,app_alias,app_entry',
			'conditions'=> 'status = 1',
			'order'	=> 'display_order asc'
		));
		foreach($arrApp as $item){ 
			if(empty($item['app_entry'])){
				$item['app_entry'] = 'index/index';
			}
			$arrNav[$item['app_name']] = array('name'=>$item['app_alias'], 'url'=>$this->url->get($item['app_name'].'/'.$item['app_entry']));
		}
    	return $arrNav; 	
    }
	// 网站主导航
	protected function _appnav($app_name){
		if (! empty ( $app_name ) && $app_name == 'home') {
	    	$app_mod = new AppMod();
	    	$arrNav = array ();
			$arrNav['index'] = array('name'=>'首页', 'url'=>$this->ik_setting->ik_site_url);
			$arrApp = $app_mod->find(array(
				'columns' => 'app_name,app_alias,app_entry',
				'conditions'=> 'status = 1',
				'order'	=> 'display_order asc'
			));
			
			foreach($arrApp as $item){ 
				if(empty($item['app_entry'])){
					$item['app_entry'] = 'index/index';
				}
				$arrNav[$item['app_name']] = array('name'=>$item['app_alias'], 'url'=>$this->url->get($item['app_name'].'/'.$item['app_entry']));
			}
	    	return $arrNav; 
		}		
	}
	// 导航logo
	protected function _applogo($app_name){
		if (! empty ( $app_name )) {
			$app_mod = new AppMod();
			$arrLogo = array ();
			$strApp = $app_mod->findFirst(array('app_name'=>$app_name))->toArray();
			if($strApp){
				$arrLogo = array('name'=>$strApp['app_alias'], 'url'=>$this->url->get($app_name.'/'.$strApp['app_entry']), 'style'=>'site_logo nav_logo');
			}else{
				$arrLogo = array('name'=>'爱客开源', 'url'=>$this->ik_setting->ik_site_url, 'style'=>'site_logo');
			}
			return $arrLogo;
		}
	}

    /**
     * 操作提示跳转的快捷方法
     * @access protected
     * @param string $message 提示信息
     * @param string $jumpUrl 页面跳转地址
     * @return void
     */
	public function noticeAction() {
		$jumpurl = $this->dispatcher->getParam('jumpurl'); 
		$this->view->setViewsDir ( APP_PATH . 'home/views/' );
		$this->view->pick ( 'public/notice' );
		
		if(empty($jumpurl)){
			$this->assign('jumpurl', 'javascript:history.back(-1);');
		}else{
			//$this->assign('jumpurl', $this->url->get($jumpurl));
			$this->assign('jumpurl', $this->ik_setting->ik_site_url.$jumpurl);//全域名地址
		}
	}
	//全站前端404信息提示页面
	public function show404Action() {
		$this->view->setViewsDir ( APP_PATH . 'home/views/' );
		$this->view->pick ( 'public/404' );
	}	
}