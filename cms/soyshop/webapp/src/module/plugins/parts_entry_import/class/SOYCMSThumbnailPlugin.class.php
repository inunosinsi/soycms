<?php

class SOYCMSThumbnailPlugin {

	const PLUGIN_ID = "soycms_thumbnail";

	const UPLOAD_IMAGE = "soycms_thumbnail_plugin_upload";
	const TRIMMING_IMAGE = "soycms_thumbnail_plugin_trimming";
	const RESIZE_IMAGE = "soycms_thumbnail_plugin_resize";
	const PREFIX_IMAGE = "soycms_thumbnail_plugin_";

	const THUMBNAIL_CONFIG = "soycms_thumbnail_plugin_config";
	const THUMBNAIL_ALT = "soycms_thumbnail_plugin_alt";

	private $ratio_w = 4;
	private $ratio_h = 3;

	private $resize_w = 120;
	private $resize_h = 90;

	private $no_thumbnail_path;

	private $label_thumbail_paths = array();

	function getId(){
		return self::PLUGIN_ID;
	}

	function getRatioW(){
		return $this->ratio_w;
	}
	function setRatioW($ratio_w){
		$this->ratio_w = $ratio_w;
	}

	function getRatioH(){
		return $this->ratio_h;
	}
	function setRatioH($ratio_h){
		$this->ratio_h = $ratio_h;
	}

	function getResizeW(){
		return $this->resize_w;
	}
	function setResizeW($resize_w){
		$this->resize_w = $resize_w;
	}

	function getResizeH(){
		return $this->resize_h;
	}
	function setResizeH($resize_h){
		$this->resize_h = $resize_h;
	}

	function getNoThumbnailPath(){
		return $this->no_thumbnail_path;
	}
	function setNoTHumbnailPath($no_thumbnail_path){
		$this->no_thumbnail_path = $no_thumbnail_path;
	}

	function getLabelThumbnailPaths(){
		return $this->label_thumbail_paths;
	}
	function setLabelThumbnailPaths($label_thumbail_paths){
		$this->label_thumbail_paths = $label_thumbail_paths;
	}
}
