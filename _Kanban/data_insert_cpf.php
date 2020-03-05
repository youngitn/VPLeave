


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=BIG5" />

<!--
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
-->

<style type="text/css">
body {
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
}
</style>

		<link rel="shortcut icon" href="assets/images/favicon_1.ico">

        <title></title>

		<link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/core.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/components.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/icons.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/pages.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/responsive.css" rel="stylesheet" type="text/css" />
        <!--<link href="assets/css/bootstrap.min.css" rel="stylesheet" >-->
        <link href="assets/css/datepicker.css" rel="stylesheet">
        <link href="assets/css/main.css" rel="stylesheet">
        
		
		
		
		
		
		
		
		
        <script language="javascript" src="assets/js/jquery-3.3.1.js"></script>


</head>		
<body>


<?php


function convertStr($str){
     //return  iconv("UTF-8" , "GB2312//IGNORE" , $str);
	 
$new_str = iconv(mb_detect_encoding($str), "big5", $str);
return $new_str;
	 
}



   //include("conf/DB_Load.php"); 
   
   
   
   
   
//-------------------------   

$Job_number="I1635";
//$Job_number=strtoupper( $_POST['Job_number'] );
//$Factory=$_POST['Factory'];

/*

if($Factory=="VP"){   
   
  //連 PL SQL 
  $conn = oci_connect("twvp", "twvp", "TopProd", "ZHT16BIG5");
  if (!$conn) {
	  die("連接失敗");
  }

  $factory_area="twvp";

}
else if($Factory=="MD"){
	
  //連 PL SQL 
  $conn = oci_connect("twmd", "twmd", "TopProd", "ZHT16BIG5");
  if (!$conn) {
	  die("連接失敗");
  }

  $factory_area="twmd";	
	
}

*/
     
   
   
/*


   
   */
   
   
   
   
  //連 PL SQL 
  $conn = oci_connect("twvp", "twvp", "TopProd", "ZHT16BIG5");
  if (!$conn) {
	  die("連接失敗");
  }

  $factory_area="twvp";



  
   
   
   
   
   
   

$sql = "select * from ".$factory_area.".AAAW_file  ";
$rs = oci_parse($conn, $sql);
oci_execute($rs);


 $rows=0;
 while ($row = oci_fetch_array($rs)) {
    print  $row['AAAW01']."x<br>";
   
   
    $rows++;
	
 }
 


 
 
 
 
/*
 

 if($rows!=0){
  //有查到值 就不能寫入
  
 
       print'<script>';
       print'alert("工號重複 請重新填寫");';
       print 'location.replace("step4.php"); ';
       print'</script>';
	   exit();
  

 }
 else{
	  //print '可寫入';
	 

	 
	 
	 
//---------------寫入 cpf_file  表單-----------------
$work_in_name_str='';
$work_in_value_str='';

$work_name_arr=array();
$work_value_arr=array();

//mb_convert_encoding($str, "UTF-8", "BIG5"); //原始編碼為BIG5轉UTF-8
//mb_convert_encoding($str, "BIG5"); //編碼轉換為utf-8


$work_name_arr[]='CPF01';         $work_value_arr[]="'".$Job_number."'";
$work_name_arr[]='CPF28';         $work_value_arr[]="'".$Factory."'";
$work_name_arr[]='CPF281';        $work_value_arr[]="'".$Factory."'";  

$work_name_arr[]='CPF02';         $work_value_arr[]="'". convertStr( $value['name'])."'";   
$work_name_arr[]='CPF03';         $work_value_arr[]="'".convertStr($value['sex'])."'";    
$work_name_arr[]='CPF07';         $work_value_arr[]="'".convertStr($value['IdentityCard'])."'"; 


$work_name_arr[]='CPF08';           $work_value_arr[]="'".convertStr($value['Ename'])."'";    
$work_name_arr[]='CPF18';           $work_value_arr[]="'".convertStr($value['Servicing'])."'";    
$work_name_arr[]='CPF19';           $work_value_arr[]="'".convertStr($value['marriage'])."'";  
  
$work_name_arr[]='CPF05';           $work_value_arr[]="'".$value['BloodType']."'";    
$work_name_arr[]='CPF06';           $work_value_arr[]="'".convertStr($value['Birthplace'])."'";  

$work_name_arr[]='CPF21';           $work_value_arr[]="'".convertStr($value['ResidenceAddress1'])."'";    
$work_name_arr[]='CPF23';           $work_value_arr[]="'".convertStr($value['ResidenceAddress'])."'";    
$work_name_arr[]='CPF22';           $work_value_arr[]="'".convertStr($value['MailingAddress'])."'";    


$work_name_arr[]='CPF68';           $work_value_arr[]="'".convertStr($value['PhoneHome'])."'";    
$work_name_arr[]='CPF09';           $work_value_arr[]="'".convertStr($value['PhoneMove'])."'";  
  
$work_name_arr[]='CPF24';           $work_value_arr[]="'".convertStr($value['HighestEducation'])."'";    
$work_name_arr[]='CPF26';           $work_value_arr[]="'".convertStr($value['GraduatedSchool'])."'";    


//---------新增部分----------
$work_name_arr[]='CPF13';           $work_value_arr[]="'P'";    
$work_name_arr[]='CPF14';           $work_value_arr[]="'2'";    
$work_name_arr[]='CPF16';           $work_value_arr[]="'Y'";    
$work_name_arr[]='CPF17';           $work_value_arr[]="'N'";    
$work_name_arr[]='CPF20';           $work_value_arr[]="'0'";
    
$work_name_arr[]='CPF36';           $work_value_arr[]="'N'";

    
$work_name_arr[]='CPFMODU';         $work_value_arr[]="'N1030'";   
 
$work_name_arr[]='CPF361';          $work_value_arr[]="'N'";    
$work_name_arr[]='CPF581';          $work_value_arr[]="'N'";    
$work_name_arr[]='CPF551';          $work_value_arr[]="'N'";    

$work_name_arr[]='CPF38';           $work_value_arr[]="'0'";    
$work_name_arr[]='CPF40';           $work_value_arr[]="'Y'";    
$work_name_arr[]='CPF46';           $work_value_arr[]="'N'";
    
$work_name_arr[]='CPF80';           $work_value_arr[]="'0'";
$work_name_arr[]='CPF91';           $work_value_arr[]="'0'";    
$work_name_arr[]='CPF92';           $work_value_arr[]="'0'";    
$work_name_arr[]='CPF97';           $work_value_arr[]="'Y'";    

$work_name_arr[]='CPFACTI';         $work_value_arr[]="'Y'";
$work_name_arr[]='CPFUSER';         $work_value_arr[]="'N1030'";
$work_name_arr[]='CPFGRUP';         $work_value_arr[]="'3300'";

//---------新增部分----------

//---------2018-10-09新增部分----------
$work_name_arr[]='CPF76';           $work_value_arr[]="'N'";







//$work_name_arr[]='CPF04';         $work_value_arr[]="'to_date('".convertStr($value['birthday'])."','yyyy-mm-dd')'";    
//$work_name_arr[]='CPF25';           $work_value_arr[]="'to_date('".convertStr($value['GraduationDate'])."','yyyy-mm-dd')'"; 



date_default_timezone_set("Asia/Taipei"); 
$Y=idate('Y');
$M=idate('m');
$D=idate('d');
$dateA=$Y."-".sprintf("%02d",$M)."-".sprintf("%02d",$D);

$date_add= date( "Y-m-d", strtotime( $dateA." +3 month" ) );  //該日期加一年  , 變數有( year , month , day  )			


$work_in_name_str=implode(",",$work_name_arr);
$work_in_value_str=implode(",",$work_value_arr);

$work_in_name_str.=",CPF04,CPF25";
$work_in_name_str.=",CPF33";
$work_in_name_str.=",CPFDATE";
$work_in_name_str.=",CPF70";



$work_in_value_str.=",to_date('".convertStr($value['birthday'])."','yyyy-mm-dd')"; 
$work_in_value_str.=",to_date('".convertStr($value['GraduationDate'])."','yyyy-mm-dd')"; 

$work_in_value_str.=",to_date('".$date_add."', 'YYYY/MM/DD')"; 
$work_in_value_str.=",to_date('".$dateA."', 'YYYY/MM/DD')"; 
$work_in_value_str.=",to_date('".$dateA."', 'YYYY/MM/DD')"; 





$sql="insert into $factory_area.cpf_file( $work_in_name_str ) values( $work_in_value_str )";				



$rsx = oci_parse($conn, $sql);
oci_execute($rsx);				

//print $sql;
	 
	
	 
	 
	 
	 
	  
	 
 }
 
 
  
*/  

  

  
  
  
oci_close($conn);




 
       print'<script>';
       print'alert("finished");';
       //print 'location.replace("step4.php"); ';
       print'</script>';
	   exit();
  



   
?>

</body>
</html>


