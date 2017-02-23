/* ============================================自助报表需求============================================ */

/* ============================================数据平台============================================ */

-- 商品销售验证退款月报
CREATE TABLE `dcp_goods_sale_verify_refund_month` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_month` varchar(10) NOT NULL DEFAULT '' COMMENT '月份',
  `goods_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '商品价格ID',
  `goods_sname` varchar(100) NOT NULL DEFAULT '' COMMENT '商品短名称',
  `first_cat_id` int(11) NOT NULL DEFAULT '0' COMMENT '一级分类ID',
  `first_cat_name` varchar(100) NOT NULL DEFAULT '' COMMENT '一级分类名称',
  `second_cat_id` int(11) NOT NULL DEFAULT '0' COMMENT '二级分类id',
  `second_cat_name` varchar(100) NOT NULL DEFAULT '' COMMENT '二级分类名称',
  `is_vender` varchar(5) NOT NULL DEFAULT '否' COMMENT '是否第三方券',
  `supplier_id` int(11) NOT NULL DEFAULT '0' COMMENT '商家ID',
  `supplier_name` varchar(100) NOT NULL DEFAULT '' COMMENT '商家名称',
  `industry_first_cat` varchar(100) NOT NULL DEFAULT '' COMMENT '行业一级分类',
  `is_new_supplier` varchar(5) NOT NULL DEFAULT '否' COMMENT '是否新商家',
  `is_first_online_supplier` varchar(5) NOT NULL DEFAULT '否' COMMENT '是否首次上单商家',
  `customer_level` varchar(10) NOT NULL DEFAULT '' COMMENT '商户等级',
  `owner_id` varchar(100) NOT NULL DEFAULT '' COMMENT '合同拥有者ID',
  `owner_name` varchar(100) NOT NULL DEFAULT '' COMMENT '合同拥有者姓名',
  `owner_city` varchar(100) NOT NULL DEFAULT '' COMMENT '合同拥有者城市',
  `owner_org` varchar(100) NOT NULL DEFAULT '' COMMENT '合同拥有者岗位',
  `source_city_id` int(11) NOT NULL DEFAULT '0' COMMENT '来源城市ID',
  `source_city_name` varchar(100) NOT NULL DEFAULT '' COMMENT '来源城市名称',
  `cost_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '结算单价',
  `goods_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '销售单价',
  `act_start_time` datetime NOT NULL DEFAULT '0000-00-00' COMMENT '上线时间',
  `act_end_time` datetime NOT NULL DEFAULT '0000-00-00' COMMENT '下线时间',
  `ticket_start_time` datetime NOT NULL DEFAULT '0000-00-00' COMMENT '券开始时间',
  `ticket_end_time` datetime NOT NULL DEFAULT '0000-00-00' COMMENT '券结束时间',
  `sale_nums` int(11) NOT NULL DEFAULT '0' COMMENT '销售数量',
  `sale_money` decimal(18,2) NOT NULL DEFAULT '0.00' COMMENT '销售金额',
  `sale_profile` decimal(18,2) NOT NULL DEFAULT '0.00' COMMENT '销售毛利',
  `verify_num` int(11) NOT NULL DEFAULT '0' COMMENT '验证笔数',
  `verify_nums` int(11) NOT NULL DEFAULT '0' COMMENT '验证数量',
  `verify_money` decimal(18,2) NOT NULL DEFAULT '0.00' COMMENT '验证金额',
  `verify_profile` decimal(18,2) NOT NULL DEFAULT '0.00' COMMENT '验证毛利',
  `refund_num` int(11) NOT NULL DEFAULT '0' COMMENT '退款笔数',
  `refund_nums` int(11) NOT NULL DEFAULT '0' COMMENT '退款数量',
  `refund_money` decimal(18,2) NOT NULL DEFAULT '0.00' COMMENT '退款金额',
  `refund_profile` decimal(18,2) NOT NULL DEFAULT '0.00' COMMENT '退款毛利',
  PRIMARY KEY (`id`),
  KEY `idx_date` (`report_month`),
  KEY `idx_goods_id` (`goods_id`),
  KEY `idx_goods_sname` (`goods_sname`),
  KEY `idx_first_cat` (`first_cat_id`),
  KEY `idx_second_cat` (`second_cat_id`),
  KEY `idx_source_city` (`source_city_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='商品销售验证退款月报';



-- 来源城市数据
CREATE TABLE `dcp_source_city_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_date` date NOT NULL DEFAULT '0000-00-00' COMMENT '日期',
  `source_city_id` int(11) NOT NULL DEFAULT '0' COMMENT '来源城市ID',
  `source_city_name` varchar(50) NOT NULL DEFAULT '' COMMENT '来源城市名称',
  `online_supplier_num` int(11) NOT NULL DEFAULT '0' COMMENT '在线商户数',
  `online_shop_num` int(11) NOT NULL DEFAULT '0' COMMENT '在线门店数',
  `online_good_num` int(11) NOT NULL DEFAULT '0' COMMENT '在线商品数',
  PRIMARY KEY (`id`),
  KEY `idx_date` (`report_date`),
  KEY `idx_source_city` (`source_city_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='来源城市数据';



-- 虚假行动量数据
CREATE TABLE `dcp_false_action` (
  `id` int NOT NULL AUTO_INCREMENT,
  `false_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '类型，0疑似虚假，1虚假，2二者皆是',
  `user_code` varchar(30) NOT NULL DEFAULT '' COMMENT '用户员工编号',
  `user_name` varchar(30) NOT NULL DEFAULT '' COMMENT '用户姓名',
  `user_city_id` int NOT NULL DEFAULT '0' COMMENT '用户所在城市id',
  `user_city_name` varchar(30) NOT NULL DEFAULT '' COMMENT '用户所在城市',
  `user_position` varchar(30) NOT NULL DEFAULT '' COMMENT '用户所在分组',
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '开始时间',
  `follow_type` varchar(50) NOT NULL DEFAULT '' COMMENT '跟进方式',
  `action_type` varchar(50) NOT NULL DEFAULT '' COMMENT '行动类型',
  `supplier_name` varchar(255) NOT NULL DEFAULT '' COMMENT '商户名称',
  `supplier_cat_id` int NOT NULL DEFAULT '0' COMMENT '商户分类ID',
  `supplier_cat_name` varchar(30) NOT NULL DEFAULT '' COMMENT '商户分类',
  `contact_name` varchar(30) NOT NULL DEFAULT '' COMMENT '联系人',
  `contact_telphone` varchar(30) NOT NULL DEFAULT '' COMMENT '商户联系电话',
  `sale_code` varchar(30) NOT NULL DEFAULT '' COMMENT '电话匹配销售员工编号',
  `sale_name` varchar(30) NOT NULL DEFAULT '' COMMENT '电话匹配销售姓名',
  `sale_city_id` int NOT NULL DEFAULT '0' COMMENT '电话匹配销售所在城市id',
  `sale_city_name` varchar(30) NOT NULL DEFAULT '' COMMENT '电话匹配销售所在城市',
  `sale_position` varchar(30) NOT NULL DEFAULT '' COMMENT '电话匹配销售所在分组',
  `is_vaild_follow` varchar(10) NOT NULL DEFAULT '' COMMENT '是否有效跟进',
  `detail_info` text COMMENT '详细信息',
  `revisit_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '回访时间',
  `remark` text COMMENT '备注',
  `is_answer_call` varchar(10) NOT NULL DEFAULT '' COMMENT '商家是否接听电话',
  `unanswer_call_reason` text COMMENT '未接通电话原因',
  `is_true_action` varchar(10) NOT NULL DEFAULT '' COMMENT '该次行动是否真实',
  `false_action_reason` text COMMENT '行动不真实原因',
  `undetermine_action_reason` text COMMENT '行动无法判断原因',
  `evaluator` varchar(30) NOT NULL DEFAULT '' COMMENT '评价人',
  `evaluation` text COMMENT '评价内容',
  PRIMARY KEY (`id`),
  KEY `idx_date` (`start_date`),
  KEY `idx_revisit` (`revisit_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='虚假行动量数据';

-- `user_area_name` varchar(30) NOT NULL DEFAULT '' COMMENT '用户所在大区',
-- `user_region_name` varchar(30) NOT NULL DEFAULT '' COMMENT '用户所在区域',
-- `sale_area_name` varchar(30) NOT NULL DEFAULT '' COMMENT '电话匹配销售所在大区',
-- `sale_region_name` varchar(30) NOT NULL DEFAULT '' COMMENT '电话匹配销售所在区域',

ALTER TABLE `dcp_false_action` DROP `user_area_name`;
ALTER TABLE `dcp_false_action` DROP `user_region_name`;
ALTER TABLE `dcp_false_action` DROP `sale_area_name`;
ALTER TABLE `dcp_false_action` DROP `sale_region_name`;
ALTER TABLE `dcp_false_action` ADD `evaluator` varchar(30) NOT NULL DEFAULT '' COMMENT '评价人';
ALTER TABLE `dcp_false_action` ADD `evaluation` text COMMENT '评价内容';


-- 行动量数据
CREATE TABLE `dcp_action_info` (
  `id` int NOT NULL AUTO_INCREMENT,
  `create_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '行动录入时间',
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '行动开始时间',
  `create_id` varchar(30) NOT NULL DEFAULT '' COMMENT '创建人ID',
  `create_name` varchar(30) NOT NULL DEFAULT '' COMMENT '创建人',
  `create_org` varchar(30) NOT NULL DEFAULT '' COMMENT '创建人所在分组',
  `supplier_city_id` int NOT NULL DEFAULT '0' COMMENT '商户所在城市id',
  `city_name` varchar(30) NOT NULL DEFAULT '' COMMENT '商户所在城市',
  `sale_city_id` int NOT NULL DEFAULT '0' COMMENT '创建人(销售)所在城市id',
  `sale_city` varchar(30) NOT NULL DEFAULT '' COMMENT '创建人(销售)所在城市',
  `supplier_name` varchar(100) NOT NULL DEFAULT '' COMMENT '商户名称',
  `supplier_from` varchar(50) NOT NULL DEFAULT '' COMMENT '商户来源',
  `supplier_level` varchar(50) NOT NULL DEFAULT '' COMMENT '商户等级',
  `action_type` varchar(50) NOT NULL DEFAULT '' COMMENT '行动类型',
  `follow_step` varchar(50) NOT NULL DEFAULT '' COMMENT '跟进阶段',
  `follow_type` varchar(50) NOT NULL DEFAULT '' COMMENT '跟进方式',
  `visit_detail` text COMMENT '拜访明细内容',
  `is_valid` varchar(10) NOT NULL DEFAULT '' COMMENT '是否有效',
  `intention_degree` varchar(50) NOT NULL DEFAULT '' COMMENT '客户意向度',
  `evaluator` varchar(30) NOT NULL DEFAULT '' COMMENT '评价人',
  `evaluation` text COMMENT '评价内容',
  PRIMARY KEY (`id`),
  KEY `idx_date` (`create_date`),
  KEY `idx_start` (`start_date`),
  KEY `idx_sale_city` (`sale_city`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='行动量数据';

ALTER TABLE `dcp_action_info` ADD `create_org` varchar(30) NOT NULL DEFAULT '' COMMENT '创建人所在分组' after `create_name`;
ALTER TABLE `dcp_action_info` ADD `supplier_city_id` int NOT NULL DEFAULT '0' COMMENT '商户所在城市id' after `create_org`;
ALTER TABLE `dcp_action_info` ADD `sale_city_id` int NOT NULL DEFAULT '0' COMMENT '创建人(销售)所在城市id' after `city_name`;
ALTER TABLE `dcp_action_info` ADD `sale_city` varchar(30) NOT NULL DEFAULT '' COMMENT '创建人(销售)所在城市' after `sale_city_id`;
ALTER TABLE `dcp_action_info` MODIFY `city_name` varchar(30) NOT NULL DEFAULT '' COMMENT '商户所在城市';
ALTER TABLE `dcp_action_info` ADD INDEX `idx_sale_city` (`sale_city`);



-- 疑似虚假行动量(按号码)
CREATE TABLE `dcp_suspect_false_action_bd` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_code` varchar(30) NOT NULL DEFAULT '' COMMENT '用户员工编号',
  `user_name` varchar(30) NOT NULL DEFAULT '' COMMENT '用户姓名',
  `user_city_id` int NOT NULL DEFAULT '0' COMMENT '用户所在城市id',
  `user_city_name` varchar(30) NOT NULL DEFAULT '' COMMENT '用户所在城市',
  `user_position` varchar(30) NOT NULL DEFAULT '' COMMENT '用户所在分组',
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '开始时间',
  `follow_type` varchar(50) NOT NULL DEFAULT '' COMMENT '跟进方式',
  `action_type` varchar(50) NOT NULL DEFAULT '' COMMENT '行动类型',
  `supplier_name` varchar(255) NOT NULL DEFAULT '' COMMENT '商户名称',
  `supplier_cat_id` int NOT NULL DEFAULT '0' COMMENT '商户分类ID',
  `supplier_cat_name` varchar(30) NOT NULL DEFAULT '' COMMENT '商户分类',
  `contact_name` varchar(30) NOT NULL DEFAULT '' COMMENT '联系人',
  `contact_telphone` varchar(30) NOT NULL DEFAULT '' COMMENT '商户联系电话',
  `sale_code` varchar(30) NOT NULL DEFAULT '' COMMENT '电话匹配销售员工编号',
  `sale_name` varchar(30) NOT NULL DEFAULT '' COMMENT '电话匹配销售姓名',
  `sale_city_id` int NOT NULL DEFAULT '0' COMMENT '电话匹配销售所在城市id',
  `sale_city_name` varchar(30) NOT NULL DEFAULT '' COMMENT '电话匹配销售所在城市',
  `sale_position` varchar(30) NOT NULL DEFAULT '' COMMENT '电话匹配销售所在分组',
  `is_vaild_follow` varchar(10) NOT NULL DEFAULT '' COMMENT '是否有效跟进',
  `detail_info` text COMMENT '详细信息',
  `suspect_reason` text COMMENT '疑似虚假原因(电话相等属于 数据造假; 电话属于多个商户属于 高危信息)',
  `suspect_type` varchar(50) NOT NULL DEFAULT '' COMMENT '疑似虚假类型(销售人员;人力资源登记;联系电话在更多的商户名称下)',
  PRIMARY KEY (`id`),
  KEY `idx_date` (`start_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='疑似虚假行动量(按号码)';



-- 疑似虚假行动量(按商户)-日期
CREATE TABLE `dcp_suspect_false_action_customer_day` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_code` varchar(30) NOT NULL DEFAULT '' COMMENT '用户员工编号',
  `user_name` varchar(30) NOT NULL DEFAULT '' COMMENT '用户姓名',
  `user_city_id` int NOT NULL DEFAULT '0' COMMENT '用户所在城市id',
  `user_city_name` varchar(30) NOT NULL DEFAULT '' COMMENT '用户所在城市',
  `user_position` varchar(30) NOT NULL DEFAULT '' COMMENT '用户所在分组',
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '开始时间',
  `follow_type` varchar(50) NOT NULL DEFAULT '' COMMENT '跟进方式',
  `action_type` varchar(50) NOT NULL DEFAULT '' COMMENT '行动类型',
  `supplier_name` varchar(255) NOT NULL DEFAULT '' COMMENT '商户名称',
  `supplier_cat_id` int NOT NULL DEFAULT '0' COMMENT '商户分类ID',
  `supplier_cat_name` varchar(30) NOT NULL DEFAULT '' COMMENT '商户分类',
  `contact_name` varchar(30) NOT NULL DEFAULT '' COMMENT '联系人',
  `contact_telphone` varchar(30) NOT NULL DEFAULT '' COMMENT '商户联系电话',
  `is_vaild_follow` varchar(10) NOT NULL DEFAULT '' COMMENT '是否有效跟进',
  `detail_info` text COMMENT '详细信息',
  `suspect_reason` text COMMENT '疑似虚假原因(行为异常)',
  `report_date` date NOT NULL DEFAULT '0000-00-00' COMMENT '日期 eg. 2015-01-01',
  PRIMARY KEY (`id`),
  KEY `idx_date` (`report_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='疑似虚假行动量(按商户)-日期';

ALTER TABLE `dcp_suspect_false_action_customer_day` DROP `sale_code`;
ALTER TABLE `dcp_suspect_false_action_customer_day` DROP `sale_name`;
ALTER TABLE `dcp_suspect_false_action_customer_day` DROP `sale_city_id`;
ALTER TABLE `dcp_suspect_false_action_customer_day` DROP `sale_city_name`;
ALTER TABLE `dcp_suspect_false_action_customer_day` DROP `sale_position`;



-- 疑似虚假行动量(按商户)-周数
CREATE TABLE `dcp_suspect_false_action_customer_week` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_code` varchar(30) NOT NULL DEFAULT '' COMMENT '用户员工编号',
  `user_name` varchar(30) NOT NULL DEFAULT '' COMMENT '用户姓名',
  `user_city_id` int NOT NULL DEFAULT '0' COMMENT '用户所在城市id',
  `user_city_name` varchar(30) NOT NULL DEFAULT '' COMMENT '用户所在城市',
  `user_position` varchar(30) NOT NULL DEFAULT '' COMMENT '用户所在分组',
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '开始时间',
  `follow_type` varchar(50) NOT NULL DEFAULT '' COMMENT '跟进方式',
  `action_type` varchar(50) NOT NULL DEFAULT '' COMMENT '行动类型',
  `supplier_name` varchar(255) NOT NULL DEFAULT '' COMMENT '商户名称',
  `supplier_cat_id` int NOT NULL DEFAULT '0' COMMENT '商户分类ID',
  `supplier_cat_name` varchar(30) NOT NULL DEFAULT '' COMMENT '商户分类',
  `contact_name` varchar(30) NOT NULL DEFAULT '' COMMENT '联系人',
  `contact_telphone` varchar(30) NOT NULL DEFAULT '' COMMENT '商户联系电话',
  `is_vaild_follow` varchar(10) NOT NULL DEFAULT '' COMMENT '是否有效跟进',
  `detail_info` text COMMENT '详细信息',
  `suspect_reason` text COMMENT '疑似虚假原因(行为异常)',
  `report_week` int NOT NULL DEFAULT '0' COMMENT '年份周数 eg. 201501',
  PRIMARY KEY (`id`),
  KEY `idx_date` (`report_week`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='疑似虚假行动量(按商户)-周数';

ALTER TABLE `dcp_suspect_false_action_customer_week` DROP `sale_code`;
ALTER TABLE `dcp_suspect_false_action_customer_week` DROP `sale_name`;
ALTER TABLE `dcp_suspect_false_action_customer_week` DROP `sale_city_id`;
ALTER TABLE `dcp_suspect_false_action_customer_week` DROP `sale_city_name`;
ALTER TABLE `dcp_suspect_false_action_customer_week` DROP `sale_position`;



-- 疑似虚假行动量(按商户)-月份
CREATE TABLE `dcp_suspect_false_action_customer_month` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_code` varchar(30) NOT NULL DEFAULT '' COMMENT '用户员工编号',
  `user_name` varchar(30) NOT NULL DEFAULT '' COMMENT '用户姓名',
  `user_city_id` int NOT NULL DEFAULT '0' COMMENT '用户所在城市id',
  `user_city_name` varchar(30) NOT NULL DEFAULT '' COMMENT '用户所在城市',
  `user_position` varchar(30) NOT NULL DEFAULT '' COMMENT '用户所在分组',
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '开始时间',
  `follow_type` varchar(50) NOT NULL DEFAULT '' COMMENT '跟进方式',
  `action_type` varchar(50) NOT NULL DEFAULT '' COMMENT '行动类型',
  `supplier_name` varchar(255) NOT NULL DEFAULT '' COMMENT '商户名称',
  `supplier_cat_id` int NOT NULL DEFAULT '0' COMMENT '商户分类ID',
  `supplier_cat_name` varchar(30) NOT NULL DEFAULT '' COMMENT '商户分类',
  `contact_name` varchar(30) NOT NULL DEFAULT '' COMMENT '联系人',
  `contact_telphone` varchar(30) NOT NULL DEFAULT '' COMMENT '商户联系电话',
  `is_vaild_follow` varchar(10) NOT NULL DEFAULT '' COMMENT '是否有效跟进',
  `detail_info` text COMMENT '详细信息',
  `suspect_reason` text COMMENT '疑似虚假原因(行为异常)',
  `report_month` varchar(10) NOT NULL DEFAULT '' COMMENT '月份 eg. 2015-01',
  PRIMARY KEY (`id`),
  KEY `idx_date` (`report_month`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='疑似虚假行动量(按商户)-月份';

ALTER TABLE `dcp_suspect_false_action_customer_month` DROP `sale_code`;
ALTER TABLE `dcp_suspect_false_action_customer_month` DROP `sale_name`;
ALTER TABLE `dcp_suspect_false_action_customer_month` DROP `sale_city_id`;
ALTER TABLE `dcp_suspect_false_action_customer_month` DROP `sale_city_name`;
ALTER TABLE `dcp_suspect_false_action_customer_month` DROP `sale_position`;



-- 网店通渠道代理商
CREATE TABLE `dcp_wdt_channel_info` (
  `chance_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '渠道ID',
  `customer_name` varchar(50) NOT NULL DEFAULT '' COMMENT '公司名称',
  `customer_city_id` int NOT NULL DEFAULT '0' COMMENT '公司所在城市id',
  `customer_city_name` varchar(30) NOT NULL DEFAULT '' COMMENT '公司所在城市',
  `customer_url` varchar(500) NOT NULL DEFAULT '' COMMENT '公司网址',
  `contacter_name` varchar(20) NOT NULL DEFAULT '' COMMENT '联系人姓名',
  `contacter_mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '手机',
  `contacter_phone` varchar(30) NOT NULL COMMENT '办公电话',
  `contacter_fax` varchar(30) NOT NULL DEFAULT '' COMMENT '传真',
  `contacter_email` varchar(30) NOT NULL DEFAULT '' COMMENT 'email',
  `found_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '成立时间',
  `legal_person` varchar(30) NOT NULL DEFAULT '' COMMENT '法人代表',
  `register_money` varchar(255) NOT NULL DEFAULT '' COMMENT '注册资金',
  `register_addr` varchar(500) NOT NULL DEFAULT '' COMMENT '注册地址',
  `customer_category` varchar(255) NOT NULL DEFAULT '' COMMENT '公司性质',
  `work_addr` text COMMENT '经营地址',
  `service_detail` text COMMENT '公司业务描述',
  `source` varchar(35) NOT NULL DEFAULT '商家入驻' COMMENT '数据来源：(商家入驻，网店通合作，网店通渠道，提供团购信息，手机客户端）',
  `create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  PRIMARY KEY (`chance_id`),
  KEY `idx_customer_city` (`customer_city_id`),
  KEY `idx_create_time` (`create_time`),
  KEY `idx_source` (`source`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='网店通渠道代理商';



-- 短信发送数据
CREATE TABLE `report_send_msg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL COMMENT '日期',
  `type` varchar(30) DEFAULT '' COMMENT '发送短信类型',
  `type_name` varchar(200) DEFAULT '' COMMENT '发送短信类型名称',
  `send_nums` int(11) DEFAULT '0' COMMENT '发送数量',
  PRIMARY KEY (`id`),
  KEY `d_idx` (`date`) USING HASH
) ENGINE=MyISAM DEFAULT CHARSET=utf8

