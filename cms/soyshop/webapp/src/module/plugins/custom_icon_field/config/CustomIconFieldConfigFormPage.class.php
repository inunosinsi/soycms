<?php

class CustomIconFieldConfigFormPage extends WebPage{
	
	private $config;
	
	function CustomIconFieldConfigFormPage(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
	}

	function doPost(){
		
		if(soy2_check_token()){	
			//$_FILES["type"]が存在する場合は何らかのファイルがアップロードされたことになる
			if(strlen($_FILES["file"]["type"]) > 0){
					
				//ファイルの拡張子をチェックする
				if(!preg_match('/(jpg|jpeg|gif|png)$/', $_FILES["file"]["name"])){
					$this->config->redirect("extension");
				}
					
				$fname = $_FILES["file"]["name"];
					
				$dest_name = $this->getIconDirectory() . "/". $fname;
	
				//iconsディレクトリの中にすでにファイルがないかチェックする				
				if(file_exists($dest_name)){
					$this->config->redirect("repetition");
				}
					
				//ファイルの移動が失敗していないかどうかをチェック
				if(@move_uploaded_file($_FILES["file"]["tmp_name"], $dest_name) === false){
					$this->config->redirect("motion");	
				}
					
				$this->config->redirect("updated");
			}
			
			//削除を押したとき
			if(isset($_POST["delete"])){
				$deletes = @$_POST["deletes"];
			
				//一応確認
				if(is_null($deletes)){
		
				}
			
				//チェックしたアイコンを削除する
				foreach($deletes as $fname){
					@unlink($this->getIconDirectory() . "/" . $fname);
				}
				$this->config->redirect("deleted");	
			}
		}
	}

	function execute(){
		WebPage::WebPage();
		
		$this->addForm("form");
		
		$this->addModel("updated", array(
			"visible" => isset($_GET["updated"])
		));
		
		$this->addModel("extension", array(
			"visible" => isset($_GET["extension"])
		));
		
		$this->addModel("repetition", array(
			"visible" => isset($_GET["repetition"])
		));
		
		$this->addModel("motion", array(
			"visible" => isset($_GET["motion"])
		));
		
		$this->addModel("deleted", array(
			"visible" => isset($_GET["deleted"])
		));
		
		$files = scandir($this->getIconDirectory());
		if(!$files) $files = array();
		
		$html = array();
		foreach($files as $file){
			if(!preg_match('/(jpg|jpeg|gif|png)$/', $file)) continue;
			$html[] = "<label for=\"" . $file . "\">";
			$html[] = "<input type=\"checkbox\" name=\"deletes[]\" id=\"" . $file . "\" value=\"" . $file . "\" />";
			$html[] = "<img src=\"" . $this->getIconPath() . $file . "\" />";
			$html[] = "</label>";
			$html[] = "&nbsp;&nbsp;";
		}
		
		$this->addLabel("custom_icon_field", array(
			"html" => (count($html) > 0) ? implode("", $html) : "登録されているアイコンはありません"
		));
		
		$this->addLabel("custom_icon_directory", array(
			"text" => $this->getIconDirectory()
		));
	}
		
	function setConfigObj($obj) {
		$this->config = $obj;
	}
	
	function getIconDirectory(){
		$siteDir = SOYSHOP_SITE_DIRECTORY;
		return $siteDir . "files/custom-icons/"; 
	}
	
	function getIconPath(){
		$shopPath = SOYSHOP_SITE_URL;
		return $shopPath . "files/custom-icons/";		
	}
}
?>