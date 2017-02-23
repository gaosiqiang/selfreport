/* ============================================自助报表一期============================================ */

CREATE DATABASE IF NOT EXISTS dc_self_report DEFAULT CHARSET utf8 COLLATE utf8_general_ci;

USE dc_self_report;

-- 自助报表配置表
-- DROP TABLE IF EXISTS `report_configuration`;
CREATE TABLE `report_configuration` (
  `id` varchar(16) NOT NULL COMMENT '报表标识',
  `report_name` varchar(100) NOT NULL default '' COMMENT '报表名称',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `data_source` int(11) NOT NULL DEFAULT '0' COMMENT '数据源ID',
  `query_sql` text NOT NULL default '' COMMENT 'SQL查询语句',
  `is_timed` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否定时任务，0否1是',
  `crontab` varchar(100) NOT NULL default '' COMMENT '执行时间，参照crontab，分 时 日 月 周',
  `show_parts` varchar(100) NOT NULL default '' COMMENT '展示区域，包括1筛选区、2主题数据区、3图表区、4详细数据区（附带导出）、5数据项定义区',
  `conditions` text NOT NULL default '' COMMENT '筛选项配置',
  `charts` text NOT NULL default '' COMMENT '图表配置',
  PRIMARY KEY (`id`),
  KEY `idx_data_source` (`data_source`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='自助报表配置表';


-- 数据源表
-- DROP TABLE IF EXISTS `data_source`;
CREATE TABLE `data_source` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(18) NOT NULL default '' COMMENT '数据源名称',
  `server_ip` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '数据源服务器IP',
  `database` varchar(100) NOT NULL default '' COMMENT '数据源库',
  `charset` varchar(18) NOT NULL default 'utf8' COMMENT '字符集',
  `username` varchar(50) NOT NULL default '' COMMENT '用户名',
  `password` varchar(255) NOT NULL default '' COMMENT '密码',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='数据源表';


-- 数据项定义表
-- DROP TABLE IF EXISTS `column_define`;
CREATE TABLE `column_define` (
  `report_id` varchar(16) NOT NULL default '' COMMENT '自助报表ID',
  `column_name` varchar(50) NOT NULL default '' COMMENT '数据项，即select数据库字段',
  `show_name` varchar(32) NOT NULL default '' COMMENT '数据项名称',
  `define` text NOT NULL default '' COMMENT '数据项定义',
  `privilege_type` varchar(18) NOT NULL default '' COMMENT '行级权限类型，如city,category,media等',
  `function` tinyint(1) NOT NULL default '0' COMMENT '附加功能，0无，1关联城市归属，2城市归属',
  `function_detail` text NOT NULL default '' COMMENT '附加功能配置',
  `status` tinyint(1) NOT NULL default '1' COMMENT '状态，0无效，1有效',
  PRIMARY KEY (`report_id`,`column_name`),
  KEY `idx_privilege_type` (`report_id`,`privilege_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='数据项定义表';


-- 权限控制字段表
-- DROP TABLE IF EXISTS `column_for_privilege`;
CREATE TABLE `column_for_privilege` (
  `report_id` varchar(16) NOT NULL COMMENT '自助报表ID',
  `expression` varchar(255) NOT NULL COMMENT '结果字段表达式，表名.字段名',
  `privilege_type` varchar(18) NOT NULL DEFAULT '' COMMENT '行级权限类型，如city,category,media等',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态，0无效，1有效',
  PRIMARY KEY (`report_id`,`expression`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='权限控制字段表'


-- 列表筛选项
-- DROP TABLE IF EXISTS `dict_list`;
CREATE TABLE `dict_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(18) NOT NULL default '' COMMENT '名称',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型，0普通列表，1联动列表',
  `value_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '值类型，0ID，1名称',
  `linked_default` text NOT NULL default '' COMMENT '联动列表默认项',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='列表筛选项';

INSERT INTO `dict_list`(`name`,`type`) VALUES('一级品类',0);
INSERT INTO `dict_list`(`name`,`type`) VALUES('媒体',0);
INSERT INTO `dict_list`(`name`,`type`) VALUES('事业部',0);
INSERT INTO `dict_list`(`name`,`type`,`linked_default`) VALUES('大区-区域-城市',1,'a:3:{s:5:"first";a:2:{s:6:"column";s:4:"area";s:4:"name";s:4:"大区";}s:6:"second";a:2:{s:6:"column";s:6:"region";s:4:"name";s:4:"区域";}s:5:"third";a:2:{s:6:"column";s:4:"city";s:4:"name";s:4:"城市";}}');
INSERT INTO `dict_list`(`name`,`type`,`linked_default`) VALUES('战区-城市',1,'a:2:{s:5:"first";a:2:{s:6:"column";s:7:"warzone";s:4:"name";s:4:"战区";}s:6:"second";a:2:{s:6:"column";s:4:"city";s:4:"name";s:4:"城市";}}');
INSERT INTO `dict_list`(`name`,`type`,`linked_default`) VALUES('一级品类-二级品类',1,'a:2:{s:5:"first";a:2:{s:6:"column";s:10:"first_cate";s:4:"name";s:8:"一级品类";}s:6:"second";a:2:{s:6:"column";s:11:"second_cate";s:4:"name";s:8:"二级品类";}}');
INSERT INTO `dict_list`(`name`,`type`,`linked_default`) VALUES('媒体类型-媒体',1,'a:2:{s:5:"first";a:2:{s:6:"column";s:10:"media_type";s:4:"name";s:8:"媒体类型";}s:6:"second";a:2:{s:6:"column";s:5:"media";s:4:"name";s:4:"媒体";}}');
INSERT INTO `dict_list`(`name`,`type`,`linked_default`) VALUES('(网店通)大区-区域-城市-团队',1,'a:3:{s:5:"first";a:2:{s:6:"column";s:4:"area";s:4:"name";s:4:"大区";}s:6:"second";a:2:{s:6:"column";s:6:"region";s:4:"name";s:4:"区域";}s:5:"third";a:2:{s:6:"column";s:4:"city";s:4:"name";s:4:"城市";}s:6:"fourth";a:2:{s:6:"column";s:4:"team";s:4:"name";s:4:"团队";}}');

UPDATE `dict_list` SET `name`='(网店通)大区-区域-分部-团队',`linked_default`='a:3:{s:5:"first";a:2:{s:6:"column";s:4:"area";s:4:"name";s:4:"大区";}s:6:"second";a:2:{s:6:"column";s:6:"region";s:4:"name";s:4:"区域";}s:5:"third";a:2:{s:6:"column";s:4:"city";s:4:"name";s:4:"分部";}s:6:"fourth";a:2:{s:6:"column";s:4:"team";s:4:"name";s:4:"团队";}}' WHERE `name`='(网店通)大区-区域-城市-团队';
UPDATE `dict_list` SET `name`='(团购)大区-区域-城市-团队',`linked_default`='a:3:{s:5:"first";a:2:{s:6:"column";s:4:"area";s:4:"name";s:4:"大区";}s:6:"second";a:2:{s:6:"column";s:6:"region";s:4:"name";s:4:"区域";}s:5:"third";a:2:{s:6:"column";s:4:"city";s:4:"name";s:4:"城市";}s:6:"fourth";a:2:{s:6:"column";s:4:"team";s:4:"name";s:4:"团队";}}' WHERE `name`='大区-区域-城市';
UPDATE `dict_list` SET `name`='(团购)战区-城市' WHERE `name`='战区-城市';

-- 列表筛选项内容
-- DROP TABLE IF EXISTS `dict_list_content`;
CREATE TABLE `dict_list_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_key` varchar(100) NOT NULL default '' COMMENT '内容项key',
  `item_value` varchar(18) NOT NULL default '' COMMENT '内容项value',
  `list_id` int(11) NOT NULL default '0' COMMENT '列表ID',
  PRIMARY KEY (`id`),
  KEY `idx_list` (`list_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='列表筛选项内容';


-- 联动列表筛选项内容
-- DROP TABLE IF EXISTS `dict_link_list_content`;
CREATE TABLE `dict_link_list_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `list_id` int(11) NOT NULL default '0' COMMENT '联动列表ID',
  `first_key` varchar(100) NOT NULL default '' COMMENT '一级列表内容项key',
  `first_value` varchar(18) NOT NULL default '' COMMENT '一级列表内容项value',
  `second_key` varchar(100) NOT NULL default '' COMMENT '二级列表内容项key',
  `second_value` varchar(18) NOT NULL default '' COMMENT '二级列表内容项value',
  `third_key` varchar(100) NOT NULL default '' COMMENT '三级列表内容项key',
  `third_value` varchar(18) NOT NULL default '' COMMENT '三级列表内容项value',
  `fourth_key` varchar(100) NOT NULL default '' COMMENT '四级列表内容项key',
  `fourth_value` varchar(18) NOT NULL default '' COMMENT '四级列表内容项value',
  PRIMARY KEY (`id`),
  KEY `idx_list` (`list_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='联动列表筛选项内容';


-- 用户组表
-- DROP TABLE IF EXISTS `user_group`;
CREATE TABLE `user_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(20) NOT NULL default '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户组表';


-- 报表权限表
-- DROP TABLE IF EXISTS `report_privileges`;
CREATE TABLE `report_privileges` (
  `report_id` varchar(16) NOT NULL default '' COMMENT '自助报表ID',
  `user_group_id` int(11) NOT NULL default '0' COMMENT '用户组ID',
  PRIMARY KEY (`report_id`,`user_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='报表权限表';


-- 用户权限表
-- DROP TABLE IF EXISTS `privileges`;
CREATE TABLE `privileges` (
  `user_id` int(11) NOT NULL default '0' COMMENT '用户ID',
  `username` varchar(32) NOT NULL default '' COMMENT '用户名',
  `user_group_id` text NOT NULL default '' COMMENT '用户组ID',
  `is_super` tinyint(1) NOT NULL default '0' COMMENT '是否超级管理员，1超级管理员，2报表配置员，3全报表查看用户',
  `privileges` text NOT NULL default '' COMMENT '用户权限',
  PRIMARY KEY  (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户权限表';

-- 初始权限用户
INSERT INTO `privileges`(`user_id`,`username`,`user_group_id`,`is_super`) VALUES(21,'chenxuan',9999,1);








-- 菜单表
-- DROP TABLE IF EXISTS `menu`;
CREATE TABLE `menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `platform` tinyint(1) NOT NULL DEFAULT '0' COMMENT '分发平台，1数据平台，2决策系统，9自助报表平台',
  `is_viewed_only_admin` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1表示只有管理员可见，0表示全部可见',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1可见，0隐藏',
  `menu_grade` tinyint(1) NOT NULL DEFAULT '0' COMMENT '菜单层级',
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '如果该值为0，说明其为顶层菜单',
  `tab_state` tinyint(1) NOT NULL DEFAULT '0' COMMENT '标签页状况:1子菜单为标签页,2本身为标签页',
  `menu_name` varchar(30) NOT NULL DEFAULT '' COMMENT '菜单名称',
  `report_id` varchar(16) NOT NULL DEFAULT '' COMMENT '自助报表ID',
  PRIMARY KEY (`id`),
  KEY `idx_report` (`report_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='菜单表';

INSERT INTO `menu`(`menu_grade`,`menu_name`) VALUES(1,'临时报表');
INSERT INTO `menu`(`menu_grade`,`menu_name`) VALUES(1,'常规报表');
INSERT INTO `menu`(`menu_grade`,`menu_name`) VALUES(1,'行动量报表');
-- ALTER TABLE `menu` ADD `tab_state` tinyint(1) NOT NULL DEFAULT '0' COMMENT '标签页状况:1子菜单为标签页,2本身为标签页' AFTER `parent_id`;
-- ALTER TABLE `menu` ADD `platform` tinyint(1) NOT NULL DEFAULT '0' COMMENT '分发平台，1数据平台，2决策系统，9自助报表平台' AFTER `id`;
-- UPDATE `menu` SET `platform`=9;


-- 用户访问日志
-- DROP TABLE IF EXISTS `log`;
CREATE TABLE `log` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(32) NOT NULL DEFAULT '' COMMENT '用户名',
  `menu` int(11) NOT NULL DEFAULT '0' COMMENT '菜单id',
  `menu_name` varchar(30) NOT NULL DEFAULT '' COMMENT '菜单名称',
  `ip` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '访问者ip',
  `url` varchar(100) NOT NULL DEFAULT '' COMMENT '访问url',
  `time` int(11) NOT NULL DEFAULT '0' COMMENT '访问时间',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户访问日志';


-- 公告表
-- DROP TABLE IF EXISTS `announcement`;
CREATE TABLE `announcement` (
  `id` int(11) NOT NULL auto_increment COMMENT '自增id',
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '公告标题',
  `content` text NOT NULL DEFAULT '' COMMENT '公告内容',
  `start_time` int(11) NOT NULL DEFAULT '0' COMMENT '开始时间',
  `end_time` int(11) NOT NULL DEFAULT '0' COMMENT '结束时间',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `author_id` int(11) NOT NULL DEFAULT '0' COMMENT '添加公告人id',
  `is_delete` tinyint(1) NOT NULL default '0' COMMENT '是否删除，1表示删除',
  PRIMARY KEY  (`id`),
  KEY `start_time` (`start_time`),
  KEY `end_time` (`end_time`),
  KEY `is_delete` (`is_delete`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='公告表';



/* ============================================城市拆分部(数据平台库)============================================ */

CREATE TABLE `branch` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '分部id',
  `name` varchar(50) DEFAULT NULL COMMENT '分部名称',
  `initials` varchar(4) NOT NULL COMMENT '首字母缩写',
  `operation_mode` varchar(50) NOT NULL DEFAULT '' COMMENT '运营模式',
  `area_id` int(11) NOT NULL DEFAULT '0' COMMENT '大区ID',
  `region_id` int(11) DEFAULT NULL COMMENT '区域ID',
  `war_zone` int(11) NOT NULL DEFAULT '0' COMMENT '战区分区ID',
  `war_step` int(11) NOT NULL DEFAULT '0' COMMENT '战区分档ID',
  `business_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '业务标识,0-团购,1-网店通,2-团购+网店通',
  `relation_city` varchar(30) NOT NULL DEFAULT '' COMMENT '权限关联城市',
  `type_sign` char(5) NOT NULL DEFAULT '' COMMENT '类型标识,空字符串:自然城市,a:部,b:事业部,c:渠道部',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否被删除,0-否,1-是',
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='分部表';


CREATE TABLE `region` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT COMMENT '区域ID',
  `parent_id` int(11) DEFAULT NULL COMMENT '0-大区,其他-区域',
  `name` varchar(30) NOT NULL COMMENT '区域名称',
  `show_order` int(11) NOT NULL COMMENT '显示顺序',
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否被删除,0-否,1-是',
  `is_group` tinyint(1) DEFAULT '0' COMMENT '是否为团购数据,0-否,1-是',
  `is_wdt` tinyint(1) DEFAULT '0' COMMENT '是否为网店通数据,0-否,1-是',
  `is_unit` tinyint(1) DEFAULT '0' COMMENT '是否为事业部数据,0-否,1-是',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_idx` (`parent_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='大区&区域表';

ALTER TABLE `region` ADD `is_group` tinyint(1) DEFAULT '0' COMMENT '是否为团购数据,0-否,1-是';
ALTER TABLE `region` ADD `is_wdt` tinyint(1) DEFAULT '0' COMMENT '是否为网店通数据,0-否,1-是';
ALTER TABLE `region` ADD `is_unit` tinyint(1) DEFAULT '0' COMMENT '是否为事业部数据,0-否,1-是';


CREATE TABLE `warzone` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '战区ID',
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '0-战区分区,其他-战区分档',
  `name` varchar(30) NOT NULL COMMENT '战区名称',
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否被删除,0-否,1-是',
  `is_group` tinyint(1) DEFAULT '0' COMMENT '是否为团购数据,0-否,1-是',
  `is_wdt` tinyint(1) DEFAULT '0' COMMENT '是否为网店通数据,0-否,1-是',
  `is_unit` tinyint(1) DEFAULT '0' COMMENT '是否为事业部数据,0-否,1-是',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='战区表';

ALTER TABLE `warzone` ADD `is_group` tinyint(1) DEFAULT '0' COMMENT '是否为团购数据,0-否,1-是';
ALTER TABLE `warzone` ADD `is_wdt` tinyint(1) DEFAULT '0' COMMENT '是否为网店通数据,0-否,1-是';
ALTER TABLE `warzone` ADD `is_unit` tinyint(1) DEFAULT '0' COMMENT '是否为事业部数据,0-否,1-是';


