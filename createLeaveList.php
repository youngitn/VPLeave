<?PHP
		include("includes/includes.php");
		$CQG04 		= str_filter($_POST["CQG04"]);	//起始日期
		$CQG05 		= str_filter($_POST["CQG05"]);   //截止日期
		$stime 		= str_filter($_POST["stime"]);   //開始時分
		$etime 		= str_filter($_POST["etime"]);	//截止時分
		$leaveAmt  	= str_filter($_POST["leaveAmt"]);	//請假時數
		$leaveDay	= str_filter($_POST["leaveDay"]);	//請假天數(照比例計算)
		$empId		= str_filter($_POST["empId"]);	//員工編號 
		$sdate = explode("/", $CQG04);	//起始日期
		$edate = explode("/", $CQG05);	//截止日期
		
		
		//判斷同一天 *同時段內* 是否重復請假
		$sql = "Select count(*) as counter From twvp.cqg_file Where CQG01 = '".$empId."' and to_char(CQG04, 'YYYY/mm/dd') = '".$CQG04."' and to_char(CQG05, 'YYYY/mm/dd') = '".$CQG05."'  and CQG041 ='".$stime."' AND CQG051='".$etime."' ";
		$rs = ConnectOracle($Oracle, $sql);
		oci_execute($rs);
		while ($row = oci_fetch_array($rs, OCI_ASSOC+OCI_RETURN_NULLS)) {
			foreach($row as $_key=>$_value) {
				$$_key = $row[$_key];
				$$_key = iconv("Big5", "UTF-8", $$_key);
				//大於0表示當天同個時間已請過假
				if ($COUNTER>0) {
					echo "F";
					die();
				}
			}
		}
		
		//總天數 
		$diff = DateDiff($CQG04, $CQG05);
		//給資料序號 先找出最後一筆序號數字 加1後為最新一筆序號
		$CQG02 = 1;
		$sql = "Select CQG02 From twvp.cqg_file Where CQG01 = '".$empId."' and rownum <= 1 order by CQG02 desc ";
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
		
		$sql = "Select CPY04,CPY02,CPY021,CPY03, CPQ03, CPQ04, CPQ05, CPQ06, CPQ19 From twvp.cpf_file, twvp.cpq_file, twvp.cpy_file Where CPF01 = CPY01 and CPY04 = CPQ02 and CPF01 = '".$empId."' and CPY02 >= '".$sdate[0]."' and CPY021 >= '".$sdate[1]."' and CPY03 >= '".$sdate[2]."' and CPY02 <= '".$edate[0]."' and CPY021 <= '".$edate[1]."' and CPY03 <= '".$edate[2]."'and CPY05 = '0' order by CPY02,CPY021,CPY03";
		$rs = ConnectOracle($Oracle, $sql);
		oci_execute($rs);
		//echo $days;
		$sqlForCount = "Select  COUNT(*) AS NUMBER_OF_ROWS From twvp.cpf_file, twvp.cpq_file, twvp.cpy_file Where CPF01 = CPY01 and CPY04 = CPQ02 and CPF01 = '".$empId."' and CPY02 >= '".$sdate[0]."' and CPY021 >= '".$sdate[1]."' and CPY03 >= '".$sdate[2]."' and CPY02 <= '".$edate[0]."' and CPY021 <= '".$edate[1]."' and CPY03 <= '".$edate[2]."'and CPY05 = '0'";
		$rsForCount = ConnectOracle($Oracle, $sqlForCount);
		oci_define_by_name($rsForCount, 'NUMBER_OF_ROWS', $numberOfDays);
		oci_execute($rsForCount);
		oci_fetch($rsForCount);
		$days = 0;
		$data = "";
		$nowDay = 1;

		$stimeInt = (int)str_replace(":","",$stime);
		$etimeInt = (int)str_replace(":","",$etime);
		$isOverDay = isOverDay($stimeInt,$etimeInt,$numberOfDays);
		//return;
		while ($row = oci_fetch_array($rs, OCI_ASSOC+OCI_RETURN_NULLS)) {
			foreach($row as $_key=>$_value) {
				$$_key = $row[$_key];
				$$_key = iconv("Big5", "UTF-8", $$_key);
				//echo $_key.":".$$_key."<br />";
			}

			$CQH01 = $empId;			//工號
			$CQH02 = $CQG02;								//行序
			$CQH04 = $CPY02."/".$CPY021."/".$CPY03;			//歸屬日期
			$CQH05 = $CPY02."/".$CPY021."/".$CPY03;			//起始日期
			$CQH06 = $CPY02."/".$CPY021."/".$CPY03;			//截止日期
		
			
			//請假第一天 
			if($nowDay == 1){
				
				//設定啟始時間=由頁面輸入之請假開始時間		
				$CQH051 = $stime;
				
				/***日班 未跨日***/
				//請單日 
				//截止時間 = 由頁面輸入之請假截止時間
				if ($numberOfDays == 1){
					$CQH061 = $etime;	//截止時分
				}
				//請多日
				//截止時間 = 該班別下班時間
				else{
					$CQH061 = sprintf("%02d", $CPQ05).":".sprintf("%02d", $CPQ06);	//截止時分
				}
				/***晚班***/
				//有跨日 則截止日期 = 請假日期+1
				if ($isOverDay == "overDay" ){
					//請單日 因跨日所以會帶2天日期 故$numberOfDays=2
					//這邊$numberOfDays=2 表示請單一天 :
					//EX:2020/4/4 20:00 ~ 2020/4/5 06:00 ,$numberOfDays=2
					if ( $numberOfDays == 2){
						$CQH061 = $etime;
					}
					
					$CQH06 = date('Y/m/d', strtotime ("+1 day", strtotime($CQH05)));					
				}
			}
			//請假超過一天
			else{
				
				$CQH051 = sprintf("%02d", $CPQ03).":".sprintf("%02d", $CPQ04);	//起始時分
				
				//最後一天 截止時間 = 由頁面輸入之請假截止時間
				if ($nowDay == $numberOfDays){
					$CQH061 = $etime;	//截止時分
				}else{
					$CQH061 = sprintf("%02d", $CPQ05).":".sprintf("%02d", $CPQ06);	//截止時分
				}
				$stimeInt = (int)(sprintf("%02d", $CPQ03).sprintf("%02d", $CPQ04));
				$etimeInt = (int)str_replace(":","",$CQH061);
				
				//echo $stimeInt.$etimeInt;
				//跨日日期判斷
				//開始時間落在20-23間 截止時間落在0-6間 表示跨日
				if ($isOverDay == "overDay"){
					
					$CQH06 = date('Y/m/d', strtotime ("+1 day", strtotime($CQH05)));
				}
			}
			
			
			
			//$CQH061 = sprintf("%02d", $CPQ05).":".sprintf("%02d", $CPQ06);	//截止時分
			$CQH07 = $CPQ19;								//請假時數
			$CQH071 = 1;									//天數
			$CQH08 = "N";									//預先借假否
			$CQH09 = "";									//預留
			$CQH10 = "";									//預留
			$CQH11 = "";									//加班說明
			
			$nowDay++;
			$CQG02++;
			//echo date('Y-m-d', strtotime ("+1 day", strtotime($CQH05)));
			echo " 序號:".$CQH02." 啟始日:".$CQH05." 啟始時間:".$CQH051." 截止日:".$CQH06." 截止時間:".$CQH061."\n";
			if ($isOverDay == "overDay" && $numberOfDays == 2){break;}
		}
		
		return;
		
		// for ($i=0; $i<$diff; $i++) {
		// 	$date = date("Y/m/d", strtotime("+".$i." day", mktime(0, 0, 1, $sdate[1], $sdate[2], $sdate[0])));
		// 	echo $date;
		// 	$date_part = explode("/", $date);		
		// 	$year = intval($date_part[0]);
		// 	$month = intval($date_part[1]);
		// 	$day = intval($date_part[2]);
		// 	//查員工行事曆 請假當天是否為上班日-->CPY05 = '0'零 表示上班日
		// 	$sql = "Select CPY04, CPQ03, CPQ04, CPQ05, CPQ06, CPQ19 From twvp.cpf_file, twvp.cpq_file, twvp.cpy_file Where CPF01 = CPY01 and CPY04 = CPQ02 and CPF01 = '".$empId."' and CPY02 = '".$year."' and CPY021 = '".$month."' and CPY03 = '".$day."' and CPY05 = '0' ";
		// 	$rs = ConnectOracle($Oracle, $sql);
		// 	oci_execute($rs);
		// 	while ($row = oci_fetch_array($rs, OCI_ASSOC+OCI_RETURN_NULLS)) {
		// 		foreach($row as $_key=>$_value) {
		// 			$$_key = $row[$_key];
		// 			$$_key = iconv("Big5", "UTF-8", $$_key);
		// 			//echo $_key.":".$$_key."<br />";
		// 		}
				
		// 		$CQH01 = $empId;			//工號
		// 		$CQH02 = $CQG02;								//行序
		// 		$CQH04 = $date;									//歸屬日期
		// 		$CQH05 = $date;									//起始日期
		// 		$CQH051 = sprintf("%02d", $CPQ03).":".sprintf("%02d", $CPQ04);	//起始時分
		// 		$CQH06 = $date;									//截止日期
		// 		$CQH061 = sprintf("%02d", $CPQ05).":".sprintf("%02d", $CPQ06);	//截止時分
		// 		$CQH07 = $CPQ19;								//請假時數
		// 		$CQH071 = 1;									//天數
		// 		$CQH08 = "N";									//預先借假否
		// 		$CQH09 = "";									//預留
		// 		$CQH10 = "";									//預留
		// 		$CQH11 = "";									//加班說明
				//CQH_FILE=請假detail
		// 		$sql = "Insert into twvp.CQH_FILE (CQH01, CQH02, CQH04, CQH05, CQH051, CQH06, CQH061, CQH07, CQH071, CQH08, CQH09, CQH10, CQH11) values ('$CQH01', '$CQH02', TO_DATE('".$CQH04."', 'YYYY/MM/DD'), TO_DATE('".$CQH05."', 'YYYY/MM/DD'), '$CQH051', TO_DATE('".$CQH06."', 'YYYY/MM/DD'), '$CQH061', '$CQH07', '$CQH071', '$CQH08', '$CQH09', '$CQH10', '$CQH11') ";
		// 		//echo $sql."\n";
		// 		$rs = ConnectOracle($Oracle, $sql);
		// 		oci_execute($rs);			
				
		// 		if (!$CQG04) {
		// 			$CQG04 = $CQH05;	//起始日期
		// 			$CQG041 = $CQH051;	//起始時分
		// 		}
		// 		$CQG05 = $CQH06;	//截止日期
		// 		$CQG051 = $CQH061;	//截止時分
				
		// 		$CQG06 += $CQH07;	//請假總時數
		// 		$CQG08 += $CQH071;	//請假天數
		// 	}
		
		// }
			
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
		//請假master
		$sql = "Insert into twvp.CQG_FILE (CQG01, CQG02, CQG03, CQG04, CQG041, CQG05, CQG051, CQG06, CQG07, CQG08, CQG09, CQG10, CQGCONF, CQGUSER, CQGGRUP, CQGMODU, CQGDATE, CQG11, CQGMKSG, CQG12, TA_CQG001) values ('$CQG01', '$CQG02', '$CQG03', TO_DATE('".$CQG04."', 'YYYY/MM/DD'), '$CQG041', TO_DATE('".$CQG05."', 'YYYY/MM/DD'), '$CQG051', '$CQG06', '$CQG07', '$CQG08', '$CQG09', '$CQG10', '$CQGCONF', '$CQGUSER', '$CQGGRUP', '$CQGMODU', TO_DATE('".$CQGDATE."', 'YYYY/MM/DD'), '$CQG11', '$CQGMKSG', '$CQG12', '$TA_CQG001') ";
		//echo $sql."\n";
		$rs = ConnectOracle($Oracle, $sql);
		oci_execute($rs);			
		//請假成功
		echo "S";
		die();


		
	