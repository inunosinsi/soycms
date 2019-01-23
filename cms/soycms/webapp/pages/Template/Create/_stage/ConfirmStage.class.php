<?php

class ConfirmStage extends StageBase{

	public function getStageTitle(){
		return "ページ雛形の新規作成 (4/5) - 作成内容の確認";
	}

	//表示部分はここに書く
	public function execute(){

		$this->createAdd("template_id","HTMLInput",array(
			"value" => $this->wizardObj->template->getId(),
			"readonly" => true,
		));

		$this->createAdd("template_name","HTMLInput",array(
			"value" => $this->wizardObj->template->getName(),
			"readonly" => true,
		));

		$this->createAdd("template_description","HTMLTextArea",array(
			"value" => $this->wizardObj->template->getDescription(),
			"readonly" => true,
		));

		$this->createAdd("template_list","TemplateList",array(
			"list" => $this->wizardObj->template->getTemplate()
		));

		$this->createAdd("add_file_list","FileList",array(
			"list" => $this->wizardObj->template->getFileList()
		));


	}

	//次へが押された際の動作
	public function checkNext(){

		$template = $this->wizardObj->template;

		//manifestファイルの作成。
		$doc = new DOMDocument();
		$doc->encoding = "UTF-8";

		//root
		$root = $doc->createElement("soycms");
		$doc->appendChild($root);

		//id
		$id = $doc->createElement("id");
		$id->appendChild($doc->createTextNode($template->getId()));
		$root->appendChild($id);

		//name
		$name = $doc->createElement("name");
		$name->appendChild($doc->createTextNode($template->getName()));
		$root->appendChild($name);

		//type
		$type = $doc->createElement("type");
		$type->appendChild($doc->createTextNode($template->getPageType()));
		$root->appendChild($type);

		//description
		$description = $doc->createElement("description");
		$description->appendChild($doc->createCDATASection($template->getDescription()));
		$root->appendChild($description);

		//files
		$files = $doc->createElement("files");
		$root->appendChild($files);

		SOY2::import("util.CMSFileManager");
		$siteRoot = UserInfoUtil::getSiteDirectory();
		$siteRoot = str_replace("\\","/",$siteRoot);

		$siteUrl = UserInfoUtil::getSiteURL();
		$tmpDir = $this->getTempDir();

		$fileReplaceList = array();

		foreach($template->getFileList() as $key => $value){
			$fileNode = $doc->createElement("file");
			$id = $value["id"];

			try{
				$file = CMSFileManager::get($siteRoot, $value["path"]);
			}catch(Exception $e){
				//todo エラーリストに追加
				continue;
			}

			$newName = str_replace("/","_",str_replace($siteRoot,"",$file->getPath()));

			if(defined("SOYCMS_ASP_MODE")){
				$filePath = str_replace($siteUrl,"",$file->getUrl());
			}else{
				$oldPath = str_replace("\\","/",$file->getPath());
				$filePath = str_replace($siteRoot,"",$oldPath);
				if($filePath[0] != "/")$filePath = "/" . $filePath;
			}

			$fileReplaceList[$file->getUrl()] = $filePath;

			//ファイルのコピー
			copy($file->getPath(),$tmpDir . "/" . $newName);

			$files->appendChild($fileNode);

			//name
			$name = $doc->createElement("name");
			$name->appendChild($doc->createTextNode($newName));
			$fileNode->appendChild($name);

			//path
			$path = $doc->createElement("path");
			$path->appendChild($doc->createTextNode($filePath));
			$fileNode->appendChild($path);

			//description
			$description = $doc->createElement("description");
			$description->appendChild($doc->createCDATASection(@$value["description"]));
			$fileNode->appendChild($description);

		}

		//templates
		$templates = $doc->createElement("templates");
		$root->appendChild($templates);
		$templateFileList = array();

		foreach($template->getTemplate() as $key => $array){

			$templateNode = $doc->createElement("template");
			$templates->appendChild($templateNode);

			//id
			$id = $doc->createElement("id");
			$id->appendChild($doc->createTextNode($key));
			$templateNode->appendChild($id);

			//name
			$name = $doc->createElement("name");
			$name->appendChild($doc->createTextNode($array["name"]));
			$templateNode->appendChild($name);

			//description
			$description = $doc->createElement("description");
			$description->appendChild($doc->createCDATASection(@$array["description"]));
			$templateNode->appendChild($description);

			$templateFileList[] = $this->getTempDir() . "/" . $key;
		}

		file_put_contents($this->getTempDir() . "/manifest.xml",$doc->saveXml());

		//zipファイルの作成
		$logic = SOY2Logic::createInstance("logic.site.Template.TemplateLogic");

		//テンプレート内部の相対パスを変更する
		$logic->replaceURL($siteUrl,$templateFileList,$fileReplaceList);

		try{
			$zipFilePath = $logic->createTemplatePack($template->getId(),$tmpDir);
		}catch(Exception $e){
			$this->addMessage("TEMPLATE_CREATE_ERROR");
			return false;
		}

		$result = $logic->uploadTemplate(null,$zipFilePath);

		@unlink($zipFilePath);

		$this->addMessage("TEMPLATE_CREATE_SUCCESS");
		return true;
	}

	//前へが押された際の動作
	public function checkBack(){
		return true;
	}

	public function getBackObject(){
		return "FileSettingStage";
	}

	public function getNextObject(){
		return "EndStage";
	}

	public function getNextString(){
		return CMSMessageManager::get("SOYCMS_CREATE");
	}

	public function getBackString(){
		return CMSMessageManager::get("SOYCMS_BACK");
	}
}

class TemplateList extends HTMLList{

	protected function populateItem($entity){
		$this->createAdd("template_list_name","HTMLLabel",array(
			"text" => $entity["name"]
		));

		$this->createAdd("template_list_description","HTMLLabel",array(
			"text" => @$entity["description"]
		));
	}

}

class FileList extends HTMLList{

	protected function populateItem($entity){
		$this->createAdd("add_file_url","HTMLLink",array(
				"link" => $entity["url"],
				"text" => $entity["path"],
				"target" => "_blank"
		));
		$this->createAdd("add_file_description","HTMLLabel",array(
				"text" => $entity["description"],
		));
	}

}
