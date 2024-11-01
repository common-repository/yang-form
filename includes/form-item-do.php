<?php
/*****************************/
/*	author:zetd@vip.sina.com */
/*	date:2012/03/20          */
/*	表单项目表单             */
/*****************************/
require_once(ABSPATH . 'wp-admin/admin.php');

if ( !defined( 'ABSPATH' ) ){ 
	//die('-1');
	header( 'HTTP/1.1 403 Forbidden', true, 403 );
	die ('Please do not load this page directly. Thanks!');
}

$parent_file = 'edit.php?post_type=page';
$submenu_file = 'edit.php?post_type=page&page=yang_fp_list';

require_once(ABSPATH . 'wp-admin/admin-header.php');




//处理表单
if( isset($_POST["do_item"]) == "ok" ){

	require_once('form-items-config.php');
	$yf_data 	= array();
	$yf_pro 	= array();
	$yf_show 	= array();

	function check_yf_data(){
		global $yf_data, $yf_pro, $yf_show, $themes_allowed_tags;
		$yf_data['fid'] = isset($_POST['fid']) ? intval($_POST['fid']):0;
		$yf_data['item_id'] = isset($_POST['item_id']) ? intval($_POST['item_id']):0;
		$yf_data['t'] = isset($_POST['t']) ? wp_kses(trim($_POST['t']),$themes_allowed_tags):'';
		$yf_data['item_title'] = isset($_POST['item_title']) ? wp_kses(trim($_POST['item_title']),$themes_allowed_tags):'';
		$yf_data['item_must'] = isset($_POST['item_must']) ? intval($_POST['item_must']):0;
		$yf_data['item_hidden_flag'] = isset($_POST['item_hidden_flag']) ? intval($_POST['item_hidden_flag']):0;
		$yf_data['item_hidden'] = isset($_POST['item_hidden']) ? intval($_POST['item_hidden']):0;
		$yf_data['item_type'] = isset($_POST['item_type']) ? wp_kses(trim($_POST['item_type']),$themes_allowed_tags):'';
		$yf_data['item_rule'] = isset($_POST['item_rule']) ? wp_kses(trim($_POST['item_rule']),$themes_allowed_tags):'';
		$yf_data['item_list'] = isset($_POST['item_list']) ? wp_kses(trim($_POST['item_list']),$themes_allowed_tags):'';
		$yf_data['item_ext'] = isset($_POST['item_ext']) ? wp_kses(trim($_POST['item_ext']),$themes_allowed_tags):'';
		$yf_data['item_size'] = isset($_POST['fid']) ? intval($_POST['fid']):8;
		$yf_data['item_add_time'] = current_time('mysql',true);
		$yf_data['item_sort'] = isset($_POST['item_sort']) ? intval($_POST['item_sort']):0;
		$yf_data['item_list_arr'] = array();
	}

	function get_data(){
		global $wpdb;
		//$table_form_items = $wpdb->prefix."yang_form_items";

		global $yf_data, $yf_pro, $yf_show;
		if(!$yf_data['fid']){
			throw new Exception("无表单ID");
		}
		if(!$yf_data['item_title']){
			throw new Exception("请输入项目标题");
		}
		if($yf_data['item_hidden_flag'] && !$yf_data['item_hidden']){
			throw new Exception("请输入控制项目ID");
		}
		if(!$yf_data['item_type']){
			throw new Exception("请选择项目类型");
		}
		if($yf_data['item_type']=='input' && !$yf_data['item_rule']){
			throw new Exception("请选择输入框规则");
		}
		if($yf_data['item_type']=='file' && !$yf_data['item_ext']){
			throw new Exception("请输入允许上传文件的类型");
		}
		if($yf_data['item_type']=='select' || $yf_data['item_type']=='radio' || $yf_data['item_type']=='checkbox' || $yf_data['item_type']=='sort'){
			$yf_data['item_list']=explode("\r\n",$yf_data['item_list']);
			foreach($yf_data['item_list'] as $value){
				if(trim($value)){
					$yf_data['item_list_arr'][]=$value;
				}
			}
			if(count($yf_data['item_list_arr'])<2){
				throw new Exception("请输入两个或者两个以上的选项");
			}
		}
		$item_arr=array(
			'form_id'=>$yf_data['fid'],
			'item_title'=>$yf_data['item_title'],
			'item_must'=>$yf_data['item_must'],
			'item_hidden'=>$yf_data['item_hidden'],
			'item_type'=>$yf_data['item_type'],
			'item_rule'=>$yf_data['item_rule'],
			'item_list'=>!$yf_data['item_list_arr'] ? '':serialize($yf_data['item_list_arr']),
			'item_ext'=>$yf_data['item_ext'],
			'item_size'=>$yf_data['item_size'],
			'item_sort'=>$yf_data['item_sort'],
			'item_add_time'=>$yf_data['item_add_time']
		);
		//var_dump( $item_arr );exit;
		
		if(!$yf_data['item_id']){
			$res = $wpdb->insert($wpdb->yang_form_items,$item_arr);
			$item_id = $wpdb->insert_id;
			if(!$item_id){
				throw new Exception("操作失败");
			}
		}else{
			$res = $wpdb->update($wpdb->yang_form_items, $item_arr, array('item_id'=>$yf_data['item_id']));
		}
	}

	try{
		$yf_show['error'] = '0';
		$yf_show['errmsg'] = "";
		
		check_yf_data();
		get_data();
	} catch (Exception $e) {
		$yf_show['error'] = '1';
		$yf_show['errmsg'] = $e->getMessage();
	}

	function show_pro(){
		global $yf_data, $yf_pro, $yf_show, $config;
		require_once(ABSPATH . 'wp-admin/admin-header.php');
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
				echo $yf_show['errmsg']." <a href=\"###\" onclick=\"history.back(-1);\">返回</a>";
			} else {
				echo "操作成功！<a href=\"###\" onclick=\"history.back(-1);\">返回</a>　　<a href=\"admin.php?page=yang-form/yang-form-do.php&mode=items&fid=".$yf_data['fid']."\">项目列表</a>";
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











global $wpdb;

$fid=isset($_GET['fid'])?intval($_GET['fid']):0;
if(!$fid){
	wp_die("无表单ID");
}

$item_type=isset($item_type)?$item_type:'';
$item_must=isset($item_must)?$item_must:1;
$item_hidden=isset($item_hidden)?$item_hidden:0;
$item_rule=isset($item_rule)?$item_rule:'normal';
$item_list=isset($item_list)?$item_list:'';
if($item_list){
	$item_list=implode("\r\n",unserialize($item_list));
}
require_once('form-items-config.php');
?>
<script type='text/javascript' src='<?php echo plugins_url('yang-form/images/jquery.min_1.5.2.js');?>'></script>
<div class="wrap columns-auto" style="width:600px;">
	<?php screen_icon('yang-form'); ?>
	<h2><?if($item_id){echo "编辑项目";}else{echo "增加项目";}?><a href="admin.php?page=yang-form/yang-form-do.php&mode=items&fid=<?php echo $fid?>" class="add-new-h2">返回项目列表</a></h2>
	<form action="" method="post">
		<input type="hidden" name="fid" value="<?echo $fid?>">
		<input type="hidden" name="item_id" value="<?echo $item_id?>">
		<input type="hidden" name="do_item" value="ok" />
		
		<div id="post-body">
			<div id="post-body-content">
				<div id="titlediv">
					<div id="titlewrap">
						<label  for="title">项目名称：</label>
						<input type="text" name="item_title" size="30" tabindex="1" value="<?=isset($item_title)?$item_title:'';?>" id="title" />
						<input type="checkbox" name="item_must" id="item_must" value="1" <?if($item_must==1) echo "checked";?>>是否必填
						<input type="checkbox" id="item_hidden_flag" name="item_hidden_flag" onclick="show_item_hidden(this);" value="1" <?if($item_hidden>0) echo "checked";?>>是否隐藏<span id="item_hidden_span" <?if($item_hidden==0) echo "style='display:none;'";?>><input type="text" name="item_hidden" id="item_hidden" value="<?if($item_hidden>0) echo $item_hidden;?>">请输入控制隐藏ID</span>

					</div>
				</div>
				<div id="titlediv">
					<div id="titlewrap">
						<label  for="title">项目类型：</label>
						<?php foreach($item_type_arr as $key=>$value){
							if($item_type==$key){
								$item_type_check='checked';
							} else {
								$item_type_check='';
							}
							echo "<input type='radio' name='item_type' value='$key' $item_type_check onclick='item_show();'>$value";
						} ?>
					</div>
				</div>
				<div id="titlediv" class="item_rule" <?if($item_type!='input'){?>style="display:none;"<?}?>>
					<div id="titlewrap">
						<label for="title">输入规则：</label>
						<?php foreach($item_rule_arr as $key=>$value){
							if($item_rule==$key){
								$item_rule_check='checked';
							} else {
								$item_rule_check='';
							}
							echo "<input type='radio' name='item_rule' value='$key' $item_rule_check>$value";
						} ?>
					</div>
				</div>
				<div id="postdivrich" class="postarea" <?if($item_type!='select' && $item_type!='radio' && $item_type!='checkbox' && $item_type!='sort'){?>style="display:none;"<?}?>>
					<label  for="content">项目选项：</label>
					<div id="wp-content-editor-container" class="wp-editor-container">
						<textarea class="wp-editor-area" rows="10" tabindex="4" cols="40" name="item_list" id="content"><?=isset($item_list)?$item_list:'';?></textarea>
						<br>每行一个选项
					</div>
				</div>
				<div id="titlediv" class="item_ext"  <?if($item_type!='file'){?>style="display:none;"<?}?>>
					<div id="titlewrap">
						<label  for="title">上传类型：</label>
						<input type="text" name="item_ext" size="30" tabindex="1" value="<?=isset($item_ext)?$item_ext:'';?>" id="title" />
						多个文件类型用英文逗号分割，如"jpg,rar,doc"
						<br><br>
					</div>
					<div id="titlewrap">
						<label  for="title">上传文件大小(单位：M)：</label>
						<input type="text" name="item_size" size="30" tabindex="1" value="<?=isset($item_size)?$item_size:'';?>" id="title" />
					</div>
				</div>
				<div id="titlediv">
					<div id="titlewrap">
						<label  for="title">项目顺序(数字越大越前)：</label>
						<input type="text" name="item_sort" size="30" tabindex="1" value="<?=isset($item_sort)?$item_sort:0;?>" id="title" />
					</div>
				</div>
				<div id="publishing-action">
					<input type="submit" name="publish" id="publish" class="button-primary" value="提 交" tabindex="5" accesskey="p"  />
				</div>

			</div>
		</div>
	</form>
</div>
<script type="text/javascript">
<!--
	function item_show(){
		var item_type=$('input:radio[name="item_type"]:checked').val();
		if(item_type=='input'){
			$(".item_rule").show();
			$(".postarea").hide();
			$(".item_ext").hide();
		}
		else if(item_type=='textarea' || item_type=='info' || item_type=='hidden'){
			$(".item_rule").hide();
			$(".postarea").hide();
			$(".item_ext").hide();
		}
		else if(item_type=='file'){
			$(".item_rule").hide();
			$(".postarea").hide();
			$(".item_ext").show();
		}
		else{
			$(".item_rule").hide();
			$(".postarea").show();
			$(".item_ext").hide();
		}
	}
	
	function show_item_hidden(obj){
		if(obj.checked){
			$("#item_hidden_span").show();
			$("#item_hidden").val(<?=!$item_hidden?'':$item_hidden;?>);
		} else {
			$("#item_hidden_span").hide();
			$("#item_hidden").val('');		
		}
	}
//-->
</script>