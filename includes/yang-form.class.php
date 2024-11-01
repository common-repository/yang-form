<?php
/**
 * $Id: yang-form.class.php 2012-06-28 12:59:55 yang $
 **/
if (!defined('ABSPATH')){
	header('HTTP/1.1 403 Forbidden', true, 403);
	die('Please do not load this page directly. Thanks!');
}

class yangform{
	const textdomain = 'yang-form';
	const version = '1.1';

	private static $_instance = null;
	
	private static $yf_data= array();
	private static $yf_pro	= array();
	private static $yf_show	= array();

	
	//NO 默认值
	private $_opts = array(
		'yangform_sites' => '',
		'yangform_template' => array('', ''),
		'yangform_template_style' => '',

		'yangform_options' => array(
			'yangform_permission' => 0,
		),
	);


	/**OK
	 *	定义数据库相关表名	
	 **/
	function __construct(){
		global $wpdb;
		$wpdb->yang_form = $wpdb->prefix . 'yang_form';
		$wpdb->yang_form_items = $wpdb->prefix . 'yang_form_items';
		$wpdb->yang_form_data = $wpdb->prefix . 'yang_form_data';
		
		$all_keys = self::get_opt_keys();
		foreach ((array) $all_keys as $key){
			$this->_opts[$key] = get_option($key);
		}
	}

	/**OK
	 * singleton
	 * instanceof 用于确定一个 PHP 变量是否属于某一类 class 的实例 http://www.php.net/instanceof
	 * @return type
	 */
	public static function instance(){
		if (!(self::$_instance instanceof yangform)){
			self::$_instance = new yangform();//实例化类
		}
		return self::$_instance;
	}
	
	/**OK
	 */
	public static function init(){
		$yangform = yangform::instance();
		
		//语言文件
		//add_action('init', 'yangform::load_textdomain');

		//installation安装启用插件时执行操作，在yang-form.php文件中执行函数
		add_action('activate_yang-form/yang-form.php', array($yangform, 'creat_yang_form_table'));
		//关闭插件
		add_action('deactivate_yang-form/yang-form.php', array($yangform, 'del_yang_form_table'));

		//添加管理菜单
		add_action('admin_menu', array($yangform, 'add_form_manage_menu'));

		//添加后台样式
		add_action('admin_print_styles', 'yangform::back_style');

		//add_action('admin_print_styles', array($yangform, 'enqueue_backend_css'));
		//add_action('admin_print_footer_scripts', array($yangform, 'print_backend_js'));
		//add footer js
		add_action('admin_footer-post-new.php', 'yangform::yangform_tinymce_button_js');
		add_action('admin_footer-post.php', 'yangform::yangform_tinymce_button_js');
		
		// add editor button
		//add_action('media_buttons', array($yangform, 'add_media_button'), 20);
		add_action('init', array($yangform, 'tinymce_addbuttons'));

		//add rewrite rule
		//add_filter('query_vars', 'yangform::add_attachment_query_vars');
		//add_filter('generate_rewrite_rules', array($yangform, 'attachment_rewrite_rule'));
		// do sutff
		//add_action('template_redirect', array($yangform, 'download_file'), 5);

		//add_filter('favorite_actions', 'yangform::favorite_actions');

		/** 为一个简码标签增加一个勾子
		 * 用法 add_shortcode( $tag , $func );
		 * 参数 $tag (字符串) (必须) 需要在文章内容中查找的简码标签
		 *		$func (字符串) (必须) 找到时需要运行的勾子函数
		 **/
		add_shortcode('yang-form', array($yangform, 'yangform_shortcode'));


		//register the js first
		//add_action('init', 'yangform::register_front_js');
		//add_action('wp_footer', array($yangform, 'print_front_js'));
		/*
		 * add popup effect css
		 * register with hook 'wp_print_styles'
		 */
		//add_action('wp_print_styles', array($yangform, 'enqueue_css'), -999);
		/**
		 * add user custom css
		 * this ensure our custom css can override the default one
		 */
		//add_action('wp_head', array($yangform, 'print_custom_stylesheet'), 999);
		
		//for create table attachment_post
		//add_action('admin_notices', array(__CLASS__, 'check_table'));//全局提示，插件升级提示
		
		//when post deleted,deattach the relationship
		//add_action('trash_post',array($yangform, 'deattach_post'));//文章加入回收站时，执行函数 deattach_post()
		//add_action('deleted_post',array($yangform, 'deattach_post'));//删除文章后，执行函数 deattach_post()
		//add_action('save_post', array($yangform, 'update_post_forminfo'));//保存文章时，执行函数：update_post_forminfo()
	}

	//OK 读取表 wp_options 设置信息，可设默认值
	public function get_opt($name, $default = ''){
		$ret = null;
		$ret = !empty($this->_opts[$name]) ? $this->_opts[$name] : $default;
		return $ret;
	}


	//重写 $_GET[]
	public static function get($key, $default = 0){
		return isset($_GET[$key]) ? $_GET[$key] : $default;
	}

	//重写 $_POST[]
	public static function post($key, $default = ''){
		return isset($_POST[$key]) ? $_POST[$key] : $default;
	}

	public static function add_message($msg){
		self::$_message .= '<span style="color:#4e9a06;">' . $msg . '</span><br />';
	}

	public static function show_message_or_error($echo = 1){
		$have_msg = !empty(self::$_message) || !empty(self::$_error);
		$message = $have_msg ? '<!-- Last Action --><div id="message" class="updated fade"><p>' : '';
		if (!empty(self::$_message)){
			$message .= self::$_message;
		}

		if (!empty(self::$_error)){
			$message .= self::$_error;
		}
		$message = $have_msg ? $message . '</p></div>' : '';
		if ($echo)
			echo $message;
		else
			return $message;
	}

	/**待修改
	 * by yang:获取该插件在保存在数据库里的信息，以备卸载插件时使用
	 * 参数：
	 *		$t：数据库表
	 *			1-wp_options表设置信息，返回 option_name 字段
	 *			2-wp_postmeta表附加到信息，返回 meta_key 字段
	 *			3-wp_options表角色权限信息，返回 option_name 字段
	 **/
	public static function get_opt_keys( $t=1 ){
		if ( is_numeric($t) ){
			if( $t==1 ){//wp_options表
				$keys = array(
					'yangform_template',
					'yangform_sites',
					'yangform_template_style',
					'yangform_options',
				);
			}
			elseif( $t==2 ){
				$keys = array(
					'yang_attached_id',
				);
			}
			elseif( $t==3 ){
				$keys = array(
					'yang_att_manage',
					'yang_att_add',
					'yang_att_del',
					'yang_att_trash',
				);
			}
		}
		return $keys;
	}

	/**OK 创建表，并添加默认选项
	 * Doing：form_nums列可以取消
	 * Create Table and Add Default Options
	 * @global type $wpdb
	 */
	public function creat_yang_form_table(){
		global $wpdb;
		//$this->load_textdomain();

		if (@is_file(ABSPATH . '/wp-admin/includes/upgrade.php')){
			include_once (ABSPATH . '/wp-admin/includes/upgrade.php');
		} elseif (@is_file(ABSPATH . '/wp-admin/upgrade-functions.php')){
			include_once (ABSPATH . '/wp-admin/upgrade-functions.php');
		} else {
			wp_die(__('We have problem finding your \'/wp-admin/upgrade-functions.php\' and \'/wp-admin/includes/upgrade.php\'', self::textdomain));
		}

		$charset_collate = '';
		if ($wpdb->supports_collation()){
			if (!empty($wpdb->charset)){
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if (!empty($wpdb->collate)){
				$charset_collate .= " COLLATE $wpdb->collate";
			}
		}
		
		// Create Table
		$create_table1 = "CREATE TABLE $wpdb->yang_form (
				form_id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
				form_title char(60) NOT NULL COMMENT '表单标题',
				form_start_time date NOT NULL COMMENT '有效时间',
				form_end_time date NOT NULL COMMENT '有效时间',
				form_nums mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '提交表单数量',
				form_add_time datetime NOT NULL COMMENT '添加时间',
				form_intro text NOT NULL COMMENT '表单介绍',
				form_info_show tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '表单信息是否显示',
				status tinyint(1) unsigned NOT NULL DEFAULT '1',
				PRIMARY KEY (form_id)) $charset_collate;";
		//var_dump($wpdb);exit;
				
		$create_table2 = "CREATE TABLE $wpdb->yang_form_items (
				item_id mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '项目id',
				form_id mediumint(8) unsigned NOT NULL COMMENT '表单id',
				item_title char(255) NOT NULL COMMENT '项目标题',
				item_type char(10) NOT NULL COMMENT '项目类型',
				item_rule char(255) NOT NULL COMMENT '项目规则',
				item_ext char(255) NOT NULL,
				item_size smallint(4) unsigned NOT NULL,
				item_list text NOT NULL COMMENT '项目选项',
				item_must tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '必选',
				item_hidden mediumint(8) unsigned NOT NULL DEFAULT '0',
				item_sort smallint(4) unsigned NOT NULL DEFAULT '0' COMMENT '项目顺序',
				item_add_time datetime NOT NULL,
				status tinyint(3) unsigned NOT NULL DEFAULT '1',
				PRIMARY KEY (item_id),
				KEY item_sort (item_sort),
				KEY form_id (form_id)
			) $charset_collate;		
		";
		
		$create_table3 = "CREATE TABLE $wpdb->yang_form_data (
				data_id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
				form_id mediumint(8) unsigned NOT NULL,
				data_info text NOT NULL,
				addtime datetime NOT NULL,
				addip char(15) NOT NULL,
				data_status tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '表单数据是否已处理',
				PRIMARY KEY (data_id),
				KEY form_id (form_id)
			) $charset_collate;
		";

		maybe_create_table($wpdb->yang_form, $create_table1);
		maybe_create_table($wpdb->yang_form_items, $create_table2);
		maybe_create_table($wpdb->yang_form_data, $create_table3);
		

		// Default Options
		add_option('yangform_sites', self::get_default_value('yangform_sites'));
		add_option('yangform_options', self::get_default_value('yangform_options'));
		add_option('yangform_template_style', self::get_default_value('yangform_template_style'));
		add_option('yangform_template', self::get_default_value('yangform_template'));
	}

	/* OK 关闭插件，清理数据 */
	public function del_yang_form_table(){
		global $wpdb;
		//$this->load_textdomain();
		delete_option('yangform_template');
		delete_option('yangform_sites');
		delete_option('yangform_template_style');
	}

	//OK 默认设置
	//Doing：待添加插件设置页面
	public static function get_default_value($key){
		$ret = null;
		switch ($key){
			//表单模板
			case 'yangform_template':
				$ret = array(
					'<div id="yang_form" class="yang_form grid col-620 "> </div>
					<link rel="stylesheet" href="/wp-content/plugins/yang-form/front/yang_form.css" type="text/css">
					<script type="text/javascript" src="/wp-content/plugins/yang-form/images/script_calendar.js"></script>
					<script type="text/javascript" src="/wp-content/plugins/yang-form/front/script_form.js"></script>
					<script type="text/javascript">
					$(function() {
						load_from(%form_id%,"yang_form");
						loadcalendar();
					});
					</script>','<div style="color:#ff0000;padding:10px;">友情提示：请登录后刷新本页面，再填写表单！</div>');
				break;
			case 'yangform_template_style':
				$ret = '这里是样式，暂时还不能后台编辑！！下个版本会有改进！！yangjunwei.com';
				break;
			case 'yangform_sites':
				$current_url = $_SERVER['SERVER_NAME'];
				$ret = 'yangjunwei.com,nuodou.com,'.$current_url;
				break;
			case 'yangform_options':
				$ret = array('yangform_permission' => 0);
				break;
		}
		return $ret;
	}

	/**OK 为一个简码标签增加一个勾子
	 *  Function: Short Code For Inserting Form Into Posts
	 **/
	public function yangform_shortcode($atts){
		//in last line of shortcodes : add_filter('the_content', 'do_shortcode', 11); 
		// so the shortcode is trigger before wp_footer
		//self::$_add_js = TRUE;

		extract(shortcode_atts(array('id' => '0', 'display' => 'both'), $atts));
		if( !is_feed() ){
			if( $id != '0' ){
				return $this->yangform_embedded($id, $display);
			} else {
				return '';
			}
		} else {
			return sprintf(__('Note: There is a form embedded within this post, please visit <a href="%s">this post</a> to submit it.', self::textdomain), get_permalink());
		}
	}

	/**OK - Doing 有待支持一页面调用多表单 id = 1,2,3,4
	 * 前台模板替换
	 * @global type $wpdb
	 * @global type $user_ID
	 * @param string $condition
	 * @param type $display
	 * @return type 
	 */
	private function yangform_embedded( $id = '' ){
		global $wpdb;
		$output = '';
		$condition = '1=0';
		$id = addslashes($id);
		if (strpos($id, ',') !== false){
			$condition = "form_id IN ($id)";
		} else {
			$id = (int) $id;
			$condition = "form_id = $id";
		}
		$condition .= ' AND ';

		$form2 = $wpdb->get_results("SELECT * FROM $wpdb->yang_form WHERE $condition status=1");//var_dump($form2);exit;
		if( $form2 ){
			$form_front_template = $this->get_opt('yangform_template');

			$form_front_template = stripslashes($form_front_template [0]);
			
			$form_front_template = str_replace("%form_id%", number_format_i18n($id), $form_front_template);
			$output .= $form_front_template;
			return apply_filters('yangform_embedded', $output);
		} else {
			$maybe_deleted = sprintf(__('<div style="color:#FF0000;"><strong>Yang Form : </strong> The Form maybe has been deleted (Form ID:%s).</div>', self::textdomain), $id);
			return $maybe_deleted;
		}
	}




	/**OK
	 * 添加后台CSS样式
	 * Add Form Administration Menu
	 */
	public static function back_style(){
		wp_enqueue_style('yangwf-style', plugins_url('yang-form/images/yangwf-style.css'), false, '1.0.0', 'all');
	}


	/**OK
	 * 添加后台左侧导航栏管理按钮
	 * Add Form Administration Menu
	 * add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
	 * add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
	 */
	public function add_form_manage_menu(){
		if (function_exists('add_menu_page')){
			add_menu_page('表单 yangjunwei.com', '表单', '7', 'yang-form/yang-form-list.php', '', plugins_url('yang-form/images/yang_form-18.png') );
		}
		if (function_exists('add_submenu_page')){

			add_submenu_page('yang-form/yang-form-list.php', __('表单管理', 'yang-form'), __('表单管理', 'yang-form'), '7', 'yang-form/yang-form-list.php');
			add_submenu_page('yang-form/yang-form-list.php', __('表单 项目', 'yang-form'), __('新建表单', 'yang-form'), '7', 'yang-form/yang-form-do.php');
			add_submenu_page('yang-form/yang-form-list.php', __('表单数据', 'yang-form'), __('数据管理', 'yang-form'), '7', 'yang-form/yangform-data.php');
			//add_submenu_page('yang-form/yang-form-list.php', __('选项设置', 'yang-form'), __('选项设置', 'yang-form'), '7', 'yang-form/yang-options.php');			
			add_submenu_page('yang-form/yang-form-list.php', __('表单使用帮助 yangjunwei.com', 'yang-form'), __('使用帮助', 'yang-form'), '7', 'yang-form/help.php');
			//add_submenu_page('yang-form/yang-form-list.php', __('卸载插件', 'yang-form'), __('用户信息', 'yang-form'), '7', 'yang-form/uninstall.php');
		}
	}


	/**OK
	 * 判断当前登录的用户级别及权限
	 * 返回用户级别 $user_level
	 */
	public function yang_get_current_user(){
		$current_user = get_userdata(get_current_user_id());
		$user_level = intval($current_user->user_level);
		
		return $user_level;
	}


	/**OK
	 * 读取数据库中已报名的人数
	 * @param type $fid
	 * 返回 $user_level
	 */
	public function yang_get_form_nums( $fid ){
		global $wpdb;
		$form_count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM $wpdb->yang_form_data where form_id=$fid") );
		
		return $form_count;
	}

	
	/**OK
	 * TinyACE编辑器扩展 “插入表单” 按钮 [yang-form id="13"]
	 * Displays Download Manager Footer  js In WP-Admin
	 * js_escape is deprecated in wp 2.8
	 * this code use the new Quicktags API function,@see quicktags.dev.js line 274
	 * TinyACE编辑器添加自定义按钮 http://yangjunwei.com/a/599.html
	 **/
	public static function yangform_tinymce_button_js(){
		echo '<script type="text/javascript">' . "\n";
		echo "\t" . 'var yangformEdL10n = {' . "\n";
		echo "\t\t" . 'enter_form_id: "' . esc_js(__('输入表单ID', self::textdomain)) . '",' . "\n";
		echo "\t\t" . 'yangform: "' . esc_js(__('表单', self::textdomain)) . '",' . "\n";
		echo "\t\t" . 'insert_form: "' . esc_js(__('插入表单ID', self::textdomain)) . '",' . "\n";
		echo "\t" . '};' . "\n";
		//插入表单
		echo "\t" . 'function insertform(where) {' . "\n";
		echo "\t\t" . 'var form_id = jQuery.trim(prompt(yangformEdL10n.enter_form_id));' . "\n";
		echo "\t\t" . 'if(form_id == null || form_id == "") {' . "\n";
		echo "\t\t\t" . 'return;' . "\n";
		echo "\t\t" . '} else {' . "\n";
		echo "\t\t\t" . 'if(where == "code") {' . "\n";
		echo "\t\t\t\t" . 'QTags.insertContent("[yang-form id=\"" + form_id + "\"]");' . "\n";
		echo "\t\t\t" . '} else {' . "\n";
		echo "\t\t\t\t" . 'return "[yang-form id=\"" + form_id + "\"]";' . "\n";
		echo "\t\t\t" . '}' . "\n";
		echo "\t\t" . '}' . "\n";
		echo "\t" . '}' . "\n";
		echo "\t" . 'if(document.getElementById("ed_toolbar")){' . "\n";
		echo "\t\t" . 'QTags.addButton( "ed_YangForm", yangformEdL10n.yangform ,function () { insertform(\'code\');},"","",yangformEdL10n.insert_form );' . "\n";
		echo "\t" . 'yangformEdL10n.insert_form' . "\n";
		echo "\t" . '}' . "\n";
		echo '</script>' . "\n";
	}

	/**
	 * 在TinyMCE编辑器中添加快速插入下载 id 按钮
	 * Add Quick Tag For Poll In TinyMCE >= WordPress 2.5
	 * @return type 
	 */
	public function tinymce_addbuttons(){
		if (!current_user_can('edit_posts') && !current_user_can('edit_pages')){
			return;
		}
		if (get_user_option('rich_editing') == 'true'){
			add_filter("mce_external_plugins", 'yangform::tinymce_addplugin');
			add_filter('mce_buttons', 'yangform::tinymce_registerbutton');
		}
	}

	/**
	 * used by tinymce_addbuttons
	 * @param type $buttons
	 * @return type 
	 */
	public static function tinymce_registerbutton($buttons){
		array_push($buttons, 'separator', 'YangForm');
		return $buttons;
	}

	/**
	 * used by tinymce_addbuttons
	 * @param array $plugin_array
	 * @return type 
	 */
	public static function tinymce_addplugin($plugin_array){
		$plugin_array ['YangForm'] = plugins_url('yang-form/tinymce/plugins/YangForm/editor_plugin.js');
		return $plugin_array;
	}












	/**
	 * 从文章内容中提取 [yang-form 包含的表单ID，将在保存文章时使用
	 * get download IDs from post content
	 * @param type $content
	 * @return type 
	 */
	public static function get_form_ids($content){
		$ids = '';
		//搜索 $content 中所有与 [yang-form ... 匹配的内容，按顺序放在 $matches 数组中
		if (preg_match_all("@\[yang-form(\s+)id=\"([0-9,\s]+)\"\]@", $content, $matches)){
			$ids = implode(',', $matches[2]);//将 ID 以逗号隔开
		}
		return $ids;
	}

	//保存文章时，执行函数：update_post_forminfo()，更新数据库表 attachment_post
	public function update_post_forminfo($post_ID){
		$post = get_post($post_ID);//通过文章id，返回文章信息
		//var_dump($post);
		$ids = $this->get_form_ids($post->post_content);//查看正文里是否包含 [yang-form id=""] 标签
		//var_dump($ids);exit;
		if(!empty($ids)){
			$id_arr = explode(',', $ids);
			foreach($id_arr as $id){
				if( $this->file_id_exists($id)){
					$this->attach_post($id, $post_ID);
				}
			}
		}
	}

	/* 过滤 */
	public static function js_fix($text_for_js){
		$text_for_js = preg_replace('/&#(x)?0*(?(1)27|39);?/i', "'", stripslashes($text_for_js));
		$text_for_js = str_replace("\r", '', $text_for_js);
		$text_for_js = str_replace("\n", '\\n', addslashes($text_for_js));
		return $text_for_js;
	}


}

// end class
