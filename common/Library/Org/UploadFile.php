<?php
// +----------------------------------------------------------------------
// | IKPHP.COM [ I can do all the things that you can imagine ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2050 http://www.ikphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 小麦 <ikphp@sina.cn> <http://www.ikphp.com>
// +----------------------------------------------------------------------


namespace IKPHP\Org;
use Phalcon\Mvc\User\Component;

class UploadFile extends Component{
    /**
     * 默认上传配置
     * @var array
     */
    private $config = array(
        'mimes'         =>  array(), //允许上传的文件MiMe类型
        'maxSize'       =>  0, //上传的文件大小限制 (0-不做限制)
        'exts'          =>  array(), //允许上传的文件后缀
        'autoSub'       =>  true, //自动子目录保存文件
        'subName'       =>  array('date', 'Y/md'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath'      =>  './uploads/', //保存根路径
        'savePath'      =>  '', //保存路径 末尾必须加 /
        'saveName'      =>  array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'       =>  '', //文件保存后缀，空则使用原后缀
        'replace'       =>  false, //存在同名是否覆盖
        'hash'          =>  true, //是否生成hash编码
        'callback'      =>  false, //检测文件是否存在回调，如果存在返回文件信息数组
        'driver'        =>  '', // 文件上传驱动
        'driverConfig'  =>  array(), // 上传驱动配置
    	'replace'		=> true, //是否覆盖
    );
    
    /**
     * 上传错误信息
     * @var string
     */
    private $error = ''; //上传错误信息
    
    private $request = '';
            
	// 架构方法 设置参数
    public function __construct($config=array()){
    	$this->request = $this->getDI()->get('request');
         /* 获取配置 */
        $this->config   =   array_merge($this->config, $config); 

        /* 调整配置，把字符串配置参数转换为数组 */
        if(!empty($this->config['mimes'])){
            if(is_string($this->mimes)) {
                $this->config['mimes'] = explode(',', $this->mimes);
            }
            $this->config['mimes'] = array_map('strtolower', $this->mimes);
        }
        if(!empty($this->config['exts'])){
            if (is_string($this->exts)){
                $this->config['exts'] = explode(',', $this->exts);
            }
            $this->config['exts'] = array_map('strtolower', $this->exts);
        }
       	
    }
    /**
     * 使用 $this->name 获取配置
     * @param  string $name 配置名称
     * @return multitype    配置值
     */
    public function __get($name) {
        return $this->config[$name];
    }

    public function __set($name,$value){
        if(isset($this->config[$name])) {
            $this->config[$name] = $value;
        }
    }    
    /**
     * 获取最后一次上传错误信息
     * @return string 错误信息
     */
    public function getError(){
        return $this->error;
    }
    /**
     * array (size=9)
     * 'size' => float 381.03515625
     * 'name' => string 'test.jpg' (length=8)
     * 'ext' => string 'jpg' (length=3)
     * 'tmp_name' => string 'D:\wamp\tmp\php7EA4.tmp' (length=23)
     * 'md5' => string '2fcdd5beaedbcaa49535099e7cced5da' (length=32)
     * 'sha1' => string '48dc94d82046c6ee3a812b0efcfcf9c65096377c' (length=40)
     * 'savename' => string 'c81e728d9d4c2f636f067f89cc14862c.jpg' (length=36)
     * 'savepath' => string 'face/000/00/00/' (length=15)
     * 'savefile' => string 'face/000/00/00/c81e728d9d4c2f636f067f89cc14862c.jpg' (length=51)
     * */    
    public function upload(){
        if($this->request->hasFiles() == false){
            $this->error = '没有上传的文件！';
            return false;
        }    	
		$files = $this->request->getUploadedFiles ();

		foreach ($files as $key => $file) {
			$arrfile['size']  = $file->getSize () / 1024; //KB
			$arrfile['name']   = $file->getName(); //文件名
            $arrfile['ext']   =   pathinfo($arrfile['name'], PATHINFO_EXTENSION);//后缀
            $arrfile['tmp_name'] = $file->getTempName(); 
            $arrfile['md5']  = md5_file($file->getTempName());
            $arrfile['sha1'] = sha1_file($file->getTempName()); 
			
            //检查
            if ($arrfile['size'] > C ( 'ik_attr_allow_size' )) {
				$this->error = '该文件'.$arrfile['size'].'KB,已超过指定大小'.C ( 'ik_attr_allow_size' ).'KB';
				continue;
			}
			if (! in_array ( $arrfile['ext'], explode ( ',', C ( 'ik_attr_allow_exts' ) ) )) {
				$this->error = '不是合法的文件类型！';
				continue;
			}
			
		    /* 生成保存文件名 */
            $savename = $this->getSaveName($arrfile);
            if(false == $savename){
                continue;
            } else {
                $arrfile['filename'] = $this->getName($this->saveName, $arrfile['name']);
                $arrfile['savename'] = $savename;
            }
			
		    /* 检测并创建子目录 */
            $subpath = $this->getSubPath($arrfile['name']); 
            if(empty($subpath)){
            	$arrfile['savepath'] = $this->mkdir($this->savePath) ? $this->savePath : '';
            } else {
                $arrfile['savepath'] = $this->savePath . $subpath;
            }     
		         
		    /* 对图像文件进行严格检测 */
            $ext = strtolower($arrfile['ext']);
            if(in_array($ext, array('gif','jpg','jpeg','bmp','png','swf'))) {
                $imginfo = getimagesize($arrfile['tmp_name']);
                if(empty($imginfo) || ($ext == 'gif' && empty($imginfo['bits']))){
                    $this->error = '非法图像文件！';
                    continue;
                }
            } 
			
		    $filename = $this->rootPath . $arrfile['savepath'] . $arrfile['savename'];
		    $arrfile['savefile'] = $arrfile['savepath'] . $arrfile['savename'];
		    
		    if($file->moveTo($filename)){
		    	$info[$key] = $arrfile;
		    }else{
		    	$this->error = $this->getError();
		    }       

		}
		
		return empty($info) ? false : $info;
    }
    /**
     * 根据上传文件命名规则取得保存文件名
     * @param string $file 文件信息
     */
    private function getSaveName($file) {
        $rule = $this->saveName; 
        if (empty($rule)) { //保持文件名不变
            /* 解决pathinfo中文文件名BUG */
            $filename = substr(pathinfo("_{$file['name']}", PATHINFO_FILENAME), 1);
            $savename = $filename; 
        } else {
            $savename = $this->getName($rule, $file['name']); 
            if(empty($savename)){
                $this->error = '文件命名规则错误！';
                return false;
            }
        }
        
        /* 文件保存后缀，支持强制更改文件后缀 */
        $ext = empty($this->config['saveExt']) ? $file['ext'] : $this->saveExt;

        return $savename . '.' . $ext;
    }
    /**
     * 根据指定的规则获取文件或目录名称
     * @param  array  $rule     规则
     * @param  string $filename 原文件名
     * @return string           文件或目录名称
     */
    private function getName($rule, $filename){
        $name = '';
        if(is_array($rule)){ //数组规则
            $func     = $rule[0];
            $param    = (array)$rule[1];
            foreach ($param as &$value) {
               $value = str_replace('__FILE__', $filename, $value);
            }
            $name = call_user_func_array($func, $param);
        } elseif (is_string($rule)){ //字符串规则
            if(function_exists($rule)){
                $name = call_user_func($rule);
            } else {
                $name = $rule;
            }
        }
        return $name;
    } 
    /**
     * 获取子目录的名称
     * @param array $file  上传的文件信息
     */
    private function getSubPath($filename) { 
        $subpath = '';
        $rule    = $this->subName; 
        if ($this->autoSub && !empty($rule)) {
            $subpath = $this->getName($rule, $filename) . '/';  

            if(!empty($subpath) && !$this->mkdir($this->savePath . $subpath)){
                $this->error = $this->getError();
                return false;
            }
        }
        return $subpath;
    }
    /**
     * 创建目录
     * @param  string $savepath 要创建的目录
     * @return boolean          创建状态，true-成功，false-失败
     */
    private function mkdir($savepath){
        $dir = $this->rootPath . $savepath;
        if(is_dir($dir)){
            return true;
        }

        if(mkdir($dir, 0777, true)){
            return true;
        } else {
            $this->error = "目录 {$savepath} 创建失败！";
            return false;
        }
    }           
}
