<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>


<meta http-equiv="Content-Type" content="text/html; charset=BIG5" />  <!-- ��ƮwŪ�X�� -->
<!--
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
-->

<script language="javascript" src="assets/js/jquery-3.3.1.js"></script>



<script>

var time_x=0;   //�O���S�ާ@�ɶ�
var myVar;

//----����֥[���
function myFunction() {
	time_x++;
    myVar = setTimeout(function(){ myFunction(); }, 1000);
	
	
	if(time_x>=300){  
	   //�]5����
	   //alert('�z�w�W�L5����') 
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
  //�i�J		
  $("#centerFrame").mouseenter(function(e){
    myStopFunction();
  });
  
  
  //���}
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
    <td width="17%" align="center" bgcolor="#666666">���f�渹</td>
    <td width="5%" align="center" bgcolor="#666666">����</td>
    <td width="40%" align="center" bgcolor="#666666">�~�W</td>
    <td width="8%" align="center" bgcolor="#666666">�ƶq</td>
    <td width="6%" align="center" bgcolor="#666666">�����</td>
    <td width="12%" align="center" bgcolor="#666666">������</td>
    <td align="center" bgcolor="#666666">����ɶ�</td>
  </tr>
  
  
  
<?php


       date_default_timezone_set("Asia/Taipei"); 



  //??PL SQL 
  $conn = oci_connect("twvp", "twvp", "TopProd", "ZHT16BIG5");
  if (!$conn) {
	  die("�s�u����");
  }

  $factory_area="twvp";



/*
AAAW14 IS NULL  �٨S����

Y������   ���}��null���X��

Y������   ���}�Ƥ���null �����X��

*/   

$now_date_day=date("Y/m/d");  //�{�b����
$now_date=date("Y/m/d H:i:s");  //�{�b���ɶ�

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
   
	   
      $date_deduct= date( "Y/m/d H:i:s", strtotime($now_date)-3600 );  //�{�b�ɶ���1�p��
   
      //print $date_deduct."<br>";
      //print $AAAW11."<br>";
	  
	  
	  
	  $bg_color="#fffff4";
	  
	  if($date_deduct>$AAAW11){ //�N��W�L1�p��
	 
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

