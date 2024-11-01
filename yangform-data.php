<?php
/*****************************/
/*	author:zetd@vip.sina.com */
/*	date:2012/03/20          */
/*	表单列表                 */
/*****************************/
/*
根据表单列表“已报名人数”传递过来的 fid，读取表 $wpdb->yang_form_data 中字段 data_info 的信息，并用 unserialize 反序列化为数组形式：
	Array ( [1] => 老段 [2] => 女 [4] => 13612345688 [3] => 30 [5] => 咨询留言 )
然后读取并输出



$con7 = 'a:5:{i:1;s:6:"老段";i:2;s:3:"女";i:4;s:11:"13612345688";i:3;i:30;i:5;s:12:"咨询留言";}';
echo "con7输出内容为：<br>";
print_r(unserialize($con7));
echo "<br><br><br><br>";


*/




require_once(ABSPATH . 'wp-admin/admin.php');

if ( !defined( 'ABSPATH' ) ){ 
	//die('-1');
	header( 'HTTP/1.1 403 Forbidden', true, 403 );
	die ('Please do not load this page directly. Thanks!');
}

require_once(ABSPATH . 'wp-admin/admin-header.php');



global $wpdb;

$fid = isset($_GET['fid'])?intval($_GET['fid']):0;
if(!$fid){
	wp_die("无表单ID");
}

/* 批量处理ID */
$_POST['action2'] = isset($_POST['action2']) ? $_POST['action2']:'';
if( $_POST['action2']=='trash' && is_array($_POST['data_ids']) ){
	$res=$wpdb->query("update $wpdb->yang_form_data set data_status=1 where data_id in (".implode(",",$_POST['data_ids']).")");
}

//处理
$do = isset($_GET['do']) ? intval($_GET['do']):'';
$did = isset($_GET['did']) ? intval($_GET['did']):0;
$dst = isset($_GET['dst']) ? intval($_GET['dst']):0;
if( $do=='change' && $did ){
	$res2 = $wpdb->query("update $wpdb->yang_form_data set data_status=$dst where data_id=$did");
}

/* 分页 */
//require_once('form-items-config.php');
$page_num = isset($_GET['page_num'])?intval($_GET['page_num']):1;
$page_num = !$page_num ? 1:$page_num;
$pcount = 10;//每页显示几条
$data_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->yang_form_data where form_id=$fid"));
$start_num = ($page_num-1)*$pcount;
$realpages = @ceil($data_count / $pcount);
$murl = "admin.php?page=yang-form/yangform-data.php&fid=".$fid;
if($data_count){
	$data_list = $wpdb->get_results("SELECT * FROM $wpdb->yang_form_data where form_id=$fid order by data_status asc,data_id desc limit $start_num,$pcount" );
	//var_dump( $data_list );exit;
}
?>

<div class="wrap columns-auto" >
	<?php screen_icon('yang-form'); ?>
	<h2><?php echo isset($form_title)?$form_title:''; ?> - 表单数据　<a href="admin.php?page=yang-form/yang-form-list.php" class="add-new-h2">返回表单列表</a></h2>
	
	<form action="<?echo $murl."&page_num=".$page_num?>" method="post">
	<table class="wp-list-table widefat fixed posts" cellspacing="0">
		<thead>
			<tr>
				<th scope='col' id='cb' class='manage-column column-cb check-column' style=""><input type="checkbox" /></th>
				<th scope='col' id='title' class='manage-column column-date sortable asc' style=""><span>ID</span></a></th>
				<th scope='col' id='title' class='manage-column column-date sortable asc' style=""><span>数据时间</span></a></th>
				<th scope='col' id='title' class='manage-column column-date sortable asc' style=""><span>状态</span></a></th>
				<th scope='col' id='title' class='manage-column column-title sortable desc' style=""><span>表单数据信息</span></a></th>
			</tr>
		</thead>
	 
		<tbody id="the-list">
		<?php if($data_count){
			foreach($data_list as $value){
				$value = get_object_vars($value);

				$data_id = 'data_'.$value['data_id'];
					if( $value['data_status']==1 ){
						$dstatus = "已处理";
						$status_change = 0;
					} else {
						$dstatus = '<span style="color:red">未处理</span>';
						$status_change = 1;
						echo '<style>#'.$data_id.'{background:#CFCFCF;}</style>';
					}
				?>
				<tr id="<?php echo $data_id?>" class="post-1 post type-post status-publish format-standard hentry category-uncategorized alternate iedit author-self" valign="top">
					<th scope="row" class="check-column"><input type="checkbox" name="data_ids[]" value="<?php echo $value['data_id']?>" /></th>
					<td class="categories column-categories"><?php echo $value['data_id']?></td>
					<td class="tags column-tags"><a class="row-title" href="admin.php?page=yang-form/yangform-data.php&fid=<?php echo $value['form_id']?>&did=<?php echo $value['data_id']?>" title=""><?php echo $value['addtime']?></a></td>
					<td class="tags column-tags"><a class="row-title" href="admin.php?page=yang-form/yangform-data.php&fid=<?php echo $value['form_id']?>&did=<?php echo $value['data_id']?>&dst=<?php echo $status_change?>&do=change" title=""><?php echo $dstatus?></a></td>
					<td class="tags column-tags">
						<ul class="form_data_list">
						<?php 
							//print_r( unserialize($value['data_info']) ); echo "<br /><br /><br />";
							//循环输出下标
							foreach( unserialize($value['data_info']) as $k=>$a ){//如果需要取得下标用该方法，$k - 数组下标，$a - 数组的值
								//echo $k."：".$a . "<br />";
								
								$item_title = $wpdb->get_var("SELECT item_title FROM $wpdb->yang_form_items where item_id=$k");
								echo "<li>".$item_title."：".$a . "</li>";
							}
							//echo "<br /><br /><br />".implode("<br>",unserialize($value['data_info']));
						?>
						</ul>
					</td>
				</tr>
			<?php
			}
		} ?>
		</tbody>
	</table>
	
	<div class="tablenav bottom">
		<div class="alignleft actions">
			<select name='action2'>
				<option value='-1' selected='selected'>批量操作</option>
				<option value='trash'>处理</option>
			</select>
			<input type="submit" name="" id="doaction2" class="button-secondary action" value="应用"  />
		</div>
		<div class="alignleft actions"> </div>
		<div class='tablenav-pages'>
			<span class="displaying-num"><?php echo $data_count?>个项目</span>
			<span class='pagination-links'>
				<?
					if($page_num<>1)
					{
						echo "<a class='first-page' title='前往第一页' href='".$murl."&page_num=1'>&laquo;</a>";
					}
					else
					{
						echo "<a class='first-page disabled' title='前往第一页' href='###'>&laquo;</a>";
					}
					if($page_num<>1 && ($page_num-1)>0)
					{
						echo "<a class='prev-page' title='前往上一页' href='".$murl."&page_num=".($page_num-1)."'>&lsaquo;</a>";
					}
					else
					{
						echo "<a class='prev-page disabled' title='前往上一页' href='###'>&lsaquo;</a>";
					}
				
				?>
				<span class="paging-input">
					第 <?echo $page_num?> 页，共 <span class='total-pages'><?echo $realpages?></span> 页
				</span>
				
				<?
					if($page_num<>$realpages && ($page_num+1)<=$realpages)
					{
						echo "<a class='next-page' title='前往下一页' href='".$murl."&page_num=".($page_num+1)."'>&rsaquo;</a>";
					}
					else
					{
						echo "<a class='next-page disabled' title='前往下一页' href=''>&rsaquo;</a>";
					}
					if($page_num!=$realpages)
					{
						echo "<a class='last-page' title='前往最后一页' href='".$murl."&page_num=".$realpages."'>&raquo;</a>";
					}
					else
					{
						echo "<a class='last-page disabled' title='前往最后一页' href='###'>&raquo;</a>";
					}
				
				?>
			</span>
		</div>
		<br class="clear" />
	</div>
	</form>
</div>
