`<?php include("includes/includes.php"); ?>
<?php include("includes/login_chk.php"); ?>
<?php
	if (!$_SESSION["MyMember"]["CQG03"] || !$_SESSION["MyMember"]["LeaveName"]) RunJs("step2.php");

	$CQG04 = str_filter($_POST["CQG04"]);	//起始日期
	$CQG05 = str_filter($_POST["CQG05"]);	//截止日期
	$sdate = explode("/", $CQG04);	//起始日期
	$edate = explode("/", $CQG05);	//截止日期
	if (!checkdate($sdate[1], $sdate[2], $sdate[0]) || !checkdate($edate[1], $edate[2], $edate[0])) RunJs("step3.php");	//未選擇日期或日期格式錯誤

	//總天數
	$diff = DateDiff($CQG04, $CQG05);
?>
<?php include("includes/head.php"); ?>
<style type="text/css">
body {
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
	background-image: url(image/background-3.jpg);
	background-repeat: repeat;
}
</style>
<script type="text/javascript">
function CreateLeave(CQG04, CQG05) {
	ajaxobj = new AJAXRequest;
	ajaxobj.method = "POST";
	ajaxobj.url = "action.php";
	ajaxobj.content = "action=CreateLeave&CQG04=" + CQG04 + "&CQG05=" + CQG05;
	ajaxobj.callback = function (xmlobj) {
		var response = xmlobj.responseText;
		if (response=="S") {
			location.href = "step5.php";
		} else {
			alert("您已於" + CQG04 + " ~ " + CQG05 + "請假過");
		}
	};
	ajaxobj.send();
}
</script>
</head>

<body>
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
                <td height="45" align="left" class="font_20px">請再次確認請假資料無誤</td>
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
          <td height="380" valign="top"><table width="830" border="0" align="center" cellpadding="0" cellspacing="0" class="font_10pt">
              <tr>
                <td height="35"><span class="font_12pt">假別類型 ： <?php echo $_SESSION["MyMember"]["LeaveName"]; ?></span></td>
              </tr>
              <tr>
                <td><table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" class="font_12pt">
                    <tr>
                      <td width="20" height="40" align="center" valign="middle"><img src="image/3.jpg" width="10" height="20" /></td>
                      <td width="810">請假天數相關細項</td>
                    </tr>
                    <tr>
                      <td colspan="2">
                        <table width="98%" border="0" align="center" cellpadding="3" cellspacing="0" class="font_10pt">
                          <tr>
                            <td height="30" background="image/4.png">&nbsp;&nbsp;班別</td>
                            <td background="image/4.png">&nbsp;&nbsp;起始日期</td>
                            <td background="image/4.png">&nbsp;&nbsp;起始時分</td>
                            <td background="image/4.png">&nbsp;&nbsp;截止日期</td>
                            <td background="image/4.png">&nbsp;&nbsp;截止時分</td>
                            <td background="image/4.png">&nbsp;&nbsp;請假時數</td>
                            <td background="image/4.png">&nbsp;&nbsp;請假天數</td>
                          </tr>
                          <?php
						    $days = 0;
						  	$list = "";
                          	for ($i=0; $i<$diff; $i++) {
								$date = date("Y/m/d", strtotime("+".$i." day", mktime(0, 0, 1, $sdate[1], $sdate[2], $sdate[0])));
								
								$date_part = explode("/", $date);		
								$year = intval($date_part[0]);
								$month = intval($date_part[1]);
								$day = intval($date_part[2]);

								$sql = "Select CPY04, CPQ03, CPQ04, CPQ05, CPQ06, CPQ19 From twvp.cpf_file, twvp.cpq_file, twvp.cpy_file Where CPF01 = CPY01 and CPY04 = CPQ02 and CPF01 = '".$_SESSION["MyMember"]["Code"]."' and CPY02 = '".$year."' and CPY021 = '".$month."' and CPY03 = '".$day."' and CPY05 = '0' ";
								$rs = ConnectOracle($Oracle, $sql);
								oci_execute($rs);
								while ($row = oci_fetch_array($rs, OCI_ASSOC+OCI_RETURN_NULLS)) {
									foreach($row as $_key=>$_value) {
										$$_key = $row[$_key];
										$$_key = iconv("Big5", "UTF-8", $$_key);
										//echo $_key.":".$$_key."<br />";
									}
									
									$list .= "
									  <tr>
										<td height=\"30\">&nbsp;&nbsp;".$CPY04."<!--班別--></td>
										<td>&nbsp;&nbsp;".$date."<!--起始日期--></td>
										<td>&nbsp;&nbsp;".sprintf("%02d", $CPQ03).":".sprintf("%02d", $CPQ04)."<!--起始時分--></td>
										<td>&nbsp;&nbsp;".$date."<!--截止日期--></td>
										<td>&nbsp;&nbsp;".sprintf("%02d", $CPQ05).":".sprintf("%02d", $CPQ06)."<!--截止時分--></td>
										<td>&nbsp;&nbsp;".$CPQ19."<!--請假時數--></td>
										<td>&nbsp;&nbsp;1<!--請假天數--></td>
									  </tr>";

									$days++;	//實際總天數
								}
							}
							echo $list;
						  ?>
                        </table>
                      </td>
                    </tr>
                  </table></td>
              </tr>
            </table></td>
        </tr>
        <tr>
          <td height="60" align="center">
            <a href="javascript:history.back()"><img src="image/back.png" width="179" height="68" border="0" /></a>
            <a href="javascript:;" id="next_step" onClick="CreateLeave('<?php echo $CQG04; ?>', '<?php echo $CQG05; ?>');" style="display: none;"><img src="image/Sent-out.png" width="179" height="68" border="0" /></a>
            <a href="index.php"><img src="image/select.png" alt="" width="179" height="68" border="0" /></a>
          </td>
        </tr>
      </table></td>
  </tr>
</table>
<?php
	if ($days<1) RunAlert("請假天數至少1天");
	
	//檢查實際總天數
	switch ($_SESSION["MyMember"]["CQG03"]) {
		case "03":	//事假無限制
			echo "<script type=\"text/javascript\">$('#next_step').show();</script>";
			break;
		case "04":	//病假30天 只判斷本次申請總和 非年度累計
			if ($days>30)
				RunAlert("您最多只可請30天".$_SESSION["MyMember"]["LeaveName"]);
			else
				echo "<script type=\"text/javascript\">$('#next_step').show();</script>";
			break;
		case "06":	//特休
			$OVER = 0;
			//$sql = "Select CQI06 - CQI09 as OVER From twvp.cqi_file Where CQI01 = '".$_SESSION["MyMember"]["Code"]."' and CQI02 = ".date("Y")." ";
			$sql = "Select CQI06 , CQI09 From twvp.cqi_file Where CQI01 = '".$_SESSION["MyMember"]["Code"]."' and CQI02 = ".date("Y")." ";
			
			$rs = ConnectOracle($Oracle, $sql);
			oci_execute($rs);
			while ($row = oci_fetch_array($rs, OCI_ASSOC+OCI_RETURN_NULLS)) {
				foreach($row as $_key=>$_value) {
					$$_key = $row[$_key];
					$$_key = iconv("Big5", "UTF-8", $$_key);
					//echo $_key.":".$$_key."<br />";
				}
			}

			
	        //---判斷-----------
	        if($CQI09==null){
		       $CQI09=0;
	        }
            $OVER=$CQI06-$CQI09;
			
			
			
			if ($days>$OVER)
				RunAlert("您最多只可請".$OVER."天".$_SESSION["MyMember"]["LeaveName"]);
			else
				echo "<script type=\"text/javascript\">$('#next_step').show();</script>";
			break;
		default:
	}
	
?>
</body>
</html>
