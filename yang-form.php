<?php
/*
 Plugin Name: Yang-Form
 Plugin URI: http://www.nuodou.com
 Description: Form.
 Version: 1.0
 Author: haibor
 Author URI: http://yangjunwei.com
 */

/**
 * $Id: yang-files.php 2012-05-01 12:26:29 haibor $
 * @encoding UTF-8 
 * @author haibor
 * @link http://yangjunwei.com
 */

//http://blog.sina.com.cn/s/blog_5939c55701013ktq.html




define('YANGFORM_DIR',__FILE__);
require plugin_dir_path(__FILE__) . '/includes/yang-form.class.php';

yangform::init();







/*//添加后台左侧管理菜单
function add_yang_form_menu() {
	if (function_exists('add_menu_page')){
		add_menu_page('表单管理', 'WP表单', 'administrator', 'yang-form/yang-form-list.php', '', plugins_url('yang-form/images/yang_form-18.png') );
	}
	if (function_exists('add_submenu_page')){
		add_submenu_page('yang-form/yang-form-list.php', __('表单 项目', 'yang-form'), __('新建表单', 'yang-form'), 'administrator', 'yang-form/yang-form-do.php');
	}
}

add_action('admin_menu', 'add_yang_form_menu');
*/


/*//添加CSS样式
function stylesheets_admin(){
	wp_enqueue_style('yangwf-style', plugins_url('yang-form/images/yangwf-style.css'), false, '1.0.0', 'all');
}
//add admin css
add_action('admin_print_styles', 'stylesheets_admin');
*/


