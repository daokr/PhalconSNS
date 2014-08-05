<?php
/*
 * IKPHP爱客网 安装程序 @copyright (c) 2012-3000 IKPHP All Rights Reserved @author 小麦
* @Email:ikphp@sina.cn
* @小麦 修改时间2014年3月15日晚 2:49 
* @基础应用model
*/
namespace IKPHP\Common\Models;

class App extends BaseModel {
    public function getSource()
    {
        return IK."app";
    }
    public function afterQuery(){
    	$this->logger->log('查询app了');
    }
}