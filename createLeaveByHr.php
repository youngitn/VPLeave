<?PHP
		include("includes/includes.php");
		//go
		$CQG04 		= str_filter($_POST["CQG04"]);	//起始日期
		$CQG05 		= str_filter($_POST["CQG05"]);   //截止日期
		$stime 		= str_filter($_POST["stime"]);   //開始時分
		$etime 		= str_filter($_POST["etime"]);	//截止時分
		$leaveAmt  	= str_filter($_POST["leaveAmt"]);	//請假時數
		$leaveDay	= str_filter($_POST["leaveDay"]);	//請假天數(照比例計算)
		$empId		= str_filter($_POST["empId"]);	//員工編號 
		$reqType	= str_filter($_POST["reqType"]);	//員工編號 
		$sdate = explode("/", $CQG04);	//起始日期
		$edate = explode("/", $CQG05);	//截止日期
		$state = "";
		$message = "";
		//訖日不可小於起日
		if ($CQG05 < $CQG04){
			$state = "F";
			$message = "請假訖日不可小於起日";
			echo buildJson($state,$message);
			die();
		}
		//特休只能請整日
		if ($_SESSION["MyMember"]["CQG03"]=="06" && !is_int($leaveDay)){
			$state = "F";
			$message = "特休只限請整天";
			echo buildJson($state,$message);
			die();
		}
		
		
		//給資料序號 先找出最後一筆序號數字 加1後為最新一筆序號
		$CQG02 = getDataMaxNumId($db,$Oracle,$empId);
		
		/***開始組data 查詢欲請假中所有可請日期***/
		$condition = " and ";
		if ($sdate[1] < $edate[1]){
			$condition = " or ";
		}
		
		$sql = " Select 
		CPY04,CPY02,CPY021,CPY03, CPQ03, CPQ04, CPQ05, CPQ06, CPQ19,CPQ15,CPQ16,CPQ17,CPQ18 
		From ".$db."cpf_file,".$db."cpq_file, ".$db."cpy_file 
		Where 
		CPF01 = CPY01 and CPY04 = CPQ02 and CPF01 = '".$empId."' and (CPY02 in ('".$sdate[0]."','".$edate[0]."')) and 
		((CPY021 ='".$sdate[1]."' and CPY03 in (select cpy03  From ".$db."cpf_file, ".$db."cpq_file, ".$db."cpy_file Where CPF01 = CPY01 and CPY04 = CPQ02 and CPF01 = '".$empId."' and (CPY02 = '".$sdate[0]."') and (CPY021 = '".$sdate[1]."') and (CPY03 >= '".$sdate[2]."') )) 
	   ".$condition." 
	   (CPY021 = '".$edate[1]."' and CPY03 in (select cpy03  From ".$db."cpf_file, ".$db."cpq_file, ".$db."cpy_file Where CPF01 = CPY01 and CPY04 = CPQ02 and CPF01 = '".$empId."' and (CPY02 = '".$edate[0]."') and (CPY021 = '".$edate[1]."') and (CPY03 <= '".$edate[2]."')))) 
		and CPY05 = '0' order by CPY02,CPY021,CPY03";

		 //$sql = "Select 
		// CPY04,CPY02,CPY021,CPY03, CPQ03, CPQ04, CPQ05, CPQ06, CPQ19, 
		// CPQ15,CPQ16,CPQ17,CPQ18 
		// From twvp.cpf_file, twvp.cpq_file, twvp.cpy_file 
		// Where CPF01 = CPY01 and CPY04 = CPQ02 and CPF01 = '".$empId."' and CPY02 >= '".$sdate[0]."' and CPY021 >= '".$sdate[1]."' and CPY03 >= '".$sdate[2]."' and CPY02 <= '".$edate[0]."' and CPY021 <= '".$edate[1]."' and CPY03 <= '".$edate[2]."' and CPY05 = '0' order by CPY02,CPY021,CPY03";
		//echo $sql; 
		$rs = ConnectOracle($Oracle, $sql);
		oci_execute($rs);
		//echo $days;
		$sqlForCount = "Select  COUNT(*) AS NUMBER_OF_ROWS From ".$db."cpf_file, ".$db."cpq_file, ".$db."cpy_file Where CPF01 = CPY01 and CPY04 = CPQ02 and CPF01 = '".$empId."' and CPY02 >= '".$sdate[0]."' and CPY021 >= '".$sdate[1]."' and CPY03 >= '".$sdate[2]."' and CPY02 <= '".$edate[0]."' and CPY021 <= '".$edate[1]."' and CPY03 <= '".$edate[2]."'and CPY05 = '0'";
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
		$CQG06 = 0;	//請假總時數
		$CQG08 = 0;	//請假天數
		$insertSqlArray = [];
		$insertSql = "";
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
			$wkEndTimeHH = $CPQ05;
			$wkEndTimeMM = sprintf("%02d",$CPQ06);
			$wkEndTime   = $wkEndTimeHH.":".$wkEndTimeMM;
			$wkStartTimeHH = $CPQ03;
			$wkeStartTimeMM = sprintf("%02d",$CPQ04);
			$wkStartTime   = $wkStartTimeHH.":".$wkeStartTimeMM;
			//請假第一天處理 
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
					$CQH061 = $wkEndTime;	//截止時分
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
				
				$CQH051 = $wkStartTime;	//起始時分
				
				//最後一天 截止時間 = 由頁面輸入之請假截止時間
				if ($nowDay == $numberOfDays){
					$CQH061 = $etime;	//截止時分
					
				}else{
					$CQH061 = $wkEndTime;	//截止時分
					
				}
				$stimeInt = (int)(sprintf("%02d", $wkStartTimeHH).sprintf("%02d", $wkeStartTimeMM));
				$etimeInt = (int)str_replace(":","",$CQH061);
				
				//echo $stimeInt.$etimeInt;
				//跨日日期判斷
				//開始時間落在20-23間 截止時間落在0-6間 表示跨日
				if ($isOverDay == "overDay"){
					
					$CQH06 = date('Y/m/d', strtotime ("+1 day", strtotime($CQH05)));
				}
			}
			
			if ($isOverDay == "overDay"){
				$diffTime = computLeaveAmt($CQH06,$CQH05,$CQH061,$CQH051,$CPY04);
			}else{
				$diffTime = computLeaveAmt(null,$CQH05,$CQH061,$CQH051,$CPY04);
			}
			
			
			//$CQH061 = sprintf("%02d", $CPQ05).":".sprintf("%02d", $CPQ06);	//截止時分
			$CQH08 = "N";									//預先借假否
			$CQH09 = "";									//預留
			$CQH10 = "";									//預留
			$CQH11 = "";									//加班說明
			
			//請假的上下班時間 = 班別上下班時間 表示請整天
			if ($wkStartTime == $CQH051 && $wkEndTime == $CQH061){
				$diffTime = 8; //這天請假時數必=8
				$leaveDay = 1; //8小時 = 1天
			}else{
				$startRestTime = $CPQ15.":".sprintf("%02d",$CPQ16);
				$endRestTime = $CPQ17.":".sprintf("%02d",$CPQ18);
				
				$diffTime = processRest($CQH051,$CQH061,$startRestTime,$endRestTime,$diffTime);
				$leaveDay = $diffTime/8;
			}
			//echo "===>".ifReLeave($Oracle,$empId,$CQH05,$CQH06,$CQH051,$CQH061);
			$isreLeave = "";
			
			if ($wkStartTimeHH == "20"){
				$isreLeave = ifReLeaveNightClass($db,$Oracle,$empId,$CQH05,$CQH06,$CQH051,$CQH061);
			}else{
				$isreLeave = ifReLeave($db,$Oracle,$empId,$CQH05,$CQH06,$CQH051,$CQH061);
			}
			
			
			if ($isreLeave == "F"){
				$state =  "F";
				$message = "請假區間重疊,請重新輸入";
				echo buildJson($state,$message);
				die();
			}
			
			$CQG06 += $diffTime;	//請假總時數
			$CQG08 += $leaveDay;	//請假天數
			
			$CQH051 = fixTimeNumber($CQH051);	//時間雙位轉換
			$CQH061 = fixTimeNumber($CQH061);	//時間雙位轉換
			$data .= "<tr>".
			"<td height='30'>&nbsp;&nbsp;".$CPY04."<!--班別--></td>".
			"<td>&nbsp;&nbsp;".$CQH05."<!--起始日期--></td>".
			"<td>&nbsp;&nbsp;".$CQH051."<!--起始時分--></td>".
			"<td>&nbsp;&nbsp;".$CQH06."<!--截止日期--></td>".
			"<td>&nbsp;&nbsp;".$CQH061."<!--截止時分--></td>".
			"<td>&nbsp;&nbsp;".$diffTime."<!--請假時數 --></td>".
			"<td>&nbsp;&nbsp;".$leaveDay."<!--請假天數--></td>".
			"</tr>";
			
			$insertSql = "Insert into ".$db."CQH_FILE (CQH01, CQH02, CQH04, CQH05, CQH051, CQH06, CQH061, CQH07, CQH071, CQH08, CQH09, CQH10, CQH11) values ('$CQH01', '$CQH02', TO_DATE('".$CQH05."', 'YYYY/MM/DD'), TO_DATE('".$CQH05."', 'YYYY/MM/DD'), '$CQH051', TO_DATE('".$CQH06."', 'YYYY/MM/DD'), '$CQH061', '$diffTime', '$leaveDay', '$CQH08', '$CQH09', '$CQH10', '$CQH11') ";
			
			if ($reqType != "view"){
				$irs = ConnectOracle($Oracle, $insertSql);
				oci_execute($irs);
			}
			
			//$insertSqlArray[$nowDay] = $insertSql;
			//echo date('Y-m-d', strtotime ("+1 day", strtotime($CQH05)));
			//echo " 序號:".$CQH02." 啟始日:".$CQH05." 啟始時間:".$CQH051." 截止日:".$CQH06." 截止時間:".$CQH061."\n";
			//晚班 跨日單一天會跑兩次 故break
			if ($isOverDay == "overDay" && $numberOfDays == 2){break;}
			$nowDay++;			
			
		}
		//for ( $i = 1; $i <= $nowDay; $i++) {
			
		//}
		
		//以上統計請假總時數後進行特休比較判斷
		if ($_SESSION["MyMember"]["CQG03"]=="06" && isOverLeave($db,$Oracle,$empId,$CQG08) == "Y"){
			$state =  "F";
			$message = "特休可休時數不足";
			echo buildJson($state,$message);
			die();
		}
		
			
		$CQG01 = $empId;		//工號
		$CQG02 = $CQH02;		//行序
		$CQG03 = $_SESSION["MyMember"]["CQG03"];	//假別代號
		//$CQG04			//起始日期
		$CQG041 = fixTimeNumber($stime); 			//起始時分
		//CQG05				//截止日期
		$CQG051 = fixTimeNumber($etime);			//截止時分
		//CQG06				//請假總時數
		$CQG07 = "N";		//預先借假否
		//CQG08				//請假天數
		$CQG09 = "";		//預留
		$CQG10 = "N";		//在請假平台上判斷主管是否瀏覽(一律都’N’)
		$CQGCONF = "N";		//確認否	Y(一律都’N’)
		$CQGUSER = $empId;			//資料所有者(寫入登入者工號)
		$CQGGRUP = $_SESSION["MyMember"]["CPF29"];			//資料所有群(帶cpf29)
		$CQGMODU = $empId;			//最後修改者(寫入登入者工號)
		$CQGDATE = date("Y/m/d");							//產生日期
		$CQG11 = 0;											//狀況碼(一律都’0’)
		$CQGMKSG = "N";										//簽核否	N(一律都’N’)
		$CQG12 = "";										//加班說明(空白)
		$TA_CQG001 = $_SESSION["MyMember"]["TA_CPF001"];	//主管人員	I1280(帶ta_cpf001)
		//請假master
		$insertSqlMaster = "Insert into ".$db."CQG_FILE (CQG01, CQG02, CQG03, CQG04, CQG041, CQG05, CQG051, CQG06, CQG07, CQG08, CQG09, CQG10, CQGCONF, CQGUSER, CQGGRUP, CQGMODU, CQGDATE, CQG11, CQGMKSG, CQG12, TA_CQG001) values ('$CQG01', '$CQG02', '$CQG03', TO_DATE('".$CQG04."', 'YYYY/MM/DD'), '$CQG041', TO_DATE('".$CQG05."', 'YYYY/MM/DD'), '$CQG051', '$CQG06', '$CQG07', '$CQG08', '$CQG09', '$CQG10', '$CQGCONF', '$CQGUSER', '$CQGGRUP', '$CQGMODU', TO_DATE('".$CQGDATE."', 'YYYY/MM/DD'), '$CQG11', '$CQGMKSG', '$CQG12', '$TA_CQG001') ";
		if ($reqType != "view"){
			$rs = ConnectOracle($Oracle, $insertSqlMaster);
			oci_execute($rs);	
		}		
		// //請假成功
		// echo "S";
		// die();
		//$data.= $insertSql;
		$state = "showCheckList".$company.$db;
		if ($reqType != "view" ){
			$state = "S";
		}
		
		echo buildJson($state,$message,$data);
		
		// if ($reqType == "view"){
		// 	return;
		// }
		function buildJson($state,$message,$data=null){
			return "{\"state\":\"".$state."\",\"data\":\"".$data."\",\"message\":\"".$message."\"} ";
		}
		function ifReLeave($db,$Oracle,$empId,$CQG04,$CQG05,$stime,$etime){
			
			//日期個位數日期補0 以符合資料庫比對格式
			$sdate = explode("/", $CQG04);	//起始日期
			$CQG04 = $sdate[0]."/".sprintf("%02d",$sdate[1])."/".sprintf("%02d",$sdate[2]);
			$edate = explode("/", $CQG05);	//起始日期
			$CQG05 = $edate[0]."/".sprintf("%02d",$edate[1])."/".sprintf("%02d",$edate[2]);
			$sqlx  = "select count(*) as counter from ".$db."CQH_FILE where cqh01 = '".$empId."' and ((to_char(CQH05, 'YYYY/mm/dd') >= '".$CQG04."' and to_char(CQH05, 'YYYY/mm/dd') <= '".$CQG05."' and CQH051 >='".$stime."') or (to_char(CQH06, 'YYYY/mm/dd') >= '".$CQG04."' and to_char(CQH06, 'YYYY/mm/dd') <= '".$CQG05."' and CQH061 <='".$etime."'))";
			//echo $sqlx;
			$rsx = ConnectOracle($Oracle, $sqlx);
			oci_define_by_name($rsx, 'COUNTER', $counter);
			$r = oci_execute($rsx);
			if (!$r) {
				$e = oci_error($rsx);  // For oci_execute errors pass the statement handle
				print htmlentities($e['message']);
				print "\n<pre>\n";
				print htmlentities($e['sqltext']);
				printf("\n%".($e['offset']+1)."s", "^");
				print  "\n</pre>\n";
			}
			oci_fetch($rsx);
			if ($counter > 0 ){
				return "F";
			}
			return "OK";
		}

		/**
		 * 時間位數轉雙位
		 * $time String
		 * ex:9:5=>09:05
		 */
		function fixTimeNumber($time){
			$tmpTimeArray = explode(":", $time);
			$ret = sprintf("%02d",$tmpTimeArray[0]).":".sprintf("%02d",$tmpTimeArray[1]);	//時間雙位轉換
			return $ret; 
		}
		
		function ifReLeaveNightClass($db,$Oracle,$empId,$CQG04,$CQG05,$stime,$etime){
			
			//日期個位數日期補0 以符合資料庫比對格式
			$sdate = explode("/", $CQG04);	//起始日期
			$CQG04 = $sdate[0]."/".sprintf("%02d",$sdate[1])."/".sprintf("%02d",$sdate[2]);
			$edate = explode("/", $CQG05);	//起始日期
			$CQG05 = $edate[0]."/".sprintf("%02d",$edate[1])."/".sprintf("%02d",$edate[2]);
			$sqlx  = "select to_char(CQH05, 'YYYY/mm/dd') as LEAVED_START_DATE,to_char(CQH06,'YYYY/mm/dd') as LEAVED_END_DATE ,CQH051,CQH061 from ".$db."CQH_FILE where cqh01 = '".$empId."' and (
			(to_char(CQH05, 'YYYY/mm/dd') >= '".$CQG04."' and to_char(CQH05, 'YYYY/mm/dd') <= '".$CQG05."') 
			or
			(to_char(CQH06, 'YYYY/mm/dd') >= '".$CQG04."' and to_char(CQH06, 'YYYY/mm/dd') <= '".$CQG05."')
			)";
			//echo $CQG04." ".$stime."-".$CQG05." ".$etime;
		
			$rsx = ConnectOracle($Oracle, $sqlx);
			oci_execute($rsx);
			$ret = "";
			while ($row = oci_fetch_array($rsx, OCI_ASSOC+OCI_RETURN_NULLS)) {
				foreach($row as $_key=>$_value) {
					$$_key = $row[$_key];
					$$_key = iconv("Big5", "UTF-8", $$_key);
					//echo $_key.":".$$_key."<br />";
				}
				$stime =  checkZero((int)str_replace(":","",$stime));
				$etime = checkZero((int)str_replace(":","",$etime)); 
				$CQH051 = checkZero((int)str_replace(":","",$CQH051));
				$CQH061 = checkZero((int)str_replace(":","",$CQH061));
				//echo $CQG04." ".$CQH05."*".$CQG05." ".$CQH06 ;
				
				
				// if (($stime >= $CQH051 && $stime <= $CQH061) || ($etime >= $CQH051 && $etime <= $CQH061)){
				// 	$ret =  "F";
				// 	break;
				// }
			
				//$ret =  "F";
				$ret = "";
				
				//echo "(".$CQG04. "==" .$LEAVED_START_DATE ."&&" .$CQG05 ."==" .$LEAVED_END_DATE .")" ."||" ."(".$CQG04 ."==". $LEAVED_START_DATE. "&&" .$CQG05 ."==" .$LEAVED_START_DATE. ") || (".$CQG04. "==" .$LEAVED_END_DATE ."&&". $CQG05 ."==". $LEAVED_END_DATE .")";
				//echo "(".$stime ."<" .$CQH051 ."&&" .$etime. "<=" .$CQH051.")" ."||" ."(".$stime .">=". $CQH061 ."&&". $etime .">". $CQH061.")";
				 if(($CQG04 == $LEAVED_START_DATE && $CQG04 == $LEAVED_END_DATE ) ||($CQG04 == $LEAVED_START_DATE && $CQG05 == $LEAVED_END_DATE ) || ($CQG04 == $LEAVED_START_DATE && $CQG05 == $LEAVED_START_DATE ) || ($CQG04 == $LEAVED_END_DATE && $CQG05 == $LEAVED_END_DATE )){
					
					//凌晨00:00 之後
					if ($CQG04 == $CQG05 && $CQG05 == $LEAVED_END_DATE){
															
						if(($stime < $CQH051 && $etime <= $CQH051) || ($stime >= $CQH061 && $etime > $CQH061)){
							$ret =  "OK";
						}else{
							$ret =  "F";
							break;
						}
						
					}
					//凌晨00:00 之前 
					else if ($CQG04 == $CQG05 && $CQG05 == $LEAVED_START_DATE){

						
						//比對已存在資料為跨日								
						if (($LEAVED_START_DATE != $LEAVED_END_DATE)){
								if ($stime >= 2400){
									$ret =  "OK";	
								}

						}
						//比對已存在資料非跨日
						else{

							if ($stime <= $CQH051 && $etime >= $CQH061){
								$ret =  "F";
								break;
							}
				
							
							//echo "===>1".$stime. "<=". $CQH051. "&&" ."(".$etime .">=". $CQH051." &&" .$etime. "<=". $CQH061;
							if ($stime <= $CQH051 && ($etime >= $CQH051 && $etime <= $CQH061)){
								$ret =  "F";
								break;
							}
						
							//echo "==>2".$etime .">=" .$CQH061 ."&&" ."(".$stime ."<=" .$CQH061 ."&&" .$stime .">=". $CQH051;
							if ($etime >= $CQH061 && ($stime <= $CQH061 && $stime >= $CQH051)){
								$ret =  "F";
								break;
							}
						}
						
						
						
					}
					else if (($stime < $CQH051 && $etime <= $CQH051) || ($stime >= $CQH061 && $etime > $CQH061)){
						$ret =  "ok";
					}
					else if (($stime >= $CQH051 && $stime <= $CQH061) || ($etime >= $CQH051 && $etime <= $CQH061)){
							$ret =  "F";
							break;
					}
					else if ($stime <= $CQH051 && $etime >= $CQH061){
							$ret =  "F";
							break;
					}
					
				}
						

			}
			return $ret;
		}
		function checkZero($inpt){
			if ($inpt == 0){
				$inpt = 2400;
			}else if($inpt < 2000){
				$inpt = 2400 + $inpt;
			}

			return $inpt;
		}
		/**
		 * 因每筆請假明細用'天'來區分,故日期為同一天
		 * $date=明細處理日期
		 * $stime = 明細開始時間
		 * $etime = 明細結束時間
		 * $class = 班別 用來識別實際計算時 7:50 => 8:00 17:10 => 17:00
		 * 
		 */
		function computLeaveAmt($edate=null,$sdate,$etime,$stime,$class){

			//請假開始時間轉換
			switch ($class) {
				case "A":
				case "B":
				case "C-1":
					if($stime == "7:50"){
						$stime = "8:00";
					}
					if($stime == "12:10"){
						$stime = "12:00";
					}					
				 break;
				
			 }
			 //請假結束時間轉換
			 switch ($class) {
				case "A":
				case "B":
					if($etime == "17:10"){
						$etime = "17:00";
					}
					break;
				case "H":
					if($etime == "4:30"){
						$etime = "4:00";
					}
				break;
				case "C-1":
					if($etime == "12:10"){
						$etime = "12:00";
					}
					
				break;
			 }
			 $edateTemp = $sdate;
			 if($edate != null){
				$edateTemp = $edate;
			 }
			 	
			 
			$diffTime = (strtotime ($edateTemp." ".$etime) - strtotime( $sdate." ".$stime))%86400/3600; //計算相差之小時數
			return $diffTime;
		}

		function getDataMaxNumId($db,$Oracle,$empId){
			$CQG02 = 1;
			$sql = "Select CQG02 From ".$db."cqg_file Where CQG01 = '".$empId."' and rownum <= 1 order by CQG02 desc ";
			$rs = ConnectOracle($Oracle, $sql);
			oci_execute($rs);
			while ($row = oci_fetch_array($rs, OCI_ASSOC+OCI_RETURN_NULLS)) {
				foreach($row as $_key=>$_value) {
					$$_key = $row[$_key];
					$$_key = iconv("Big5", "UTF-8", $$_key);
				}
				$CQG02++;
			}
			return $CQG02;
		}

		function processRest($stime,$etime,$srest,$erest,$amt){
			//取得休息時間長度
			if($erest == "12:10")$erest="12:00";
			$diffTime = abs((strtotime($erest) - strtotime($srest))/ (60*60));
			//產生休息時間字串

			$stimeInt = (int)str_replace(":","",$stime);
			$etimeInt = (int)str_replace(":","",$etime);
			$srestInt = (int)str_replace(":","",$srest);
			$erestInt = (int)str_replace(":","",$erest);
			//當休息開始時間==0 表示為24:00,將時間整數+2400來做凌晨的比較
			if ($srestInt == 0 ){
				$srestInt = 2400;
				$erestInt += 2400;
				//開始時間/結束時間 = 0 則設為2400
				if ($stimeInt == 0){
					$stimeInt = 2400;
				} 
				if ($etimeInt == 0){
					$etimeInt = 2400;
				}
				//當結束時間 大於 休息開始時間 或 結束時間小於開始時間 
				//則將結束時間+2400
				if ($etimeInt > $srestInt || $etimeInt < $stimeInt){
					
					$etimeInt += 2400;
				}
				
				
					
			} 
			//判斷請假時間是否涵蓋休息時間
			if(($stimeInt <= $srestInt && $etimeInt > $srestInt) ){
				$amt = $amt-$diffTime;
			}
			// if ($stimeInt >= $srestInt && $stimeInt <= $erestInt && $etimeInt <= $erestInt && $etimeInt >= $srestInt){
			// 	$amt = 0;
			// }
			//return $amt." ".$stimeInt."<=".$srestInt."&&".$etimeInt.">".$srestInt;
			return $amt;
		}	

		function getWorkTime($db,$Oracle,$date,$empId){
	
			$date = explode("/", $date);		
			$year = $date[0];
			$month = $date[1];
			$day = $date[2];
			//echo $empId.$year.$month.$day;
			$stime = "00:01";	//上班時間
			$etime = "23:59";	//下班時間	
			$sql = "Select CPQ03, CPQ04, CPQ05, CPQ06 From ".$db."cpf_file, ".$db."cpq_file, ".$db."cpy_file Where CPF01 = CPY01 and CPY04 = CPQ02 and CPF01 = '".$empId."' and CPY02 = '".intval($year)."' and CPY021 = '".intval($month)."' and CPY03 = '".intval($day)."' and CPY05='0'";		
			$rs = ConnectOracle($Oracle, $sql);
			oci_execute($rs);
			$count = 0;
			while ($row = oci_fetch_array($rs, OCI_ASSOC+OCI_RETURN_NULLS)) {
				foreach($row as $_key=>$_value) {
					$$_key = $row[$_key];
					$$_key = iconv("Big5", "UTF-8", $$_key);
					//echo $_key.":".$$_key."<br />";
				}
				$time["startTime"] = $CPQ03.":".sprintf("%02d",$CPQ04);
				$time["endTime"]  = $CPQ05.":".sprintf("%02d",$CPQ06);
				$count++;
			}
			if($count > 0){
				return  $time;
			}
			return null;
		}
		/**
		 * 特休是否超時
		 * $days:本次請假總天數
		 * 
		 */
		function isOverLeave($db,$Oracle,$empId,$days){
			$sql = "Select CQI06 , CQI09 From ".$db."cqi_file Where CQI01 = '".$empId."' and CQI02 = ".date("Y")." ";
			
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
			if($CQI06==null){
				$CQI06=0;
			 }
            $OVER=$CQI06-$CQI09;
					
			if ($days>$OVER)
				return "Y";
			else
				return "N";
			
		}
		


		
	