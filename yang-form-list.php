<?php
/*****************************/
/*	author:zetd@vip.sina.com */
/*	date:2012/03/20          */
/*	表单列表                 */
/*****************************/
require_once(ABSPATH . 'wp-admin/admin.php');

if ( !defined( 'ABSPATH' ) ){ 
	//die('-1');
	header( 'HTTP/1.1 403 Forbidden', true, 403 );
	die ('Please do not load this page directly. Thanks!');
}

require_once(ABSPATH . 'wp-admin/admin-header.php');

global $wpdb;

/* 批量处理ID */
$_POST['action2']=isset($_POST['action2'])?$_POST['action2']:'';
if($_POST['action2']=='trash' && is_array($_POST['fids'])){
	$res = $wpdb->query("update $wpdb->yang_form set status=0 where form_id in (".implode(",",$_POST['fids']).")");
}

$page_num = isset($_GET['page_num'])?intval($_GET['page_num']):1;
$page_num = !$page_num?1:$page_num;
$pcount = 5;
$form_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->yang_form where status=1"));
$start_num = ($page_num-1)*$pcount;
$realpages = @ceil($form_count / $pcount);
$murl="admin.php?page=yang-form/yang-form-list.php";

if($form_count){
	$form_list = $wpdb->get_results("SELECT * FROM $wpdb->yang_form where status=1 order by form_id desc limit $start_num,$pcount" );
	//var_dump($form_list);exit;
}
?>
<div class="wrap columns-auto" >
	<?php screen_icon('yang-form'); ?>
	<h2><?=isset($form_title)?$form_title:'';?> 所有表单　<?php if( yangform::yang_get_current_user()>=7 ){ ?><a href="admin.php?page=yang-form/yang-form-do.php" class="add-new-h2">新建表单</a><?php }?></h2>
	<form action="<?echo $murl."&page_num=".$page_num?>" method="post">
	<table class="wp-list-table widefat fixed posts" cellspacing="0">
		<thead>
		<tr>
			<th scope='col' id='cb' class='manage-column column-cb check-column'  style=""><input type="checkbox" /></th>
			<th scope='col' id='title' class='manage-column column-date sortable asc' style=""><span>ID</span></a></th>
			<th scope='col' id='title' class='manage-column column-title sortable desc'  style=""><span>标题</span></a></th>
			<th scope='col' id='title' class='manage-column column-title sortable desc'  style=""><span>有效时间</span></a></th>
			<th scope='col' id='title' class='manage-column column-author sortable desc'  style=""><span>表单数据</span></a></th>
			<th scope='col' id='title' class='manage-column column-date sortable asc'  style=""><span>发布时间</span></a></th>
			<th scope='col' id='title' class='manage-column column-date sortable asc'  style=""><span>操作</span></a></th>
		</tr>
		</thead>
		
		<tbody id="the-list">
		<?php
			$base_name = plugin_basename('yang-form/yang-form-do.php');//返回 /wp-content/plugins/
			$base_page = 'admin.php?page=' . $base_name;
			if($form_count){
				foreach($form_list as $value){
					$value = get_object_vars($value);
					$form_nums = yangform::yang_get_form_nums( $value['form_id'] );
					?>
					<tr id="post-1" class="post-1 post type-post status-publish format-standard hentry category-uncategorized alternate iedit author-self" valign="top">
						<th scope="row" class="check-column"><input type="checkbox" name="fids[]" value="<?echo $value['form_id']?>" /></th>
						<td class="categories column-categories"><?php echo $value['form_id']?></td>
						<td class="post-title page-title column-title"><strong><a class="row-title" href="admin.php?page=yang-form/yang-form-do.php&mode=form_edit&fid=<?echo $value['form_id']?>" title=""><?echo $value['form_title']?></a></strong></td>
						<td class="categories column-categories"><?echo $value['form_start_time']?> ~ <?echo $value['form_end_time']?></td>
						<td class="tags column-tags"><?php if( yangform::yang_get_current_user()>=7 ){ ?><a href="admin.php?page=yang-form/yangform-data.php&fid=<?echo $value['form_id']?>" class="add-new-h2"><?php echo $form_nums?></a><?php }?></td>
						<td class="tags column-tags"><?echo $value['form_add_time']?></td>
						<!-- <td class="tags column-tags"><a class="row-title" href="edit.php?post_type=page&page=yang-form/includes/form-items-list.php&fid=<?echo $value['form_id']?>" title="">编辑表单项目</a></td> -->
						<td class="tags column-tags"><a class="row-title" href="<?php echo $base_page ?>&mode=items&fid=<?echo $value['form_id']?>" title="">项目管理</a></td>
					</tr>
				<?php }
			}
		?>
		</tbody>
	</table>
	<div class="tablenav bottom">
		<div class="alignleft actions">
			<select name='action2'>
				<option value='-1' selected='selected'>批量操作</option>
				<option value='trash'>删除</option>
			</select>
			<input type="submit" name="" id="doaction2" class="button-secondary action" value="应用"  />
		</div>
		<div class="alignleft actions">
		</div>
		<div class='tablenav-pages'>
			<span class="displaying-num"><?echo $form_count?>个表单</span>
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

<?
include(ABSPATH . 'wp-admin/admin-footer.php');
?>