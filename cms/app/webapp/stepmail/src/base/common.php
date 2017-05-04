<?php

function stepmail_convert_number($str, $n){
	if(!isset($str) || !is_numeric($str)) return $n;
	return mb_convert_kana($str, "a");
}

function stepmail_get_first_line($str){
	if(strpos($str, "\n")) $str = substr($str, 0, strpos($str, "\n"));
	return $str;
}
?>