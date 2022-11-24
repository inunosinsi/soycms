<?php
/**
 * @param string
 * @return bool
 */
function soycms_check_is_image_path(string $path){
	// 拡張子がない場合は調べない
	if(is_bool(strpos($path, ".")) || !strlen(trim(substr($path, strrpos($path, "."))))) return false;
	
	//シンプルにDOCUMENT_ROOT + pathでファイルが存在しているか？
	if(file_exists($_SERVER["DOCUMENT_ROOT"] . $path)) return true;

	// @ToDo サイトの設定で特殊なものがある場合に下記に追加していく

	return false;
}