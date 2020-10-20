<?php

class ItemImageListComponent extends HTMLList{

	protected function populateItem($entity, $key){
		$isImage = (is_string($entity) && preg_match('/\.(jpg|jpeg|png|gif)$/i', $entity));
		$imagePath = ($isImage) ? soyshop_get_image_file_path($entity) : null;

		//画像のサイズを取得
		$values = self::_getImageFileInfo($imagePath);
		$filename = $values[0];
		$width = $values[1];
		$height = $values[2];

		$this->addImage("image", array(
			"src" => ($isImage) ? $entity : "",
			"attr:data-toggle" => "tooltip",	//画像にカーソルを合わせると画像ファイルの情報が表示される
			"attr:title" => "ファイル名：" . $filename . "  サイズ：" . $width . " * " . $height
		));


		if(!$isImage) return false;
	}

	private function _getImageFileInfo($imagePath){
		if(!isset($imagePath) || !strlen($imagePath) || !file_exists($imagePath)) return array(null, 0, 0);

		$filename = trim(substr($imagePath, strrpos($imagePath, "/")), "/");
		$info = @getimagesize($imagePath);

		$width = (isset($info[0])) ? (int)$info[0] : 0;
		$height = (isset($info[1])) ? (int)$info[1] : 0;
		return array($filename, $width, $height);
	}
}
