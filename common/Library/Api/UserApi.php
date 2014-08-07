<?php
// +----------------------------------------------------------------------
// | IKPHP.COM [ I can do all the things that you can imagine ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2050 http://www.ikphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 小麦 <ikphp@sina.cn> <http://www.ikphp.com>
// +----------------------------------------------------------------------

namespace IKPHP\Api;
use Phalcon\Mvc\User\Component;
use IKPHP\Common\Models\UcenterUser;

class UserApi extends Component{
	/**
	 * API调用模型实例
	 * @access  protected
	 * @var object
	 */
	protected $model;
	
	
    /**
     * 构造方法，实例化操作模型
     */
    public function __construct(){
        $this->model = new UcenterUser();
    }
    	
    /**
     * 注册一个新用户
     * @param  string $username 用户名
     * @param  string $password 用户密码
     * @param  string $email    用户邮箱
     * @param  string $mobile   用户手机号码
     * @return integer          注册成功-用户信息，注册失败-错误编号
     */
    public function register($username, $password, $email, $mobile = ''){
        return $this->model->register($username, $password, $email, $mobile);
    }
    
    /**
     * 用户登录认证
     * @param  string  $username 用户名
     * @param  string  $password 用户密码
     * @param  integer $type     用户名类型 （1-用户名，2-邮箱，3-手机，4-UID）
     * @return integer           登录成功-用户ID，登录失败-错误编号
     */
    public function login($username, $password, $type = 2){
        return $this->model->login($username, $password, $type);
    }

    /**
     * 获取用户信息
     * @param  string  $uid         用户ID或用户个性域名
     * @param  boolean $is_username 是否使用户个性域名
     * @return array                用户信息
     */
    public function info($uid, $is_doname = false){
        return $this->model->info($uid, $is_doname);
    }

	/**
	 * 验证email是否存在
	 * @param int $id 用户id
	 * @param string $email 邮箱
	 * @return true 验证成功，false 验证失败
	 * @author 小麦 <810578553@qq.com>
	 */	
	public function email_exists($email, $id = 0) {
		return $this->model->field_exists('email',$email,$id);
	}
	/**
	 * 验证username是否存在
	 * @param int $id 用户id
	 * @param string $name 用户名
	 * @return true 验证成功，false 验证失败
	 * @author 小麦 <810578553@qq.com>
	 */		
	public function username_exists($name, $id = 0) {
		return $this->model->field_exists('username',$name,$id);
	}
	/**
	 * 验证域名是否存在
	 * @param int $id 用户id
	 * @param string $doname 个性域名
	 * @return true 验证成功，false 验证失败
	 * @author 小麦 <810578553@qq.com>
	 */		
	public function doname_exists($doname, $id = 0)
	{
		return $this->model->field_exists('doname',$doname,$id);
	}
    /**
     * 更新用户信息
     * @param int $uid 用户id
     * @param string $password 密码，用来验证
     * @param array $data 修改的字段数组
     * @return true 修改成功，false 修改失败
     * @author 小麦 <810578553@qq.com>
     */
    public function updateInfo($uid, $data){
        if($this->model->updateUserFields($uid, $data) !== false){
            $return['status'] = true;
        }else{
            $return['status'] = false;
            $return['info'] = $this->model->getError();
        }
        return $return;
    }	
    /**
     * 更新用户密码
     * @param int $uid 用户id
     * @param string $password 密码，用来验证
     * @param array $newpwd 修改的字段数组
     * @return true 修改成功，false 修改失败
     * @author 小麦 <810578553@qq.com>
     */
    public function updatePassword($uid, $password, $newpwd){
        if($this->model->updatePassWord($uid, $password, $newpwd) !== false){
            $return['status'] = true;
        }else{
            $return['status'] = false;
            $return['info'] = $this->model->getError();
        }
        return $return;
    }
}
