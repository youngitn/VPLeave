<?php
session_start();
header("Cache-control:private");
header("Content-Type: text/html; charset=utf-8");
date_default_timezone_set("Asia/Taipei");
putenv("TZ=Asia/Taipei");
error_reporting(0);
include("connect.php");

//分隔線----------------------------------------------------------------------------------------------------------------

$page = (intval($_GET["page"]) <= 0) ? 1 : intval($_GET["page"]);	//目前在第幾頁

function ConnectDB($DB, $sql)
{
	global $DB;
	$conn = mysql_connect($DB["HostName"], $DB["UserName"], $DB["Password"]);
	if (!$conn) die("無法連接，請確認帳號及密碼是否正確");
	if (!mysql_select_db($DB["Database"], $conn)) die("資料庫連接失敗");
	$rs = mysql_query($sql, $conn);
	mysql_query("SET NAMES 'utf8'");
	return $rs;
}

function ConnectOracle($Oracle, $sql)
{
	global $Oracle;
	$conn = oci_connect($Oracle["UserName"], $Oracle["Password"], $Oracle["ConnStr"], $Oracle["Charset"]);
	if (!$conn) {
		//var_dump(oci_error());
		//$e = oci_error();
		//trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
		die("無法連接Oracle，請確認帳號及密碼是否正確");
	}
	$rs = oci_parse($conn, $sql);
	return $rs;
}

function setWeb()
{	//上線更改
	return "http://leave.vpcomponents.com";
}

//上稿轉成絕對路徑
function OnlineRoot($str)
{
	$str = str_replace("/uploadfiles/", setWeb() . "/uploadfiles/", $str);
	return $str;
}

function RunJs($url, $msg = null)
{
	echo "<script language=\"javascript\">";
	if ($msg != null) echo "alert('" . $msg . "');";
	if ($url != "") echo "location.href='" . $url . "';";
	echo "</script>";
	die();
}

function RunAlert($msg)
{
	die("<script language=\"javascript\">alert('" . $msg . "');window.history.go(-1);</script>");
}

//編輯
function str_edit($str)
{
	$str = str_replace(array("&acute;", "\"", "\&quot;", "&lt;", "&gt;"), array("'", "&quot;", "&quot;", "<", ">"), $str);
	$str = trim($str);
	return $str;
}

//過瀘
function str_filter($str)
{
	$str = str_replace(array("'", "\"", "<", ">"), array("&acute;", "&quot;", "&lt;", "&gt;"), $str);
	$str = trim($str);
	return $str;
}

//顯示
function str_front($str)
{
	$str = str_replace(array("&acute;", "&quot;", "&lt;", "&gt;"), array("'", "\"", "<", ">"), $str);
	$str = trim($str);
	return $str;
}

function getPwd($password_len = 8)
{
	//$word = "abcdefghijkmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ0123456789";
	$word = "0123456789";
	$len = strlen($word);
	for ($i = 0; $i < $password_len; $i++) $password .= $word[rand() % $len];
	return $password;
}

//列表頁搜尋
function list_search($field_array)
{
	$list_search[sql_sub] = " Where 1 = 1 ";
	$field = $_POST["field"] ? str_filter($_POST["field"]) : str_filter($_GET["field"]);	//欄位
	$keyword = $_POST["keyword"] ? str_filter($_POST["keyword"]) : str_filter($_GET["keyword"]);	//關鍵字
	if ($keyword != "") {
		$keywords = explode(" ", $keyword);
		for ($i = 0; $i < sizeof($keywords); $i++) {
			if ($keywords[$i] != "") $list_search[sql_sub] .= " and (" . $field . " like '%" . $keywords[$i] . "%') ";
		}
		$list_search[link] = "&field=" . $field . "&keyword=" . urlencode($keyword);
	}

	foreach ($field_array as $_key => $_value) {
		$field_list .= "\n\t  <option value=\"" . $_key . "\"";
		if ($field == $_key) $field_list .= " selected";
		$field_list .= ">" . $_value . "</option>";
	}

	echo "<input name=\"keyword\" type=\"text\" value=\"" . $keyword . "\" maxlength=\"100\" />
    <select name=\"field\">" . $field_list . "
    </select>
    <input name=\"submit\" type=\"submit\" value=\"搜尋\" />
";

	$list_search[hidden] = "
  <input type=\"hidden\" name=\"field\" value=\"" . $field . "\" />
  <input type=\"hidden\" name=\"keyword\" value=\"" . $keyword . "\" />
";

	return $list_search;
}

//列表頁取得總頁數和總紀錄數
function list_paging($page, $record_per_page)
{
	$sql = "SELECT FOUND_ROWS() as counter";	//取得紀錄數
	$rs = ConnectDB($DB, $sql);
	$total_records = mysql_result($rs, 0, "counter");

	if ($record_per_page <= 0) $record_per_page = 1;			//每頁幾筆
	$total_pages = ceil($total_records / $record_per_page);	//計算總頁數
	if ($page > $total_pages) $page = $total_pages;			//修正輸入過大的頁數

	$list_paging[pages] = $total_pages;		//總頁數
	$list_paging[records] = $total_records;	//總紀錄數

	return $list_paging;
}

//列表頁分頁
function list_page($url, $page, $total_pages, $total_records, $hidden, $search)
{
	/*
	$url: 連結目標
	$page: 目前頁數
	$total_pages: 總頁數
	$total_records: 總紀錄數
	*/

	$symbol = (strpos($url, "?") !== false) ? "&" : "?";
	if ($search) $search = "&" . trim($search, "&");

	if ($total_pages >= 100) {
		$s_page = $page - 3;
		$e_page = $page + 3;
	} else {
		$s_page = $page - 5;
		$e_page = $page + 5;
	}
	if ($s_page < 1) $s_page = 1;
	if ($e_page > $total_pages) $e_page = $total_pages;

	if ($total_pages > 1) {
		$page_list = "<!--分頁開始-->\n  <div id=\"page\">\n\t<div id=\"num\">\n";
		if ($page > 1) {
			$page_list .= "\t  <a href=\"" . $url . $symbol . "page=1" . $search . "\">«</a>\n\t  <a href=\"" . $url . $symbol . "page=" . ($page - 1) . $search . "\">‹</a>\n";
		}

		for ($i = $s_page; $i <= $e_page; $i++) {
			if ($i == $page) {
				$page_list .= "\t  <a class=\"stay\">" . $i . "</a>\n";
			} else {
				$page_list .= "\t  <a href=\"" . $url . $symbol . "page=" . $i . $search . "\">" . $i . "</a>\n";
			}
		}

		if ($page < $total_pages) {
			$page_list .= "\t  <a href=\"" . $url . $symbol . "page=" . ($page + 1) . $search . "\">›</a>\n\t  <a href=\"" . $url . $symbol . "page=" . $total_pages . $search . "\">»</a>\n";
		}
		$page_list .= "\t</div>\n";

		//跳頁
		$page_list .= "\t<div id=\"statistics\">\n";
		$page_list .= "\t  <select name=\"jump\" onchange=\"MM_jumpMenu('self', this, 0)\">\n";
		if ($total_pages >= 100) {	//大於100頁
			for ($i = 1; $i <= $total_pages; $i = $i + 10) {
				$page_list .= "\t  <option value=\"" . $url . $symbol . "page=" . $i . $search . "\"";
				if ($page == $i) $page_list .= " selected";
				$page_list .= ">第 " . $i . " 頁</option>\n";
			}

			//最後一頁
			if (($i - 10) != $total_pages) {
				$i = $total_pages;
				$page_list .= "\t  <option value=\"" . $url . $symbol . "page=" . $i . $search . "\"";
				if ($page == $i) $page_list .= " selected";
				$page_list .= ">第 " . $i . " 頁</option>\n";
			}
		} else {
			for ($i = 1; $i <= $total_pages; $i++) {
				$page_list .= "\t  <option value=\"" . $url . $symbol . "page=" . $i . $search . "\"";
				if ($page == $i) $page_list .= " selected";
				$page_list .= ">第 " . $i . " 頁</option>\n";
			}
		}
		$page_list .= "\t</select>共" . number_format($total_records) . "筆／共" . number_format($total_pages) . "頁\n\t</div>\n  </div>\n  <!--分頁結束-->\n";
	}
	$page_list .= "  <input type=\"hidden\" name=\"page\" value=\"" . $page . "\" />" . $hidden;

	echo $page_list;
}

//前台分頁
function front_page($url, $page, $total_pages, $total_records, $search)
{
	/*
	$url: 連結目標
	$page: 目前頁數
	$total_pages: 總頁數
	$total_records: 總紀錄數
	*/

	$symbol = (strpos($url, "?") !== false) ? "&" : "?";
	if ($search) $search = "&" . trim($search, "&");

	$s_page = $page - 5;
	$e_page = $page + 5;
	if ($s_page < 1) $s_page = 1;
	if ($e_page > $total_pages) $e_page = $total_pages;

	if ($total_pages > 1) {
		$page_list = "<div><div class=\"pager center\">";
		if ($page > 1) {
			$page_list .= "
			<span><a href=\"" . $url . $symbol . "page=1" . $search . "\"> « </a></span>
			<span><a href=\"" . $url . $symbol . "page=" . ($page - 1) . $search . "\"> ‹ </a></span>
			";
		}

		for ($i = $s_page; $i <= $e_page; $i++) {
			if ($i == $page) {
				$page_list .= "<span>" . $i . "</span>";
			} else {
				$page_list .= "<span><a href=\"" . $url . $symbol . "page=" . $i . $search . "\">" . $i . "</a></span>";
			}
		}

		if ($page < $total_pages) {
			$page_list .= "
			<span><a href=\"" . $url . $symbol . "page=" . ($page + 1) . $search . "\"> › </a></span>
			<span><a href=\"" . $url . $symbol . "page=" . $total_pages . $search . "\"> » </a></span>
			";
		}
		$page_list .= "</div></div>";
	}

	echo $page_list;
}

//上一則下一則	
function LeftRight($sql, $id)
{
	if ($sql == "" || $id == 0) return;
	$page_array = array();
	$rs = ConnectDB($DB, $sql);
	for ($i = 0; $i < mysql_num_rows($rs); $i++) {
		$row = mysql_fetch_assoc($rs);
		$page_array[$i] = $row["id"];
		$subject_array[$i] = $row["subject"];
	}

	$page = array_search($id, $page_array);
	$LeftRight[left] = intval($page_array[$page - 1]);
	$LeftRight[left_subject] = $subject_array[$page - 1];
	$LeftRight[right] = intval($page_array[$page + 1]);
	$LeftRight[right_subject] = $subject_array[$page + 1];

	return $LeftRight;
}

function CuttingStr($str, $strlen)
{
	//把' '先轉成空白
	$str = str_replace(' ', ' ', $str);

	$output_str_len = 0; //累計要輸出的擷取字串長度
	$output_str = ''; //要輸出的擷取字串

	//逐一讀出原始字串每一個字元
	for ($i = 0; $i < $strlen; $i++) { //擷取字數已達到要擷取的字串長度，跳出回圈
		if ($output_str_len >= $strlen) break;

		//取得目前字元的ASCII碼
		$str_bit = ord(substr($str, $i, 1));
		if ($str_bit < 128) {	//ASCII碼小於 128 為英文或數字字符
			$output_str_len += 1; //累計要輸出的擷取字串長度，英文字母算一個字數
			$output_str .= substr($str, $i, 1); //要輸出的擷取字串
		} elseif ($str_bit > 191 && $str_bit < 224) {	//第一字節為落於192~223的utf8的中文字(表示該中文為由2個字節所組成utf8中文字)
			$output_str_len += 2; //累計要輸出的擷取字串長度，中文字需算二個字數
			$output_str .= substr($str, $i, 2); //要輸出的擷取字串
			$i++;
		} elseif ($str_bit > 223 && $str_bit < 240) {	//第一字節為落於223~239的utf8的中文字(表示該中文為由3個字節所組成的utf8中文字)
			$output_str_len += 2; //累計要輸出的擷取字串長度，中文字需算二個字數
			$output_str .= substr($str, $i, 3); //要輸出的擷取字串
			$i += 2;
		} elseif ($str_bit > 239 && $str_bit < 248) {	//第一字節為落於240~247的utf8的中文字(表示該中文為由4個字節所組成的utf8中文字)
			$output_str_len += 2; //累計要輸出的擷取字串長度，中文字需算二個字數
			$output_str .= substr($str, $i, 4); //要輸出的擷取字串
			$i += 3;
		}
	}

	if ($str != $output_str)	//要輸出的擷取字串為空白時，輸出原始字串
		return ($output_str == '') ? $str : $output_str . "...";
	else
		return ($output_str == '') ? $str : $output_str;
}

//顯示兩個日期差異
function DateDiff($stime, $etime)
{
	$timeDiff = strtotime($etime) - strtotime($stime);
	//return floor($timeDiff / 60);		//分
	//return floor($timeDiff / (60 * 60));	//時
	return floor($timeDiff / (60 * 60 * 24)) + 1;	//日
}

//圖片
function ShowPic($pic, $root, $null)
{
	$pic_list = "";
	$file_array = explode("/", $pic);
	$Files = $file_array[0];
	if ($Files != "" && file_exists($root . $Files)) {
		$eFile = explode(".", $Files);
		foreach ($eFile as $value) $format = strtolower($value);
		if ($format == "jpg" || $format == "jpeg" || $format == "gif" || $format == "png") $pic_list = $root . $Files;
	}
	if ($pic_list == "") $pic_list = $null;

	return $pic_list;
}

//檔案
function ShowFile($filename, $root)
{
	$file_array = explode("/", trim($filename, "/"));
	$Files = $file_array[0];	//第幾個
	if ($Files != "" && file_exists($root . $Files))
		return $root . $Files;
	else
		return "#";
}

// ref http://plog.longwin.com.tw/programming/2007/08/20/php_image_resize_2007
function ImageResize($from_filename, $save_filename, $in_width = 800, $in_height = 600, $quality = 100)
{
	$allow_format = array("jpeg", "png", "gif");
	$sub_name = $t = "";

	$img_info = getimagesize($from_filename);
	$width    = $img_info["0"];
	$height   = $img_info["1"];
	$imgtype  = $img_info["2"];
	$imgtag   = $img_info["3"];
	$bits     = $img_info["bits"];
	$channels = $img_info["channels"];
	$mime     = $img_info["mime"];

	list($t, $sub_name) = split("/", $mime);
	if ($sub_name == "jpg") {
		$sub_name = "jpeg";
	}

	if (!in_array($sub_name, $allow_format)) return false;

	$percent = getResizePercent($width, $height, $in_width, $in_height);
	$new_width  = $width * $percent;
	$new_height = $height * $percent;

	$image_new = imagecreatetruecolor($new_width, $new_height);

	if (strpos($from_filename, ".jpeg") !== false || strpos($from_filename, ".jpg") !== false) {
		$image = imagecreatefromjpeg($from_filename);
		imagecopyresampled($image_new, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
		return imagejpeg($image_new, $save_filename, $quality);
	}

	if (strpos($from_filename, ".gif") !== false) {
		$image = imagecreatefromgif($from_filename);
		imagecopyresampled($image_new, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
		return imagegif($image_new, $save_filename, $quality);
	}

	if (strpos($from_filename, ".png") !== false) {
		$image = imagecreatefrompng($from_filename);
		imagealphablending($image_new, false);
		imagesavealpha($image_new, true);
		imagecopyresampled($image_new, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
		return imagepng($image_new, $save_filename, (int) $quality / 10 - 1);	//quality 0~9之間
	}
}

// ref http://plog.longwin.com.tw/programming/2007/08/20/php_image_resize_2007
function getResizePercent($source_w, $source_h, $inside_w, $inside_h)
{
	if ($source_w < $inside_w && $source_h < $inside_h) return 1;
	$w_percent = $inside_w / $source_w;
	$h_percent = $inside_h / $source_h;
	return ($w_percent > $h_percent) ? $h_percent : $w_percent;
}

//浮水印
function Watermark($image, $waterimage)
{
	$org_img_path = $image;			//原本圖片路徑
	$png_img_path = $waterimage;	//浮水印圖片路徑

	$org_img_size = getImageSize($org_img_path);	//讀取原本圖片大小資訊
	$png_img_size = getImageSize($png_img_path);	//讀取浮水印圖片大小資訊

	$org_img_x = 0;		//原本圖片擺放位置X
	$org_img_y = 0;		//原本圖片擺放位置Y

	$png_img_x = 0;		//浮水印圖片擺放位置X
	$png_img_y = 0;		//浮水印圖片擺放位置Y

	$org_img_w = $org_img_size[0];	//原本圖片寬
	$org_img_h = $org_img_size[1];	//原本圖片高

	$png_img_w = $png_img_size[0];	//浮水印圖片寬
	$png_img_h = $png_img_size[1];	//浮水印圖片高

	$org_img = imagecreatefromjpeg($org_img_path);	//原本圖片
	$png_img = imagecreatefrompng($png_img_path);	//浮水印圖片

	//合併圖片函式，將 $png_img 合併到 $org_img
	imagecopyresampled(
		$org_img,		//原本圖片
		$png_img,		//浮水印圖片
		$org_img_x,	//原本圖片擺放位置X
		$org_img_y,	//原本圖片擺放位置Y
		$png_img_x,	//浮水印圖片擺放位置X
		$png_img_y,	//浮水印圖片擺放位置Y
		$org_img_w,	//原本圖片寬
		$org_img_h,	//原本圖片高
		$png_img_w,	//浮水印圖片寬
		$png_img_h 	//浮水印圖片高
	);

	return imagejpeg($org_img, $image, 100);
}

//寄信
function SendMail($MailInfo)
{
	$sql = "Select Init_SMTP_Host, Init_SMTP_Port, Init_SMTP_Email, Init_SMTP_Pw, Init_SMTP_Secure From pdm_basicset ";
	$rs = ConnectDB($DB, $sql);
	for ($i = 0; $i < mysql_num_rows($rs); $i++) {
		$row = mysql_fetch_assoc($rs);
		foreach ($row as $_key => $_value) $$_key = str_edit($row[$_key]);
	}

	require($MailInfo[root] . "class/phpmailer/PHPMailerAutoload.php");

	$mail = new PHPMailer;
	$mail->SMTPDebug = 0;				//除錯模式, 0 = off, 1 = client messages, 2 = client and server messages
	$mail->Debugoutput = "html";
	$mail->CharSet = "utf-8";			//設定語言編碼
	$mail->Encoding = "base64";			//設定內容編碼方式
	$mail->isSMTP();					//send via SMTP (是否使用smtp寄信方式) 
	$mail->SMTPAuth = true;				//是否需要 smtp 驗證
	$mail->Host = $Init_SMTP_Host;		//SMTP Host
	$mail->Port = $Init_SMTP_Port;		//SMTP Port
	$mail->Username = $Init_SMTP_Email;	//SMTP 帳號
	$mail->Password = $Init_SMTP_Pw;	//SMTP 登入密碼
	if ($Init_SMTP_Secure != "") {		//gmail的SMTP主機需要使用SSL
		$mail->SMTPSecure = $Init_SMTP_Secure;
	}
	$mail->IsHTML(true);				//信件內容是否使用HTML方式編寫

	//寄件人
	$mail->setFrom($MailInfo[FromMail], $MailInfo[FromName]);

	//收件人
	$MailInfo[ToMail] = str_replace(";", ",", $MailInfo[ToMail]);
	$mail_array = explode(",", $MailInfo[ToMail]);
	$name_array = explode(",", $MailInfo[ToName]);
	for ($i = 0; $i < sizeof($mail_array); $i++) {
		if ($mail_array[$i] != "") {
			if ($name_array[0] != "")  $name_array[$i] = $name_array[0];
			if ($name_array[$i] == "") $name_array[$i] = $mail_array[$i];
			$mail->addAddress($mail_array[$i], $name_array[$i]);
		}
	}

	//密件副本
	$MailInfo[BccMail] = str_replace(";", ",", $MailInfo[BccMail]);
	if ($MailInfo[BccMail] != "") {
		$bcc_array = explode(",", $MailInfo[BccMail]);
		for ($i = 0; $i < sizeof($bcc_array); $i++) {
			if ($bcc_array[$i] != "") $mail->addBCC($bcc_array[$i]);
		}
	}

	//回信人
	$MailInfo[ReplyMail] = str_replace(";", ",", $MailInfo[ReplyMail]);
	if ($MailInfo[ReplyMail] != "") $mail->addReplyTo($MailInfo[ReplyMail], $MailInfo[ReplyName]);

	$mail->Subject = $MailInfo[subject];	//信件主旨
	$MailInfo[body] = "＊提醒您，本信件由系統自動發送，請勿直接回覆，謝謝＊<br /><br />" . $MailInfo[body];
	$mail->msgHTML($MailInfo[body]);		//信件內容
	//$mail->AltBody = $MailInfo[text];		//純文字信件內容
	if ($MailInfo[Files] != "") $mail->addAttachment($MailInfo[Files]);	//附件

	if (!$mail->Send()) {
		//改用mail()
		$subjects = "=?UTF-8?B?" . base64_encode($MailInfo[subject]) . "?=";	//信件主旨
		$body = iconv("UTF-8", "Big5", $MailInfo[body]);					//信件內容

		//寄件人
		$mail_from_array = explode(",", $MailInfo[FromMail]);

		//收件人
		$mail_array = explode(",", $MailInfo[ToMail], 2);
		$name_array = explode(",", $MailInfo[ToName]);

		$headers  = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=Big5\r\n";
		$headers .= "From: " . $MailInfo[FromName] . "<" . $mail_from_array[0] . ">\r\n";
		$headers .= "To: " . $name_array[0] . "<" . $mail_array[0] . ">\r\n";
		if ($mail_array[1] != "") $headers .= "Cc: " . $mail_array[1] . "\r\n";
		if ($MailInfo[BccMail] != "") $headers .= "Bcc: " . $MailInfo[BccMail] . "\r\n";
		$headers = iconv("UTF-8", "Big5", $headers);

		if (mail($mail_array[0], $subjects, $body, $headers))
			$message = "信件已寄出";
		else
			$message = "信件無法寄出，錯誤訊息：" . $mail->ErrorInfo . "，mail()亦同";
	} else {
		$message = "信件成功寄出";
	}

	return $message;
}

//連結
function getLink($url, $target, $subject)
{
	$subject = strip_tags($subject);
	if ($url == "#") $url = "";
	if ($url != "") {
		$link = " href=\"" . $url . "\"";
		if ($target == "_blank") {
			$link .= " title=\"" . $subject . "（另開新視窗）\" target=\"_blank\"";
		} else {
			$link .= " title=\"" . $subject . "\"";
		}
	} else {
		$link = " href=\"javascript:;\" title=\"" . $subject . "\"";
	}

	return $link;
}

//根據起訖時間判斷是否跨日
function isOverDay($stimeInt, $etimeInt, $days)
{

	if ($stimeInt <= 2300 && $stimeInt >= 2000 && $etimeInt <= 600 && $etimeInt >= 0) {

		return 'overDay';
	} //晚班超過兩天
	else if ($stimeInt >= 2000 && $days >= 3) {
		return 'overDay';
	}
	return 'sameDay';
}

//根據起訖時間判斷是否跨日
function getLeaveName($leaveCode)
{
	$arrayName = array();

	switch ($leaveCode) {
		case "06": //特休
			$ret["ch"] = "特休";
			$ret["vietnam"]	= "";
			$ret["tai"]	= "";
			break;
		case "03": //事假
			$ret["ch"] = "事假";
			$ret["vietnam"]	= "việc riêng";
			$ret["tai"]	= "การลากิจ";
			break;
		case "04": //病假
			$ret["ch"] = "病假";
			$ret["vietnam"]	= "nghỉ ốm";
			$ret["tai"]	= "การลาป่วย";
			break;
	}
	return $ret;
}
