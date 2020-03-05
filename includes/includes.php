<?php
	include_once("control/includes/function.php");
	
	$UrlArray = explode("/", htmlentities($_SERVER["PHP_SELF"]));
	$PageURL = strtolower($UrlArray[sizeof($UrlArray)-1]);
	//$db = "";
	$company = $_GET["company"] ? str_filter($_GET["company"]) : str_filter($_POST["company"]);
	switch ($company) {
		case "modus":
			$bg = "background_md.jpg";
			$logo = "<img src=\"image/md_logo-1.png\" width=\"450\" height=\"100\" />";
			$line = "<img src=\"image/md_logo-2.png\" width=\"450\" height=\"40\" />";
			$db = "twmd.";
			break;
		default:
			$company = "vp";
			$bg = "background.jpg";
			$logo = "<img src=\"image/logo.png\" width=\"178\" height=\"117\" />";
			$line = "<img src=\"image/logo-2.png\" width=\"450\" height=\"40\" />";
			$db = "twvp.";
	}

	if ($PageURL=="") RunJs("index.php");

?>