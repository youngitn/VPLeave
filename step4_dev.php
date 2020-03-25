`<?php include("includes/includes.php"); ?>
<?php include("includes/login_chk.php"); ?>
<?php
if (!$_SESSION["MyMember"]["CQG03"] || !$_SESSION["MyMember"]["LeaveName"]) RunJs("step2.php");

$CQG04			= str_filter($_POST["CQG04"]);	//起始日期
$CQG05 			= str_filter($_POST["CQG05"]);	//截止日期
$classCode 		= $_SESSION["MyMember"]["class"]; //班別
$empId 			= $_SESSION["MyMember"]["Code"]; //員工編號
$leaveName		= getLeaveName($_SESSION["MyMember"]["CQG03"]); //各國假名
$workTimeOfDay 	= $_SESSION["MyMember"]["workTimeOfDay"]; //一日總工時

$timeStart 	= str_filter($_POST["timeStart"]);	//起始時間
list($startHH, $startMM) = split(':', $timeStart);

$timeEnd = str_filter($_POST["timeEnd"]);	//截止時間\
list($endHH, $endMM) = split(':', $timeEnd);

$sdate = explode("/", $CQG04);	//起始日期
$edate = explode("/", $CQG05);	//截止日期
//echo $CQG04." ".$startHH.":".$startMM;
$diffTime = abs((strtotime($CQG04 . " " . $startHH . ":" . $startMM) - strtotime($CQG05 . " " . $endHH . ":" . $endMM)) / (60 * 60)); //計算相差之小時數
//$diffTime = abs((strtotime($startHH.":".$startMM) - strtotime($endHH.":".$endMM))/ (60*60)); //計算相差之小時數

if ($endHH > 12 && $startHH <= 12) {
	$diffTime = $diffTime - 1;
}

if (sprintf("%02d", $endHH) . ":" . sprintf("%02d", $endMM) == (sprintf("%02d", $_SESSION["MyMember"]["classStartHHOri"]) . ":" . sprintf("%02d", $_SESSION["MyMember"]["classStartMMOri"]))) {
	RunAlert("請假截止時間,請勿選擇上班時間");
	RunJs("step3_dev.php");
}

if (!checkdate($sdate[1], $sdate[2], $sdate[0]) || !checkdate($edate[1], $edate[2], $edate[0])) RunJs("step3_dev.php");	//未選擇日期或日期格式錯誤
if ($diffTime == 0) {
	RunAlert("請假時數為0,請重新輸入");
	RunJs("step3_dev.php");
}
// if ($diffTime > 8){
// 	RunAlert("請假時數錯誤,如有跨日請調整日期選項");
// 	RunJs("step3_dev.php");	
// }

//總天數
$diff = DateDiff($CQG04, $CQG05);
//請假時數佔上班時間的比例
$leaveTimeOfDay = $diffTime / $workTimeOfDay;

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
	$(function() {
		jqAjaxCreateLeave('<?= $CQG04; ?>', '<?= $CQG05; ?>', '<?= $startHH . ":" . $startMM ?>', '<?= $endHH . ":" . $endMM ?>', '<?= $diffTime ?>', '<?= $leaveTimeOfDay ?>', '<?= $empId ?>', 'view', '<?= $company ?>');
	});

	function CreateLeave(CQG04, CQG05) {
		ajaxobj = new AJAXRequest;
		ajaxobj.method = "POST";
		ajaxobj.url = "action.php";
		ajaxobj.content = "action=CreateLeave&CQG04=" + CQG04 + "&CQG05=" + CQG05;
		ajaxobj.callback = function(xmlobj) {
			var response = xmlobj.responseText;
			if (response == "S") {
				location.href = "step5.php";
			} else {
				alert("您已於" + CQG04 + " ~ " + CQG05 + " 區間內請假過");
			}
		};
		ajaxobj.send();
	}
	/**
	 * 改丟json
	 * 送出新增假單
	 */
	function jqAjaxCreateLeave(sdate, edate, stime, etime, leaveAmt, leaveDayRate, empid, reqType, company) {
		var reData = {
			empId: empid, //員編
			CQG04: sdate, //開始日期 
			CQG05: edate, //結束日期
			stime: stime, //開始時間
			etime: etime, //結束時間
			leaveAmt: leaveAmt, //請假時數
			leaveDay: leaveDayRate, //請假天數 請假小時/當日總工時(N/8天)
			reqType: reqType, //view or doInsert
			company: company
		};

		$.ajax({
			method: "POST",
			url: "createLeaveByHr.php",
			dateType: "json",
			data: reData

		}).done(function(msg) {
			console.log(msg);
			var rep = JSON.parse(msg);
			var state = rep.state;
			var data = rep.data;
			var message = rep.message;


			if (state == "F") {

				alert(message);
				history.back();
			}
			if (state == "S") {
				location.href = "step5.php";
			}
			//alert(rep.data);
			// if (reqType == 'view'){
			$('#leaveTable > tbody:last-child').remove();
			$('#leaveTable > tbody:last-child').append(data);
			// }

		});
	}
</script>
</head>

<body>
	<div id="123"></div>
	<table width="900" border="0" align="center" cellpadding="0" cellspacing="0">
		<tr>
			<td height="760" valign="top">
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td height="200" valign="bottom">
							<table width="830" border="0" align="center" cellpadding="0" cellspacing="0">

								<tr>
									<td height="45" align="left" class="font_20px">請再次確認請假資料無誤
										<br>hãy xác nhận lại một lần nữa dữ liệu xin nghỉ có sai xót gì không
										<br>โปรดตรวจสอบอีกครั้งว่าข้อมูลการลานั้นถูก
									</td>
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
						<td height="380" valign="top">
							<table width="830" border="0" align="center" cellpadding="0" cellspacing="0" class="font_10pt">
								<tr>
									<td width="100%" height="35">
										<div width="45%" style="display: inline-block;" class="font_12pt">假別類型：<?= $leaveName["ch"] ?></div>
										&nbsp;/&nbsp;
										<div width="45%" style="display: inline-block;" class="font_12pt"> loại hình nghỉ：<?= $leaveName["vietnam"] ?></div>
										&nbsp;/&nbsp;
										<div width="33%" style="display: inline-block;" class="font_12pt"> ประเภทกา：<?= $leaveName["tai"] ?></div>
									</td>
								</tr>
								<tr>
									<td>
										<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" class="font_12pt">
											<tr>
												<td width="20" height="40" align="center" valign="middle"><img src="image/3.jpg" width="10" height="20" /></td>
												<td width="810">請假天數相關細項 &nbsp;/ &nbsp;Hạng mục liên quan số ngày nghỉ &nbsp;/ &nbsp;รายละเอียดการ</td>
											</tr>
											<tr>
												<td colspan="2">
													<table id="leaveTable" width="98%" border="1" align="center" cellpadding="3" cellspacing="0" class="font_10pt">
														<tr>
															<td height="30">&nbsp;&nbsp;班別<br>Loại<br>เวลางานกะไ</td>
															<td>&nbsp;&nbsp;起始日期<br>Thời gian bắt đầu nghỉ<br>เริ่มต้นวันล </td>
															<td>&nbsp;&nbsp;起始時分<br>Nghỉ từ<br>เริ่มเวลาลา </td>
															<td>&nbsp;&nbsp;截止日期<br>Nghỉ đến ngày<br>สิ้นสุดวัน</td>
															<td>&nbsp;&nbsp;截止時分<br>Nghỉ đến<br> สิ้นสุดเวลา</td>
															<td>&nbsp;&nbsp;請假時數<br>Số h xin nghỉ<br>จำนวนชั่วโมง</td>
															<td>&nbsp;&nbsp;請假天數<br>Số ngày nghỉ<br>จำนวนวั</td>
														</tr>
														<tbody>
														</tbody>
													</table>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td height="60" align="center">
							<div>
								<div width="33%" style="display: inline-block;">
									<a href="javascript:history.back()"><img src="image/back.png" width="179" height="68" border="0" /><br>Trở về trang 1<br>กลับไปก่อนหน้านี้</a>
								</div>
								<div width="33%" style="display: inline-block;">
									<a href="javascript:;" id="next_step" onClick="jqAjaxCreateLeave('<?= $CQG04; ?>', '<?= $CQG05; ?>','<?= $startHH . ":" . $startMM ?>','<?= $endHH . ":" . $endMM ?>','<?= $diffTime ?>','<?= $leaveTimeOfDay ?>','<?= $empId ?>','doInsert','<?= $company ?>');"><img src="image/Sent-out.png" width="179" height="68" border="0" />

										<br>Bấm ok
										<br>ยืนยันเพื่อส่งออก
									</a>
								</div>
								<div width="33%" style="display: inline-block;">
									<a href="index.php"><img src="image/select.png" alt="" width="179" height="68" border="0" />
										<br>Trở về menu chính
										<br>กลับไปหน้าหลัก

									</a>
								</div>
							</div>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>

</body>

</html>