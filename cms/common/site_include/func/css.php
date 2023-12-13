<?php
require 'vendor/autoload.php';

/**
 * @param string, string
 */
function soycms_compile_scss(string $in, string $out){
	exec(dirname(__FILE__)."/vendor/bin/pscss ".$in." > ".$out);
}

/**
 * @param string
 * @return string
 */
function soycms_compress_css(string $s){
	$s = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $s); 
	$s = str_replace(': ', ':', $s);
	$s = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $s);
	return str_replace(";}", "}", $s);
}

/**
 * @param string
 * @return string
 */
function soycms_compress_css_by_filepath(string $p){
	if(!file_exists($p) || !preg_match("/\.css$/", $p)) return "";
	return soycms_compress_css(file_get_contents($p));
}