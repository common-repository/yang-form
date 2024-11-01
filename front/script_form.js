
//表单相关JS

var submit_flag=0;
var form_submit=1;

//加载表单
function load_from(fid,to_div){
	$("#append_parent").remove();
	$("body").append('<div id="append_parent"></div>');//添加
	$.ajax({
		url: "/wp-content/plugins/yang-form/front/yangform_get.php", 
		data: "fid="+fid+"&t=jsonp",
		dataType: 'jsonp', 
		jsonp: 'callback', 
		timeout: 3000,
		success: function(json) {
			$("#"+to_div).html(json.tempstr);
		}
	});
}

//添加其他输入
function add_other(obj){
	if(obj.checked){
		var str=prompt("请输入其他内容","");
		if(str){
			obj.value=str;
		} else {
			obj.checked=false;
		}
	}else {
		obj.value="";
	}
}
//隐藏控制
function show_hidden(obj,hidden_num){
	if(obj.checked){
		$(".hidden_"+hidden_num).show();
	} else {
		$(".hidden_"+hidden_num).hide();
	}
}

//排序函数
function do_sort(div_num,sort_num,flag,other){
	if(flag==1){
		var no_sort_val=$("#no_sort_"+sort_num).html().toUpperCase();
		no_sort_val=no_sort_val.replace("<EM>√</EM> ","");
		if(other==1){
			var sort_div_val=$("#sort_div"+div_num).html();
			$("#sort_div"+div_num).html(sort_div_val+'<span id="sort_'+sort_num+'" onclick="do_sort('+div_num+','+sort_num+',2,'+other+');"><em>×</em> '+no_sort_val+'</span>');
			$("#no_sort_"+sort_num).remove();
		} else {
			var str=prompt("请输入其他内容","");
			if(str){
				var sort_div_val=$("#sort_div"+div_num).html();
				$("#sort_div"+div_num).html(sort_div_val+'<span id="sort_'+sort_num+'" onclick="do_sort('+div_num+','+sort_num+',2,'+other+');"><em>×</em> '+str+'</span>');
				$("#no_sort_"+sort_num).remove();
			}
		}
	}
	else if(flag==2){
		var sort_val=$("#sort_"+sort_num).html().toUpperCase();
		sort_val=sort_val.replace("<EM>×</EM> ","");
		var no_sort_div_val=$("#no_sort_div"+div_num).html();
		if(other==1){
			$("#no_sort_div"+div_num).html(no_sort_div_val+'<span id="no_sort_'+sort_num+'" onclick="do_sort('+div_num+','+sort_num+',1,'+other+');"><em>√</em> '+sort_val+'</span>');
		} else {
			$("#no_sort_div"+div_num).html(no_sort_div_val+'<span id="no_sort_'+sort_num+'" onclick="do_sort('+div_num+','+sort_num+',1,'+other+');"><em>√</em> 其他</span>');
		}
		$("#sort_"+sort_num).remove();
	}
	var sort_arr=Array();
	var temp_sort='';
	$("#sort_div"+div_num+" span").each(function(){
		temp_sort=$(this).html().toUpperCase().replace("<EM>×</EM> ","");
		sort_arr.push(temp_sort);
	});
	$("#form_input_"+div_num).val(sort_arr);
}


//验证表单
	//去左右空格;
	function trim(s){
		return rtrim(ltrim(s));
	}
	//去左空格;
	function ltrim(s){
		return s.replace( /^\s*/, "");
	}
	//去右空格;
	function rtrim(s){
		return s.replace( /\s*$/, "");
	}
	//手机号码;
	function isMobile(s){
		s = trim(s);
		var p = /13\d{9}/;
		return p.test(s);
	}
	//数字;
	function isNumber(s){
		return !isNaN(s);
	}
	//Integer;
	function isInteger(s){
		s = trim(s);
		var p = /^[-\+]?\d+$/;
		return p.test(s);
	}
	function isChinese(s){
		s = trim(s);
		var p = /^[\u0391-\uFFE5]+$/;
		return p.test(s);
	}
	//Email;
	function isEmail(s){
		s = trim(s);
		var p = /^[_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.){1,4}[a-z]{2,3}$/i;
		return p.test(s);
	}
	//空字符值;
	function isEmpty(s){
		s = trim(s);
		return s.length == 0;
	}
	//后缀名;
	function isext(s,exts){
		s = trim(s);
		s_len=s.lastIndexOf(".");
		check_ext=s.substring(s_len+1).toLowerCase();
		var ext_ary=new Array(exts);
		if(ext_ary.in_array(check_ext)==false){
			return false;
		} else {
			return true;
		}
	}
	//radio选项是否选;
	function isradio(s){
		if($('input:radio[name="'+s+'"]:checked').val()==null){
			return false;
		} else {
			return true;
		}
	}
	//checked选项是否选;
	function ischeckbox(s) {
		if($('input:checkbox[name="'+s+'"]:checked').val()==null) {
			return false;
		} else {
			return true;
		}
	}
	//获取真实文件路径
	function getPath(obj,id) {
		if (obj) {
			if (window.navigator.userAgent.indexOf("MSIE") >= 1) {
				obj.select();
				$("#form_input_"+id).val(document.selection.createRange().text);
			} else {
				$("#form_input_"+id).val(obj.value);
			}
		}
	}

	Array.prototype.in_array = function(e) {
		for(i=0;i<this.length;i++){
			if(this[i] == e)
			return true;
		}
		return false;
	}

//提交表单
function check_form(){
	//if(submit_flag==1) return false;
	submit_flag=1;
	form_submit=1;
	$(".tips").html("");
	$(".tips_sub").html("");
	$('#yangform_form div').each(function(){
		var td_id=$(this).attr("id");
		if(td_id>0 && $(this).css('display')!='none'){
			var td_val=$(this).attr("value");
			if(td_val!=''){
				if(td_val=='radio'){
					if($('input:radio[name="form_input_'+td_id+'"]:checked').val()==null){
						show_error('请至少选一个选项',td_id);
						return false;
					}
				}
				else if(td_val=="checkbox"){
					if($('input:checkbox[name="form_input_'+td_id+'[]"]:checked').val()==null){
						show_error('请至少选一个选项',td_id);
						return false;
					}
				} else {
					var check_val=$("#form_input_"+td_id).val();
					switch(td_val){
						case 'normal':{
							if(isEmpty(check_val)){
								show_error('此为必填项',td_id);
								return false;
							}
							break;
						}
						case 'chinese':{
							if(!isChinese(check_val)){
								show_error('请输入中文！',td_id);
								return false;
							}
							break;
						}
						case 'number':{
							if(!isNumber(check_val) || isEmpty(check_val)){
								show_error('请输入数字！',td_id);
								return false;
							}
							break;
						}
						case 'mp':{
							if(!isMobile(check_val)){
								show_error('请输入正确的手机号码！',td_id);
								return false;
							}
							break;
						}
						case 'mail':{
							if(!isEmail(check_val)){
								show_error('请输入正确的邮箱！',td_id);
								return false;
							}
							break;
						}
						case 'select':{
							if(isEmpty(check_val)){
								show_error('请选择一个选项',td_id);
								return false;
							}
							break;
						}
						case 'time':{
							if(isEmpty(check_val)){
								show_error('请选择日期',td_id);
								return false;
							}
							break;
						}
						case 'sort':{
							if(isEmpty(check_val) || check_val.split(",").length<2){
								show_error('请至少选择两个选项并排序',td_id);
								return false;
							}
							break;
						}
						case 'textarea':{
							if(isEmpty(check_val)){
								show_error('此为必填项',td_id);
								return false;
							}
							break;
						}
						case 'file':{
							if(isEmpty(check_val)){
								show_error('请上传文件',td_id);
								return false;
							}
							s=check_val.lastIndexOf(".");
							check_ext=check_val.substring(s+1).toUpperCase();
							check_ext=check_ext.toLowerCase();
							var ext_ary=$("#upload_ext_"+td_id).val().split(",");
							if(ext_ary.in_array(check_ext)==false){
								show_error('文件格式错误，请重新选择',td_id);
								return false;
							}
							break;
						}
						default:{
							break;
						}
					}
				}
			}
		}
	});
	if(form_submit==1){
		$("#yangform_form").submit();
	}
}

//错误
function show_error(str,td_id){
	if(!str)var str='表单未填完整';
	$(".tips_sub").html(str);
	if(td_id>0){
		$("#tips_"+td_id).html(str);
		$("#form_input_"+td_id).focus();
	}
	submit_flag=0;
	form_submit=0;
}

//成功
function success_submit(){
	alert("提交成功！");
	window.location.reload();//重置表单
}
