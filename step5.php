<?php include("includes/includes.php"); ?>
<?php include("includes/login_chk.php"); ?>
<?php include("includes/head.php"); ?>
<script type="text/javascript" language="javascript">
var Run;
var second = 4;

function CountDown() {
    if (second != 0) {
        second -= 1;
        $("#mytime").html(second);
    } else {
        location.href = "index.php";
        return;
    }
    Run = setTimeout("CountDown()", 1000);
}

CountDown();
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
                <td height="45" align="left" class="font_20px">&nbsp;</td>
              </tr>
              <tr>
                <td height="45" align="center" class="font_20px"><?php echo $_SESSION["MyMember"]["Name"]; ?></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td height="30" background="image/2.png">&nbsp;</td>
        </tr>
        <tr>
          <td height="410" valign="top"><table width="830" border="0" align="center" cellpadding="0" cellspacing="0" class="font_10pt">
              <tr>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td height="35" align="center"><p>恭喜你!!</p></td>
              </tr>
              <tr>
                <td height="60" align="center" valign="middle" class="font_25px-red">完成請假作業</td>
              </tr>
              <tr>
                <td height="60" align="center" valign="middle" class="font_10pt">於 <span id="mytime">3</span> 秒後返回首頁...</td>
              </tr>
            </table></td>
        </tr>
        <tr>
          <td height="60" align="center"><a href="index.php"><img src="image/select.png" alt="" width="179" height="68" border="0" /></a></td>
        </tr>
      </table></td>
  </tr>
</table>
</body>
</html>
