<?php

class FileSettingStage extends StageBase{

	public function getStageTitle(){
		return "ページ雛形の新規作成 (3/5) - ファイルの追加";
	}

	public function execute(){
		$list = $this->wizardObj->template->getFileList();

		$this->createAdd("add_file_table","HTMLModel",array(
			"style" => (count($list)) ? "" : "display:none;"
		));

		$this->createAdd("add_file_list","AddFileList",array(
			"list" => $list
		));
	}

	//次へが押された際の動作
	public function checkNext(){
		$fileList = @$_POST["add_file_list"];
		$url = @$_POST["add_file_list_url"];
		$descriptions = @$_POST["descriptions"];

		$list = array();
		if(is_null($fileList)){
			$fileList = array();
		}
		foreach($fileList as $id => $path){
			$list[$id] = array(
				"id" => $id,
				"path" => $path,
				"url" => @$url[$id],
				"description" => @$descriptions[$id]
			);
		}

		$this->wizardObj->template->setFileList($list);

		return true;
	}

	public function getNextObject(){
		return "ConfirmStage";
	}

	public function getBackObject(){
		return "TemplateSettingStage";
	}
}

class AddFileList extends HTMLList{

	protected function populateItem($entity){
		$this->createAdd("add_file_li","HTMLModel",array(
			"id" => "add_file_list_" . $entity["id"],
		));

		$this->createAdd("add_file_link","HTMLLink",array(
			"link" => $entity["url"],
			"target"=>"_blank",
			"text" => $entity["path"]
		));

		$this->createAdd("add_file_hidden_input","HTMLInput",array(
			"value" => $entity["path"],
			"name" => "add_file_list[".$entity["id"]."]",

		));

		$this->createAdd("add_file_url_input","HTMLInput",array(
			"value" => $entity["url"],
			"name" => "add_file_list_url[".$entity["id"]."]",

		));

		$this->createAdd("add_file_description_input","HTMLInput",array(
			"value" => $entity["description"],
			"name" => "descriptions[".$entity["id"]."]",

		));
	}
}
