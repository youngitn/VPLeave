<?php include("includes/includes.php"); ?>
<?php include("includes/login_chk.php"); ?>
<?php
	if (!$_SESSION["MyMember"]["CQG03"] || !$_SESSION["MyMember"]["LeaveName"]) RunJs("step2.php");
	$date = date("Y/m/d");
	//$_SESSION["MyMember"]["Code"] = "L2623";
	//$_SESSION["MyMember"]["Code"] = "G2033";
	//$_SESSION["MyMember"]["Code"] = "I1674";
	$empId = $_SESSION["MyMember"]["Code"];
	
	//找出cpy_file最遠的一筆日期離今天有幾天 可選日期卡控
	//取得請假人班別在ERP中已設置的行事曆,距今有幾日
	//ex:今天5/15日,hr人員設置A班別的行事曆到5/31,則這邊會取得離最遠的一天是31日
	//這部分目的在防止如果沒有做限制,萬一選擇6/1請假,則程式可能出現錯誤.
	$maxDate = 0;
	$sql = "Select CPY02, CPY021, CPY03 From ".$db."cpy_file Where CPY01 = '".$empId."' and rownum <= 1 order by CPY02 desc, CPY021 desc, CPY03 desc ";
	$rs = ConnectOracle($Oracle, $sql);
	oci_execute($rs);
	while ($row = oci_fetch_array($rs, OCI_ASSOC+OCI_RETURN_NULLS)) {
		foreach($row as $_key=>$_value) {
			$$_key = $row[$_key];
			$$_key = iconv("Big5", "UTF-8", $$_key);
			//echo $_key.":".$$_key."<br />";
		}
		$edate = $CPY02."-".sprintf("%02d", $CPY021)."-".sprintf("%02d", $CPY03);
		$maxDate = DateDiff(date("Y-m-d"), $edate) - 1;
	}
	
	
	//取班別與上下班時間
	//$_SESSION["MyMember"]["Code"] = "L2654";	
	$sql = "Select ".
	"CPY04,".//班別 
	"CPQ03,".//正常上班時間 小時
	"CPQ04,".//分
	"CPQ05,".//正常下班時間 小時 
	"CPQ06,".//分
	"CPQ19,".//工作時數
	"CPQ15,".//休息開始時間 小時
	"CPQ16,". //分
	"CPQ17,".//休息結束時間 小時
	"CPQ18". //分
	" From ".$db."cpf_file, ".$db."cpq_file, ".$db."cpy_file Where CPF01 = CPY01 and CPY04 = CPQ02 and CPF01 = '".$empId."' and ROWNUM = 1" ;		

	$rs = ConnectOracle($Oracle, $sql);
	oci_execute($rs);

	while ($row = oci_fetch_array($rs, OCI_ASSOC+OCI_RETURN_NULLS)) {
		
		foreach($row as $_key=>$_value) {
			$$_key = $row[$_key];
			$$_key = iconv("Big5", "UTF-8", $$_key);
			//echo $_key.":".$$_key."<br />";
		}
		$_SESSION["MyMember"]["classStartHHOri"] = $CPQ03;
		$_SESSION["MyMember"]["classStartMMOri"] = $CPQ04;
		$classCode = $CPY04;
		$stime = $CPQ03.":".$CPQ04;
		$etime = $CPQ05.":".$CPQ06;
		
		$_SESSION["MyMember"]["class"] = $classCode ;
		if ($classCode == 'B'){
			$CPQ03 = $CPQ03 + 1;
			
		}
		$_SESSION["MyMember"]["classStartHH"] = $CPQ03;//正常上班時間 小時
		$_SESSION["MyMember"]["classStartMM"] = $CPQ04;//分
		$_SESSION["MyMember"]["classEndHH"] =  $CPQ05 ;//正常下班時間 小時 
		$_SESSION["MyMember"]["classEndMM"] =  $CPQ06 ;//分
		$_SESSION["MyMember"]["workTimeOfDay"] =  $CPQ19 ;//工作時數
		$_SESSION["MyMember"]["restStartTimeHH"] =  $CPQ15 ;//休息開始時間		小時
		$_SESSION["MyMember"]["restStartTimeMM"] =  $CPQ16 ;//休息開始時間 分
		$_SESSION["MyMember"]["restEndTimeHH"] =  $CPQ17 ;//休息結束時間 小時
		$_SESSION["MyMember"]["restEndTimeMM"] =  $CPQ18 ;//休息結時間 分
		
	}
	
	//echo getWorkTime("2020/02/27")["stime"];
	
?>
<?php include("includes/head.php"); ?>
<link rel="stylesheet" type="text/css" href="js/datepick/smoothness.datepick.css"> 
<script type="text/javascript" src="js/datepick/jquery.plugin.min.js"></script> 
<script type="text/javascript" src="js/datepick/jquery.datepick.js"></script>
<script type="text/javascript" src="js/datepick/jquery.datepick-zh-TW.js"></script>

<script type="text/javascript">
<!--

 let timeStart = <?=$_SESSION["MyMember"]["classStartHH"] ?>;
 let timeEnd = <?=$_SESSION["MyMember"]["classEndHH"]?>;
 let workclass = '<?=$_SESSION["MyMember"]["class"]?>';
 
 /*
 let workclass = 'E';
 let timeStart = 20;
 let timeEnd = 6;
 */
 
$(function() {
	$('#CQG04').datepick({
		minDate: -8,
		maxDate: <?=$maxDate; ?>,
		onSelect: function(dateText) {
			//$("#CQG05").val(this.value);
			jqAjaxGetWorkTime(this.value,'<?=$empId?>','timeStart');
		}
    });
	
    $('#CQG05').datepick({
		minDate: -8,
		maxDate: <?=$maxDate; ?>,
		onSelect: function(dateText) {
			//$("#CQG05").val(this.value);
			//截止日期不做假日檢查 如開啟後 當起日周五20:00挑好後 截止日要挑周六會被擋
			//jqAjaxGetWorkTime(this.value,'<?=$empId?>','timeEnd')
			if(gtimeStart == ''  || gtimeEnd == ''){
				alert("請先選擇起始日期");
			}else{
				var selTimeObj  = $("#timeEndSelect");
				var optionObj = $("#timeEndSelect option" );
				optionObj.remove();
				buildTimeSelectDomByClass(selTimeObj,parseInt(gtimeStart),parseInt(gtimeEnd));
			}
			
		}

    });
	
	
	
	 	
});



/**
* 根據班別與上下班時間 建立請假小時下拉選單
*/
function buildTimeSelectDomByClass(obj,beginTime,endTime){
	
	//日班 
	if (beginTime < 20 ){
		beginTime = beginTime + 1;
		var op = "";
		for (var i = beginTime; i <= endTime; i++) {
			op = "<option value='"+i+":00'>"+i+":00</option>";
			if (workclass == 'A' || workclass == 'B'){
				switch(i) {
				case 8:
					op = "<option value='7:50'>7:50</option>";
					break;
				case 17:
					op = "<option value='17:10'>17:10</option>";
					break;
				default:
					;
				} 
			}else if (workclass == 'C-1'){
				switch(i) {
				case 8:
					op = "<option value='7:50'>7:50</option>";
					break;
				case 17:
					op = "<option value='17:10'>17:10</option>";
					break;
				case 12:
					op = "<option value='12:10'>12:10</option>";
					break;
				default:
					;
				} 
			}
			
			
			obj.append(op);
		  
		}
	}//晚班 跨日
	else {
		//處理20:00-00:00 上班時間正常20:00開始 有例外需額外再處理
		if (beginTime >= 20){
			for (var i = beginTime; i <= 24; i++) {
				
				var op = "";
				if (i == 24){
					op = "<option value='00:00'>00:00</option>";
				}else{
					op = "<option value='"+i+":00'>"+i+":00</option>";
				}
				if (i > <?=$_SESSION["MyMember"]["restStartTimeHH"]?> && i < <?=$_SESSION["MyMember"]["restEndTimeHH"]?>){
					continue;
				}
				obj.append(op);
			}
			//處理01:00 ~ 下班時間...
			for (var i = 1; i <= endTime; i++) {
				if (i > <?=$_SESSION["MyMember"]["restStartTimeHH"]?> && i < <?=$_SESSION["MyMember"]["restEndTimeHH"]?>){
					continue;
				}
			  obj.append("<option value='"+i+":00'>"+i+":00</option>");
			}
		}else{
			
			for (var i = beginTime; i <= endTime; i++) {
				if (i > <?=$_SESSION["MyMember"]["restStartTimeHH"]?> && i < <?=$_SESSION["MyMember"]["restEndTimeHH"]?>){
					continue;
				}
			  obj.append("<option value='"+i+":00'>"+i+":00</option>");
			}
		}
		
		
	}	
	
	return obj;
	
}

//**未使用**
function handlerEndTime(timeEndSelectId,timeValue){
	var objOption = $("#"+timeEndSelectId+" option" );
	var obj = $("#"+timeEndSelectId);
	//  移除全部時間選項
	objOption.remove();
	timeStart = timeValue.split(":", 1);	
	buildTimeSelectDomByClass(obj,timeStart,timeEnd)
}

//給截止日期onchange函數產生截止時間選單的全域變數
let gtimeStart = '';
let gtimeEnd = '';
//日期onChange事件函數 選擇日期後檢查是否上班日 非上班日彈出提醒視窗
function jqAjaxGetWorkTime(date,empid){
	$.ajax({
	  method: "POST",
	  url: "action.php",
	  dateType:"json",
	  data: { 			
				date: date,//結束日期
				empId:empid,
				action:'getWorkTime',
				company:'<?=$company?>'
			}
	})
	  .done(function( msg ) {
		var selTimeObj  = $("#timeStartSelect");
		var optionObj = $("#timeStartSelect option" );
		if (msg == '' ){			
			alert(date+'該日期非上班日,請重新選擇!');
			optionObj.remove();			
			
		}else{
			//取得班別上下班作息時間
			var dateObj = JSON.parse(msg);			
			var timeStart = 0;
			var timeEnd = 0;
			
			timeStart = dateObj.stime.split(":", 1);
			timeEnd	  = dateObj.etime.split(":", 1);
			//賦值給全域變數 給截止日期的onChange函數產生時間選單
			gtimeStart = timeStart;
			gtimeEnd =   timeEnd;
			optionObj.remove();
			//建立時間下拉選單 根據班別上下班時間
			buildTimeSelectDomByClass(selTimeObj,parseInt(timeStart),parseInt(timeEnd));
			
		}
		
	  });
	}
//-->
</script>

</head>

<body>
<form name="form" method="post" action="step4_dev.php">
	<input type="hidden" name="company" value="<?=$company?>"/>"
<table width="900" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td height="760" valign="top">
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td height="200" valign="bottom">
            <table width="830" border="0" align="center" cellpadding="0" cellspacing="0">
              <tr>
                <td height="45" align="left" class="font_20px">歡迎 , <?php echo $_SESSION["MyMember"]["Name"]; ?></td>
              </tr>
              <tr>
                <td height="45" align="left" class="font_20px">請選擇請假開始與結束時間</td>
              </tr>
			  <tr> 
			  <td><font color="red">***請先點選日期,再選擇時間***</font></td>
			  </tr>
              <tr>
                <td>&nbsp;</td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td height="30" background="image/2-md.png">&nbsp;</td>
        </tr>
        <tr>
          <td height="410" valign="top">
            <table width="830" border="0" align="center" cellpadding="0" cellspacing="0" class="font_20px-red">
              <tr>
                <td width="415" valign="bottom">請假起始日期</td>
                <td width="415" valign="bottom">請假起始時分</td>
              </tr>
              <tr>
                <td height="45">
				<input  type="text" name="CQG04" id="CQG04" class="font_13pt" value="<?= $date; ?>" maxlength="10" readonly />
				</td>
                <td><select name="timeStart" class="font_13pt" id="timeStartSelect" /> </td>
              </tr>
              <tr>
                <td height="50">&nbsp;</td>
                <td></td>
              </tr>
              <tr>
                <td>請假截止日期</td>
                <td>請假截止時分</td> 
              </tr>
              <tr>
                <td height="45"><input type="text" name="CQG05" id="CQG05" class="font_13pt" value="<?=$date; ?>" maxlength="10" readonly /></td>
                <td><select class="font_13pt" name="timeEnd" id="timeEndSelect" /> </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td height="60" align="center">
            <a href="javascript:history.back()"><img src="image/back.png" width="179" height="68" border="0" /></a>
            <a href="javascript:;" onClick="document.form.submit();"><img src="image/Sent-out.png" width="179" height="68" border="0" /></a>
            <a href="./"><img src="image/select.png" alt="" width="179" height="68" border="0" /></a>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>
</body>
</html>
