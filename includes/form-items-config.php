<?php
/*****************************/
/*	author:zetd@vip.sina.com */
/*	date:2012/03/20          */
/*	表单条目配置             */
/*****************************/
//定义类型
$item_type_arr = array(
			'info'=>'文本',//文字说明
			'input'=>'输入框',//输入框
			'file'=>'文件上传',//上传
			'textarea'=>'文本框',//文本框
			'select'=>'选择框',//选择框
			'radio'=>'单选',//单选
			'checkbox'=>'多选',//多选
			'sort'=>'排序',//排序
			'hidden'=>'控制隐藏'//控制隐藏
);
//输入框规则
$item_rule_arr = array(
			'normal'=>'普通',
			'mail'=>'邮箱',
			'number'=>'数字',
			'mp'=>'手机',
			'chinese'=>'中文',
			'time'=>'时间'
);
$themes_allowed_tags = array(
	'a' => array(
		'href' => array(),'title' => array()
		),
	'abbr' => array(
		'title' => array()
		),
	'acronym' => array(
		'title' => array()
		),
	'code' => array(),
	'em' => array(),
	'strong' => array()
);

?>
