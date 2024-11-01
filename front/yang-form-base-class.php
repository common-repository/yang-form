<?php
/*****************************/
/*	author:zetd@vip.sina.com */
/*	date:2012/03/20          */
/*	yang-form-get              */
/*****************************/
/**
 * cgi�����ȡ��
 */
class cgi
{
	/**
	 * ��get��ʽȡcgi����
	 * @return mexid
	 * @param	string	$cgivv
	 *			string	$cgi_instr
	 *			integer	$defval
	 */
	static function get(&$cgivv, $cgi_instr, $defval = 0)
	{
		return cgi::input($cgivv, $cgi_instr, $defval, 0);
	}
	
	/**
	 * ��get��ʽȡcgi����
	 * @return mexid
	 * @param	string	$cgivv
	 *			string	$cgi_instr
	 *			integer	$defval
	 */
	static function post(&$cgivv, $cgi_instr, $defval = 0)
	{
		return cgi::input($cgivv, $cgi_instr, $defval, 1);
	}
	
	/**
	 * ��get��ʽȡcgi����
	 * @return mexid
	 * @param	string	$cgivv
	 *			string	$cgi_instr
	 *			integer	$defval
	 */
	static function both(&$cgivv, $cgi_instr, $defval = 0)
	{
		return cgi::input($cgivv, $cgi_instr, $defval, 2);
	}
	
	/**
	 * ȡpost��ʽ�ı���ֵ
	 * @return mexid
	 * @param	string	$v	ȡֵ������
	 */
	static function _method_post($v)
	{
		if (isset($_POST[$v]))
		{
			return $_POST[$v];
		}
	}
	
	/**
	 * ȡpost��ʽ�ı���ֵ
	 * @return mexid
	 * @param	string	$v	ȡֵ������
	 */
	static function _method_get($v)
	{
		if (isset($_GET[$v]))
		{
			return $_GET[$v];
		}
	}
	
	/**
	 * ȡpost��ʽ��������ڣ�ȡget��ʽ�ı���ֵ
	 * @return mexid
	 * @param	string	$v	ȡֵ������
	 */
	static function _method_both($v)
	{
		if (isset($_POST[$v]))
		{
			return $_POST[$v];
		}
		else if (isset($_GET[$v]))
		{
			return $_GET[$v];
		}
	}

	/**
	 * CGI��������
	 * @return mexid
	 * @param	string	$cgivv
	 *			string	$cgi_instr
	 *			integer	$defval
	 */
	static function input(&$cgivv, $cgi_instr, $defval, $cgitype)
	{
		$cgi_in = NULL;
		switch($cgitype)
		{
			case 1:
				$cgi_in = cgi::_method_post($cgi_instr);
				break;
			case 2:
				$cgi_in = cgi::_method_both($cgi_instr);
				break;
			default:
				$cgi_in = cgi::_method_get($cgi_instr);
				break;
		}
		
		if (is_null($cgi_in) or $cgi_in == '')
		{
			if (is_numeric($cgivv))
			{
				$cgivv = $defval + 0;
			}
			else
			{
				$cgivv = $defval . '';
			}
			return false;
		}
		else
		{
			if (is_numeric($defval))
			{
				if (!is_numeric($cgi_in))	// ���Ҫ������ֵ���������Ƿ���ֵ
				{
					$cgivv	= $defval + 0;
					return false;
				}
			}
			$cgivv	= $cgi_in;
			return true;
		}
	}
}

/**
 * cgiУ����
 */
class cgi_chk
{
	/**
	 * ��֤Email�ĺϷ���
	 * @return boolean
	 * @param string $str Email�ַ���
	 */
	function email($str)
	{
		return preg_match('/^[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)*@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+){1,4}$/', $str) ? true : false;
	}
	
	/**
	 * ��֤��ݵĺϷ���
	 * @return boolean
	 * @param string $str ����ַ���
	 */
	function year($str)
	{
		if (is_numeric($str))
		{
			preg_match('/^19|20[0-9]{2}$/', $str) ? true : false;
		}
		return false;
	}

	/**
	 * ��֤�·ݵĺϷ���
	 * @return boolean
	 * @param string $str �·��ַ���
	 */
	function month($str)
	{
		if (is_numeric($str) && $str > 0 && $str < 13)
		{
			return true;
		}
		return false;
	}

	/**
	 * ��֤���ڵĺϷ���
	 * @return boolean
	 * @param string $str �·��ַ���
	 */
	function day($str)
	{
		if (is_numeric($str) && $str > 0 && $str < 32)
		{
			return true;
		}
		return false;
	}
	
	/**
	 * ���URL�ĺϷ��ԣ����URLͷ�Ƿ�Ϊ http, https, ftp
	 * @return boolean
	 * @param string $str ����ַ���
	 */
	function uri($str)
	{
		$allow = array('http', 'https', 'ftp');
		
		if (preg_match('!^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?!', $str, $matchs))
		{
			$scheme = $matches[2];
			if (in_array($scheme, $allow))
			{
				return true;
			}
		}
		return false;
	}


	// �ж��ַ��������Ƿ���Ч
	static function check_str_len($str, $len_max, $len_min = 0)
	{
		$len = string::length($str);
		if ($len > $len_max)
			return false;
	
		if ($len < $len_min)
			return false;
	
		return true;
	}

	// �ж��ַ��������Ƿ���Ч(�����ַ���1��)
	function check_str_len_new($str)
	{

		$str = preg_replace("/[".chr(0x80)."-".chr(0xff)."]{2}/",'*', $str);
		$len = string::length($str);

		return $len;

	}
	
	/**
	 * ֧��utf�ļ����ַ������Ⱥ���
	 * @author liujia0905@gmail.com
	 * @param $str
	 * @return int
	 */
	function check_str_len_utf8($str)
	{
		$n = 0; $p = 0; $c = '';
		$len = strlen($str);

		for($i = 0; $i < $len; $i++) 
		{
			$c = ord($str{$i});
			if($c > 252) 
			{
				$p = 5;
			} 
			elseif($c > 248) 
			{
				$p = 4;
			} 
			elseif($c > 240) 
			{
				$p = 3;
			} 
			elseif($c > 224) 
			{
				$p = 2;
			} 
			elseif($c > 192) 
			{
				$p = 1;
			} 
			else 
			{
				$p = 0;
			}
			$i+=$p;
			$n++;
		}
		
		return $n;
	}
}
/**
 * STRING������
 * @package baseLib
 * @author ���о� <shijun@staff.sina.com.cn>
 * @version 1.0
 * @copyright (c) 2005, �������з����� All rights reserved.
 * @example ./string.php �鿴Դ����
 * @example ./string.example.php ���ʹ���������(���ڻ���������)
 */

Class string
{
	function string()
	{
		/****/
	} 
	
	/**
	 * �����ȡ�����ַ����Ĳ���
     * @return string
     * @param string $str Ҫ������ַ�
	 * 		  string $start ��ʼλ��
	 *        string $offset ƫ����
	 *        string $t_str �ַ����β�����ӵ��ַ�����Ĭ��Ϊ��
	 *        boolen $ignore $startλ������������ĵ�ĳ���ֺ�벿���Ƿ���Ը��ַ���Ĭ��true
	 */
	
	function substr_cn($str, $start, $offset, $t_str = '', $ignore = true)
	{
	 	$length  = strlen($str);
		if ($length <=  $offset && $start == 0)
		{
			return $str;
		}
		if ($start > $length)
		{
			return $str;
		}
		$r_str     = "";
		for ($i = $start; $i < ($start + $offset); $i++)
		{ 
			if (ord($str{$i}) > 127)
			{
				if ($i == $start)  //���ͷһ���ַ���ʱ���Ƿ���Ҫ���԰������
				{
					if (string::is_cn_str($str, $i) == 1)
					{
						if ($ignore)
						{
							continue;
						}
						else
						{
							$r_str .= $str{($i - 1)}.$str{$i};
						}
					}
					else
					{
						$r_str .= $str{$i}.$str{++$i};
					}
				}
				else
				{
					$r_str .= $str{$i}.$str{++$i};
				}
			}
			else
			{
				$r_str .= $str{$i};
				continue;
			}
		}
		return $r_str . $t_str;
		//return preg_replace("/(&)(#\d{5};)/e", "string::un_html_callback('\\1', '\\2')", $r_str . $t_str);
		
	}
	
	/**
	function un_html_callback($a, $b){
        	if ($b){
                	return $a. $b;
        	}
        	return '&amp;';
	}
	**/
	
	//-- �ж��ַ����Ƿ��зǷ��ַ� -------
	function check_badchar($str, $allowSpace = false)
	{
		if ($allowSpace)
			return preg_match ("/[><,.\][{}?\/+=|\\\'\":;~!@#*$%^&()`\t\r\n-]/i", $str) == 0 ? true : false;
		else
			return preg_match ("/[><,.\][{}?\/+=|\\\'\":;~!@#*$%^&()` \t\r\n-]/i", $str) == 0 ? true : false;
	}
	
	/**
	 * �ж��ַ�ĳ��λ���������ַ�����벿�ֻ����Ұ벿�֣���������
	 * ���� 1 ����� 0 �������� -1���ұ�
     * @return int
	 * @param string $str ��ʼλ��
	 * @param int $location λ��
	 */
	 
	function is_cn_str($str, $location)
	{ 
		$result	= 1;
		$i		= $location;
		while(ord($str{$i}) > 127 && $i >= 0)
		{ 
			$result *= -1; 
			$i --; 
		} 
		
		if($i == $location)
		{ 
			$result = 0; 
		} 
		return $result; 
	} 
	
	/**
	 * �ж��ַ��Ƿ�ȫ�������ַ����
	 * 2 ȫ�� 1������ 0û������
     * @return boolean
	 * @param string $str Ҫ�жϵ��ַ���
	 */
	 
	function chk_cn_str($str)
	{ 
		$result = 0;
		$len = strlen($str);
		for ($i = 0; $i < $len; $i++)
		{
			if (ord($str{$i}) > 127)
			{
				$result ++;
				$i ++;
			}
			elseif ($result)
			{
				$result = 1;
				break;
			}
		}
		if ($result > 1)
		{
			$result = 2;
		}
		return $result;
	} 
	
	/**
	 * �ж��ʼ���ַ����ȷ��
	 * @return boolean
	 * @param string $mail �ʼ���ַ
	 */
	
	function is_mail($mail)
	{
		//return preg_match("/^[a-z0-9_\-\.]+@[a-z0-9_]+\.[a-z0-9_\.]+$/i" , $mail);
		return preg_match('/^[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)*@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+){1,4}$/', $mail) ? true : false;
	}
	
	/**
	 * �ж�App��CallbackURL�Ƿ�Ϸ������԰����˿ںţ�
	 * @return boolean
	 * @param string $url URL��ַ
	 */
	
	function is_callback_url($url)
	{
		return  preg_match("/(ht|f)tp(s?):\/\/([\w-]+\.)+[\w-]+(\/[\w-.\/?%&=]*)?/i" , $url);
	}
	
	/**
	 * �ж�URL�Ƿ���http(s):// ftp://��ʽ��ʼ�ĵ�ַ
	 * @return boolean
	 * @param string $url URL��ַ
	 */
	
	function is_http_url($url)
	{
		return  preg_match("/^(https?|ftp):\/\/([\w-]+\.)+[\w-]+(\/[\w;\/?:@&=+$,# _.!~*'\"()%-]*)?$/i" , $url);
		//return preg_match("/^(http(s)|ftp):\/\/[a-z0-9\.\/_-]*?$/i" , $url);
	}
	
	/**
	 * ��������
	 */
	function is_url($url)
	{
		//return  preg_match("/^(https?|ftp|mms|mmsu|mmst|rtsp):\/\/([\w-]+\.)+[\w-]+(\/[\w;\/?:@&=+$,# _.!~*'\"()%-]*)?$/i" , $url);
		//return  preg_match("/^(https?|ftp|mms|mmsu|mmst|rtsp):\/\/([\w-]+\.)+[\w-]+(\/[^ \t\r\n{}\[\]`^<>\\\\]*)?$/i" , $url);
		//return preg_match("/^(http(s)|ftp):\/\/[a-z0-9\.\/_-]*?$/i" , $url);
		return preg_match("/^(https?|ftp|mms|mmsu|mmst|rtsp):\/\/([\w-]+\.)+[\w-]+(:\d{1,9}+)?(\/[^ \t\r\n{}\[\]`^<>\\\\]*)?$/i" , $url);

	}

	/**
	 * �ж�URL�Ƿ�����ȷ�����ֵ�ַ
	 * @return boolean
	 * @param string $url URL��ַ
	 */
	
	function is_music_url($url)
	{
		return preg_match("/^(https?|ftp|mms|mmsu|mmst|rtsp):\/\/([\w-]+\.)+[\w-]+(:\d{1,9}+)?(\/[^ \t\r\n{}\[\]`^<>\\\\]*)?$/i" , $url);
		//return preg_match("/^(https?|ftp|mms|mmsu|mmst|rtsp):\/\/([\w-]+\.)+[\w-]+(\/[^ \t\r\n{}\[\]`^<>\\\\]*)?$/i" , $url);
		//return  preg_match("/^(https?|ftp|mms|mmsu|mmst|rtsp):\/\/([\w-]+\.)+[\w-]+(\/[\w;\/?:@&=+$,# _.!~*'\"()%-]*)?$/i" , $url);
		//return preg_match("/^(http(s)|ftp):\/\/[a-z0-9\.\/_-]*?$/i" , $url);
	}

	/**
	 * �ж�URL�Ƿ�����ĵ�ַ
	 * @return boolean
	 * @param string $url URL��ַ
	 */
	
	function is_my_url($url,$site)
	{
		//return preg_match("/^http:\/\/[a-z0-9_]*?.sina.com.cn[a-z0-9\.\/_-]*?$/i" , $url);
		return  preg_match("/^https?:\/\/([\w-]+\.)+".$site."(\/[\w;\/?:@&=+$, _.!~*'\"()%-]*)?$/i" , $url);
	}
	

	/**
	 * �����ַ����е������ַ�
	 * @return string
	 * @param string $str ��Ҫ���˵��ַ�
	 * @param string $filtStr ��Ҫ�����ַ������飨�±�Ϊ��Ҫ���˵��ַ���ֵΪ���˺���ַ���
	 * @param boolen $regexp �Ƿ�����������Խ����滻��Ĭ��false
	 */
	
	function filt_string($str, $filtStr, $regexp = false)
	{
		if (!is_array($filtStr))
		{
			return $str;
		}
		$search		= array_keys($filtStr);
		$replace	= array_values($filtStr);
				
		if ($regexp)
		{
			return preg_replace($search, $replace, $str);
		}
		else
		{
			return str_replace($search, $replace, $str);
		}
	}
	
	/**
	 * �����ַ����е�HTML��� < >
	 * @return string
	 * @param string $str ��Ҫ���˵��ַ�
	 */
	
	function un_html($str)
	{
			$s	= array(
				"&"     => "&amp;",
				"<"	=> "&lt;",
				">"	=> "&gt;",
				"\n"	=> "<br>",
				"\t"	=> "&nbsp;&nbsp;&nbsp;&nbsp;",
				"\r"	=> "",
				" "	=> "&nbsp;",
				"\""	=> "&quot;",
				"'"	=> "&#039;",
			);
		//$str = string::esc_korea_change($str);
		$str = strtr($str, $s);
		//$str = string::esc_korea_restore($str);
		return $str;
	}
	
	/**
	 * �����ַ����������ַ����Ա�����ݴ���mysql���ݿ�
	 */
	function esc_mysql($str)
	{
		return mysql_escape_string($str);
	}

	/**
	 * �����ַ����������ַ����Ա�����������ҳ�����༭��ʾ
	 */
	function esc_edit_html($str)
	{
		$s	= array(
			//"&"     => "&amp;",
			"<"		=> "&lt;",
			">"		=> "&gt;",
			"\""	=> "&quot;",
			"'"		=> "&#039;",
		);
		$str = string::esc_korea_change($str);
		$str = strtr($str, $s);
		$str = string::esc_korea_restore($str);        
		return $str;
	}


	/**
	 * �����ַ����������ַ����Ա�����������ҳ���������ʾ
	 */
	function esc_show_html($str)
	{
		$s	= array(
			"&"     => "&amp;",
			"<"		=> "&lt;",
			">"		=> "&gt;",
			"\n"	=> "<br>",
			"\t"	=> "&nbsp;&nbsp;&nbsp;&nbsp;",
			"\r"	=> "",
			" "		=> "&nbsp;",
			"\""	=> "&quot;",
			"'"		=> "&#039;",
		);
		
		
		$str = string::esc_korea_change($str);
		$str = strtr($str, $s);
		$str = string::esc_korea_restore($str);
		return $str;
	}
	
       
	function esc_ascii($str)
	{
		$esc_ascii_table = array(
   	    	chr(0),chr(1), chr(2),chr(3),chr(4),chr(5),chr(6),chr(7),chr(8),
   		    chr(11),chr(12),chr(14),chr(15),chr(16),chr(17),chr(18),chr(19),
      		chr(20),chr(21),chr(22),chr(23),chr(24),chr(25),chr(26),chr(27),chr(28),
        	chr(29),chr(30),chr(31)
		);


		$str = str_replace($esc_ascii_table, '', $str);
		return $str;
	}

	function esc_user_input($str)
	{
		//$str = iconv("utf-8", "gb2312", $str);
		$str = iconv("utf-8", "gbk//IGNORE", $str);
		// ���˷Ƿ��ʻ�

		// ���˷Ƿ�ASCII�ַ���
		$str = string::esc_ascii($str);

		// ����SQL���	
		//$str = string::esc_mysql($str);
		

		return $str;
	}
	
	/**
	 * �����ַ����е�<script ...>....</script>
	 * @return string
	 * @param string $str ��Ҫ���˵��ַ�
	 */
	 
	function un_script_code($str)
	{
		$s			= array();
		$s["/<script[^>]*?>.*?<\/script>/si"] = "";
		return string::filt_string($str, $s, true);
	}
	
	/**
	 * ��HTML����ת��ducument.write���������
	 * @return string
	 * @param string $html ��Ҫ�����HTML����
	 */
	 
	function html2script($html)
	{
		//��Ҫ����ת����ַ�
		$s			= array();
		$s["\\"]	= "\\\\";
		$s["\""]	= "\\\"";
		$s["'"]		= "\\'";
		$html = string::filt_string($html, $s);
		$html = implode("\\\r\n", explode("\r\n", $html));
		
		return "document.write(\"\\\r\n" . $html . "\\\r\n\");";
	}
	
	// ת��js��������غϷ���js�ַ���
	function js_esc($str)
	{
		$s_tag = array("\\", "\"", "/", "\r", "\n");
		$r_tag = array("\\\\", "\\\"", "\/", "\\r", "\\n");
		$str = str_replace($s_tag, $r_tag, $str);

		return $str;
	}

	/**
	 * ��ducument.write���������ת����HTML����(������html2script��������ת���Ľ��)
	 * @return string
	 * @param string $jsCode ��Ҫ�����JS����
	 */
	 	 
	function script2html($jsCode)
	{
		$html = explode("\\\r\n", $jsCode);
		array_shift($html);		//ȥ�����鿪ͷ��Ԫ
		array_pop($html);		//ȥ������ĩβ��Ԫ
		return implode("\r\n", $html);
	}

	static function length($str)
	{
		$str = preg_replace("/&(#\d{5});/", "__", $str);
		return strlen($str);
	}
	
}


function GetIP()
{
	if(!empty($_SERVER["HTTP_CLIENT_IP"]))
	   $cip = $_SERVER["HTTP_CLIENT_IP"];
	else if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
	   $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	else if(!empty($_SERVER["REMOTE_ADDR"]))
	   $cip = $_SERVER["REMOTE_ADDR"];
	else
	   $cip = "";
	return $cip;
}

?>
