<?php
	include("includes/includes.php");
	$action = $_POST["action"];

	//step2.php 選擇請假類別
	if ($action=="SelectLeave") {
		$CQG03 = str_filter($_POST["CQG03"]);	//請假類別
		
		$_SESSION["MyMember"]["CQG03"] = "";
		$_SESSION["MyMember"]["LeaveName"] = "";
		
		$sql = "Select CPJ02 From twmd.CPJ_FILE Where CPJ01 = '".$CQG03."' ";
		$rs = ConnectOracle($Oracle, $sql);
		oci_execute($rs);
		while ($row = oci_fetch_array($rs, OCI_ASSOC+OCI_RETURN_NULLS)) {
			foreach($row as $_key=>$_value) {
				$$_key = $row[$_key];
				$$_key = iconv("Big5", "UTF-8", $$_key);
				//echo $_key.":".$$_key."<br />";
			}
			$_SESSION["MyMember"]["CQG03"] = $CQG03;
			$_SESSION["MyMember"]["LeaveName"] = $CPJ02;
		}

		echo ($_SESSION["MyMember"]["CQG03"]) ? "S" : "F";	//請假類別
		die();
	}

	//step4.php 送出假單
	if ($action=="CreateLeave") {
		$CQG04 = str_filter($_POST["CQG04"]);	//起始日期
		$CQG05 = str_filter($_POST["CQG05"]);	//截止日期
		$sdate = explode("/", $CQG04);	//起始日期
		$edate = explode("/", $CQG05);	//截止日期

		//總天數
		$diff = DateDiff($CQG04, $CQG05);
	
		$CQG02 = 1;
		$sql = "Select CQG02 From twmd.cqg_file Where CQG01 = '".$_SESSION["MyMember"]["Code"]."' and rownum <= 1 order by CQG02 desc ";
		$rs = ConnectOracle($Oracle, $sql);
		oci_execute($rs);
		while ($row = oci_fetch_array($rs, OCI_ASSOC+OCI_RETURN_NULLS)) {
			foreach($row as $_key=>$_value) {
				$$_key = $row[$_key];
				$$_key = iconv("Big5", "UTF-8", $$_key);
			}
			$CQG02++;
		}
	
		$CQG04 = "";	//起始日期
		$CQG041 = "";	//起始時分
		for ($i=0; $i<$diff; $i++) {
			$date = date("Y/m/d", strtotime("+".$i." day", mktime(0, 0, 1, $sdate[1], $sdate[2], $sdate[0])));
			
			$date_part = explode("/", $date);		
			$year = intval($date_part[0]);
			$month = intval($date_part[1]);
			$day = intval($date_part[2]);
	
			$sql = "Select CPY04, CPQ03, CPQ04, CPQ05, CPQ06, CPQ19 From twmd.cpf_file, twmd.cpq_file, twmd.cpy_file Where CPF01 = CPY01 and CPY04 = CPQ02 and CPF01 = '".$_SESSION["MyMember"]["Code"]."' and CPY02 = '".$year."' and CPY021 = '".$month."' and CPY03 = '".$day."' and CPY05 = '0' ";
			$rs = ConnectOracle($Oracle, $sql);
			oci_execute($rs);
			while ($row = oci_fetch_array($rs, OCI_ASSOC+OCI_RETURN_NULLS)) {
				foreach($row as $_key=>$_value) {
					$$_key = $row[$_key];
					$$_key = iconv("Big5", "UTF-8", $$_key);
					//echo $_key.":".$$_key."<br />";
				}
				
				$CQH01 = $_SESSION["MyMember"]["Code"];			//工號
				$CQH02 = $CQG02;								//行序
				$CQH04 = $date;									//起始日期
				$CQH05 = $date;									//起始日期
				$CQH051 = sprintf("%02d", $CPQ03).":".sprintf("%02d", $CPQ04);	//起始時分
				$CQH06 = $date;									//截止日期
				$CQH061 = sprintf("%02d", $CPQ05).":".sprintf("%02d", $CPQ06);	//截止時分
				$CQH07 = $CPQ19;								//請假時數
				$CQH071 = 1;									//天數
				$CQH08 = "N";									//預先借假否
				$CQH09 = "";									//預留
				$CQH10 = "";									//預留
				$CQH11 = "";									//加班說明
				
				$sql = "Insert into twmd.CQH_FILE (CQH01, CQH02, CQH04, CQH05, CQH051, CQH06, CQH061, CQH07, CQH071, CQH08, CQH09, CQH10, CQH11) values ('$CQH01', '$CQH02', TO_DATE('".$CQH04."', 'YYYY/MM/DD'), TO_DATE('".$CQH05."', 'YYYY/MM/DD'), '$CQH051', TO_DATE('".$CQH06."', 'YYYY/MM/DD'), '$CQH061', '$CQH07', '$CQH071', '$CQH08', '$CQH09', '$CQH10', '$CQH11') ";
				//echo $sql."\n";
				$rs = ConnectOracle($Oracle, $sql);
				oci_execute($rs);			
				
				if (!$CQG04) {
					$CQG04 = $CQH05;	//起始日期
					$CQG041 = $CQH051;	//起始時分
				}
				$CQG05 = $CQH06;	//截止日期
				$CQG051 = $CQH061;	//截止時分
				
				$CQG06 += $CQH07;	//請假總時數
				$CQG08 += $CQH071;	//請假天數
			}
		}
		
			
		$CQG01 = $_SESSION["MyMember"]["Code"];		//工號
		//$CQG02			//行序
		$CQG03 = $_SESSION["MyMember"]["CQG03"];	//假別代號
		//$CQG04			//起始日期
		//$CQG041 			//起始時分
		//CQG05				//截止日期
		//CQG051			//截止時分
		//CQG06				//請假總時數
		$CQG07 = "N";		//預先借假否
		//CQG08				//請假天數
		$CQG09 = "";		//預留
		$CQG10 = "N";		//在請假平台上判斷主管是否瀏覽(一律都’N’)
		$CQGCONF = "N";		//確認否	Y(一律都’N’)
		$CQGUSER = $_SESSION["MyMember"]["Code"];			//資料所有者(寫入登入者工號)
		$CQGGRUP = $_SESSION["MyMember"]["CPF29"];			//資料所有群(帶cpf29)
		$CQGMODU = $_SESSION["MyMember"]["Code"];			//最後修改者(寫入登入者工號)
		$CQGDATE = date("Y/m/d");							//產生日期
		$CQG11 = 0;											//狀況碼(一律都’0’)
		$CQGMKSG = "N";										//簽核否	N(一律都’N’)
		$CQG12 = "";										//加班說明(空白)
		$TA_CQG001 = $_SESSION["MyMember"]["TA_CPF001"];	//主管人員	I1280(帶ta_cpf001)
	
		$sql = "Insert into twmd.CQG_FILE (CQG01, CQG02, CQG03, CQG04, CQG041, CQG05, CQG051, CQG06, CQG07, CQG08, CQG09, CQG10, CQGCONF, CQGUSER, CQGGRUP, CQGMODU, CQGDATE, CQG11, CQGMKSG, CQG12, TA_CQG001) values ('$CQG01', '$CQG02', '$CQG03', TO_DATE('".$CQG04."', 'YYYY/MM/DD'), '$CQG041', TO_DATE('".$CQG05."', 'YYYY/MM/DD'), '$CQG051', '$CQG06', '$CQG07', '$CQG08', '$CQG09', '$CQG10', '$CQGCONF', '$CQGUSER', '$CQGGRUP', '$CQGMODU', TO_DATE('".$CQGDATE."', 'YYYY/MM/DD'), '$CQG11', '$CQGMKSG', '$CQG12', '$TA_CQG001') ";
		//echo $sql."\n";
		$rs = ConnectOracle($Oracle, $sql);
		oci_execute($rs);			

		die();
	}
?>