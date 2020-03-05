<?php

//連 PL SQL 
$conn = oci_connect("twvp", "twvp", "TopProd", "ZHT16BIG5");
if (!$conn) {
	die("連接失敗");
	//var_dump(oci_error());
    //$e = oci_error();
    //trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
	
}

$factory_area="twvp";





//連 MYSQL





?>