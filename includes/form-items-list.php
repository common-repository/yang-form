<?php
/*****************************/
/*	author:zetd@vip.sina.com */
/*	date:2012/03/20          */
/*	表单项目列表             */
/*****************************/
//require_once('../../../../wp-admin/admin.php');

if ( !defined('ABSPATH') ){
	die('-1');
	$parent_file = 'edit.php?post_type=page';
	$submenu_file = 'edit.php?post_type=page&page=yang_fp_list';
}

//require_once('../../../../wp-admin/admin-header.php');

global $wpdb;
//$table_form_items = $wpdb->prefix."yang_form_items";

$fid = isset($_GET['fid'])?intval($_GET['fid']):0;
if(!$fid){
	wp_die("无表单ID");
}

/* 批量处理ID */
$_POST['action2']=isset($_POST['action2'])?$_POST['action2']:'';
if($_POST['action2']=='trash' && is_array($_POST['item_ids'])){
	$res=$wpdb->query("update $wpdb->yang_form_items set status=0 where item_id in (".implode(",",$_POST['item_ids']).")");
}

require_once('form-items-config.php');
$page_num = isset($_GET['page_num'])?intval($_GET['page_num']):1;
$page_num = !$page_num?1:$page_num;
$pcount = 10;//每页显示几条
$item_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->yang_form_items where form_id=$fid and status=1"));
$start_num = ($page_num-1)*$pcount;
$realpages = @ceil($item_count / $pcount);
$murl = "admin.php?page=yang-form/yang-form-do.php&mode=items&fid=".$fid;
if($item_count){
	$item_list = $wpdb->get_results("SELECT * FROM $wpdb->yang_form_items where form_id=$fid and status=1 order by item_sort desc,item_id asc limit $start_num,$pcount" );
	//var_dump( $item_list );exit;
}
?>
<div class="wrap columns-auto" >
	<?php screen_icon('yang-form'); ?>
	<h2><?=isset($form_title)?$form_title:'';?> - 项目　<a href="admin.php?page=yang-form/yang-form-do.php&mode=items&action=item_new&fid=<?php echo $fid; ?>" class="add-new-h2">增加项目</a></h2>
	 <form action="<?echo $murl."&page_num=".$page_num?>" method="post">
	<table class="wp-list-table widefat fixed posts" cellspacing="0">
		<thead>
			<tr>
				<th scope='col' id='cb' class='manage-column column-cb check-column'  style=""><input type="checkbox" /></th>
				<th scope='col' id='title' class='manage-column column-title sortable desc'  style=""><span>ID</span></a></th>
				<th scope='col' id='title' class='manage-column column-title sortable desc'  style=""><span>标题</span></a></th>
				<th scope='col' id='title' class='manage-column column-title sortable desc'  style=""><span>类型</span></a></th>
				<th scope='col' id='title' class='manage-column column-date sortable asc'  style=""><span>选项/规则</span></a></th>
			</tr>
		</thead>
	 
		<tbody id="the-list">
		<?php if($item_count){
			foreach($item_list as $value){
				$value=get_object_vars($value);?>
				<tr id="post-1" class="post-1 post type-post status-publish format-standard hentry category-uncategorized alternate iedit author-self" valign="top">
					<th scope="row" class="check-column"><input type="checkbox" name="item_ids[]" value="<?echo $value['item_id']?>" /></th>
					<td class="categories column-categories"><?echo $value['item_id']?></td>
					<td class="post-title page-title column-title"><strong><a class="row-title" href="admin.php?page=yang-form/yang-form-do.php&mode=items&action=item_edit&fid=<?=$value['form_id']?>&item_id=<?=$value['item_id']?>" title=""><?echo $value['item_title']?></a></strong>&nbsp;<?=!$value['item_hidden']?'':'隐藏'?></td>
					<td class="categories column-categories"><?echo $item_type_arr[$value['item_type']]?></td>
					<td class="tags column-tags">
						<?php
							if($value['item_type']=='input'){
								echo $item_rule_arr[$value['item_rule']];
							} elseif($value['item_type']=='file') {
								echo "文件类型：".$value['item_ext']."<br>文件大小：".$value['item_size'];
							} elseif($value['item_type']=='info' || $value['item_type']=='textarea' || $value['item_type']=='hidden') {
								echo "无";
							} else {
								echo implode("<br>",unserialize($value['item_list']));
							}
						?>
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
				<option value='trash'>删除</option>
			</select>
			<input type="submit" name="" id="doaction2" class="button-secondary action" value="应用"  />
		</div>
		<div class="alignleft actions">
		</div>
		<div class='tablenav-pages'>
			<span class="displaying-num"><?echo $item_count?>个项目</span>
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
