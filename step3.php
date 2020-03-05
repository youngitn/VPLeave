<?php include("includes/includes.php"); ?>
<?php include("includes/login_chk.php"); ?>
<?php
	if (!$_SESSION["MyMember"]["CQG03"] || !$_SESSION["MyMember"]["LeaveName"]) RunJs("step2.php");
	$date = date("Y/m/d");
	
	//查詢人員行事曆cpy_file 判斷系統能處理請假的日期和今天差幾天
	//用在選擇日期的DatePicker套件屬性中 datepick[maxDate]
	$maxDate = 0;
	$sql = "Select CPY02, CPY021, CPY03 From twvp.cpy_file Where CPY01 = '".$_SESSION["MyMember"]["Code"]."' and rownum <= 1 order by CPY02 desc, CPY021 desc, CPY03 desc ";
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
	

?>
<?php include("includes/head.php"); ?>
<link rel="stylesheet" type="text/css" href="js/datepick/smoothness.datepick.css"> 
<script type="text/javascript" src="js/datepick/jquery.plugin.min.js"></script> 
<script type="text/javascript" src="js/datepick/jquery.datepick.js"></script>
<script type="text/javascript" src="js/datepick/jquery.datepick-zh-TW.js"></script>
<script type="text/javascript">
<!--
$(function() {
	$('#CQG04').datepick({
		minDate: -8,//開放今天算起前8天
		maxDate: <?php echo $maxDate; ?>,//開放今天算起後x天
		onSelect: function(dateText) {
			$("#CQG05").val(this.value);
		}
    });
	
    $('#CQG05').datepick({
		minDate: -8,
		maxDate: <?php echo $maxDate; ?>
    });
});
//-->
</script>
</head>

<body>
<form name="form" method="post" action="step4.php">
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
                <td height="45" align="left" class="font_20px">請選擇您請假天數</td>
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
                <td width="415" valign="bottom"><!--請假起始時分--></td>
              </tr>
              <tr>
                <td height="45"><input type="text" name="CQG04" id="CQG04" class="font_13pt" value="<?php echo $date; ?>" maxlength="10" readonly /></td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td height="50">&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td>請假截止日期</td>
                <td><!--請假截止時分--></td>
              </tr>
              <tr>
                <td height="45"><input type="text" name="CQG05" id="CQG05" class="font_13pt" value="<?php echo $date; ?>" maxlength="10" readonly /></td>
                <td>&nbsp;</td>
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
