<?php
namespace IKPHP\Common\Models;
use \Phalcon\Config as Config;

class BaseModel extends \Phalcon\Mvc\Model
{
	 public function initialize()
	 {
	 	defined('IK') or define('IK', $this->getDI ()->get ( 'config' )->database->prefix);
	 	defined('AUTHKEY') or define('AUTHKEY', $this->getDI ()->get ( 'config' )->database->authkey);
	 }
	 
	 /* 封装一个查看SQL的方法 */
	 public function getLastSql()
	 {
	 	return $this->getReadConnection()->getSQLStatement();
	 }
}
