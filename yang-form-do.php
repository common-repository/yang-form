<?php
/*****************************/
/*	author:zetd@vip.sina.com */
/*	date:2012/03/20          */
/*	表单处理                 */
/*****************************/
require_once(ABSPATH . 'wp-admin/admin.php');
global $wpdb;
//$table_form = $wpdb->prefix."yang_form";


$mode	= isset($_GET["mode"]) ? $_GET["mode"]:"form_edit";
$fid	= isset($_GET['fid'])  ? intval($_GET['fid']):0;


switch( $mode ){
	//表单
	case "form_edit" :
		
		$page_title='新建表单';
		if($fid){
			$page_title='编辑表单';
			$form_info = $wpdb->get_results("SELECT * FROM $wpdb->yang_form where form_id=$fid" );
			if( $form_info ){
				$form_info = get_object_vars($form_info[0]);//返回由对象属性组成的关联数组 http://baike.baidu.com/view/5583308.htm
				foreach($form_info as $key => $value){//php：foreach()用法 http://www.php.net/manual/zh/control-structures.foreach.php
					$$key = $value;
					//echo "Key: $key; Value: $value<br />\n";exit;//看看这个输出什么？
				}
			} else {
				echo "数据读取失败，可能的原因是：此id的表单不存在导致的！<a href=\"admin.php?page=yang-form/yang-form-list.php\">返回表单列表</a>";
				exit;
			}
		}
		// Show post form. var_dump( $form_info );exit;
		require_once('includes/new-form-edit.php');
		
		break;

	//表单项
	case "items" :
		$action	= isset($_GET["action"]) ? $_GET["action"]:"";
		
		if( !$action ){//action为空则显示表单项列表
			$page_title='项目列表';
			if($fid){
				$page_title='编辑表单';
				$form_info = $wpdb->get_results("SELECT * FROM $wpdb->yang_form where form_id=$fid" );
				if( $form_info ){
					$form_info = get_object_vars($form_info[0]);//返回由对象属性组成的关联数组 http://baike.baidu.com/view/5583308.htm
					foreach($form_info as $key => $value){//php：foreach()用法 http://www.php.net/manual/zh/control-structures.foreach.php
						$$key = $value;
						//echo "Key: $key; Value: $value<br />\n";exit;//看看这个输出什么？
					}
				} else {
					echo "数据读取失败，可能的原因是：此id的表单不存在导致的！<a href=\"admin.php?page=yang-form/yang-form-list.php\">返回表单列表</a>";
					exit;
				}
			}
			require_once('includes/form-items-list.php');
		} else {
			if( $action=="item_new" ){
				$page_title='新建项目';
				require_once('includes/form-item-do.php');
			}
			elseif( $action=="item_edit" ){
				$page_title='编辑项目';
				
				$item_id = isset($_GET['item_id']) ? intval($_GET['item_id']):0;
				if($item_id){
					$item_info = $wpdb->get_results("SELECT * FROM $wpdb->yang_form_items where item_id=$item_id and form_id=$fid" );
					if(!$item_info){
						wp_die("项目不存在！");
					}
					$item_info = get_object_vars($item_info[0]);
					foreach($item_info as $key => $value){
						$$key = $value;
					}
				}

				require_once('includes/form-item-do.php');
			}
		}
		
		break;
	
	//用户信息
	case "form_data" :
		
		
		break;

}

