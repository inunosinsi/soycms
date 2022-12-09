<?php
/*
 */
class ConvertImageFileNameUpload extends SOYShopUploadImageBase{

	function convertFileName($pathinfo){
		/**
	     * @ToDo ファイル名の末尾に日付を入れる
	     * @ToDo 管理画面で様々な設定を持ちたい
	     */
	    return $pathinfo["filename"] . "_" . date("YmdHis") . "." . $pathinfo["extension"];
	}
}
SOYShopPlugin::extension("soyshop.upload.image", "convert_image_file_name", "ConvertImageFileNameUpload");
