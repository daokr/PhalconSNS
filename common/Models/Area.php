<?php
/*
 * IKPHP爱客网 安装程序 @copyright (c) 2012-3000 IKPHP All Rights Reserved @author 小麦
* @Email:810578553@qq.com
* @小麦 修改时间2014年3月15日 23:44 地区基础类
*/
namespace IKPHP\Common\Models;

class Area extends BaseModel {
    public function getSource()
    {
        return IK."area";
    }
	// 通过连贯性找到三级区域
	public function getArea($areaid) { 
		$strAreaThree = self::findFirst($areaid)->toArray();
		
		if ($strAreaThree) {
			
			if ($strAreaThree ['referid'] > 0) {
				$strAreaTwo = $this->findFirst ($strAreaThree ['referid'])->toArray();
				if ($strAreaTwo ['referid'] > 0) {
					$strAreaOne = $this->findFirst($strAreaTwo ['referid'])->toArray(); 
					$strArea = array (
							'one' => array (
									'areaid' => $strAreaOne ['areaid'],
									'areaname' => $strAreaOne ['areaname'] 
							),
							'two' => array (
									'areaid' => $strAreaTwo ['areaid'],
									'areaname' => $strAreaTwo ['areaname'] 
							),
							'three' => array (
									'areaid' => $strAreaThree ['areaid'],
									'areaname' => $strAreaThree ['areaname'] 
							) 
					);
				} else {
					$strArea = array (
							'two' => array (
									'areaid' => $strAreaTwo ['areaid'],
									'areaname' => $strAreaTwo ['areaname'] 
							),
							'three' => array (
									'areaid' => $strAreaThree ['areaid'],
									'areaname' => $strAreaThree ['areaname'] 
							) 
					);
				}
			} else {
				$strArea = array (
						'three' => array (
								'areaid' => $strAreaThree ['areaid'],
								'areaname' => $strAreaThree ['areaname'] 
						) 
				);
			}
		
		} else {
			$strArea = array (
					'three' => array (
							'areaid' => '0',
							'areaname' => '火星' 
					) 
			);
		}
		return $strArea;
	
	}
	
	// 获取单个区域
	public function getOneArea($areaid){
		$result = $this->findFirst("areaid = '$areaid'");
		return $result;	
	}
	// 获取区域下的区域
	public function getReferArea($areaid) {
		$result = $this->find("referid = '$areaid'")->toArray();
		return $result;
	}
}