<?php include("includes/includes.php"); ?>
<?php include("includes/login_chk.php"); ?>
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
  function SelectLeave(CQG03) {
    ajaxobj = new AJAXRequest;
    ajaxobj.method = "POST";
    ajaxobj.url = "action.php";
    ajaxobj.content = "action=SelectLeave&CQG03=" + CQG03 + "&company=<?= $company ?>";
    ajaxobj.callback = function(xmlobj) {
      var response = xmlobj.responseText;
      if (response == "S") {
        location.href = "step3_dev.php?company=<?= $company ?>";
      } else {
        alert("請假類別錯誤");
        window.location.reload();
      }
    };
    ajaxobj.send();
  }
</script>
</head>

<body>
  <?php

  $OVER = 0;
  //$sql = "Select CQI06 - CQI09 as OVER From twvp.cqi_file, twvp.cpf_file Where CQI01 = CPF01 and CQI01 = '".$_SESSION["MyMember"]["Code"]."' and CQI02 = ".date("Y")." ";
  //$sql = "Select CQI06 - CQI09 as OVER From twvp.cqi_file Where CQI01 = '".$_SESSION["MyMember"]["Code"]."' and CQI02 = ".date("Y")." ";
  $sql = "Select CQI06 , CQI09 , CQI07, CQI10 From " . $db . "cqi_file Where CQI01 = '" . $_SESSION["MyMember"]["Code"] . "' and CQI02 = " . date("Y") . " ";

  $rs = ConnectOracle($Oracle, $sql);
  oci_execute($rs);
  while ($row = oci_fetch_array($rs, OCI_ASSOC + OCI_RETURN_NULLS)) {
    foreach ($row as $_key => $_value) {
      $$_key = $row[$_key];
      $$_key = iconv("Big5", "UTF-8", $$_key);
      //echo $_key.":".$$_key."<br />";
    }
  }

  //---判斷-----------
  if ($CQI09 == null) {
    $CQI09 = 0;
  }
  if ($CQI07 == null) {
    $CQI07 = 0;
  }
  if ($CQI10 == null) {
    $CQI10 = 0;
  }
  $OVER = $CQI06 - $CQI09;
  $TOTALHOUR = $CQI07;
  $HOUR = $CQI07 - $CQI10;
  $USED = $CQI10;


  ?>
  <table width="900" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
      <td height="760" valign="top">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td height="200">
              <table width="25%" border="0" align="left" cellpadding="0" cellspacing="0">
                <tr>
                  <td height="45" align="left" class="font_20px">歡迎 , </td>
                </tr>
                <tr>
                  <td height="45" align="left" class="font_20px">請選擇您請假類別</td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                </tr>
              </table>
              <table width="42%" border="0" align="left" cellpadding="0" cellspacing="0">
                <tr>
                  <td height="45" align="left" class="font_20px">hoan nghênh , </td>
                </tr>
                <tr>
                  <td height="45" align="left" class="font_20px">mời bạn lựa chọn loại hình xin nghỉ</td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                </tr>
              </table>
              <table width="33%" border="0" align="left" cellpadding="0" cellspacing="0">
                <tr>
                  <td height="45" align="left" class="font_20px">ยินดีต้อนรับสู่ร , </td>
                </tr>
                <tr>
                  <td height="45" align="left" class="font_20px">โปรดกดเลือกประเภทการลางาน</td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                </tr>
              </table>
            </td>
            <td height="200" valign="bottom">

            </td>
          </tr>
          <tr>
            <td height="30" background="image/2.png">&nbsp;</td>
          </tr>
          <tr>
            <td height="410" valign="top">
              <table width="850" border="0" align="center" cellpadding="0" cellspacing="0">

                <tr>
                  <td width="283" height="160" align="center"><a href="javascript:;" onClick="SelectLeave('03');"><img src="image/leave_01.png" width="283" height="160" border="0" />
                      <!--事假無限制--></a></td>
                  <td width="284" height="160" align="center"><a href="javascript:;" onClick="SelectLeave('04');"><img src="image/leave_02.png" width="283" height="160" border="0" />
                      <!--病假30天--></a></td>
                  <td width="283" height="160" align="center"><?php if ($TOTALHOUR > 0) { ?><a href="javascript:;" onClick="SelectLeave('06');"><?php } ?><img src="image/leave_03.png" width="283" height="160" border="0" /><?php if ($OVER > 0) { ?></a><?php } ?></td>
                </tr>

                <tr align="center">
                  <td>
                    <p>
                      <font size="4">việc riêng
                      </font>
                    </p>
                    <p>
                      <font size="4">การลากิจ
                      </font>
                    </p>
                  </td>
                  <td>
                    <p>
                      <font size="4">nghỉ ốm
                      </font>
                    </p>
                    <p>
                      <font size="4">การลาป่วย
                      </font>
                    </p>
                  </td>
                  <td align="center" class="font_10pt">
                    <p>
                      <font size="4">nghỉ đặc biệt
                      </font>
                    </p>
                    <p>
                      <font size="4">การลาด้วยใช้วันหยุดประจำปีของตน
                      </font>
                    </p>
                  </td>
                </tr>
                <tr align="center">
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>
                    <p>
                      <font size="4">時數總額:<?= $TOTALHOUR ?> 小時</br>已使用時數:<?= $USED ?> 小時</br>剩餘時數:<?= $HOUR ?> 小時</font>
                    </p>
                  </td>
                </tr>
                <tr align="center">
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>
                    <p>
                      <font size="4">Tổng số giờ: <?= $TOTALHOUR ?> giờ</br>
                        Số giờ đã sử dụng: <?= $USED ?> giờ</br>
                        Số giờ còn lại: <?= $HOUR ?> giờ</font>
                    </p>
                  </td>
                </tr>
                <tr align="center">
                  <td>&nbsp;</td>
                  <td><a href="./"><img src="image/select.png" width="179" height="68" border="0" /></a>
                    <p>
                      <font size="4">Quay lại menu chính</font>
                    </p>
                    <p>
                      <font size="4">กลับไปหน้าหลัก</font>
                    </p>
                  </td>
                  <td>
                    <p>
                      <font size="4">ยอดจำนวนชั่วโมง：<?= $TOTALHOUR ?></br>
                        ชั่วโมง : <?= $USED ?> </br>
                        จำนวนชั่วโมงที่ใช้ไป：<?= $HOUR ?> </font>
                    </p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td height="60" align="center" valign="top">
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>

</html>