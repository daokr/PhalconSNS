<?php
/*
 * IKPHP 爱客开源社区 @copyright (c) 2012-3000 IKPHP All Rights Reserved 
 * @author 小麦
 * @Email:810578553@qq.com
 */

namespace IKPHP\Apps\Home\Controllers;

use IKPHP\Org\Verify as Verify;
use IKPHP\Common\Controllers\FrontendController;
use IKPHP\Api\UserApi;
use IKPHP\Common\Models\Area;
use IKPHP\Common\Models\User as UserMod;
use IKPHP\Org\UploadFile;
use IKPHP\Apps\Home\Models\UserLevel;
use IKPHP\Apps\Home\Models\UserScoreLog;

class UserController extends FrontendController {
	
	private $userid = 0;

    public function initialize()
    {
        parent::initialize();
        $this->assign('user_menu_list',$this->_init_setmenu());
    	// 访问者控制
		if ($this->visitor){
			$this->userid = $this->visitor['userid'];
		}
    }	
    //个人设置初始化
    protected function _init_setmenu(){
    	$menu = array ();
    	$menu = array(
    				'setbase' => array('text'=>'基本信息', 'url'=>'home/user/setbase'),
    				'setface' => array('text'=>'会员头像', 'url'=>'home/user/setface'),
    				'setdoname' => array('text'=>'个性域名', 'url'=>'home/user/setdoname'),
    				'setcity'   => array('text'=>'常居地', 'url'=>'home/user/setcity'),
    				'setpassword' => array('text'=>'修改密码', 'url'=>'home/user/setpassword'),
    				//'bind' => array('text'=>'第三方绑定', 'url'=>U('user/bind')),
    			);
		return $menu;
    }
	public function captchaAction() {
		$verify = new Verify ();
		$verify->entry ( 1 );
		$this->view->disable ();
	}
	public function registerAction() {
		//会员已经登录直接跳转
		if($this->visitor){
			return $this->_redirect ( 'space/'.$this->visitor['doname']);
		}
		if (IS_POST) {
			
			$email    = $this->_post( 'email' , 'email'); 
			$password = $this->_post( 'password' );
			$repassword = $this->_post( 'repassword' );
			$username = $this->request->getPost( 'username','trim');
			$username = str_replace(' ','',$username);
			$authcode = $this->_post( 'authcode' );
			$simple   = $this->_get( 'simple','trim');

			/* 验证邮箱 */
			if( !preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',$email) ){
				return $this->error('邮箱格式不正确！');
			}
			/* 检测密码 */
            //简单注册没有二次密码
			if(empty($simple)){
				if ($password != $repassword) { 
					return $this->error('密码和重复密码不一致！');
				}
			}
			
			if(empty($email) || empty($username) || empty($password)){ 
				return $this->error('Email、用户名、密码不能为空！');
			}

			/* 检测验证码 */
			$verify = new Verify ();
			if (!$verify->check ( $authcode, 1 )) { 
				return $this->error('验证码错误');
			}
			
			/* 调用注册接口注册用户 */
            $User = new UserApi();
			$arr_user = $User->register($username, $password, $email);

			if(0 < $arr_user['uid']){ //注册成功
				//TODO: 发送验证邮件
				return $this->success('注册成功！','home/user/login');
			} else { //注册失败，显示错误信息
				return $this->error($arr_user['msg']);
			}			
			
		}

        $this->_config_seo ( array (
			'title' => '会员注册'
		) );
	}
	/**
	 * 检测用户
	 */
	public function checkuserAction($type = '') {
		$type = (string)$type;
		$user_mod = new UserApi();	 
		switch ($type) {
			case 'email' :
				$email = $this->_get ( 'email', 'email' ); 
				echo $user_mod->email_exists ( $email ) ? 'false' : 'true';
				break;
			
			case 'username' :
				$username = $this->_get ( 'username', 'trim' );
				echo $user_mod->username_exists ( $username ) ? 'false' : 'true';
				break;
		}
		$this->view->disable();
	}
	/**
	 * 会员登录
	 */	
	public function loginAction(){
		//会员已经登录直接跳转
		if($this->visitor){
			return $this->_redirect ( 'space/'.$this->visitor['doname']);
		}	
		if (IS_POST) {
			$email    = $this->_post( 'email', 'email'); 
			$password = $this->_post( 'password', 'trim');
			
			/* 验证邮箱 */
			if(empty($email) || empty($password)){ 
				return $this->error('Email、密码不能为空！');
			}			
			if( !preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',$email) ){
				return $this->error('邮箱格式不正确！');
			}			
			/* 调用UC登录接口登录 */
			$user = new UserApi;
			$uid = $user->login($email, $password);
			
			if(0 < $uid){ //UC登录成功
				/* 登录用户 */
				$Member = new UserMod();
				if($Member->login($uid)){ //登录用户
					// 跳转到登陆前页面（执行同步操作）
					$ret_url = $this->_post ( 'ret_url', 'trim');
					if(empty($ret_url)){
						return $this->_redirect('home/index/index');
					}
					header ( "Location: " .$ret_url);
					exit();
				} else {
					$msg = $Member->getMessages();
					$this->error($msg[0]);
				}

			} else { //登录失败
				switch($uid) {
					case -1: $error = '用户不存在或被禁用！'; break; //系统级别禁用
					case -2: $error = '密码错误！'; break;
					case -3: $error = '更新用户登录信息失败！'; break;
					default: $error = '未知错误！'; break; // 0-接口参数错误（调试阶段使用）
				}
				$this->error($error);
			}
			
		}

		// 来路
		if(isset ( $_SERVER ['HTTP_REFERER'] ) && strpos($_SERVER ['HTTP_REFERER'], 'register') == false && strpos($_SERVER ['HTTP_REFERER'], 'logout') == false)
		{
			$ret_url = $_SERVER ['HTTP_REFERER'];
		}else{
			$ret_url = $this->ik_setting->ik_site_url;
		}
		
		$this->assign ( 'ret_url', $ret_url );
		
		$this->_config_seo ( array (
			'title' => '会员注册'
		) );
	}
	/**
	 * 用户退出
	 */
	public function logoutAction() {
		if($this->visitor){
			$Member = new UserMod();
			$Member->logout();
			$this->success('退出成功！', 'home/user/login');
		} else {
			$this->_redirect('home/user/login');
		}
	}	
	public function setbaseAction() {
		//访问控制
		if(!$this->visitor){return $this->_redirect('home/user/login');}
		
		if (IS_POST) {
			
			$data ['sex'] = $this->_post('sex', 'int');
			$data ['signed'] = $this->_post('signed', 'string');
			$data ['address'] = $this->_post('address', 'string');
			$data ['phone'] = $this->_post('phone', 'int');
			$data ['blog'] = $this->_post('blog', 'string');
			$data ['about'] = $this->_post('about', 'string');

			if(!empty($data['blog']) && !regex($data['blog'], 'url')){
				return $this->error('Blog地址不是有效的URL地址：必须以 http:// 开头');
			}
			
			if (false !== UserMod::findFirst($this->userid)->save ( $data )) {		
				$this->success ('修改基本信息成功');
			} else {
				$this->error ( '修改基本信息失败' );
			}
		} else {
			$member = new UserMod();
			$info = $member->getInfo($this->userid); 
			$area_mod = new Area();
			$strarea = $area_mod->getArea($info->areaid);
			$this->assign ( 'info', $info );
			$this->assign ( 'strarea', $strarea );
			$this->_config_seo (array('title'=>'基本设置','subtitle'=>'用户'));
		}
	
	}
	//设置头像
	public function setfaceAction(){
		//访问控制
		if(!$this->visitor){return $this->_redirect('home/user/login');}
		if (IS_POST) {
			if($this->request->hasFiles() == true){
				//类型判断
				$up_files = $this->request->getUploadedFiles();
				$up_file = $up_files[0];
				$up_file_type = $up_file->getType();

				if($up_file_type !== 'image/png' && $up_file_type !== 'image/jpeg'){
					return $this->error("图像类型不符；只允许上传jpeg,jpg,png类型图片");
				}
				//大小判断
				if($up_file->getSize()/1024 > 1024)
				{
					return $this->error("头像不允许大于1024KB");
				}
				//会员头像保存文件夹
	            $uid = abs(intval($this->userid));
	            $suid = sprintf("%09d", $uid);
	            $dir1 = substr($suid, 0, 3);
	            $dir2 = substr($suid, 3, 2);
	            $dir3 = substr($suid, 5, 2);
	            $avatar_dir = 'face/'.$dir1.'/'.$dir2.'/'.$dir3.'/';
			            
				$upload = new UploadFile(array('autoSub'=>false,'savePath'=>$avatar_dir,'saveName'=> md5($this->userid)));
				$result = $upload->upload();
				if($result){
					
					$imgpath = C('ik_attach_path').$result[0]['savefile'];
					$image = new \IKPHP\Org\Image();
					//打开图像生成thumb
					try {
						//获取配置头像大小
						$arr_face_size = explode(',', C('ik_avatar_size'));	            
			            //打开图像
			            foreach ($arr_face_size as $item){
			            	$image->open($imgpath);
			            	$thumb_name = $result[0]['filename'].'_'.$item.'_'.$item.'.'.$result[0]['ext']; //文件名
			            	$thumb_img = C('ik_attach_path').$result[0]['savepath'].$thumb_name;//保存路径
			            	$image->thumb($item, $item, \IKPHP\Org\Image::IMAGE_THUMB_FIXED)->save($thumb_img);
			            }
			            //更新数据库
			            $user = UserMod::findFirst("userid='$this->userid'");
			            $user->path = $avatar_dir;
			            $user->face = $result[0]['savename'];
			            
			            if($user->save()){
			            	return $this->success('头像修改成功！');
			            }
			            
					} catch(\Exception $e) {
						return $this->error($e->getMessage());
					}

				}else{
					$this->error($upload->getError());
				}
			}else{
				$this->error('没有可上传的头像文件！');
			}
		}else{
			$member = new UserMod();
			$info = $member->getInfo($this->userid, array('face')); 
			$this->assign ( 'info', $info );
			$this->_config_seo (array('title'=>'会员头像','subtitle'=>'用户'));		
		}
	}
	//设置域名
	public function setdonameAction(){
		//访问控制
		if(!$this->visitor){return $this->_redirect('home/user/login');}
				
		$userid = $this->userid;
		$user_mod = new UserApi;
		$strUser = $user_mod->info($userid);
		if(IS_POST){
			$doname = $this->_post('doname',array('trim'));

			if(empty($doname))
			{
				$this->error ("个性域名不能为空！");
				
			}else if(strlen($doname)<4)
			{
				$this->error ("个性域名至少要2位数字、字母、或下划线(_)组成！");
			
			}else if(!preg_match ( '/^[a-zA-Z]{1}[a-zA-Z0-9\-_]{0,14}$/', $doname ))
			{
				$this->error ("首字符必须是英文字母, 域名必须是数字、字母或下划线(_)组成！");
			}
			
			$ishave = $user_mod->doname_exists($doname, $this->userid);
			if($ishave)
			{
				$this->error ("该域名已经被其他人抢注了,请试试别的吧！");
				
			}else{
				$result = $user_mod->updateInfo($userid, array('doname'=>$doname));
				if($result['status']){
					UserMod::findFirst("userid='$userid'")->save(array('doname'=>$doname));
					$this->error ("个性域名修改成功！");
				}else{
					$this->error ($result['info']);
				}				
			}
		
		}else{
			$this->assign ( 'doname', $strUser[4] );
			$this->_config_seo (array('title'=>'个性域名','subtitle'=>'用户'));
		}			
	}
	public function areaAction($type) {
		$oneid = $this->_get ( 'oneid' ); 
		$area_mod = new Area();
		switch ($type) {
			case 'two' :
				$arrArea = $area_mod->getReferArea ( $oneid );
				if ($arrArea) {
					echo '<select id="twoid" name="twoid" class="txt">';
					echo '<option value="0">请选择</option>';
					foreach ( $arrArea as $item ) {
						echo '<option value="' . $item ['areaid'] . '">' . $item ['areaname'] . '</option>';
					}
					echo "</select>";
				} else {
					echo '';
				}
				break;
			
			case 'three' :
				$twoid = $this->_get ( 'twoid' );
				$arrArea = $area_mod->getReferArea ( $twoid );
				if ($arrArea) {
					echo '<select id="threeid" name="threeid" class="txt">';
					echo '<option value="0">请选择</option>';
					foreach ( $arrArea as $item ) {
						echo '<option value="' . $item ['areaid'] . '">' . $item ['areaname'] . '</option>';
					}
					echo "</select>";
				} else {
					echo '';
				}
				break;
		}
		$this->view->disable();
	}	
	//设置居住地
	public function setcityAction(){
		
		if(!$this->visitor){return $this->_redirect('home/user/login');}
		
		$user_mod = new UserMod();
		if (IS_POST) {
			
			$oneid   = $this->_post('oneid','int','0');
			$twoid   = $this->_post('twoid','int','0');
			$threeid = $this->_post('threeid','int','0');
			
			if ($oneid != 0 && $twoid == 0 && $threeid == 0) {
				$areaid = $oneid;
			} elseif ($oneid != 0 && $twoid != 0 && $threeid == 0) {
				$areaid = $twoid;
			} elseif ($oneid != 0 && $twoid != 0 && $threeid != 0) {
				$areaid = $threeid;
			} else {
				$areaid = 0;
			}
			if (false !== $user_mod->findFirst("userid = '$this->userid'")->save ( array ('areaid' => $areaid ) )) {
				$this->success ( '常居地设置成功' );
			} else {
				$this->error ( '常居地设置失败' );
			}
		
		} else {
			
			$result = $user_mod->getInfo($this->userid, array('areaid'));
			
			$area_mod = new Area();
			
			$strarea = $area_mod->getArea ( $result->areaid );

			// 调出省份数据
			$arrOne = $area_mod->getReferArea ( 0 );
			
			$this->assign ( 'strarea', $strarea );
			$this->assign ( 'arrOne', $arrOne );
			$this->_config_seo (array('title'=>'常居地修改','subtitle'=>'用户'));
		}
			
	}
	//设置居住地
	public function setpasswordAction(){
		
		if(!$this->visitor){return $this->_redirect('home/user/login');}
	
		$userid = $this->userid;
		if(empty($userid)){
			return $this->error( '你应该出发去火星报到啦。','home/user/login');
		}
		if (IS_POST) {
			//获取参数
            $uid        =   $userid;
            $oldpassword   =   $this->_post('old','trim');
            $repassword =   $this->_post('repassword','trim');
            $newpassword = $this->_post('password','trim');

            if(empty($oldpassword) || empty($newpassword) || empty($repassword)){
            	return $this->error('旧密码，新密码，确认密码，都必须输入');
            }
            if($newpassword != $repassword){
                return $this->error('您输入的新密码与确认密码不一致');
            }
            
            $Api = new UserApi();
            
		    $res = $Api->updatePassword($uid, $oldpassword, $newpassword);
            if($res['status']){
                return $this->success('修改密码成功！');
            }else{
                return $this->error($res['info']);
            }
                        
		}else{
			//下期开发第三方登录
/*			$count_user_bind = M('user_bind')->where(array('uid'=>$userid))->count('*');
			if($count_user_bind>0 &&  md5('000000') == $strUser['password']){
				$ispassword = false;
			}else{
				$ispassword = true;
			}*/
			$this->assign('ispassword',true);
			$this->_config_seo (array('title'=>'密码修改','subtitle'=>'用户'));
		}
	}	

	//找回密码服务
	public function forgetpwdAction(){
		if(IS_POST){
			$user_mod = new UserMod();
			
			$email	= $this->_post('email',array('trim','email'));
			$emailNum = $user_mod->findFirst("email='$email'");

			if($email==''){
				return $this->error('Email输入不能为空^_^');
			}elseif($emailNum == false){
				return $this->error('Email不存在，你可能还没有注册^_^');
			}else{
			
				//随机MD5加密
				$resetpwd = md5(rand()); 
			
				$user_mod->resetpwd = $resetpwd;
				
				$subject  = C('ik_site_title').'会员密码找回';
				
				$reseturl = C('ik_site_url').'home/user/resetpwd?mail='.$email.'&set='.$resetpwd;
				$content = '您的登录信息：<br />Email：'.$email.'<br />重设密码链接：<br /><a href="'.$reseturl.'">'.$reseturl.'</a>';

				$mailObject = new \IKPHP\Org\Mail($this->ik_setting);
				
				$result = $mailObject->postMail($email, $subject, $content);
				if($result == '0'){
					$this->error("找回密码所需信息不完整^_^");
				}elseif($result == '1'){
					//更新数据库
					$user_mod->save();
					$this->error("系统已经向你的邮箱发送了邮件，请尽快查收^_^");
				}
					
			}
		}
		$this->_config_seo ( array (
				'title' => '找回密码'
		) );
	}
	// 用户等级
	public function levelAction(){
		$arrRole = UserLevel::find();
		$this->assign('arrRole',$arrRole);

		$this->_config_seo ( array (
				'title' => '会员等级'
		) );	
	}
	// 用户积分
	public function scoreAction($id = 0){

		if(!empty($id) && $id>0){
			
			$strUser = UserMod::findFirst("userid='$id'");
			if($strUser){

				$map = "uid='$id'";
				$count = UserScoreLog::count("uid='$id'");
				$pager = $this->_pager($count, 10);
				$where = array(
							"uid='$id'",
				 			"order" => "add_time", 
				 			"limit" => array("number"=>$pager->listRows, 'offset'=>$pager->firstRow)
				);
				$list  = UserScoreLog::find($where);
				//var_dump($list);
				$this->assign('list',$list);
				$this->assign('pageUrl', $pager->show());
				
				$this->_config_seo ( array (
						'title' => $strUser->username.'的积分'
				) );
			
			}else{
				$this->error('系统不存在的用户');
			}			
		}else{
			return $this->error('无法查看该用户的积分');
		}
	}	
}