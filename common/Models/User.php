<?php
/*
 * IKPHP爱客网 安装程序 @copyright (c) 2012-3000 IKPHP All Rights Reserved @author 小麦
* @Email:810578553@qq.com
* @小麦 修改时间2014年3月19日晚 用户基础类
*/
namespace IKPHP\Common\Models;
use IKPHP\Api\UserApi;
use \Phalcon\Mvc\Model\Message as Message;
use IKPHP\Common\Models\Setting;

class User extends BaseModel
{
	public $userid;
	public $username;
	
    public function getSource()
    {
        return IK."user";
    }
    /**
     * 登录指定用户
     * @param  integer $uid 用户ID
     * @return boolean      ture-登录成功，false-登录失败
     */
    public function login($uid){
        /* 检测是否在当前应用注册 */
    	;
        $user = $this->findFirst("userid='$uid'");
        if(!$user){ //未注册
            /* 在当前应用中注册用户 */
        	$Api = new UserApi();
        	$info = $Api->info($uid); 
        	if($info){
        		$user = new User();
        	    $user->userid = $uid;
        	    $user->username = $info[1];
        	    $user->email = $info[2];
        	    $user->reg_ip = $this->getDI()->getRequest()->getClientAddress();
        	    $user->reg_time = time();
        	    $user->status = 0;
        	    //注册成功
	            if(!$user->create()){
	            	$this->appendMessage(new Message("前台用户信息注册失败，请重试！"));
	                return false;
	            }        		
        	}else{
        		 $this->appendMessage(new Message("用户未激活或已禁用！")); //应用级别禁用
        		 return false;
        	}
        } elseif(0 != $user->status) {
            $this->appendMessage(new Message("用户未激活或已禁用！")); //应用级别禁用
            return false;
        }

        /* 登录用户 */
        $this->autoLogin($user);

        //记录行为
       // action_log('user_login', 'member', $uid, $uid);

        return true;
    }
    /**
     * 自动登录用户
     * @param  integer $user 用户信息数组
     */
    private function autoLogin($user){
        /* 更新登录信息 */
    	$resuser = $this->findFirst($user->userid);
        if($resuser){
        	
        	$resuser->count_login = $resuser->count_login+1;
        	$resuser->last_login_time = time();
        	$resuser->last_login_ip = $this->getDI()->getRequest()->getClientAddress();
        	$resuser->save();
        	
        	$doname = empty($resuser->doname) ? $resuser->userid : $resuser->doname;
        	
        	/* 记录登录SESSION和COOKIES */
	        $auth = array(
	            'userid'             => $resuser->userid,
	            'username'        => $resuser->username,
	        	'email'        => $resuser->email,
				'doname'        => $doname,        
	            'last_login_time' => $resuser->last_login_time,
	        );
	
	        $this->getDI()->get('session')->set('user_auth', $auth);
	        $this->getDI()->get('session')->set('user_auth_sign', data_auth_sign($auth));
	        
        	return true;
        	
        }else{
        	$this->appendMessage(new Message("自动登录用户失败！"));
        	return false;
        }
        
        return true;
    }
    /**
     * 注销当前用户
     * @return void
     */
    public function logout(){
        $this->getDI()->get('session')->remove('user_auth');
        $this->getDI()->get('session')->remove('user_auth_sign');
        return true;
    }
    /**
     * 获取字段信息
     * @param  integer $uid 用户ID
     * @param  array   $field 要获取的字段
     * @return result
     */ 
    public function getInfo($id, $field = array('*')){ 
    	$info = $this->findFirst(array('columns'=>$field, 'conditions'=>'userid = '.$id));
    	if(!$info){
    		return false;
    	}
    	return $info;
    }
    
    //获取一个用户的信息
	public function getOneUser($userid){
		return C('ik_attach_path');
	}
}
