 
-- -----------------------------
-- 新增表ucenter_user表  `ik_ucenter_user` 2014年4月11日 新增
-- -----------------------------
DROP TABLE IF EXISTS `ik_ucenter_user`;
CREATE TABLE `ik_ucenter_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `username` char(32) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` char(32) NOT NULL DEFAULT '' COMMENT '用户密码',
  `email` char(32) NOT NULL DEFAULT '' COMMENT '用户email',
  `doname` char(32) DEFAULT '',
  `mobile` char(15) DEFAULT '' COMMENT '电话号码',
  `reg_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `reg_ip` varchar(16) NOT NULL DEFAULT '' COMMENT '注册IP',
  `last_login_time` int(11) DEFAULT '0' COMMENT '登陆时间',
  `last_login_ip` varchar(16) DEFAULT '' COMMENT '登陆IP',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(1) DEFAULT '0' COMMENT '是否启用：0启用1禁用',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='UC用户表';