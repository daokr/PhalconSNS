<?php
/**
 * 获取和设置配置参数 支持批量定义
 * @param string|array $name 配置变量
 * @param mixed $value 配置值
 * @param mixed $default 默认值
 * @return mixed
 */
function C($name=null, $value=null,$default=null) {
    static $_config = array();
    // 无参数时获取所有
    if (empty($name)) {
        return $_config;
    }
    // 优先执行设置获取或赋值
    if (is_string($name)) {
        if (!strpos($name, '.')) {
            $name = strtoupper($name);
            if (is_null($value))
                return isset($_config[$name]) ? $_config[$name] : $default;
            $_config[$name] = $value;
            return;
        }
        // 二维数组设置和获取支持
        $name = explode('.', $name);
        $name[0]   =  strtoupper($name[0]);
        if (is_null($value))
            return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : $default;
        $_config[$name[0]][$name[1]] = $value;
        return;
    }
    // 批量设置
    if (is_array($name)){
        $_config = array_merge($_config, array_change_key_case($name,CASE_UPPER));
        return;
    }
    return null; // 避免非法参数
}
/**
 * 获取用户头像
 */
function avatar($uid, $size, $face) {
	if(empty($face)){
		$ext = '.jpg';
	}else{
		$ext = '.'.fileext($face);
	}
    $avatar_size = explode(',', C('ik_avatar_size'));
    $size = in_array($size, $avatar_size) ? $size : '48';
    $avatar_dir = avatar_dir($uid);
    $avatar_file = C('ik_attach_path') .'face/'.$avatar_dir.md5($uid).'_'.$size.'_'.$size.$ext;
    if (!is_file($avatar_file)) {
    	$avatar_file = "user_48.jpg";
    	return C('ik_site_url') . 'static/images/' . $avatar_file.'?v='.time();
    }else{
    	return C('ik_site_url') .  $avatar_file.'?v='.time();
    }
}
function avatar_dir($uid) {
    $uid = abs(intval($uid));
    $suid = sprintf("%09d", $uid);
    $dir1 = substr($suid, 0, 3);
    $dir2 = substr($suid, 3, 2);
    $dir3 = substr($suid, 5, 2);
    return $dir1 . '/' . $dir2 . '/' . $dir3 . '/';
}
/**
 * 输入过滤函数
 * @param  string  $string 输入的字符串
 * @return string  转码后的安全串
 */
function shtmlspecialchars($string) {
	if (is_array ( $string )) {
		foreach ( $string as $key => $val ) {
			$string [$key] = shtmlspecialchars ( $val );
		}
	} else {
		$string = preg_replace ( '/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1', str_replace ( array ('&', '"', '<', '>' ), array ('&amp;', '&quot;', '&lt;', '&gt;' ), $string ) );
	}
	return $string;
}
/**
 * 2014年3月19日 新增认证函数
 * 数据签名认证
 * @param  array  $data 被认证的数据
 * @return string       签名
 * @author 小麦 <ikphp@sina.cn>
 */
function data_auth_sign($data) {
	//数据类型检测
	if (! is_array ( $data )) {
		$data = ( array ) $data;
	}
	ksort ( $data ); //排序
	$code = http_build_query ( $data ); //url编码并生成query字符串
	$sign = sha1 ( $code ); //生成签名
	return $sign;
}
//长度检查函数
function ck_strlen($string, $charset = 'utf8') {
	if (function_exists ( 'mb_strlen' )) {
		return mb_strlen ( $string, $charset );
	} else {
		return iconv_strlen ( $string, $charset );
	}
}
/*
* utf8字符串截取
* 用法：getsubstrutf8($string,5,20);
* @param $str string 需要截取的串
* @param $start intval 开始位置
* @param $sublen intval 要保留的字符长度
* @param $append boolean 是否要添加 ...
*/
function getsubstrutf8($string, $start = 0, $sublen, $append = true) {
	$pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
	preg_match_all ( $pa, $string, $t_string );
	if (count ( $t_string [0] ) - $start > $sublen && $append == true) {
		return join ( '', array_slice ( $t_string [0], $start, $sublen ) ) . "...";
	} else {
		return join ( '', array_slice ( $t_string [0], $start, $sublen ) );
	}
}
/*
* 新增与2014年4月7日 修改人小麦
* IKPHP 专用文本输出截取字符串 弃用getsubstrutf8()函数
* 用法：sub_str(strip_tags($strNote["content"]),180); || sub_str($strNote["content"],180);
* @param $str string 需要截取的串
* @param $length intval 要保留的字符长度
* @param $append boolean 是否要添加 ...
* @param $charset string 'utf8' 文本编码格式
*/
function sub_str($str, $length = 0, $append = true, $charset = 'utf8') {
	$str = trim ( $str );
	$strlength = strlen ( $str );
	$charset = strtolower ( $charset );
	if ($charset == 'utf8') {
		$l = 0;
		$i = 0;
		while ( $i < $strlength ) {
			if (ord ( $str {$i} ) < 0x80) {
				$l ++;
				$i ++;
			} else if (ord ( $str {$i} ) < 0xe0) {
				$l ++;
				$i += 2;
			} else if (ord ( $str {$i} ) < 0xf0) {
				$l += 2;
				$i += 3;
			} else if (ord ( $str {$i} ) < 0xf8) {
				$l += 1;
				$i += 4;
			} else if (ord ( $str {$i} ) < 0xfc) {
				$l += 1;
				$i += 5;
			} else if (ord ( $str {$i} ) < 0xfe) {
				$l += 1;
				$i += 6;
			}
			if ($l >= $length) {
				$newstr = substr ( $str, 0, $i );
				break;
			}
		}
		if ($l < $length) {
			return $str;
		}
	} elseif ($charset == 'gbk') {
		if ($length == 0 || $length >= $strlength) {
			return $str;
		}
		while ( $i <= $strlength ) {
			if (ord ( $str {$i} ) > 0xa0) {
				$l += 2;
				$i += 2;
			} else {
				$l ++;
				$i ++;
			}
			if ($l >= $length) {
				$newstr = substr ( $str, 0, $i );
				break;
			}
		}
	}
	if ($append && $str != $newstr) {
		$newstr .= '..';
	}
	return $newstr;
}
/**
 * 使用正则验证数据
 * @access public
 * @param string $value  要验证的数据
 * @param string $rule 验证规则
 * @return boolean
 */
function regex($value,$rule) {
	$validate = array(
		'require'   =>  '/\S+/',
		'email'     =>  '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
		'url'       =>  '/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/',
		'currency'  =>  '/^\d+(\.\d+)?$/',
		'number'    =>  '/^\d+$/',
		'zip'       =>  '/^\d{6}$/',
		'integer'   =>  '/^[-\+]?\d+$/',
		'double'    =>  '/^[-\+]?\d+(\.\d+)?$/',
		'english'   =>  '/^[A-Za-z]+$/',
	);
	// 检查是否有内置的正则表达式
	if(isset($validate[strtolower($rule)]))
		$rule       =   $validate[strtolower($rule)];
	return preg_match($rule,$value)===1;
}
//获取文件名后缀
function fileext($filename) {
	return strtolower(trim(substr(strrchr($filename, '.'), 1)));
}
//获取文件名称
function filemain($filename) {
	return trim(substr($filename, 0, strrpos($filename, '.')));
}
/**
 * 抛出异常处理
 * @param string $msg 异常消息
 * @param integer $code 异常代码 默认为0
 * @return void
 */
function E($msg, $code=0) {
    throw new \Phalcon\Exception($msg, $code);
}