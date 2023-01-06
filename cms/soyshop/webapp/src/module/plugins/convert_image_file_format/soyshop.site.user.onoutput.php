<?php
$convert_image_file_format_user_output = false;
SOY2::import("module.plugins.convert_image_file_format.util.ImgFmtUtil");
if(defined("SOYSHOP_CART_MODE") && SOYSHOP_CART_MODE && ImgFmtUtil::getAppPageDisplayConfig(ImgFmtUtil::APP_TYPE_CART)){
	$convert_image_file_format_user_output = true;
}else if(defined("SOYSHOP_MYPAGE_MODE") && SOYSHOP_MYPAGE_MODE && ImgFmtUtil::getAppPageDisplayConfig(ImgFmtUtil::APP_TYPE_MYPAGE)){
	$convert_image_file_format_user_output = true;
}

if($convert_image_file_format_user_output){
	include_once(SOY2::RootDir() . "logic/plugin/extensions/soyshop.site.onoutput.php");
	include_once(dirname(__FILE__) . "/soyshop.site.onoutput.php");
	if(!defined("SOYSHOP_PAGE_ID")) define("SOYSHOP_PAGE_ID", -1);	// ページIDが-1の場合はアプリケーションページ
	SOYShopPlugin::extension("soyshop.site.user.onoutput", "convert_image_file_format", "ConvertImageFileFormatOnOutput");
}
unset($convert_image_file_format_user_output);
