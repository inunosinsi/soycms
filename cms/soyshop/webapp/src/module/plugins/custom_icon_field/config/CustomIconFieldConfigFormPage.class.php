<?php

class CustomIconFieldConfigFormPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
		SOY2::import("module.plugins.custom_icon_field.util.CustomIconFieldUtil");
	}

	function doPost(){

		if(soy2_check_token()){
			//$_FILES["type"]が存在する場合は何らかのファイルがアップロードされたことになる
			if(strlen($_FILES["file"]["type"]) > 0){

				//ファイルの拡張子をチェックする
				if(!preg_match('/(jpg|jpeg|gif|png)$/', $_FILES["file"]["name"])){
					$this->configObj->redirect("extension");
				}

				$fname = $_FILES["file"]["name"];

				$dest_name = CustomIconFieldUtil::getIconDirectory() . "/". $fname;

				//iconsディレクトリの中にすでにファイルがないかチェックする
				if(file_exists($dest_name)){
					$this->configObj->redirect("repetition");
				}

				//ファイルの移動が失敗していないかどうかをチェック
				if(@move_uploaded_file($_FILES["file"]["tmp_name"], $dest_name) === false){
					$this->configObj->redirect("motion");
				}

				$this->configObj->redirect("updated");
			}

			//削除を押したとき
			if(isset($_POST["delete"])){
				$deletes = @$_POST["deletes"];

				//一応確認
				if(is_null($deletes)){

				}

				//チェックしたアイコンを削除する
				foreach($deletes as $fname){
					@unlink(CustomIconFieldUtil::getIconDirectory() . "/" . $fname);
				}
				$this->configObj->redirect("deleted");
			}
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		foreach(array("extension", "repetition", "motion", "deleted") as $t){
			DisplayPlugin::toggle($t, isset($_GET[$t]));
		}

		$files = @scandir(CustomIconFieldUtil::getIconDirectory());
		if(!$files) $files = array();

		$html = array();
		foreach($files as $file){
			if(!preg_match('/(jpg|jpeg|gif|png)$/', $file)) continue;
			$html[] = "<label for=\"" . $file . "\">";
			$html[] = "<input type=\"checkbox\" name=\"deletes[]\" id=\"" . $file . "\" value=\"" . $file . "\" />";
			$html[] = "<img src=\"" . CustomIconFieldUtil::getIconPath() . $file . "\" />";
			$html[] = "</label>";
			$html[] = "&nbsp;&nbsp;";
		}

		$this->addLabel("custom_icon_field", array(
			"html" => (count($html) > 0) ? implode("", $html) : "登録されているアイコンはありません"
		));

		$this->addLabel("custom_icon_directory", array(
			"text" => CustomIconFieldUtil::getIconDirectory()
		));
	}

	function setConfigObj($configObj) {
		$this->configObj = $configObj;
	}
}
