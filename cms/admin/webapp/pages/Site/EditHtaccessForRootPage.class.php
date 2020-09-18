<?php

class EditHtaccessForRootPage extends CMSUpdatePageBase {

	private $logic;

	function doPost(){

		if(soy2_check_token() && soy2_check_referer()){
			if($this->saveFile($_POST["contents"])){
				$this->addMessage("UPDATE_SUCCESS");
			}

			$this->reload();
			exit;
		}
	}

	function __construct($args) {
		if(!UserInfoUtil::isDefaultUser()){
			$this->jump("Site");
		}

		$this->logic = SOY2Logic::createInstance("logic.admin.Site.DomainRootSiteLogic");

		parent::__construct();

		$filepath = $this->logic->getPathOfHtaccess();
		if(!is_writable($filepath)){
			$this->addErrorMessage("SOYCMS_NOT_WRITABLE");
		}

		$this->buildSubMenu();
		$this->buildForm($filepath);
		$this->showDefault();
		$this->showMessage();
		$this->showBackupFiles($filepath);
	}

	private function buildSubMenu(){
		$this->addLink("edit_indexphp", array(
				"link"    => SOY2PageController::createLink("Site.EditControllerForRoot"),
		));
		$this->addModel("can_edit_indexphp", array(
				"visible" => UserInfoUtil::isDefaultUser() && file_exists($this->logic->getPathOfController()),
		));
	}

	private function buildForm($filepath){
		$this->addForm("update_form", array(
			"disabled" => !is_writable($filepath)
		));

		$content= is_readable($filepath) ? file_get_contents($filepath) : "";
		$this->addTextArea("contents", array(
			"name"  => "contents",
			"value" => $content,
			"rows" => count(explode("\n", $content)),
			"disabled" => !is_writable($filepath),
		));

		$this->addInput("button", array(
			"value"	 => CMSMessageManager::get("SOYCMS_SAVE"),
			"disabled" => !is_writable($filepath)
		));

	}

	private function showDefault(){
		$default = $this->logic->getHtaccess();
		$this->addTextArea("default_contents", array(
				"name"  => "default_contents",
				"value" => $default,
				"rows" => count(explode("\n", $default)),
				"readonly" => true,
		));
	}
	private function showMessage(){
		$messages = CMSMessageManager::getMessages();
		$errors= CMSMessageManager::getErrorMessages();
		$this->addLabel("message", array(
			"text" => implode($messages),
			"visible" => count($messages),
		));
		$this->addLabel("error", array(
			"text" => implode($errors),
			"visible" => count($errors),
		));
		$this->addModel("has_message_or_error",array(
				"visible" => count($messages) || count($errors),
		));
	}

	private function showBackupFiles($filepath){
		$list = CMSUtil::getBackupList($filepath);
		$this->addModel("has_backup",array(
				"visible" => count($list),
		));
		$this->createAdd("backup_file_list","BackupFileList",array(
				"list" => $list,
		));
	}

	private function saveFile($contents){
		return file_put_contents($this->logic->getPathOfHtaccess(), $contents);
	}

}

class BackupFileList extends HTMLList{
	protected function populateItem($filepath, $key, $counter){
		$this->addModel("heading",array(
				"id" => "heading_".$counter,
		));

		$this->addLink("collapse-link",array(
				"link" => "#collapse_".$counter,
		));
		$this->addLabel("filename",array(
				"text" => basename($filepath),
		));

		$this->addLabel("filemtime",array(
				"text" => date("Y-m-d H:i:s", filemtime($filepath)),
		));

		$this->addModel("collapse-body",array(
				"aria-labelledby" => "heading_".$counter,
				"class" => "panel-collapse collapse" . ( $counter ==1 ? " in" : "" ),
				"id" => "collapse_".$counter,
		));

		$content= is_readable($filepath) ? file_get_contents($filepath) : "";
		$this->addTextArea("contents", array(
				"name"  => "contents",
				"value" => $content,
				"rows" => count(explode("\n",$content)),
				"readonly" => true,
				"style" => "background: white;"
		));
	}
}
