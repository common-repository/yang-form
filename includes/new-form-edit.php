<?php
/*****************************/
/*	author:zetd@vip.sina.com */
/*	date:2012/03/20          */
/*	表单                     */
/*****************************/


if ( !defined('ABSPATH') ){
	die('-1');
}

//require_once(ABSPATH . 'wp-admin/admin-header.php');


//处理表单
if( isset($_POST["isdo"]) == "doform" ){
	
	//require_once('form-items-config.php');
	$yf_data	= array();
	$yf_pro		= array();
	$yf_show		= array();
	function check_yf_data(){
		global $yf_data, $yf_pro, $yf_show,$themes_allowed_tags;
		$yf_data['fid'] = isset($_POST['fid']) ? intval($_POST['fid']):0;
		$yf_data['form_info_show']=isset($_POST['form_info_show'])?intval($_POST['form_info_show']):0;
		$yf_data['t']=isset($_POST['t'])?wp_kses(trim($_POST['t']),$themes_allowed_tags):'';
		$yf_data['form_title']=isset($_POST['form_title'])?wp_kses(trim($_POST['form_title']),$themes_allowed_tags):'';
		$yf_data['form_start_time']=isset($_POST['form_start_time'])?wp_kses(trim($_POST['form_start_time']),$themes_allowed_tags):'';
		$yf_data['form_end_time']=isset($_POST['form_end_time'])?wp_kses(trim($_POST['form_end_time']),$themes_allowed_tags):'';
		$yf_data['form_intro']=isset($_POST['form_intro'])?wp_kses(trim($_POST['form_intro']),$themes_allowed_tags):'';
		$yf_data['form_add_time']=current_time('mysql',true);
	}

	function get_data(){
		global $wpdb;
		//$table_form = $wpdb->prefix."yang_form";//jxqy_yang_form
		//var_dump($table_form);exit;
		
		global $yf_data, $yf_pro, $yf_show;

		if(!$yf_data['form_title']){
			throw new Exception("请输入表单标题");
		}
		if(!$yf_data['form_start_time']){
			throw new Exception("请输入开始时间");
		}
		if(!$yf_data['form_end_time']){
			throw new Exception("请输入结束时间");
		}
		if($yf_data['form_start_time']>=$yf_data['form_end_time']){
			throw new Exception("开始时间不能大于结束时间");
		}
		$form_arr = array(
			'form_title'=>$yf_data['form_title'],
			'form_start_time'=>$yf_data['form_start_time'],
			'form_end_time'=>$yf_data['form_end_time'],
			'form_add_time'=>$yf_data['form_add_time'],
			'form_info_show'=>$yf_data['form_info_show'],
			'form_intro'=>$yf_data['form_intro']
		);

		if(!$yf_data['fid']){
			$res = $wpdb->insert($wpdb->yang_form, (array)$form_arr);
		//var_dump($wpdb->yang_form);exit;

			$fid = $wpdb->insert_id;
			if(!$fid){
				throw new Exception("操作失败");
			} else {
				$yf_data['fid']=$fid;
			}
		} else {
			$res = $wpdb->update( $wpdb->yang_form, $form_arr, array('form_id'=>$yf_data['fid']) );
		}
	}

	try{
		$yf_show['error'] = '0';
		$yf_show['errmsg'] = "";
		
		check_yf_data();
		get_data();
	}

	catch (Exception $e){
		$yf_show['error'] = '1';
		$yf_show['errmsg'] = $e->getMessage();
	}



	function show_pro(){
		global $yf_data, $yf_pro, $yf_show, $config;
		
		if ($yf_data['t'] == "serialize"){
			echo serialize($yf_show);
		}
		elseif ($yf_data['t'] == "json"){
			echo json_encode($yf_show);
		}
		elseif($yf_data['t'] == "jsonp"){
			echo $yf_data['callback'] ."(".json_encode($yf_show).")";
		}
		elseif($yf_data['t'] == "perl"){
			$string = php_to_perl::php_array_to_perl_hash($yf_show);
			$string = mb_convert_encoding($string, "GBK", "UTF-8");
			echo $string;
		}
		elseif($yf_data['t'] == "iframe"){
			echo("<script>parent.callback_error('".json_encode($yf_show["errmsg"])."')</script>");
		}
		else{
			if($yf_show['errmsg']){
				echo $yf_show['errmsg']."　<a href=\"###\" onclick=\"history.back(-1);\">返回</a>";
			} else {
				echo "操作成功！<a href=\"###\" onclick=\"history.back(-1);\">返回</a>　　<a href=\"admin.php?page=yang-form/yang-form-do.php&mode=items&fid=".$yf_data['fid']."\">编辑此表单项目</a>";
			}
		}
		unset($yf_data);
		unset($yf_pro);
		unset($yf_show);
		unset($config);
	}

	show_pro();

	exit;

}

?>


<?
if(!isset($form_start_time)){
	$form_start_time = gmdate('Y-m-d', current_time('timestamp'));
}
if(!isset($form_end_time)){
	$form_end_time = gmdate('Y-m-d', current_time('timestamp')+86400);
}
$form_info_show = isset($form_info_show) ? $form_info_show:1;
?>

<div class="wrap columns-auto" style="width:500px;" id="append_parent">
	<?php screen_icon('yang-form'); ?>
	<h2><?echo $page_title ?></h2>
	<!--<div id="notice" class="error"><p><?php echo $notice ?></p></div>
	<div id="message" class="updated"><p><?php echo $message; ?></p></div>-->
	<form name="post" action="" method="post" id="post"><!-- action="<?php echo WP_PLUGIN_URL ?>/yang-form/includes/new-form-save.php" -->
	<input type="hidden" name="fid" value="<?echo $fid?>">
	<input type="hidden" name="isdo" value="doform" />
	
	<div id="post-body">
		<div id="post-body-content">
			<div id="titlediv">
				<div id="titlewrap">
					<label  for="title">表单名称：</label>
					<input type="text" name="form_title" size="30" tabindex="1" value="<?=isset($form_title)?$form_title:'';?>" id="title" />
					<input type="checkbox" name="form_info_show" id="form_info_show" value="1" <?if($form_info_show==1) echo'checked';?>> 前台是否显示名称和说明
				</div>
			</div>
			<div id="titlediv">
				<div id="titlewrap">
					<label  for="title">表单有效期：</label>
					<input type="text" readonly name="form_start_time" size="30" tabindex="2" id="title" value="<?echo $form_start_time?>"  onclick="showcalendar(event,this,1,'<?echo $form_start_time?>', '<?echo $form_start_time?>')"/>
					<input type="text" readonly name="form_end_time" size="30" tabindex="3" id="title" value="<?echo $form_end_time?>"  onclick="showcalendar(event,this,1,'<?echo $form_end_time?>', '<?echo $form_end_time?>')"/>
				</div>
			</div>
			<div id="postdivrich" class="postarea">
				<label  for="content">表单说明：</label>
				<div id="wp-content-editor-container" class="wp-editor-container"><textarea class="wp-editor-area" rows="10" tabindex="4" cols="40" name="form_intro" id="content"><?=isset($form_intro)?$form_intro:'';?></textarea></div>
			</div>
			<div id="publishing-action">
				<input type="submit" name="publish" id="publish" class="button-primary" value="提 交" tabindex="5" accesskey="p"  />
			</div>

		</div>
	</div>

	</form>
</div>

<script type="text/javascript" src="<?php echo WP_PLUGIN_URL ?>/yang-form/images/script_calendar.js" charset="utf-8"></script>
