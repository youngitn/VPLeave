<?php include("includes/includes.php"); ?>
<?php
unset($_SESSION["MyMember"]);


$action = $_POST["action"];
if ($action == "login") {
  $CPF72 = str_filter($_POST["loginID"]);  //帳號
  if ($CPF72 == "") RunJs("step1.php?company=" . $company);

  unset($_SESSION["MyMember"]);

  //使用帳號CPF72取得員工代號CPF01, 員工姓名CPF02, 單位CPF29, 主管TA_CPF001
  $sql = "Select CPF01, CPF02, CPF29, TA_CPF001 From " . $db . "cpf_file Where CPF72 like '" . $CPF72 . "' ";
  $rs = ConnectOracle($Oracle, $sql);
  oci_execute($rs);
  while ($row = oci_fetch_array($rs, OCI_ASSOC + OCI_RETURN_NULLS)) {
    foreach ($row as $_key => $_value) {
      $$_key = $row[$_key];
      $$_key = iconv("Big5", "UTF-8", $$_key);
      //echo $_key.":".$$_key."<br />";
    }
    $_SESSION["MyMember"]["Card"] = $CPF72;        //員工卡號
    $_SESSION["MyMember"]["Code"] = $CPF01;        //員工代號
    $_SESSION["MyMember"]["Name"] = $CPF02;        //員工姓名
    $_SESSION["MyMember"]["CPF29"] = $CPF29;      //資料所有群
    $_SESSION["MyMember"]["TA_CPF001"] = $TA_CPF001;  //主管人員
  }

  if ($_SESSION["MyMember"]["Card"])
    RunJs("step2.php?company=" . $company);
  else
    RunJs("step1.php?company=" . $company, "登入失敗");
}
?>
<?php include("includes/head.php"); ?>
<style type="text/css">
  body {
    margin-left: 0px;
    margin-top: 0px;
    margin-right: 0px;
    margin-bottom: 0px;
    /* background-image: url(image/<?php echo $bg; ?>); */
    background-repeat: repeat;
  }
</style>
<script type="text/javascript">
  function CheckLength() {
    if (document.form.loginID.value.length == 10) document.form.submit();
    Run = setTimeout("CheckLength()", 1000);
  }

  function init() {
    document.form.loginID.focus();
    CheckLength();
  }
</script>
</head>

<body onLoad="init()">
  <table width="450" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
      <td width="1416" height="188" align="center" valign="bottom"><?php echo $logo; ?>

      </td>
    </tr>
    <tr>
      <td height="0" align="center">

        <img src="image/word.png" width="450" height="100" />
        <p>
          <font size="4">trở về chức năng ban đầu</font>
        </p>
        <p>
          <font size="4">ระบบการลางาน
          </font>
        </p>
      </td>
    </tr>
    <tr>
      <td height="350" align="center" valign="top">
        <form name="form" method="post" action="step1.php">
          <table width="100%" border="0" cellpadding="0" cellspacing="0" class="font_10pt">
            <tr>
              <td height="40" align="center" valign="top"><?php echo $line; ?></td>
            </tr>
            <tr>
              <td height="40" align="center"><input name="loginID" type="password" id="loginID" maxlength="10" autocomplete="off" /></td>
            </tr>
            <tr>
              <td height="25" align="center">使用者請靠卡登入

              </td>
            </tr>
            <tr>
              <td height="25" align="center" class="font_12px-red"><span class="font_13px-red">請正確使用識別證登入，謝謝</span></td>
            </tr>
            <tr>
              <td align="center">
                <p>
                  <font size="4">Người sử dụng xin hãy đăng nhập bằng thẻ
                    Xin hãy sử dụng cấp bậc chính xác đăng nhập. Cảm ơn
                  </font>
                </p>
                <p>
                  <font size="4">โปรดเข้าสู่ระบบด้วยบัตรอย่างถูกต้อง ขอบคุณค่ะ
                  </font>
                </p>
              </td>
            </tr>

          </table>
          <input name="action" type="hidden" value="login" />
          <input name="company" type="hidden" value="<?php echo $company; ?>" />
        </form>
      </td>
    </tr>
    <tr>
      <td height="71" align="center" valign="top"><a href="./"><img src="image/select.png" alt="" width="179" height="68" border="0" /></a>
        <p>
          <font size="4">Quay lại menu chính
          </font>
        </p>
        <p>
          <font size="4">กลับไปหน้าหลั
          </font>
        </p>

      </td>
    </tr>
  </table>
</body>

</html>