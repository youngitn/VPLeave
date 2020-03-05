<?php

    $loction=mysql_connect("localhost", "vpsupply", "supplypass");           
	if (!$loction) die("建立資料連接失敗");
	
	$db =mysql_select_db("new_staff",$loction);
	if (!$db) die("開啟資料庫失敗");


?>
