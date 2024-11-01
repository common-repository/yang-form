<?php
class upfile {
	//上传文件信息
	var $filename;
	// 保存名
	var $savename;
	// 原文件保存路径
	var $savepath = '../upfiles';
	//水印保存路径
	var $waterpath = 'watermark';
	// 文件格式限定，为空时不限制格式
	var $format = "";
	// 覆盖模式
	var $overwrite = 0;
	/* $overwrite = 0 时不覆盖同名文件
	 * $overwrite = 1 时覆盖同名文件
	 */
	//文件最大字节
	var $maxsize = 200000;
	//文件扩展名
	var $ext;
	//错误代号
	var $errno = 0;

	/* 构造函数
	 * $path 保存路径
	 * $format 文件格式(用逗号分开)
	 * $maxsize 文件最大限制
	 * $over 复盖参数
	 */
	function upfile($format = '',$path = '',$maxsize = 0, $over = 0) {
		if (empty($path)) {
			$path = $this->savepath.'/'.date('Ymd').'/';
			/* $water = $this->savepath.'/'.date('Ymd').'/watermark/';
			if (!file_exists($water)) {
				if (!$this->make_dir($water)) {
					return $this->halt('creat water error');
				}
			}
			$this->waterpath = $water;*/
		} else {
			$this->savepath = substr($path,  - 1) == "/" ? $path : $path."/";
		}
		if (!file_exists($path)) {
			if (!$this->make_dir($path)) {
				return $this->halt('Creat folder error');
			}
		}
		
		$this->savepath = $path;
		$this->overwrite = $over; //是否复盖相同名字文件
		$this->maxsize = !$maxsize ? $this->maxsize: $maxsize; //文件最大字节
		$this->format = $format;
	}

	/*
	 * 功能：检测并组织文件
	 * $form 文件域名称
	 * $file 上传文件保存名称，为空或者上传多个文件时由系统自动生成名称
	 */
	function upload($form, $file = "",$uids=0) {
		if (is_array($form)) {
			$filear = $form;
		} else {
			$filear = $_FILES[$form];
		}
		if (!is_writable($this->savepath)) {
			$this->halt("Creat folder error");
		}
		$this->getext($filear["name"]); //取得扩展名
		$this->set_savename($file,$uids); //设置保存文件名
		$this->copyfile($filear);
		//return $this->savepath.$this->savename;
		return $this->savename;
	}

	/*
	 * 功能：检测并复制上传文件
	 * $filear 上传文件资料数组
	 */
	function copyfile($filear) {
		if ($filear["size"] > $this->maxsize) {
			$this->halt(" ".$filear["name"]." more than[".$this->maxsize." byte],upload fail");
		}
		
		if (!$this->overwrite && file_exists($this->savename)) {
			$this->halt($this->savename." file existed");
		}

		if ($this->format != "" && !in_array(strtolower($this->ext), explode(",",strtolower($this->format)))) {
			$this->halt($this->ext." File format error");
		}
		
		if (!copy($filear["tmp_name"], $this->savepath.$this->savename)) {
			$errors = array(0 => "Upload success", 1 =>
				"upload error", 2 => "upload error", 3 => "upload error", 4 => "upload error");
			$this->halt($errors[$filear["error"]]);
		} else {
			@unlink($filear["tmp_name"]); //删除临时文件
		}
	}

	/*
	 * 功能: 取得文件扩展名
	 * $filename 为文件名称
	 */
	function getext($filename) {
		if ($filename == "") {
			return ;
		}
		
		$ext = explode(".", $filename);
		$len=count($ext);
		return $this->ext = $ext[$len-1];
	}

	/*
	 * 功能: 设置文件保存名
	 * $savename 保存名，如果为空，则系统自动生成一个随机的文件名
	 */
	function set_savename($savename = "",$uids=0) {
		if ($savename == ""){ // 如果未设置文件名，则生成一个随机文件名
			srand((double)microtime() * 1000000);
			$rnd = rand(100, 999);
			$name = date('U') + $rnd;
			$name = $uids.$name.".".$this->ext;
		} else {
			$name = $savename.".".$this->ext;
		}
		return $this->savename = $name;
	}

	/*
	 * 功能：错误提示
	 * $msg 为输出信息
	 */
	function halt($msg) {
		//echo "<script>alert('".$msg."');</Script>";
		echo("<script charset=\"utf-8\">parent.callback_error('".json_encode($msg)."')</script>");
		exit;
	}
	
	/**缩图图片宽高判断处理**/
	function setWidthHeight($width, $height, $maxwidth, $maxheight) {
		if ($width > $height) {
			if ($width > $maxwidth) {
				$difinwidth = $width / $maxwidth;
				$height = intval($height / $difinwidth);
				$width = $maxwidth;
				if ($height > $maxheight) {
					$difinheight = $height / $maxheight;
					$width = intval($width / $difinheight);
					$height = $maxheight;
				}
			} else {
				if ($height > $maxheight) {
					$difinheight = $height / $maxheight;
					$width = intval($width / $difinheight);
					$height = $maxheight;
				}
			}
		} else {
			if ($height > $maxheight) {
				$difinheight = $height / $maxheight;
				$width = intval($width / $difinheight);
				$height = $maxheight;
				if ($width > $maxwidth) {
					$difinwidth = $width / $maxwidth;
					$height = intval($height / $difinwidth);
					$width = $maxwidth;
				}
			} else {
				if ($width > $maxwidth) {
					//Rescale it.
					$difinwidth = $width / $maxwidth;
					$height = intval($height / $difinwidth);
					$width = $maxwidth;
				}
			}
		}
		
		$widthheightarr = array("$width", "$height");
		return $widthheightarr;
	}
	
	/**
	 * 根据来源文件的文件生成缩图
	 *
	 * @access public
	 * @param string $img 原始图片的路径
	 * @param string $constrainw 缩略图的宽度
	 * @param string $constrainh 缩略图的高度
	 * @return resource 如果成功则返回完整路径文件名
	 */
	function createthumb($img,$constrainw,$constrainh,$tofilename="") {
		$oldsize = getimagesize($img);
		$newsize = $this->setWidthHeight($oldsize[0], $oldsize[1], $constrainw, $constrainh);
		$exp = explode(".", $img);
		if ($exp[3] == "gif") {
			$src = imagecreatefromgif($img);
		} elseif ($exp[3] == "png") {
			$src = imagecreatefrompng($img);
		} else {
			$src = imagecreatefromjpeg($img);
		}
		$dst = imagecreatetruecolor($newsize[0], $newsize[1]);
		imagecopyresampled($dst, $src, 0, 0, 0, 0, $newsize[0], $newsize[1], $oldsize[0], $oldsize[1]);
		$path = $this->savepath.'thumb/';
		if (!file_exists($path)) {
			if (!$this->make_dir($path)) {
				return $this->halt('创建目录失败');
			}
		}
		$thumbname = $path."thumb_".$this->savename;
		if ($tofilename) $thumbname = $path."thumb_".$tofilename;
		if ($exp[3] == "gif") {
			imagegif($dst, $thumbname);
		}else if ($exp[3] == "png") {
			imagepng($dst, $thumbname);
		}else if ($exp[3] == "jpg") {
			imagejpeg($dst, $thumbname);
		} else {
			imagejpeg($dst, $thumbname);
		}
		imagedestroy($dst);
		imagedestroy($src);
		if ($tofilename) {
			return "thumb_".$tofilename;
		} else {
			return "thumb_".$this->savename;
		}
	}

	function make_dir($folder) {
		$reval = false;
		if (!file_exists($folder)) {
			@umask(0);
			preg_match_all('/([^\/]*)\/?/i', $folder, $atmp);
			$base = ($atmp[0][0] == '/') ? '/' : '';
			foreach($atmp[1]AS $val) {
				if ('' != $val) {
					$base .= $val;
					if ('..' == $val || '.' == $val) {
						$base .= '/';
						continue;
					}
				} else {
					continue;
				}
				
				$base .= '/';
				if (!file_exists($base)) {
					if (@mkdir($base, 0777)) {
						@chmod($base, 0777);
						$reval = true;
					}
				}
			}
		} else {
			$reval = is_dir($folder);
		}
		clearstatcache();
		return $reval;
	}
}
