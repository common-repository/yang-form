<?php
/*****************************/
/*	author:zetd@vip.sina.com */
/*	date:2012/03/20          */
/*	yang-form-get              */
/*****************************/

/*
//可调用表单的站点
$yangform_sites = "localhost,01nv.com,jinxiuqianyan.com";

//本地测试$con = explode("localhost",$_SERVER['SERVER_NAME']);
$con = explode("jxqy.01nv.com",$_SERVER['SERVER_NAME']);
if (count($con)<=1){
	exit("非法路径！");
}

$yangform_sites = "jxqy.01nv.com,jinxiuqianyan.com";
function check_url( $str ){
	$current_url = $_SERVER['SERVER_NAME'];//判断是否包含当前域名
	$tmparray = explode($current_url,$str);
	if(count($tmparray)>1){
		return true;
	} else{
		return false;
	}
}
if( !check_url($yangform_sites) ){
	exit("非法路径！");
}
*/

require_once('../../../../wp-load.php');
if (!defined('ABSPATH')){
	header('HTTP/1.1 403 Forbidden', true, 403);
	die('Please do not load this page directly. Thanks!');
}


require( dirname(__FILE__) . '/yang-form-base-class.php' );
$yf_data 	= array();
$yf_pro 	= array();
$yf_show 	= array();

function check_yf_data(){
	global $yf_data, $yf_pro, $yf_show;
	cgi::both($yf_data['t'],"t","");
	cgi::both($yf_data['fid'],"fid",0);
	cgi::both($yf_data['callback'],"callback","");
	$yf_data['fid']= intval($yf_data['fid']);
	$yf_data['t']= string::un_script_code($yf_data['t']);
	$yf_data['t']= string::un_html($yf_data['t']);
	$yf_data['callback']= string::un_script_code($yf_data['callback']);
	$yf_data['callback']= string::un_html($yf_data['callback']);
}

/* 读取数据库表单信息，生成前端表单 */
function get_data(){
	global $wpdb;
	$table_form = $wpdb->prefix."yang_form";
	$table_form_items = $wpdb->prefix."yang_form_items";

	global $yf_data, $yf_pro, $yf_show;
	if(!$yf_data['fid']){
		throw new Exception("无表单ID");
	}
	$now_date=gmdate('Y-m-d', current_time('timestamp'));
	
	$form_info = $wpdb->get_results("SELECT * FROM $table_form where form_id=".$yf_data['fid']." and status=1 and form_end_time>='$now_date' limit 0,1" );
	if(!$form_info){
		throw new Exception("表单信息获取错误");
	}
	$form_info=get_object_vars($form_info[0]);

	$item_info = $wpdb->get_results("SELECT * FROM $table_form_items where form_id=".$yf_data['fid']." and status=1 order by item_sort desc,item_id asc" );
	if(!$item_info){
		throw new Exception("表单项目获取错误");
	}

	/* 输出DIV表单模板 */
	$yf_show['tempstr'] = '<form id="yangform_form" name="yangform_form" target="post_iframe" method="post" enctype="multipart/form-data" action="/wp-content/plugins/yang-form/front/yangform_post.php" class="form_info">
		<input type="hidden" name="yangform_fid" value="'.$yf_data['fid'].'">';
		if( $form_info['form_info_show']==1 ){
			$yf_show['tempstr'] .= '
				<div class="form_info">
					<h2><span style="">'.$form_info['form_title'].'</span></h2>
					<p>'.$form_info['form_intro'].'</p>
				</div>
			';
		}
		foreach($item_info as $value){
			$value = get_object_vars($value);
			$item_id = $value['item_id'];
			$item_title = $value['item_title'];
			$item_type = $value['item_type'];
			$item_must = $value['item_must'];
			$item_hidden = $value['item_hidden'];
			
			$item_hidden = ($item_hidden==0)?'':' style="display:none" class="hidden_'.$item_hidden.'" ';
			
			if($item_type=='input'){//输入框
				$item_con = '<div '.$item_hidden.' class="field email">';

				$item_con .= '<label id="'.$item_id.'" value="'.(!($item_must)?'no_must':$value['item_rule']).'" for=""><strong>'.$item_title.'</strong></label>';
				$item_con .= '<span class="f60">'. (!($item_must)?'':'<strong>*</strong>') .'</span>';
				if($value['item_rule']=='time'){
					$item_con .= '<input type="text" name="form_input_'.$item_id.'" id="form_input_'.$item_id.'" class="t_input" readonly value="" onclick="showcalendar(event,this,1)">';
				} else {
					$item_con .= '<input type="text" name="form_input_'.$item_id.'" id="form_input_'.$item_id.'" class="t_input" value="">';
				}
				$item_con .= ' <span id="tips_'.$item_id.'" class="tips"></span>';
				$item_con .= '</div>';
			} elseif($item_type=='select') {//选择框
				$item_con = '<div '.$item_hidden.' class="field email">';

				$item_con .= '<label id="'.$item_id.'" value="'.(!($item_must)?'no_must':$value['item_rule']).'" for=""><strong>'.$item_title.'</strong></label>';
				$item_con .= '<span class="f60">'. (!($item_must)?'':'<strong>*</strong>') .'</span>';
				$item_con .= '<select name="form_input_'.$item_id.'" id="form_input_'.$item_id.'">';
					$value['item_list'] = unserialize($value['item_list']);
					$item_con .= '<option value="">请选择</option>';
					foreach($value['item_list'] as $value) {
						$item_con .= '<option value="'.$value.'">'.$value.'</option>';
					}
				$item_con .= '</select> <span id="tips_'.$item_id.'" class="tips"></span>';
				$item_con .= '</div>';

			} elseif($item_type=='hidden') {//隐藏项
				$item_con = '<div '.$item_hidden.' class="field email">';
				$item_con .= '<input type="checkbox" value="1" id="form_input_'.$item_id.'" name="form_input_'.$item_id.'" onclick="show_hidden(this,'.$item_id.');">';
				$item_con .= '</div>';
			} elseif($item_type=='info') {//文本
				$item_con = '<div '.$item_hidden.' class="field email"><strong>'.$item_title.'</strong></div>';
			} else {
				$item_con = '<div '.$item_hidden.' class="field email">';

				$item_con .= '<label id="'.$item_id.'" value="'.(!($item_must)?'no_must':$item_type).'"><strong>'.$item_title.'</strong></label>';
				$item_con .= '<span class="f60">'. (!($item_must)?'':'<strong>*</strong>') .'</span>';

				$item_con .= '<div class="fl">';
				if($item_type=='file'){//文件上传
					$item_con .= '
						<input type="text" id="form_input_'.$item_id.'" name="form_input_'.$item_id.'" value="" class="t_input" readonly>
						<input type="button" value="上传" class="submit"/>
						<input type="file" name="upload_file_'.$item_id.'" id="upload_file_'.$item_id.'" class="input_file" onchange="getPath(this,'.$item_id.');" >
						<input type="hidden" value="'.$value['item_ext'].'" id="upload_ext_'.$item_id.'">
						<input type="hidden" value="'.$value['item_size'].'" id="upload_size_'.$item_id.'"><br />
						<span class="hint">请上传后缀为 <strong>'.$value['item_ext'].'</strong> 的文件，文件大小不超过<strong>'.$value['item_size'].'M</strong></span>';
				} elseif($item_type=='radio') {//单选
					$value['item_list'] = unserialize($value['item_list']);
					foreach($value['item_list'] as $key=>$value){
						if($value!='其他'){
							$item_con.='<input type="radio" name="form_input_'.$item_id.'" '.(!($key)?'id="form_input_'.$item_id.'"':'').' value="'.$value.'">'.$value.' ';
						} else {
							$item_con.='<input type="radio" name="form_input_'.$item_id.'" '.(!($key)?'id="form_input_'.$item_id.'"':'').' value=""  onclick="add_other(this);">'.$value.' ';
						}
					}
				} elseif($item_type=='checkbox') {//多选
					$value['item_list']=unserialize($value['item_list']);
					foreach($value['item_list'] as $key=>$value){
						if($value!='其他'){
							$item_con.='<input type="checkbox" name="form_input_'.$item_id.'[]" '.(!($key)?'id="form_input_'.$item_id.'"':'').' value="'.$value.'">'.$value.' ';
						} else {
							$item_con.='<input type="checkbox" name="form_input_'.$item_id.'[]" '.(!($key)?'id="form_input_'.$item_id.'"':'').' value=""  onclick="add_other(this);">'.$value.' ';
						}
					}
				} elseif($item_type=='sort') {//排序
					$value['item_list'] = unserialize($value['item_list']);
					$item_con .= '<div class="sort" id="sort_div'.$item_id.'"></div>';
					$item_con .= '<div class="no_sort" id="no_sort_div'.$item_id.'">';
					foreach($value['item_list'] as $key=>$value) {
						$item_con.='<span id="no_sort_'.$item_id.($key+1).'" onclick="do_sort('.$item_id.','.$item_id.($key+1).',1,'.(!($value=='其他')?1:2).');"><em>√</em> '.$value.'</span>';
					}
					$item_con.='<input type="hidden" id="form_input_'.$item_id.'" name="form_input_'.$item_id.'" value=""></div>';
				} elseif($item_type=='textarea') {//文本框
					$item_con.='<textarea id="form_input_'.$item_id.'" name="form_input_'.$item_id.'" class=""></textarea>';
				}
				$item_con .= '</div> <div id="tips_'.$item_id.'" class="tips"></div> </div>';//错误提示
			}

			$yf_show['tempstr'] .= $item_con.'</tr>';
		}
		$yf_show['tempstr'] .= '<div class="yangform_sub">
			<input type="button" id="yangform_form_submit" name="yangform_form_submit" value="提交" class="submit" onclick="check_form();"/>
			 <span id="tips" name="tips" class="tips_sub"></span></div>
	</form>
	<iframe name="post_iframe" id="post_iframe" src="about:blank" style="display:none;"></iframe>';
	$yf_show['tempstr'] = str_replace(array("\n","\r","\r\n","\t"),array('','','',''),$yf_show['tempstr']);



/*	//Table表格模板 2012.07.02 12:00
	$yf_show['tempstr'] = '<form id="yangform_form" name="yangform_form" target="post_iframe" method="post" enctype="multipart/form-data" action="/wp-content/plugins/yang-form/front/yangform_post.php">
		<input type="hidden" name="yangform_fid" value="'.$yf_data['fid'].'">
		<table cellpadding="0" cellspacing="0" class="formtable" id="yangform_table" name="yangform_table">';
		if( $form_info['form_info_show']==1 ){
			$yf_show['tempstr'] .= '
				<caption>
					<h2><span style="float:left;">'.$form_info['form_title'].'</span></h2>
					<p>'.$form_info['form_intro'].'</p>
				</caption>
			';
		}
		foreach($item_info as $value){
			$value = get_object_vars($value);
			$item_id = $value['item_id'];
			$item_title = $value['item_title'];
			$item_type = $value['item_type'];
			$item_must = $value['item_must'];
			$item_hidden = $value['item_hidden'];
			
			$item_hidden = ($item_hidden==0)?'':' style="display:none" class="hidden_'.$item_hidden.'" ';
			$yf_show['tempstr'] .= '<tr '.$item_hidden.'>';
			
			if($item_type=='input'){//输入框
				$item_con = '<td id="'.$item_id.'" value="'.(!($item_must)?'no_must':$value['item_rule']).'" width="17%" align="right"><strong>'.$item_title.'</strong></td>';
				$item_con .= '<td width="3%" align="center" class="f60">'. (!($item_must)?'':'<strong>*</strong>') .'</td>';
				$item_con .= '<td width="80%"><label>';
					//$item_con .= '<input type="text" class="xo" id="username" name="username">';
					if($value['item_rule']=='time'){
						$item_con .= '<input type="text" name="form_input_'.$item_id.'" id="form_input_'.$item_id.'" class="t_input" readonly value="" onclick="showcalendar(event,this,1)">';
					} else {
						$item_con .= '<input type="text" name="form_input_'.$item_id.'" id="form_input_'.$item_id.'" class="t_input" value="">';
					}
					$item_con .= ' <span id="tips_'.$item_id.'" class="tips"></span>';
				$item_con .= '</label></td>';
			} elseif($item_type=='select') {//选择框
				$item_con = '<tdid="'.$item_id.'" value="'.(!($item_must)?'no_must':$value['item_rule']).'" width="17%" align="right"><strong>'.$item_title.'</strong></td>';
				$item_con .= '<td width="3%" align="center" class="f60">'. (!($item_must)?'':'<strong>*</strong>') .'</td>';
				$item_con .= '<td width="80%"><select name="form_input_'.$item_id.'" id="form_input_'.$item_id.'">';
					$value['item_list'] = unserialize($value['item_list']);
					$item_con .= '<option value="">请选择</option>';
					foreach($value['item_list'] as $value) {
						$item_con .= '<option value="'.$value.'">'.$value.'</option>';
					}
					$item_con .= '</select> <span id="tips_'.$item_id.'" class="tips"></span>';
				$item_con .= '</td>';
			} elseif($item_type=='hidden') {//隐藏项
				$item_con = '<td id="'.$item_id.'" value="'.$item_type.'" width="17%" align="right"><strong>'.$item_title.'</strong></td>';
				$item_con .= '<td width="3%" align="center" class="f60"> </td>';
				$item_con .= '<td width="80%"><input type="checkbox" value="1" id="form_input_'.$item_id.'" name="form_input_'.$item_id.'" onclick="show_hidden(this,'.$item_id.');"></td>';
			} elseif($item_type=='info') {//文本
				$item_con = '<td colspan="2" align="left"> </td>';
				$item_con = '<td align="left"><strong>'.$item_title.'</strong> </td>';
			} else {
				$item_con = '<td width="17%" align="right"><strong>'.$item_title.'</strong></td>';
				$item_con .= '<td width="3%" align="center" class="f60">'. (!($item_must)?'':'<strong>*</strong>') .'</td>';

				$item_con .= '<td id="'.$item_id.'" value="'.(!($item_must)?'no_must':$item_type).'">';
				if($item_type=='file'){//文件上传
					$item_con .= '
						<input type="text" id="form_input_'.$item_id.'" name="form_input_'.$item_id.'" value="" class="t_input" readonly>
						<input type="button" value="上传" class="submit"/>
						<input type="file" name="upload_file_'.$item_id.'" id="upload_file_'.$item_id.'" class="input_file" onchange="getPath(this,'.$item_id.');" >
						<input type="hidden" value="'.$value['item_ext'].'" id="upload_ext_'.$item_id.'">
						<input type="hidden" value="'.$value['item_size'].'" id="upload_size_'.$item_id.'"><br />
						<span class="hint">请上传后缀为 <strong>'.$value['item_ext'].'</strong> 的文件，文件大小不超过<strong>'.$value['item_size'].'M</strong></span>';
				} elseif($item_type=='radio') {//单选
					$value['item_list'] = unserialize($value['item_list']);
					foreach($value['item_list'] as $key=>$value){
						if($value!='其他'){
							$item_con.='<input type="radio" name="form_input_'.$item_id.'" '.(!($key)?'id="form_input_'.$item_id.'"':'').' value="'.$value.'">'.$value.' ';
						} else {
							$item_con.='<input type="radio" name="form_input_'.$item_id.'" '.(!($key)?'id="form_input_'.$item_id.'"':'').' value=""  onclick="add_other(this);">'.$value.' ';
						}
					}
				} elseif($item_type=='checkbox') {//多选
					$value['item_list']=unserialize($value['item_list']);
					foreach($value['item_list'] as $key=>$value){
						if($value!='其他'){
							$item_con.='<input type="checkbox" name="form_input_'.$item_id.'[]" '.(!($key)?'id="form_input_'.$item_id.'"':'').' value="'.$value.'">'.$value.' ';
						} else {
							$item_con.='<input type="checkbox" name="form_input_'.$item_id.'[]" '.(!($key)?'id="form_input_'.$item_id.'"':'').' value=""  onclick="add_other(this);">'.$value.' ';
						}
					}
				} elseif($item_type=='sort') {//排序
					$value['item_list']=unserialize($value['item_list']);
					$item_con.='<div class="sort" id="sort_div'.$item_id.'"></div>';
					$item_con.='<div class="no_sort" id="no_sort_div'.$item_id.'">';
					foreach($value['item_list'] as $key=>$value) {
						$item_con.='<span id="no_sort_'.$item_id.($key+1).'" onclick="do_sort('.$item_id.','.$item_id.($key+1).',1,'.(!($value=='其他')?1:2).');"><em>√</em> '.$value.'</span>';
					}
					$item_con.='<input type="hidden" id="form_input_'.$item_id.'" name="form_input_'.$item_id.'" value=""></div>';
				} elseif($item_type=='textarea') {//文本框
					$item_con.='<textarea id="form_input_'.$item_id.'" name="form_input_'.$item_id.'" class=""></textarea>';
				}
				$item_con .= ' <span id="tips_'.$item_id.'" class="tips"></td>';//错误提示
			}

			$yf_show['tempstr'] .= $item_con.'</tr>';
		}
		$yf_show['tempstr'] .= '
						<tr><td colspan="3"><input type="button" id="yangform_form_submit" name="yangform_form_submit" value="提交" class="submit" onclick="check_form();"/><span id="tips" name="tips" class="tips_sub"></span></td></tr>
					</table>
					</form>
					<iframe name="post_iframe" id="post_iframe" src="about:blank" style="display:none;"></iframe>
		';
		$yf_show['tempstr'] = str_replace(array("\n","\r","\r\n","\t"),array('','','',''),$yf_show['tempstr']);
*/

/*	//输出表单模板 old
	$yf_show['tempstr'] = '<form id="yangform_form" name="yangform_form" target="post_iframe" method="post" enctype="multipart/form-data" action="/wp-content/plugins/yang-form/front/yangform_post.php">
		<input type="hidden" name="yangform_fid" value="'.$yf_data['fid'].'">
		<table cellpadding="0" cellspacing="0" class="formtable" id="yangform_table" name="yangform_table">';
		if($form_info['form_info_show']==1){
			$yf_show['tempstr'] .= '
				<caption>
					<h2><span style="float:left;">'.$form_info['form_title'].'</span></h2>
					<p>'.$form_info['form_intro'].'</p>
				</caption>
			';
		}
		foreach($item_info as $value){
			$value=get_object_vars($value);
			$item_id=$value['item_id'];
			$item_title=$value['item_title'];
			$item_type=$value['item_type'];
			$item_must=$value['item_must'];
			$item_hidden=$value['item_hidden'];
			$yf_show['tempstr'] .= '<tr>';
			$item_hidden=($item_hidden==0)?'':' style="display:none" class="hidden_'.$item_hidden.'" ';
			if($item_type=='input'){
				$item_con='<td '.$item_hidden.'id="'.$item_id.'" value="'.(!($item_must)?'no_must':$value['item_rule']).'">'.(!($item_must)?'':'<strong>*</strong>').$item_title;
				if($value['item_rule']=='time'){
					$item_con.='<input type="text" name="form_input_'.$item_id.'" id="form_input_'.$item_id.'" class="t_input" readonly value="" onclick="showcalendar(event,this,1)">';
				} else {
					$item_con.='<input type="text" name="form_input_'.$item_id.'" id="form_input_'.$item_id.'" class="t_input" value="">';
				}
				$item_con.='<span id="tips_'.$item_id.'" class="tips"></span>';
			} elseif($item_type=='select') {
				$item_con='<td '.$item_hidden.'id="'.$item_id.'" value="'.(!($item_must)?'no_must':$value['item_rule']).'">'.(!($item_must)?'':'<strong>*</strong>').$item_title.'<select name="form_input_'.$item_id.'" id="form_input_'.$item_id.'">';
				$value['item_list']=unserialize($value['item_list']);
				$item_con.='<option value="">请选择</option>';
				foreach($value['item_list'] as $value) {
					$item_con.='<option value="'.$value.'">'.$value.'</option>';
				}
				$item_con.='</select><span id="tips_'.$item_id.'" class="tips"></span>';

			} elseif($item_type=='hidden') {
				$item_con='<td '.$item_hidden.' id="'.$item_id.'" value="'.$item_type.'">'.$item_title.'<input type="checkbox" value="1" id="form_input_'.$item_id.'" name="form_input_'.$item_id.'" onclick="show_hidden(this,'.$item_id.');">';
			} elseif($item_type=='info') {
				$item_con='<td><strong>'.$item_title.'</strong>';
			} else {
				$item_con='<td '.$item_hidden.'>'.(!($item_must)?'':'<strong>*</strong>').$item_title.'<span id="tips_'.$item_id.'" class="tips"></td><tr><td id="'.$item_id.'"'.$item_hidden.' value="'.(!($item_must)?'no_must':$item_type).'">';
				if($item_type=='file'){
					$item_con.='
					<input type="text" id="form_input_'.$item_id.'" name="form_input_'.$item_id.'" value="" class="t_input" readonly>
					<input type="button" value="上传" class="submit"/>
					<input type="file" name="upload_file_'.$item_id.'" id="upload_file_'.$item_id.'" class="input_file" onchange="getPath(this,'.$item_id.');" >
					<input type="hidden" value="'.$value['item_ext'].'" id="upload_ext_'.$item_id.'"><input type="hidden" value="'.$value['item_size'].'" id="upload_size_'.$item_id.'">请上传后缀为 <strong>'.$value['item_ext'].'</strong> 的文件，文件大小不超过<strong>'.$value['item_size'].'M</strong>
					';
				} elseif($item_type=='radio') {
					$value['item_list']=unserialize($value['item_list']);
					foreach($value['item_list'] as $key=>$value){
						if($value!='其他'){
							$item_con.='<input type="radio" name="form_input_'.$item_id.'" '.(!($key)?'id="form_input_'.$item_id.'"':'').' value="'.$value.'">'.$value.' ';
						} else {
							$item_con.='<input type="radio" name="form_input_'.$item_id.'" '.(!($key)?'id="form_input_'.$item_id.'"':'').' value=""  onclick="add_other(this);">'.$value.' ';
						}
					}
				} elseif($item_type=='checkbox') {
					$value['item_list']=unserialize($value['item_list']);
					foreach($value['item_list'] as $key=>$value){
						if($value!='其他'){
							$item_con.='<input type="checkbox" name="form_input_'.$item_id.'[]" '.(!($key)?'id="form_input_'.$item_id.'"':'').' value="'.$value.'">'.$value.' ';
						} else {
							$item_con.='<input type="checkbox" name="form_input_'.$item_id.'[]" '.(!($key)?'id="form_input_'.$item_id.'"':'').' value=""  onclick="add_other(this);">'.$value.' ';
						}
					}
				} elseif($item_type=='sort') {
					$value['item_list']=unserialize($value['item_list']);
					$item_con.='<div class="sort" id="sort_div'.$item_id.'"></div>';
					$item_con.='<div class="no_sort" id="no_sort_div'.$item_id.'">';
					foreach($value['item_list'] as $key=>$value) {
						$item_con.='<span id="no_sort_'.$item_id.($key+1).'" onclick="do_sort('.$item_id.','.$item_id.($key+1).',1,'.(!($value=='其他')?1:2).');"><em>√</em> '.$value.'</span>';
					}
					$item_con.='<input type="hidden" id="form_input_'.$item_id.'" name="form_input_'.$item_id.'" value=""></div>';
				} elseif($item_type=='textarea') {
					$item_con.='<textarea id="form_input_'.$item_id.'" name="form_input_'.$item_id.'" class=""></textarea>';
				}
			}
			$yf_show['tempstr'] .= $item_con.'</td></tr>';
		}
		$yf_show['tempstr'] .= '
						<tr><td><input type="button" id="yangform_form_submit" name="yangform_form_submit" value="提交" class="submit" onclick="check_form();"/><span id="tips" name="tips" class="p_tips"></span></td></tr>
					</table>
					</form>
					<iframe name="post_iframe" id="post_iframe" src="about:blank" style="display:none;"></iframe>
		';
		$yf_show['tempstr'] = str_replace(array("\n","\r","\r\n","\t"),array('','','',''),$yf_show['tempstr']);
*/
}

try{
	$yf_show['error'] = '0';
	$yf_show['errmsg'] = "";
	
	check_yf_data();
	get_data();
}catch (Exception $e){
	$yf_show['error'] = '1';
	$yf_show['errmsg'] = $e->getMessage();//输出来自该异常的错误消息
}

show_pro();
exit;

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
	} else {
		if($yf_show['error']==1){
			echo $yf_show['errmsg'];
		} else {
			echo $yf_show['tempstr'];
		}
	}
	unset($yf_data);
	unset($yf_pro);
	unset($yf_show);
	unset($config);
}

?>
