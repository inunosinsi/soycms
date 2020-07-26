<?php

class CustomIconFieldConfigPage extends WebPage {

	private $message;
	private $pluginObj;

	function __construct(){
		SOY2::import("site_include.plugin.soycms_custom_icon_field.util.CustomIconFieldUtil");
		SOY2::import("site_include.plugin.soycms_custom_icon_field.component.IconListComponent");
		SOY2::import("site_include.plugin.soycms_custom_icon_field.component.LabelListComponent");
	}

	function doPost(){
		if(soy2_check_token()){
			//ラベルの設定
			$labels = (isset($_POST["labels"])) ? $_POST["labels"] : array();
			$this->pluginObj->setLabels($labels);
			CMSPlugin::savePluginConfig(SOYCMS_CUSTOM_ICON_FIELD_PLUGIN, $this->pluginObj);

			//アップロードを押したとき
			if($_SERVER["REQUEST_METHOD"] == "POST"){

				//$_FILES["type"]が存在する場合は何らかのファイルがアップロードされたことになる
				if(strlen($_FILES["file"]["type"]) > 0){

					//ファイルの拡張子をチェックする
					if(!preg_match('/(jpg|jpeg|gif|png)$/', $_FILES["file"]["name"])) CMSPlugin::redirectConfigPage("ファイル形式が不正です。");

					$fname = $_FILES["file"]["name"];
					$dest_name = CustomIconFieldUtil::getIconDirectory() . "/" . $fname;

					//iconsディレクトリの中にすでにファイルがないかチェックする
					if(file_exists($dest_name)) CMSPlugin::redirectConfigPage("ファイルがすでに存在するためアップロードすることができません。");

					//ファイルの移動が失敗していないかどうかをチェック
					if(@move_uploaded_file($_FILES["file"]["tmp_name"], $dest_name) === false) CMSPlugin::redirectConfigPage("ファイルの移動に失敗しました。");

					CMSPlugin::redirectConfigPage("ファイルのアップロードに成功しました。");

				}else{
					//
				}
			}

			//削除を押したとき
			if(isset($_POST["delete"])){
				$deletes = (isset($_POST["deletes"])) ? $_POST["deletes"] : null;

				//一応確認
				if(is_null($deletes)) CMSPlugin::redirectConfigPage("削除するファイルがありません");

				//チェックしたアイコンを削除する
				foreach($deletes as $fname){
					if(strpos($fname, "/")===false && strpos($fname, ".")!==0 && file_exists(CustomIconFieldUtil::getIconDirectory() . "/" . $fname)){
						@unlink(CustomIconFieldUtil::getIconDirectory() . "/" . $fname);
					}
				}

				CMSPlugin::redirectConfigPage();
			}
		}
	}

	function execute(){
		parent::__construct();

		DisplayPlugin::toggle("error", (is_string($this->message) && strlen($this->message)));
		$this->addLabel("error_message", array(
			"text" => (is_string($this->message)) ? $this->message : ""
		));

		$this->addForm("form", array(
			"attr:enctype" => "multipart/form-data"
		));

		$this->addLabel("icon_dir", array(
			"text" => UserInfoUtil::getSiteDirectory() . CustomIconFieldUtil::getIconDirectory()
		));

		$files = self::_files();
		DisplayPlugin::toggle("files", (count($files)));

		$this->createAdd("icon_list", "IconListComponent", array(
			"list" => $files,
			"iconDir" => $this->pluginObj->getIconDirecotry()
		));

		$this->createAdd("label_list", "LabelListComponent", array(
			"list" => self::_labels(),
			"cnf" => $this->pluginObj->getLabels()
		));
	}

	private function _files(){
		$files = @scandir(UserInfoUtil::getSiteDirectory() . $this->pluginObj->getIconDirecotry());
		if(!is_array($files)) $files = array();
		return $files;
	}

	private function _labels(){
		try{
			$labels = SOY2DAOFactory::create("cms.LabelDAO")->get();
		}catch(Exception $e){
			$labels = array();
		}
		if(!count($labels)) return array();

		$list = array();
		foreach($labels as $label){
			$list[$label->getId()] = $label->getCaption();
		}
		return $list;
	}

	function setMessage($message){
		$this->message = $message;
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
