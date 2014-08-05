<?php
namespace IKPHP\Common\Models;

use \Phalcon\Config as Config;

class Setting extends BaseModel
{

    public function getSource()
    {
        return IK."setting";
    } 
    /**
     * 获取配置信息写入缓存
     */
    public function setting_cache() { 
        $arr_setting = array();
        $cacheKey = 'setting';
        $cache  = $this->getDI()->getShared('modelCache');
        $setting = $cache->get($cacheKey);
        if($setting == null){ 
            $res = $this->find(array('columns' => 'name, data'));  
            $cache->save($cacheKey, $res);
        }

        foreach ($setting as $item) {
        	$arr_setting['ik_'.$item->name] = $item->data;
        }
        //第二中方法配置C方法
		C($arr_setting);
        //设置配置
        $conf = new Config($arr_setting); 
        return $conf;
    }

}
