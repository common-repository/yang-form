<?php
/**
 * $Id: yang-files-options.php 2012-05-12 13:04:47 haibor $
 */
if (!defined('ABSPATH')){
	header('HTTP/1.1 403 Forbidden', true, 403);
	die('Please do not load this page directly. Thanks!');
}

//load the admin class
//require plugin_dir_path(__FILE__) . '/includes/yang-form.class.php';
//yangform::init();

//require_once(ABSPATH . 'wp-admin/admin-header.php');
### Variables Variables Variables
$base_name = plugin_basename('yang-form/yang-options.php');
$base_page = 'admin.php?page=' . $base_name;

//global $wp_rewrite;

if (isset($_POST['Submit'])){
	$yangform_sites	= trim(yangform::post('yangform_sites'));
	$yangform_permission = trim(yangform::post('yangform_permission'));
	$yangform_template_style = trim( yangform::post('yangform_template_style') );
	$yangform_template [] = trim(yangform::post('yangform_template'));
	$yangform_template [] = trim(yangform::post('yangform_template_2'));
	
	$yangform_options = array(
		'yangform_permission' => $yangform_permission,
	);
	

	$update_tips = array();
	$update_tip_text = array();

	$yangform_sites [] = update_option('yangform_sites', $yangform_sites);
	//$yangform_permission [] = update_option('yangform_sites_url', untrailingslashit($yangform_permission));
	$update_tips [] = update_option('yangform_options', $yangform_options);
	$update_tips [] = update_option('yangform_template_style', $yangform_template_style);
	$update_tips [] = update_option('yangform_template', $yangform_template);
	
	$update_tip_text [] = __('许可域名列表', yangform::textdomain);
	$update_tip_text [] = __('表单设置 (是否登录可见、等)', yangform::textdomain);
	$update_tip_text [] = __('前端表单样式CSS', yangform::textdomain);
	$update_tip_text [] = __('前端表单模板', yangform::textdomain);
	
	$i = 0;
	foreach ($update_tips as $update_tip){
		if ($update_tip){
			yangform::add_message($update_tip_text [$i] . ' ' . __('更新：', yangform::textdomain));
		}
		$i++;
	}
}
/*
$yangform_sites = yangform::get_opt('yangform_sites');
$yangform_options = yangform::get_opt('yangform_options');
$yangform_template_style = yangform::get_opt('yangform_template_style');
$yangform_template = yangform::get_opt('yangform_template');
*/
$yangform_sites = get_option('yangform_sites');
$yangform_options = get_option('yangform_options');
$yangform_template_style = get_option('yangform_template_style');
$yangform_template = get_option('yangform_template');


yangform::show_message_or_error();

?>


<form method="post" action="<?php echo $_SERVER ['PHP_SELF']; ?>?page=<?php echo plugin_basename(__FILE__); ?>">
	<div class="wrap">
		<?php screen_icon('yang-form'); ?>
		<h2><?php _e('表单设置', yangform::textdomain); ?></h2>
		<h3><?php _e('表单设置', yangform::textdomain); ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th><?php _e('许可域名列表', yangform::textdomain); ?></th>
				<td>
					<input type="text" name="yangform_sites" value="<?php echo stripslashes(get_option('yangform_sites')); ?>" size="50" dir="ltr" /><br />
					<?php _e('允许前端调用表单信息的域名列表，多个域名用英文逗号隔开，如yangjunwei.com,nuodou.com', yangform::textdomain); ?></td></tr>
			<tr valign="top">
				<th><?php _e('是否登录可见', yangform::textdomain); ?></th>
				<td><select name="yangform_permission" size="1">
						<option value="0" <?php selected('0', $yangform_options['yangform_permission']); ?>><?php _e('任何人可见', yangform::textdomain); ?></option>
						<option value="1" <?php selected('1', $yangform_options['yangform_permission']); ?>><?php _e('登录后可见', yangform::textdomain); ?></option>
					</select> <br />
					<?php _e('<strong>是否登录后才能查看登录并提交.</strong>', yangform::textdomain); ?>
				</td></tr>
			<tr valign="top">
				<th><?php _e('前端表单样式CSS', yangform::textdomain); ?>
					<input type="button" name="RestoreDefault" value="<?php _e('重置为默认模板样式', yangform::textdomain); ?>" onclick="reset_default_info('reset_style');" class="button" />
				</th>
				<td>	
					<textarea name="yangform_template_style" id="yangform_template_style" cols="80" rows="10">
						<?php echo htmlspecialchars(stripslashes($yangform_template_style)); ?>
					</textarea>
				</td></tr>
		</table>
		
		<h3><?php _e('前端表单模板 (有权限 查看表单)', yangform::textdomain);?></h3>
		<table class="form-table" id="table_yangform_template">
			<tr valign="top">
				<td width="30%"><strong><?php _e('表单模板', yangform::textdomain);?></strong><br />
				<?php _e('当文章或页面中嵌入了表单并且用户有权限查看时显示表单。', yangform::textdomain);?><br />
				<br />
				<?php _e('可用的变量', yangform::textdomain);?><br />
					** %form_id% - 表单ID<br />
					** %form%<br />
					<br />
					<input type="button" name="RestoreDefault" value="<?php _e('重置为默认模板', yangform::textdomain); ?>" onclick="reset_default_info('reset_template');" class="button" /></td>
				<td><textarea cols="80" rows="20" id="yangform_template" name="yangform_template"><?php echo htmlspecialchars(stripslashes($yangform_template [0])); ?></textarea></td>
			</tr>
		</table>
		
		<h3><?php _e('前端表单模板 (无权限 查看表单))', yangform::textdomain); ?></h3>
		<table class="form-table" id="table_yangform_template_2">
			<tr valign="top">
				<td width="30%"><strong><?php _e('Download Template', yangform::textdomain); ?></strong><br />
				<?php _e('当文章或页面中嵌入了表单并且用户没有权限查看时显示提醒。', yangform::textdomain); ?><br />
				<br />
				<?php _e('可用的变量', yangform::textdomain);?><br />
					** %form_id% - 表单ID<br />
					** %form%<br />
					<br />
					<input type="button" name="RestoreDefault" value="<?php _e('重置为默认模板', yangform::textdomain); ?>" onclick="reset_default_info('reset_template_2');" class="button" />
				</td>
				<td><textarea cols="80" rows="20" id="yangform_template_2" name="yangform_template_2"><?php echo htmlspecialchars(stripslashes($yangform_template [1])); ?></textarea></td>
			</tr>
		</table>
			
		<p class="submit" align="center"><input type="submit" name="Submit" class="button" value="<?php _e('保 存', yangform::textdomain); ?>" /></p>
	</div>
</form>


<?php
$yangform_template_default = yangform::get_default_value('yangform_template');
$yangform_template_style_default = yangform::get_default_value('yangform_template_style');
?>
<script type="text/javascript">
	/* <![CDATA[*/
	//重置为默认数据
	function reset_default_info(template) {
		var default_template;
		switch(template) {
			case "reset_template":
				default_template = "<?php echo yangform::js_fix($yangform_template_default[0]); ?>";
				break;
			case "reset_template_2":
				default_template = "<?php echo yangform::js_fix($yangform_template_default[1]); ?>";
			case 'reset_style':
				default_template = "<?php echo yangform::js_fix($yangform_template_style_default); ?>";
				break;
		}
		jQuery("#attachment_template_" + template).val(default_template);
	}
	/* ]]> */
</script>
