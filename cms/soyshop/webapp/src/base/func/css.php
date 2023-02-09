<?php
// SOY CMSの方のCSS関連の関数群のファイルを読み込む
function soyshop_include_css_functions(){
	$cmsCssFnPath = dirname(dirname(dirname(SOY2::RootDir()))) . "/common/site_include/func/css.php";
	if(file_exists($cmsCssFnPath)) include_once($cmsCssFnPath);
}

/**
 * @param string, string
 */
function soyshop_compile_scss(string $in, string $out){
	if(!function_exists("soycms_compile_scss")) soyshop_include_css_functions();
	if(!function_exists("soycms_compile_scss")) return;
	soycms_compile_scss($in, $out);
}

/**
 * @param string
 * @return string
 */
function soyshop_compress_css(string $s){
	if(!function_exists("soycms_compress_css")) return soyshop_include_css_functions();
	if(!function_exists("soycms_compress_css")) return "";
	return soycms_compress_css($s);
}

/**
 * @param string
 * @return string
 */
function soyshop_compress_css_by_filepath(string $p){
	if(!function_exists("soycms_compress_css_by_filepath")) soyshop_include_css_functions();
	if(!function_exists("soycms_compress_css_by_filepath")) return "";
	return soycms_compress_css_by_filepath($p);
}