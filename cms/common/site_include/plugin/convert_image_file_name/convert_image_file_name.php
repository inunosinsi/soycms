<?php
ConvertImageFileNamePlugin::registerPlugin();

class ConvertImageFileNamePlugin {

	const PLUGIN_ID = "convert_image_file_name";

	private $len = 15;	//ファイル名の長さ

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID, array(
			"name"=> "画像ファイル名変換プラグイン",
			"type" => Plugin::TYPE_IMAGE,
			"description"=> "",
			"author"=> "齋藤毅",
			"url"=> "https://saitodev.co",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"1.1"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){

			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
				$this,"config_page"
			));

            CMSPlugin::setEvent("onFileUploadConvertFileName", self::PLUGIN_ID, array($this, "onFileUploadConvertFileName"));
		}
	}

    function onFileUploadConvertFileName($args){
        $filename = $args["filename"];
		if(is_bool(strpos($filename, "."))) return null;
		$extension = substr($filename, strrpos($filename, ".") + 1);
		$extension = mb_strtolower($extension);	//すべて小文字
		switch($extension){
			case "jpg":
			case "jpeg":
				$extension = "jpg";
				break;
			case "png":
			case "gif":
			case "webp":
			case "avif":
				// そのまま
				break;
			default:
				$extension = null;
		}	
		return (is_string($extension)) ? self::_generateJpgFile($filename, $extension) : null;
    }

	private function _generateJpgFile(string $filename, string $ext){
		if(!is_numeric($this->len) || $this->len > 32) $this->len = 32;
		return substr((string)date("s") . md5($filename . (string)time()), 0, $this->len) . "." . $ext;
	}

	function config_page($message){
		SOY2::import("site_include.plugin.convert_image_file_name.config.ConvertFileNameConfigPage");
		$form = SOY2HTMLFactory::createInstance("ConvertFileNameConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	function getLen(){
		return (is_numeric($this->len)) ? $this->len : 15;
	}
	function setLen($len){
		$this->len = $len;
	}

	/**
	 * プラグインの登録
	 */
	public static function registerPlugin(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)) $obj = new ConvertImageFileNamePlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
}
