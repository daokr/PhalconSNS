<?php
// +----------------------------------------------------------------------
// | IKPHP.COM [ I can do all the things that you can imagine ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2050 http://www.ikphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 小麦 <ikphp@sina.cn> <http://www.ikphp.com> 2014年7月29日14:47:45
// +----------------------------------------------------------------------
namespace IKPHP\Common\Models;

use Phalcon\Mvc\Model\Validator\ExclusionIn,
	Phalcon\Mvc\Model\Validator\StringLength,
    Phalcon\Mvc\Model\Validator\Uniqueness,
    IKPHP\Common\Models\Validator\Email;
    
class UcenterUser extends BaseModel {
	private $error = null;
	/* 用户模型自动验证 */
    public function validation()
    {

    	//用户名验证
        $this->validate(new ExclusionIn(
            array(
                'field'  => 'username',
                'domain' => array('admin', 'ikphp'),
            	'message' => '用户名已被系统使用',
            	'code'	 => -1
            )
        ));

        $this->validate(new Uniqueness(
            array(
                'field'   => 'username',
                'message' => '用户名已被占用'
            )
        ));
        
	    $this->validate(new StringLength(array(
	            'field' => 'username',
	            'max' => 16,
	            'min' => 3,
	            'messageMaximum' => '用户名长度必须在16个字符以内',
	            'messageMinimum' => '用户名长度不少于少3个字符'
	    )));
	    
	    //密码验证
	    $this->validate(new StringLength(array(
	            'field' => 'password',
	            'max' => 32,
	            'min' => 6,
	            'messageMaximum' => '密码长度必须在30个字符以内',
	            'messageMinimum' => '密码长度不少于少6个字符'
	    ))); 

	    //邮箱验证
	    $this->validate(new Uniqueness(
            array(
                'field'   => 'email',
                'message' => '邮箱已被占用'
            )
        ));
 	    $this->validate(new Email(
            array(
                'field'   => 'email',
            )
        ));       
		return $this->validationHasFailed() != true;
    } 	
	//关系映射
    public function initialize()
    {
    	$this->setSource(IK.'ucenter_user');
        $this->hasMany('id', 'User', 'userid');
    }
    /* 用户模型自动完成 */	
    public function beforeCreate()
    {
    	$this->password = $this->ik_ucenter_md5($this->password, AUTHKEY);
        $this->reg_time = time();
        $this->reg_ip	= $this->getDI()->getRequest()->getClientAddress();
        $this->status   = 0; //是否启用：0启用1禁用
    }
    public function beforeUpdate()
    {	
        $this->update_time = time();
    }        

	/**
	 * 验证字段是否存在是否存在
	 * @param int $id 用户id
	 * @param string $fieldname 字段
	 * @return true 验证成功，false 验证失败
	 * @author 小麦 <810578553@qq.com>
	 */		
	public function field_exists($fieldname, $fieldvalue, $id = 0)
	{
        $params = array(
               $fieldname.'= ?0 AND id <> ?1',
               'bind' => array($fieldvalue, $id)
         );
		$result = $this->count($params);
		if ($result > 0) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * 注册一个新用户
	 * @param  string $username 用户名
	 * @param  string $password 用户密码
	 * @param  string $email    用户邮箱
	 * @param  string $mobile   用户手机号码
	 * @return integer          注册成功-用户信息，注册失败-错误编号
	 */
	public function register($username, $password, $email, $mobile ) {
		$data = array ('username' => $username, 'password' => $password, 'email' => $email, 'mobile' => $mobile );
		
		//验证手机
		if (empty ( $data ['mobile'] ))
			unset ( $data ['mobile'] );
			
		/* 添加用户 */
		if ($this->create ( $data )) {
			$uid = $this->id;
			return $uid ? array('uid'=>$uid) : array('uid'=>0); //0-未知错误，大于0-注册成功
		} else {
			$msg = $this->getMessages(); 
			return array('uid'=>-1, 'msg'=>$msg); //错误详情见自动验证注释
		}
	}
	/**
	 * 系统非常规MD5加密方法
	 * @param  string $str 要加密的字符串
	 * @return string 
	 */
	function ik_ucenter_md5($str, $key = 'ik_ucenter'){
		return md5($str);
		//return '' === $str ? '' : md5(sha1($str) . $key);
	}

	/**
	 * 用户登录认证
	 * @param  string  $username 用户名
	 * @param  string  $password 用户密码
	 * @param  integer $type     用户名类型 （1-用户名，2-邮箱，3-手机，4-UID）
	 * @return integer           登录成功-用户ID，登录失败-错误编号
	 */
	public function login($username, $password, $type = 2){ 
		$map = '';
		switch ($type) {
			case 1:
				$map = "username = '".$username."'";
				break;
			case 2:
				$map = "email = '".$username."'";
				break;
			case 3:
				$map = "mobile = '".$username."'";
				break;
			case 4:
				$map = "id = '".$username."'";
				break;
			default:
				return 0; //参数错误
		}
		/* 获取用户数据 */
		
		$userObj = $this->findFirst($map);
		if($userObj){
			$user = $userObj->toArray();
			if(is_array($user) && !$user['status']){
				//验证用户密码 
				if($this->ik_ucenter_md5($password, AUTHKEY) === $user['password']){
					//更新用户登录信息
					if($this->updateLogin($user['id'])){
						return $user['id']; //登录成功，返回用户ID
					}else{
						return -3; //更新数据失败
					}
				} else {
					return -2; //密码错误
				}
			}
		}else{
			return -1; //用户不存在或被禁用
		}
	}
	/**
	 * 更新用户登录信息
	 * @param  integer $uid 用户ID
	 */
	protected function updateLogin($uid){
		$data = array(
			'last_login_time' => time(),
			'last_login_ip'   => $this->getDI()->getRequest()->getClientAddress(),
		);
		$user = $this->findFirst($uid);
		if($user){
			$user->last_login_time = time();
			$user->last_login_ip = $this->getDI()->getRequest()->getClientAddress();
			if($user->save()){
				return true;
			}
		}
		return false;
	}
	/**
	 * 获取用户信息
	 * @param  string  $uid         用户ID或个性域名
	 * @param  boolean $is_doname   是否使用个性域名查询
	 * @return array                用户信息
	 */
	public function info($uid, $is_doname = false){
		$map = array();
		if($is_doname){ //通过用户名获取
			$map = "doname = '".$uid."'";
		} else {
			$map = "id = '".$uid."'";
		}

		$user = $this->findFirst(array('columns'=>'id,username,email,mobile,status,doname', 'conditions'=>$map))->toArray();

		if(is_array($user) && $user['status'] == 0){
			return array($user['id'], $user['username'], $user['email'], $user['mobile'], $user['doname']);
		} else {
			return false; //用户不存在或被禁用
		}
	}
	/**
	 * 更新用户信息
	 * @param int $uid 用户id
	 * @param array $data 修改的字段数组
	 * @return true 修改成功，false 修改失败
	 * @author 小麦 
	 */
	public function updateUserFields($uid, $data){
		if(empty($uid) || empty($data)){
			$this->error = '参数错误！';
			return false;
		}
		//更新用户信息
		$user = self::findFirst("id='$uid'");
		if($user->save($data))
		{
			return true;
		}
		return false;
	}
	public function getError(){
		if($this->error){
			return $this->error;
		}
		return false;
	}
	/**
	 * 更新用户密码
	 * @param int $uid 用户id
	 * @param string $password 密码，用来验证
	 * @param array $newpwd 新密码
	 * @return true 修改成功，false 修改失败
	 * @author 小麦 <810578553@qq.com>
	 */
	public function updatePassWord($uid, $password, $newpwd){
		if(empty($uid) || empty($password) || empty($newpwd)){
			$this->error = '参数错误！';
			return false;
		}
	
		//更新前检查用户密码
		$res = self::findFirst($uid);
		if($res){
			if($res->password == $this->ik_ucenter_md5($password, AUTHKEY) ){
				 $newpwd = $this->ik_ucenter_md5($newpwd, AUTHKEY);
				 if($res->save(array('password'=>$newpwd))){
				 	return true;
				 }
				 $this->error = '更新密码失败！';
				 return false;
			}else{
				$this->error = '验证出错：密码不正确！';
				return false;
			}
		}else{
			$this->error = '该用户不存在，你无权修改！';
			return false;
		}

		return false;
	}				
}
