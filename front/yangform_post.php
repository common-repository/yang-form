<?php
/*****************************/
/*	author:zetd@vip.sina.com */
/*	date:2012/03/20          */
/*	yang-form-get              */
/*****************************/
/*
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
	cgi::both($yf_data['t'],"t","iframe");
	cgi::both($yf_data['fid'],"yangform_fid",0);
	cgi::both($yf_data['callback'],"callback","");
	$yf_data['callback']= string::un_script_code($yf_data['callback']);
	$yf_data['callback']= string::un_html($yf_data['callback']);
	$yf_data['t']= string::un_script_code($yf_data['t']);
	$yf_data['t']= string::un_html($yf_data['t']);
	$yf_data['fid']= intval($yf_data['fid']);
}

function get_data(){
	global $wpdb;
	//$table_form	= $wpdb->prefix."yang_form";

	global $yf_data, $yf_pro, $yf_show;

	if(!$yf_data['fid']){
		throw new Exception("无表单ID");
	}
	$now_date=gmdate('Y-m-d', current_time('timestamp'));
	$form_info = $wpdb->get_results("SELECT * FROM $wpdb->yang_form where form_id=".$yf_data['fid']." and status=1 and form_end_time>='$now_date' limit 0,1" );
	if(!$form_info){
		throw new Exception("表单信息获取错误");
	}
	$form_info=get_object_vars($form_info[0]);
	$item_info = $wpdb->get_results("SELECT * FROM $wpdb->yang_form_items where form_id=".$yf_data['fid']." and status=1 and item_type<>'info' order by item_sort desc,item_id asc" );
	if(!$item_info){
		throw new Exception("表单项目获取错误");
	}
	$yf_data['td_id']=0;
	$form_save_arr=array();
	
	//获取提交值
	foreach($item_info as $value){
		$value=get_object_vars($value);
		$item_post_name='form_input_'.$value['item_id'];
		if($value['item_type']=='file'){
			$item_post_name='upload_file_'.$value['item_id'];
			if($value['item_must']==1 && $_FILES[$item_post_name]['name']=='' && $value['item_hidden']==0){//必填项			
				throw new Exception("请上传附件");
			}
			if($value['item_hidden']>0 && $yf_data['hidden'][$value['item_hidden']]==1 && $_FILES[$item_post_name]['name']==''){//隐藏必填项
				throw new Exception("请上传附件");
			}
			if($_FILES[$item_post_name]['name']!=''){
				if($_FILES[$item_post_name]['size']>(1000000*$value['item_size'])){
					throw new Exception("附件大小不能超过".$value['item_size']."M");
				}
				require_once 'upfile.class.php';
				$f = new upfile($value['item_ext'],'',(1000000*$value['item_size']),1);
				$out_file = $f->upload($item_post_name);
				$source_file=$_FILES[$item_post_name]['name'];
				$form_save_arr[$value['item_id']]=array('out_file'=>$out_file,'source_file'=>$source_file);
			}
		}
		else if($value['item_type']=='hidden')
		{
			cgi::both($post_val,$item_post_name,"");
			$post_val= intval($post_val);
			$yf_data['hidden'][$value['item_id']]=$post_val;
			$form_save_arr[$value['item_id']]=!$post_val?"否":"是";
		}
		else
		{
			cgi::both($post_val,$item_post_name,"");
			if($value['item_type']=='checkbox'){//如果是多选的数组转换			
				if(is_array($post_val)){
					$post_val=implode(",",$post_val);
				} else {
					throw new Exception("数据错误");
				}
			}
			if($value['item_rule']=='number'){//防止注入
				$post_val= intval($post_val);
			} else {
				$post_val= string::un_script_code($post_val);
				$post_val= string::un_html($post_val);
			}
			if($value['item_must']==1 && !$post_val && $value['item_hidden']==0){//必填项
				$yf_data['td_id']=$value['item_id'];
				throw new Exception("此为必填项！");
			}
			if($value['item_hidden']>0 && $yf_data['hidden'][$value['item_hidden']]==1 && !$post_val){//隐藏必填项
				$yf_data['td_id']=$value['item_id'];
				throw new Exception("此为必填项！");
			}
			$form_save_arr[$value['item_id']]=$post_val;
		}
	}

	//保存表单
	$insert_arr = array(
		'form_id'=>$yf_data['fid'],
		'data_info'=>serialize($form_save_arr),
		'addtime'=>	gmdate('Y-m-d H:i:s', current_time('timestamp')),
		'addip'=>GetIP()
	);
	$wpdb->insert($wpdb->yang_form_data,$insert_arr);
	$data_id = $wpdb->insert_id;
	if(!$data_id){
		throw new Exception("表单提交失败，请重试");
	}
}

try{
	
	$yf_show['error'] = '0';
	$yf_show['errmsg'] = "";
	
	check_yf_data();
	get_data();
	
}catch (Exception $e){
	
	$yf_show['error'] = '1';
	$yf_show['errmsg'] = $e->getMessage();
	
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
		if($yf_show['error']==1){
			echo("<script>parent.show_error('".($yf_show["errmsg"])."','".$yf_data['td_id']."')</script>");
		} else {
			echo("<script>parent.success_submit()</script>");
		}
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
