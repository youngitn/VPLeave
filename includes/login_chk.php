<?php
	if (!isset($_SESSION["MyMember"])) {
		RunJs("./");
		die();
	}
?>