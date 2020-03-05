<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>


<meta http-equiv="Content-Type" content="text/html; charset=BIG5" />  <!-- 資料庫讀出來 -->
<!--
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
-->

<script language="javascript" src="assets/js/jquery-3.3.1.js"></script>



<script>

var time_x=0;   //記錄沒操作時間
var myVar;

//----持續累加秒數
function myFunction() {
	time_x++;
    myVar = setTimeout(function(){ myFunction(); }, 1000);
	
	
	if(time_x>=300){  
	   //設5分鐘
	   //alert('您已超過5分鐘') 
	   location.href="become.php";
	}	
	
	
    //$("#xxa").text(time_x);
	
}


myFunction();

/*

function myStopFunction() {
	time_x=0;
    clearTimeout(myVar);
}





$(document).ready(function(){
  //進入		
  $("#centerFrame").mouseenter(function(e){
    myStopFunction();
  });
  
  
  //離開
  $("#centerFrame").mouseleave(function(e){
    myFunction();
  });
  
});

*/


</script>




<style type="text/css">

body {
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
	
	padding: 30px 0px 0px 0px;
}



</style>

<link href="css/vp.css" rel="stylesheet" type="text/css" />

</head>

<body>











<table width="95%" border="1" align="center" cellpadding="5" cellspacing="0">
  
  
  <tr class="font_20px_white">
    <td width="17%" align="center" bgcolor="#666666">收貨單號</td>
    <td width="5%" align="center" bgcolor="#666666">項次</td>
    <td width="40%" align="center" bgcolor="#666666">品名</td>
    <td width="8%" align="center" bgcolor="#666666">數量</td>
    <td width="6%" align="center" bgcolor="#666666">檢驗者</td>
    <td width="12%" align="center" bgcolor="#666666">檢驗日期</td>
    <td align="center" bgcolor="#666666">檢驗時間</td>
  </tr>
  
  
  
<?php


       date_default_timezone_set("Asia/Taipei"); 



  //??PL SQL 
  $conn = oci_connect("twvp", "twvp", "TopProd", "ZHT16BIG5");
  if (!$conn) {
	  die("連線失敗");
  }

  $factory_area="twvp";



/*
AAAW14 IS NULL  還沒檢驗

Y有檢驗   不良數null為合格

Y有檢驗   不良數不為null 為不合格

*/   

$now_date_day=date("Y/m/d");  //現在的天
$now_date=date("Y/m/d H:i:s");  //現在的時間

//print $now_date;
   

$sql = "select AAAW01,AAAW02,AAAW06,AAAW08,AAAW13, to_char(AAAW12,'yyyy/mm/dd HH24:MI:SS') as AAAW12
from ".$factory_area.".AAAW_file 
where (AAAW14='Y' and  AAAW15 IS NULL) and   to_char(AAAW12,'yyyy/mm/dd')='$now_date_day'
order by AAAW11
";
$rs = oci_parse($conn, $sql);
oci_execute($rs);


 $rows=0;
 while ($row = oci_fetch_array($rs)) {
    //print  $row['AAAW01']."x<br>";
   
   $AAAW11_arr=array();
   $AAAW11=$row['AAAW12'];
   $AAAW11_arr=explode(" ",$AAAW11);
   
	   
      $date_deduct= date( "Y/m/d H:i:s", strtotime($now_date)-3600 );  //現在時間扣1小時
   
      //print $date_deduct."<br>";
      //print $AAAW11."<br>";
	  
	  
	  
	  $bg_color="#fffff4";
	  
	  if($date_deduct>$AAAW11){ //代表超過1小時
	 
           //$bg_color="#ffffcc";
      }
	  else{
		  
		   $bg_color="#fffff4";
	  }
 
 
 
 
 
 
?>   
   
   
   
 
   
  
  <tr class="font_20px">
    <td align="center" bgcolor="<?=$bg_color?>"><?=$row['AAAW01']?></td>
    <td align="center" bgcolor="<?=$bg_color?>"><?=$row['AAAW02']?></td>
    <td align="left" bgcolor="<?=$bg_color?>"><?=$row['AAAW06']?></td>
    <td align="center" bgcolor="<?=$bg_color?>"><?=$row['AAAW08']?></td>
    <td align="center" bgcolor="<?=$bg_color?>"><?=$row['AAAW13']?></td>
    <td align="center" bgcolor="<?=$bg_color?>"><?=$AAAW11_arr[0]?></td>
    <td align="center" bgcolor="<?=$bg_color?>"><?=$AAAW11_arr[1]?></td>
  </tr>
  


  
   
   
<?php  
   
   
   
   
   
    $rows++;
	
 }
 
 
 
   
oci_close($conn);




 
?>  
  
  
  
  
  
</table>
</body>
</html>

