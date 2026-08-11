<?php

//SOY CMSのDateLabelを使う
if(file_exists(dirname(SOYSHOP_ROOT) . "/common/site_include/DateLabel.class.php")){
	include_once(dirname(SOYSHOP_ROOT) . "/common/site_include/DateLabel.class.php");
}else{
	error_log("Fatal Error: class DateLabel is missing (common/site_include/DateLabel.class.php).");
	echo "Fatal Error: class DateLabel is missing (common/site_include/DateLabel.class.php).";
	exit;
}
